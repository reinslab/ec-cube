<?php
/*
 * Plugin Name : ProductOption
 *
 * Copyright (C) 2015 BraTech Co., Ltd. All Rights Reserved.
 * http://www.bratech.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\ProductOption\Service;

use Eccube\Application;
use Doctrine\ORM\EntityManager;
use Eccube\Common\Constant;
use Eccube\Entity\CartItem;
use Symfony\Component\HttpFoundation\Session\Session;
use Plugin\ProductOption\Entity\CartOption;
use Plugin\ProductOption\Entity\CartItemOption;

class ProductOptionCartService
{
    private $cartOption;
    private $session;
    private $entityManager;
    private $cart;
    private $BaseInfo;
    
    public function __construct(\Eccube\Application $app)
    {
        $this->app = $app;
        $this->session = $app['session'];
        $this->entityManager = $app['orm.em'];

        if ($this->session->has('cart')) {
            $this->cart = $this->session->get('cart');
        }
        
        if ($this->session->has('cart_option')) {
            $this->cartOption = $this->session->get('cart_option');
        } else {
            $this->cartOption = new \Plugin\ProductOption\Entity\CartOption();
        }
        
        $this->BaseInfo = $app['eccube.repository.base_info']->get();
    }
    
    public function addProductOption($productClassId, $Options, $quantity = 1)
    {

        if($this->cart == null)return;
        $cartItems = $this->cart->getCartItems();
        $cartItemOptions = $this->cartOption->getCartOptions();
        $quantity_index = array();
        $add_option = true;
        $cartNo = null;
        foreach($cartItems as $index => $cartitem){
            if($cartitem->getClassId() == $productClassId){
                //すでに追加されているオプションかどうかの判定
                //追加されている場合、数量を変更
                if(isset($cartItemOptions[$index])){
                    if($this->app['eccube.productoption.service.util']->compareOptions($cartItemOptions[$index]->getOption(),$Options)){
                        $add_option = false;
                        $cartNo = $index;
                        $quantity += $cartItemOptions[$index]->getQuantity();
                        break;
                    }elseif(!isset($copy_index)){
                        $copy_index = $index;
                    }
                }
            }
        }

        $quantity = $this->setProductQuantity($productClassId, $quantity, $cartNo);

        if($quantity <= 0)$add_option = false;


        //オプション情報を追加
        if($add_option){
            $ProductClass = $this->app['eccube.repository.product_class']->find($productClassId);
            if(isset($copy_index)){
                $CartItem = clone $cartItems[$copy_index];
                $ProductClassId = $cartItems[$copy_index]->getClassId();
                $ProductClass = $this->app['eccube.repository.product_class']->find($productClassId);
                $CartItem->setPrice($ProductClass->getPrice02IncTax());
                $CartItem->setQuantity($quantity);
                $this->cart->addCartItem($CartItem);
            }            
            $arrLabel = $this->app['eccube.productoption.service.util']->getLabelFromOptions($Options);
            $option_price = $this->app['eccube.productoption.service.util']->getPriceFromOptions($Options);
            $delivery_free_flg = $this->app['eccube.productoption.service.util']->getDeliveryFreeFlgFromOptions($Options);

            $option_price_inctax = $option_price + $this->app['eccube.service.tax_rule']->getTax($option_price, $ProductClass->getProduct(), $ProductClass);
            $CartItemOption = new CartItemOption();
            $CartItemOption
                    ->setClassId((string) $productClassId)
                    ->setQuantity($quantity)
                    ->setOption($Options)
                    ->setLabel($arrLabel)
                    ->setPrice($ProductClass->getPrice02IncTax() + $option_price_inctax)
                    ->setOptionPrice($option_price)
                    ->setOptionPriceIncTax($option_price_inctax)
                    ->setDeliveryFreeFlg($delivery_free_flg);
            $this->cartOption->addCartOption($CartItemOption);
        }

        foreach($cartItemOptions as $cartNo => $cartItemOption){
            if($cartItems[$cartNo]){
                $cartItems[$cartNo]->setPrice($cartItemOption->getPrice());
            }
        }
        $this->save();

    }
    
    public function clear()
    {
        $this->cartOption
            ->clearCartOptions();

        return $this;
    }    
    
    public function save()
    {
        $this->session->set('cart', $this->cart);
        return $this->session->set('cart_option', $this->cartOption);
    }
    
    public function getCartOption()
    {
        return $this->cartOption;
    }
    
    public function getProductQuantityByCartNo($productClassId, $cartNo = null)
    {
        if($cartNo !== null)$cartNo = intval($cartNo);
        foreach ($this->cart->getCartItems() as $index => $CartItem) {
            if ($CartItem->getClassId() === $productClassId && $index === $cartNo) {
                return $CartItem->getQuantity();
            }
        }
        return 0;
    }
    
    public function removeProduct($productClassId, $cartNo)
    {
        if($cartNo !== null)$cartNo = intval($cartNo);
        $cartItems = $this->cart->getCartItems();
        $cartOptions = $this->cartOption->getCartOptions();
        foreach ($cartItems as $index => $CartItem) {
            if ($CartItem->getClassId() === $productClassId && $index === $cartNo) {
                $cartItems->removeElement($CartItem);
                $cartOptions->removeElement($cartOptions[$index]);
            }
        }

        // 支払方法の再設定
        if ($this->BaseInfo->getOptionMultipleShipping() == Constant::ENABLED) {

            // 複数配送対応
            $productTypes = array();
            foreach ($cartItems as $item) {
                /* @var $ProductClass \Eccube\Entity\ProductClass */
                $ProductClass = $item->getObject();
                if(!$ProductClass){
                    $ProductClass = $this->app['eccube.repository.product_class']->find($item->getClassId());
                }
                $productTypes[] = $ProductClass->getProductType();
            }

            // 配送業者を取得
            $deliveries = $this->entityManager->getRepository('Eccube\Entity\Delivery')->getDeliveries($productTypes);

            // 支払方法を取得
            $payments = $this->entityManager->getRepository('Eccube\Entity\Payment')->findAllowedPayments($deliveries);

            $this->cart->setPayments($payments);
        }

        return $this;
    }
    
    public function changeProductQuantity($productClassId, $cartNo, $quantity)
    {
        if($cartNo !== null)$cartNo = intval($cartNo);
        foreach ($this->cart->getCartItems() as $index => $CartItem) {
            if ($CartItem->getClassId() === $productClassId && $index === $cartNo) {
                $this->setProductQuantity($productClassId, $quantity, $cartNo);
            }
        }
    }
    
    public function upProductQuantity($productClassId, $cartNo)
    {
        $quantity = $this->getProductQuantityByCartNo($productClassId, $cartNo) + 1;
        $this->changeProductQuantity($productClassId, $cartNo, $quantity);

        return $this;
    }
    
    public function downProductQuantity($productClassId, $cartNo)
    {
        $quantity = $this->getProductQuantityByCartNo($productClassId, $cartNo) - 1;
        
        if ($quantity > 0) {
            $this->changeProductQuantity($productClassId, $cartNo, $quantity);
        } else {
            $this->removeProduct($productClassId, $cartNo);
        }

        return $this;
    }
    
    public function getProductQuantity($productClassId, $cartNo = null){
        if($cartNo !== null)$cartNo = intval($cartNo);
        $set_quantity = 0;
        foreach($this->cartOption->getCartOptions() as $index => $cartItemOption){
            if($cartItemOption->getClassId() == $productClassId){
                if($cartNo !== $index){
                    $set_quantity += $cartItemOption->getQuantity();
                }
            }
        }
        return $set_quantity;
    }
    
    public function setProductQuantity($productClassId, $quantity, $cartNo = null){
        $cartItems = $this->cart->getCartItems();
        $cartItemOptions = $this->cartOption->getCartOptions();
        $tmp_quantity = 0;

        $set_quantity = $this->getProductQuantity($productClassId, $cartNo);
        $ProductClass = $this->app['eccube.repository.product_class']->find($productClassId);

        $productName = $ProductClass->getProduct()->getName();
        if ($ProductClass->hasClassCategory1()) {
            $productName .= " - ".$ProductClass->getClassCategory1()->getName();
        }
        if ($ProductClass->hasClassCategory2()) {
            $productName .= " - ".$ProductClass->getClassCategory2()->getName();
        }

        if (!$ProductClass->getStockUnlimited() && $set_quantity + $quantity > $ProductClass->getStock()) {
            if ($ProductClass->getSaleLimit() && $ProductClass->getStock() > $ProductClass->getSaleLimit()) {
                $tmp_quantity = $ProductClass->getSaleLimit();
                $this->addError('cart.over.sale_limit', $productName);
            } else {
                $tmp_quantity = $ProductClass->getStock();
                $this->addError('cart.over.stock', $productName);
            }
        }
        if ($ProductClass->getSaleLimit() && $set_quantity + $quantity > $ProductClass->getSaleLimit()) {
            $tmp_quantity = $ProductClass->getSaleLimit();
            $this->addError('cart.over.sale_limit', $productName);
        }
        if($tmp_quantity){
            $quantity = $tmp_quantity - $set_quantity;
        }

        if($quantity > 0 && $cartNo !== null){
            if($cartItemOptions[$cartNo] instanceof \Plugin\ProductOption\Entity\CartItemOption){
                $cartItemOptions[$cartNo]->setQuantity($quantity);
            }
        }
        
        foreach($cartItemOptions as $idx => $cartItemOption){
            if($cartItems[$idx]){
                $cartItems[$idx]->setQuantity($cartItemOption->getQuantity());
                $cart_quantity = $cartItems[$idx]->getQuantity();
                if($cart_quantity <= 0){
                    $cartItems->removeElement($cartItems[$idx]);
                    $cartItemOptions->removeElement($cartItemOptions[$idx]);
                }
            }else{
                $cartItemOptions->removeElement($cartItemOptions[$idx]);
            }
        }
        
        return $quantity;
        
    }
    
    public function addError($error = null, $productName = null)
    {
        $errors = $this->session->getFlashBag()->get('eccube.front.request.error');
        if(!in_array($error,$errors)){
            $errors[] = $error;
            if (!is_null($productName)) {
                $this->session->getFlashBag()->add('eccube.front.request.product', $productName);
            }
        }
        $this->session->getFlashBag()->add('eccube.front.request.error', $error);

        return $this;
    }    
}
