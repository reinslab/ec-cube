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

namespace Plugin\ProductOption\Event\WorkPlace;

use Eccube\Common\Constant;
use Eccube\Event\EventArgs;
use Eccube\Event\TemplateEvent;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;


class FrontShoppingMultiple extends AbstractWorkPlace
{
    public function createTwig(TemplateEvent $event)
    {
        $app = $this->app;
        
        $parameters = $event->getParameters();

        $plgShipmentItems = array();
        $ShipmentItems = array();
        $compItemQuantities = array();
        $productClassIds = array();
        $checkOptions = array();
        $Order = $app['eccube.service.shopping']->getOrder($app['config']['order_processing']);
        $no=0;
        foreach ($Order->getShippings() as $Shipping) {
            foreach ($Shipping->getShipmentItems() as $ShipmentItem) {
                $exits_flg = false;
                $plgShipmentItem = $app['eccube.productoption.repository.shipment_item']->findOneBy(array('item_id' => $ShipmentItem->getId()));
                if (in_array($ShipmentItem->getProductClass()->getId(), $productClassIds)) {
                    if($plgShipmentItem){
                        foreach($checkOptions as $options){
                            if($app['eccube.productoption.service.util']->compareOptions($plgShipmentItem->getOrderOption()->getSerial(),$options)){
                                $exits_flg = true;
                            }
                        }
                    }
                }
                $quantity = $ShipmentItem->getQuantity();
                if(!$exits_flg){
                    $ShipmentItems[$no] = $ShipmentItem;
                    $compItemQuantities[$no] = $quantity;
                    $plgShipmentItems[$no] = $plgShipmentItem->getOrderOption()->getLabel();
                    $checkOptions[$no] = $plgShipmentItem->getOrderOption()->getSerial();
                    $no++;                 
                }else{
                    foreach($ShipmentItems as $key => $tmpShipmentItem){
                        if($tmpShipmentItem->getProductClass()->getId() == $ShipmentItem->getProductClass()->getId() && $app['eccube.productoption.service.util']->compareOptions($checkOptions[$key],$plgShipmentItem->getOrderOption()->getSerial())){
                            $compItemQuantities[$key] += $quantity;
                        }
                    }
                }
                $productClassIds[] = $ShipmentItem->getProductClass()->getId();
            }
        }

        $builder = $app->form();
        $builder
            ->add('shipping_multiple', 'collection', array(
                'type' => 'plg_shipping_multiple',
                'data' => $ShipmentItems,
                'allow_add' => true,
                'allow_delete' => true,
            ));
        $form = $builder->getForm();

        $form->handleRequest($app['request']);

        $source = $event->getSource();
        if(preg_match('/\{%\s*for\s*shipmentItem\s*in\s*shipmentItems\s*%\}/',$source, $result)){
            $search = $result[0];
            $replace = '{% for no, shipmentItem in shipmentItems %}';
            $source = str_replace($search, $replace, $source);
        }
        
        if(preg_match('/\{%\s*if\s*shipmentItem\.productClass\.id\s*\=\=\s*key\s*%\}/',$source, $result)){
            $search = $result[0];
            $replace = '{% if idx == key %}';
            $source = str_replace($search, $replace, $source);
        }
        
        if(preg_match('/<.*id="multiple_list__total_price/',$source, $result)){
            $search = $result[0];
            $snipet = file_get_contents($app['config']['plugin_realdir']. '/ProductOption/Resource/template/default/Shopping/shipping_multiple_option.twig');
            $replace = $snipet.$search;
            $source = str_replace($search, $replace, $source);
        }

        $event->setSource($source);
        $parameters['form'] = $form->createView();
        $parameters['shipmentItems'] = $ShipmentItems;
        $parameters['compItemQuantities'] = $compItemQuantities;
        $parameters['plgShipmentItems'] = $plgShipmentItems;
        $event->setParameters($parameters);
    }
    
