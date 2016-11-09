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

class ShoppingService extends \Eccube\Service\ShoppingService
{
    public function customOrder(\Eccube\Entity\Order $Order){

        $cartItemOptions = $this->app['eccube.productoption.service.cart']->getCartOption()->getCartOptions();
        $OrderDetails = $Order->getOrderDetails();
        $arrCheck = array();
        $deliv_free_flg = false;
        foreach($OrderDetails as $orderDetail){
            $ProductClass = $orderDetail->getProductClass();
            foreach($cartItemOptions as $cartNo => $cartItemOption){
                if($ProductClass->getId() == $cartItemOption->getClassId() && !in_array($cartNo,$arrCheck)){
                    $arrCheck[] = $cartNo;
                    $price = $ProductClass->getPrice02();
                    $price += $cartItemOption->getOptionPrice();
                    if(!$deliv_free_flg){
                        if($cartItemOption->getDeliveryFreeFlg())$deliv_free_flg = true;
                    }
                    
                    $orderDetail->setPrice($price);
                    $tax = $this->app['eccube.service.tax_rule']
                        ->calcTax($orderDetail->getPrice(), $orderDetail->getTaxRate(), $orderDetail->getTaxRule());
                    $orderDetail->setPriceIncTax($orderDetail->getPrice() + $tax);
                    $plgOrderDetail = $this->app['eccube.productoption.repository.order_detail']->findOneBy(array('order_detail_id' => $orderDetail->getId()));
                    if(!$plgOrderDetail){
                        $plgOrderDetail = new \Plugin\ProductOption\Entity\OrderDetail();
                        $plgOrderDetail->setOrderDetailId($orderDetail->getId())
                                       ->setOrderDetail($orderDetail);
                        
                        $plgOrderOption = new \Plugin\ProductOption\Entity\OrderOption();
                        $plgOrderOption->setOrder($Order)
                                       ->setOrderId($Order->getId());
                        $plgOrderOption->setSerial($cartItemOption->getOption());
                        $plgOrderOption = $this->app['eccube.productoption.repository.order_option']->save($plgOrderOption);
                        
                        $plgOrderDetail->setOrderOptionId($plgOrderOption->getId())
                                       ->setOrderOption($plgOrderOption);

                        $this->app['orm.em']->persist($plgOrderDetail);
                    }else{
                        // オプション情報を更新
                        $plgOrderOption = $plgOrderDetail->getOrderOption();
                        $plgOrderOption->setSerial($cartItemOption->getOption());
                        $this->app['eccube.productoption.repository.order_option']->save($plgOrderOption);
                    }
    
                    break;
                }
            }
        }

        $Shippings = $Order->getShippings();
        $arrCheck = array();
        foreach($Shippings as $shipping){
            $ShipmentItems = $shipping->getShipmentItems();
            foreach($ShipmentItems as $shipmentItem){
                $plgShipmentItem = $this->app['eccube.productoption.repository.shipment_item']->findOneBy(array('item_id' => $shipmentItem->getId()));
                if(!$plgShipmentItem){
                    $ProductClass = $shipmentItem->getProductClass();
                    foreach($cartItemOptions as $cartNo => $cartItemOption){
                        if($ProductClass->getId() == $cartItemOption->getClassId() && !in_array($cartNo,$arrCheck)){
                            $arrCheck[] = $cartNo;
                            $price = $ProductClass->getPrice02();
                            $price += $cartItemOption->getOptionPrice();

                            $shipmentItem->setPrice($price);
                            $tax = $this->app['eccube.service.tax_rule']
                                ->calcTax($shipmentItem->getPrice(), $ProductClass->getTaxRate(), $ProductClass->getTaxRule());
                            $shipmentItem->setPriceIncTax($shipmentItem->getPrice() + $tax);

                            $plgShipmentItem = $this->app['eccube.productoption.repository.shipment_item']->findOneBy(array('item_id' => $shipmentItem->getId()));
                            if(!$plgShipmentItem){
                                $plgShipmentItem = new \Plugin\ProductOption\Entity\ShipmentItem();
                                $plgShipmentItem->setItemId($shipmentItem->getId())
                                                ->setShipmentItem($shipmentItem);

                                $plgOrderOption = new \Plugin\ProductOption\Entity\OrderOption();
                                $plgOrderOption->setOrder($Order)
                                               ->setOrderId($Order->getId());
                                $plgOrderOption->setSerial($cartItemOption->getOption());
                                $plgOrderOption = $this->app['eccube.productoption.repository.order_option']->save($plgOrderOption);

                                $plgShipmentItem->setOrderOptionId($plgOrderOption->getId())
                                                ->setOrderOption($plgOrderOption);

                                $this->app['orm.em']->persist($plgShipmentItem);
                            }else{
                                // オプション情報を更新
                                $plgOrderOption = $plgShipmentItem->getOrderOption();
                                $plgOrderOption->setSerial($cartItemOption->getOption());
                                $this->app['eccube.productoption.repository.order_option']->save($plgOrderOption);
                            }
                            break;
                        }
                    }
                }
            }
        }
        
        $this->app['orm.em']->flush();
        
        // 小計
        $subTotal = $this->orderService->getSubTotal($Order);
        $Order->setSubTotal($subTotal);
        
        // 消費税のみの小計
        $tax = $this->orderService->getTotalTax($Order);
        $Order->setTax($tax);

        // 配送料合計金額
        $Order->setDeliveryFeeTotal($this->getShippingDeliveryFeeTotal($Order->getShippings()));

        // 配送料無料条件(合計金額)
        $this->setDeliveryFreeAmount($Order);

        // 配送料無料条件(合計数量)
        $this->setDeliveryFreeQuantity($Order);
        
        // オプションによる送料無料
        if($deliv_free_flg){
            $Order->setDeliveryFeeTotal(0);
            // お届け先情報の配送料も0にセット
            $shippings = $Order->getShippings();
            foreach ($shippings as $Shipping) {
                $Shipping->setShippingDeliveryFee(0);
            }
        }

        $total = $subTotal + $Order->getCharge() + $Order->getDeliveryFeeTotal();
        
        if($this->app['eccube.productoption.service.util']->checkInstallPlugin('Point')){
            if($this->app->isGranted('ROLE_USER')){
                $usePoint = $this->app['eccube.plugin.point.repository.point']->getLatestPreUsePoint($Order);
                $usePoint = abs($usePoint);
                $calculator = $this->app['eccube.plugin.point.calculate.helper.factory'];
                $calculator->setUsePoint($usePoint); // calculatorに渡す際は絶対値
                $calculator->addEntity('Order', $Order);
                $calculator->addEntity('Customer', $Order->getCustomer());
                $discount = $calculator->getConversionPoint();
                $Order->setDiscount($discount);
                $total -= $discount;
            }
        }

        $Order->setTotal($total);
        $Order->setPaymentTotal($total);   
        
        return $Order;
    }
    
