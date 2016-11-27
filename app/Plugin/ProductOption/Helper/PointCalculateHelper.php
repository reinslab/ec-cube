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

namespace Plugin\ProductOption\Helper;

use Eccube\Entity\Product;
use Plugin\Point\Entity\PointInfo;

class PointCalculateHelper extends \Plugin\Point\Helper\PointCalculateHelper\PointCalculateHelper
{
    public function getAddPointByCart()
    {
        $app = $this->app;

        $this->addPoint = 0;
        $basicRate = $this->pointInfo->getPlgBasicPointRate();
        if ($app['session']->has('cart_option')) {
            $cartOption = $app['session']->get('cart_option');
            $cartItemOptions = $cartOption->getCartOptions();
        }
        foreach ($this->entities['Cart']->getCartItems() as $idx => $cartItem) {
            $rate = $basicRate;
            $ProductClass = $cartItem->getObject();
            $Product = $ProductClass->getProduct();
            // 商品ごとの付与率を取得
            $productRates = $this->app['eccube.plugin.point.repository.pointproductrate']
                ->getPointProductRateByEntity(array($ProductClass));

            if ($productRates) {
                // 商品ごとの付与率が設定されている場合は、基本付与率ではなく、商品ごとの付与率を利用する
                $productId = $Product->getId();
                $rate = $productRates[$productId];
            }

            $price = $ProductClass->getPrice02();
            if(isset($cartItemOptions[$idx])){
                $price += $cartItemOptions[$idx]->getOptionPrice();
            }
            $rate /= 100;
            $addPoint = ($price * $rate) * $cartItem->getQuantity();
            $this->addPoint += $addPoint;
        }

        $this->addPoint = $this->getRoundValue($this->addPoint);
        return $this->addPoint;
    }
    
    public function getAddPointByOrder()
    {
        // 必要エンティティを判定
        $this->addPoint = 0;
        if (!$this->hasEntities('Order')) {
            return false;
        }

        // 商品詳細情報ををオーダーから取得
        $this->products = $this->getOrderDetail();

        if (!$this->products) {
            // 商品詳細がなければ処理終了
            return;
        }

        // 商品ごとのポイント付与率を取得
        $productRates = $this->app['eccube.plugin.point.repository.pointproductrate']->getPointProductRateByEntity(
            $this->products
        );

        // 付与率の設定がされていない場合
        if (count($productRates) < 1) {
            $productRates = false;
        }

        // 商品ごとのポイント付与率セット
        $this->productRates = $productRates;

        // 取得ポイント付与率商品ID配列を取得
        if ($this->productRates) {
            $productKeys = array_keys($this->productRates);
        }

        $basicRate = $this->pointInfo->getPlgBasicPointRate();

        // 商品詳細ごとの購入金額にレートをかける
        // レート計算後個数をかける
        foreach ($this->products as $node) {
            // 商品毎ポイント付与率が設定されていない場合
            $rate = $basicRate;
            if ($this->productRates) {
                if (in_array($node->getProduct()->getId(), $productKeys)) {
                    // 商品ごとポイント付与率が設定されている場合
                    $rate = $this->productRates[$node->getProduct()->getId()];
                }
            }

            $price = $node->getProductClass()->getPrice02();
            $plgOrderDetail = $this->app['eccube.productoption.repository.order_detail']->findOneBy(array('order_detail_id' => $node->getId()));
            if($plgOrderDetail){
                $price += $plgOrderDetail->getOrderOption()->getPrice();
            }
            $rate /= 100;
            $this->addPoint += ($price * $rate) * $node->getQuantity();
        }

        // 減算処理の場合減算値を返却
        if ($this->isSubtraction() && !empty($this->usePoint)) {
            return $this->getSubtractionCalculate();
        }

        return $this->getRoundValue($this->addPoint);
    }
    
    public function getPointByOption(Product $Product, $price)
    {
        // 商品毎の付与率を取得.
        $productRate = $this->app['eccube.plugin.point.repository.pointproductrate']->getLastPointProductRateById(
            $Product->getId()
        );
        // 基本ポイント付与率を取得
        $basicRate = $this->pointInfo->getPlgBasicPointRate();

        // 商品毎の付与率あればそちらを優先
        // なければ基本ポイント付与率を利用
        $calculateRate = $basicRate;
        if (!is_null($productRate)) {
            $calculateRate = $productRate;
        }

        return (integer)$this->getRoundValue($price * ($calculateRate / 100));
    }
}