    public function execute(EventArgs $event)
    {
        $app = $this->app;
        $request = $app['request'];
        
        $plgShipmentItems = array();
        $ShipmentItems = array();
        $compItemQuantities = array();
        $productClassIds = array();
        $checkOptions = array();
        $Order = $app['eccube.service.shopping']->getOrder($app['config']['order_processing']);
        $no=0;
        foreach ($Order->getShippings() as $Shipping) {
            foreach ($Shipping->getShipmentItems() as $ShipmentItem) {
                $exits_flg = false;
                $plgShipmentItem = $app['eccube.productoption.repository.shipment_item']->findOneBy(array('item_id' => $ShipmentItem->getId()));
                if (in_array($ShipmentItem->getProductClass()->getId(), $productClassIds)) {
                    if($plgShipmentItem){
                        foreach($checkOptions as $option){
                            if($app['eccube.productoption.service.util']->compareOptions($plgShipmentItem->getOrderOption()->getSerial(),$option->getSerial())){
                                $exits_flg = true;
                            }
                        }
                    }
                }
                $quantity = $ShipmentItem->getQuantity();
                if(!$exits_flg){
                    $ShipmentItems[$no] = $ShipmentItem;
                    $compItemQuantities[$no] = $quantity;
                    $plgShipmentItems[$no] = $plgShipmentItem->getOrderOption()->getLabel();
                    $checkOptions[$no] = $plgShipmentItem->getOrderOption();
                    $no++;
                }else{
                    foreach($ShipmentItems as $key => $tmpShipmentItem){
                        if($tmpShipmentItem->getProductClass()->getId() == $ShipmentItem->getProductClass()->getId() && $app['eccube.productoption.service.util']->compareOptions($checkOptions[$key]->getSerial(),$plgShipmentItem->getOrderOption()->getSerial())){
                            $compItemQuantities[$key] += $quantity;
                        }
                    }
                }
                $productClassIds[] = $ShipmentItem->getProductClass()->getId();
            }
        }

        
        $builder = $app->form();
        $builder
            ->add('shipping_multiple', 'collection', array(
                'type' => 'plg_shipping_multiple',
                'data' => $ShipmentItems,
                'allow_add' => true,
                'allow_delete' => true,
            ));

        $form = $builder->getForm();

        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form['shipping_multiple'];

            // 数量が超えていないか、同一でないとエラー
            $itemQuantities = array();
            foreach ($data as $no => $mulitples) {
                /** @var \Eccube\Entity\ShipmentItem $multipleItem */
                $multipleItem = $mulitples->getData();
                foreach ($mulitples as $items) {
                    foreach ($items as $item) {
                        $quantity = $item['quantity']->getData();
                        if (isset($itemQuantities[$no])) {
                            $itemQuantities[$no] += $quantity;
                        } else {
                            $itemQuantities[$no] = $quantity;
                        }
                    }
                }
            }

            foreach ($compItemQuantities as $key => $value) {
                if (array_key_exists($key, $itemQuantities)) {
                    if ($itemQuantities[$key] != $value) {
                        $errors[] = array('message' => '数量の数が異なっています。');

                        // 対象がなければエラー
                        $app->render('Shopping/shipping_multiple.twig', array(
                            'form' => $form->createView(),
                            'shipmentItems' => $ShipmentItems,
                            'compItemQuantities' => $compItemQuantities,
                            'errors' => $errors,
                        ))->send();
                        exit;
                    }
                }
            }

            // お届け先情報をdelete/insert
            $shippings = $Order->getShippings();
            foreach ($shippings as $shipping) {
                $Order->removeShipping($shipping);
                $app['orm.em']->remove($shipping);
            }

            $sessionCustomerAddressKey = 'eccube.front.shopping.nonmember.customeraddress';
            $checkAddresses = array();
            $memShippings = array();
            $arrShipmentItem = array();
            $arrNo = array();
            foreach ($data as $no => $mulitples) {
                /** @var \Eccube\Entity\ShipmentItem $multipleItem */
                $multipleItem = $mulitples->getData();

                foreach ($mulitples as $items) {
                    foreach ($items as $item) {
                        // 追加された配送先情報を作成
                        $Delivery = $multipleItem->getShipping()->getDelivery();

                        // 選択された情報を取得
                        $address_data = $item['customer_address']->getData();
                        if ($address_data instanceof \Eccube\Entity\CustomerAddress) {
                            // 会員の場合、CustomerAddressオブジェクトを取得
                            $CustomerAddress = $address_data;
                        } else {
                            // 非会員の場合、選択されたindexが取得される
                            $customerAddresses = $app['session']->get($sessionCustomerAddressKey);
                            $customerAddresses = unserialize($customerAddresses);
                            $CustomerAddress = $customerAddresses[$address_data];
                            $pref = $app['eccube.repository.master.pref']->find($CustomerAddress->getPref()->getId());
                            $CustomerAddress->setPref($pref);
                        }
                        
                        if(isset($Shipping))unset($Shipping);
                        $compare_key = $CustomerAddress->getName01() . $CustomerAddress->getName02() . $CustomerAddress->getAddr01() . $CustomerAddress->getAddr02();
                        foreach($checkAddresses as $key => $checkAddress){
                            $check_key = $checkAddress->getName01() . $checkAddress->getName02() . $checkAddress->getAddr01() . $checkAddress->getAddr02();
                            if($compare_key == $check_key){
                                $Shipping = $memShippings[$key];
                                break;
                            }
                        }

                        if(!isset($Shipping)){
                            $Shipping = new \Eccube\Entity\Shipping();
                            $Shipping
                                ->setFromCustomerAddress($CustomerAddress)
                                ->setDelivery($Delivery)
                                ->setDelFlg(Constant::DISABLED)
                                ->setOrder($Order);
                            $app['orm.em']->persist($Shipping);
                            $checkAddresses[] = $CustomerAddress;
                            $memShippings[] = $Shipping;
                        }

                        $ProductClass = $multipleItem->getProductClass();
                        $Product = $multipleItem->getProduct();
                        $quantity = $item['quantity']->getData();

                        $ShipmentItem = new \Eccube\Entity\ShipmentItem();
                        $ShipmentItem->setShipping($Shipping)
                            ->setOrder($Order)
                            ->setProductClass($ProductClass)
                            ->setProduct($Product)
                            ->setProductName($Product->getName())
                            ->setProductCode($ProductClass->getCode())
                            ->setPrice($ProductClass->getPrice02() + $checkOptions[$no]->getPrice())
                            ->setQuantity($quantity);                      

                        $ClassCategory1 = $ProductClass->getClassCategory1();
                        if (!is_null($ClassCategory1)) {
                            $ShipmentItem->setClasscategoryName1($ClassCategory1->getName());
                            $ShipmentItem->setClassName1($ClassCategory1->getClassName()->getName());
                        }
                        $ClassCategory2 = $ProductClass->getClassCategory2();
                        if (!is_null($ClassCategory2)) {
                            $ShipmentItem->setClasscategoryName2($ClassCategory2->getName());
                            $ShipmentItem->setClassName2($ClassCategory2->getClassName()->getName());
                        }
                        $Shipping->addShipmentItem($ShipmentItem);
                        $app['orm.em']->persist($ShipmentItem);

                        $arrShipmentItem[] = $ShipmentItem;
                        $arrNo[] = $no;
                        // 配送料金の設定
                        $app['eccube.service.shopping']->setShippingDeliveryFee($Shipping);
                    }
                }
            }
            // 配送先を更新
            $app['orm.em']->flush();
            
            $arrItems = array();
            foreach($arrShipmentItem as $key => $ShipmentItem){
                $addItem = array('item_id' => $ShipmentItem->getId(), 'order_option_id' => $checkOptions[$arrNo[$key]]->getId());
                $arrItems[] = $addItem;
            }
            
            $app['orm.em']->clear();
            
            foreach($arrItems as $item){
                $plgShipmentItem = new \Plugin\ProductOption\Entity\ShipmentItem();
                $ShipmentItem = $app['orm.em']->getRepository('Eccube\Entity\ShipmentItem')->find($item['item_id']);
                $plgShipmentItem->setItemId($item['item_id'])
                                ->setShipmentItem($ShipmentItem);
                $OrderOption = $app['eccube.productoption.repository.order_option']->find($item['order_option_id']);
                $plgShipmentItem->setOrderOptionId($item['order_option_id'])
                                ->setOrderOption($OrderOption);
                $app['orm.em']->persist($plgShipmentItem);
            }
            $app['orm.em']->flush();

            $app->redirect($app->url('shopping'))->send();
            exit;
        }        
        
        return $event;
    }
}