    public function findShippingsProduct($ShipmentItem)
    {
        $app = $this->app;
        
        $plgShipmentItem = $app['eccube.productoption.repository.shipment_item']->findOneBy(array('item_id' => $ShipmentItem->getId()));
        $qb = $app['orm.em']->createQueryBuilder();
        $qb
            ->select('s')
            ->from('Eccube\Entity\Shipping', 's')
            ->innerJoin('Eccube\Entity\Order', 'o', 'WITH', 'o.id = s.Order')
            ->innerJoin('Eccube\Entity\ShipmentItem', 'si', 'WITH', 'si.Shipping = s.id')
            ->where('o.id = (:order)')
            ->andWhere('si.ProductClass = (:productClass)')
            ->setParameter('order', $ShipmentItem->getOrder())
            ->setParameter('productClass', $ShipmentItem->getProductClass());

        if($plgShipmentItem){
            $qb
            ->innerJoin('Plugin\ProductOption\Entity\ShipmentItem', 'plgsi', 'WITH', 'plgsi.ShipmentItem = si.id')
            ->andWhere('plgsi.OrderOption = (:orderOption)')
            ->setParameter('orderOption', $plgShipmentItem->getOrderOption());
        }
        $shippings = $qb
            ->getQuery()
            ->getResult();

        return $shippings;

    }
}
