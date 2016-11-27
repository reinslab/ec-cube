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
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\Validator\Constraints as Assert;

class AdminOrderEdit extends AbstractWorkPlace
{    
    public function createTwig(TemplateEvent $event)
    {
        $app = $this->app;
        $parameters = $event->getParameters();
        $request = $app['request'];
        $BaseInfo = $app['eccube.repository.base_info']->get();

        $Order = $parameters['Order'];
        $formOrderDetails = new \Doctrine\Common\Collections\ArrayCollection();
        $formShipmentItems = new \Doctrine\Common\Collections\ArrayCollection();
        if ('POST' === $request->getMethod()) {
            $builder = $app['form.factory']
                    ->createBuilder('plg_productoption_order');

            $form = $builder->getForm();
            $form->handleRequest($request);
            $orderDetailData = $form->get('OrderDetailOptions')->getData();
            $shipmentItemData = $form->get('ShipmentItemOptions')->getData();

            if(count($orderDetailData) > 0){
                foreach($orderDetailData as $data){
                    $formOrderDetails->add($data);
                }
            }
            if(count($shipmentItemData) > 0){
                foreach($shipmentItemData as $data){
                    $formShipmentItems->add($data);
                }
            }
            if ($request->get('modal') === 'modal') {
                $refOrderDetails = $_POST['order']['OrderDetails'];
                if (count($refOrderDetails) > 0) {
                    foreach ($refOrderDetails as $idx => $refOrderDetail) {
                        if (isset($refOrderDetail['new'])) {
                            $target_detail_idx = $idx;
                        }
                    }
                }

                $addOptions = $form->get('AddProductOptions')->getData();
                $Options = array();
                if (count($addOptions) > 0) {
                    foreach ($addOptions as $option) {
                        $Option = $app['eccube.productoption.repository.option']->find($option['option_id']);
                        if ($Option) {
                            $add = true;
                            if ($Option->getType()->getId() == 1 || $Option->getType()->getId() == 2) {
                                if ($Option->getDisableCategory()) {
                                    if ($Option->getDisableCategory()->getId() == $option['value']) {
                                        $add = false;
                                    }
                                }
                            } elseif ($Option->getType()->getId() == 3 || $Option->getType()->getId() == 4) {
                                if (strlen($option['value']) == 0)
                                    $add = false;
                            }
                            if ($add) {
                                $Options['productoption' . $option['option_id']] = (string) $option['value'];
                            }
                        }
                    }
                }
                $formOrderDetail = new \Plugin\ProductOption\Entity\OrderDetailOption();
                $formOrderDetail->setIndex((string) $target_detail_idx);
                $formOrderDetail->setSerial(serialize($Options));
                $formOrderDetails->add($formOrderDetail);

                if ($BaseInfo->getOptionMultipleShipping() == Constant::ENABLED) {
                    $refShippings = $_POST['order']['Shippings'];
                    foreach ($refShippings as $shipping_idx => $refShipping) {
                        foreach ($refShipping['ShipmentItems'] as $item_idx => $refShipmentItem) {
                            if (isset($refShipmentItem['new'])) {
                                $target_shipping_idx = $shipping_idx;
                                $target_item_idx = $item_idx;
                            }
                        }
                    }

                    $formShipmentItem = new \Plugin\ProductOption\Entity\ShipmentItemOption();
                    $formShipmentItem->setShippingIndex((string) $target_shipping_idx);
                    $formShipmentItem->setItemIndex((string) $target_item_idx);
                    $formShipmentItem->setSerial(serialize($Options));
                    $formShipmentItems->add($formShipmentItem);
                }
            }                
        }

        $OrderDetails = $Order->getOrderDetails();
        foreach ($OrderDetails as $idx => $orderDetail) {
            if ($orderDetail->getId() != null) {
                $plgOrderDetail = $app['eccube.productoption.repository.order_detail']->findOneBy(array('order_detail_id' => $orderDetail->getId()));
                $formOrderDetail = new \Plugin\ProductOption\Entity\OrderDetailOption();
                $formOrderDetail->setIndex((string) $idx);
                if ($plgOrderDetail) {
                    $formOrderDetail->setSerial(serialize($plgOrderDetail->getOrderOption()->getSerial()));
                }
                $add_flg = true;
                foreach ($formOrderDetails as $tmpOrderDetail) {
                    if ($tmpOrderDetail->getIndex() == $formOrderDetail->getIndex()) {
                        $add_flg = false;
                    }
                }
                if ($add_flg)
                    $formOrderDetails->add($formOrderDetail);
            }
        }

        $Shippings = $Order->getShippings();
        foreach ($Shippings as $shipping_idx => $shipping) {
            $ShipmentItems = $shipping->getShipmentItems();
            foreach ($ShipmentItems as $item_idx => $shipmentItem) {
                if ($shipmentItem->getId() != null) {
                    $plgShipmentItem = $app['eccube.productoption.repository.shipment_item']->findOneBy(array('item_id' => $shipmentItem->getId()));
                    if ($plgShipmentItem && $shipmentItem->getId() != null) {
                        $plgShipmentItems[$shipping_idx][$item_idx] = $plgShipmentItem->getOrderOption()->getLabel();
                    }
                    $formShipmentItem = new \Plugin\ProductOption\Entity\ShipmentItemOption();
                    $formShipmentItem->setShippingIndex((string) $shipping_idx);
                    $formShipmentItem->setItemIndex((string) $item_idx);
                    if ($plgShipmentItem) {
                        $formShipmentItem->setSerial(serialize($plgShipmentItem->getOrderOption()->getSerial()));
                    }
                    $add_flg = true;
                    foreach ($formShipmentItems as $tmpShipmentItem) {
                        if ($tmpShipmentItem->getShippingIndex() == $formShipmentItem->getShippingIndex() && $tmpShipmentItem->getItemIndex() == $formShipmentItem->getItemIndex()) {
                            $add_flg = false;
                        }
                    }
                    if ($add_flg)
                        $formShipmentItems->add($formShipmentItem);
                }
            }
        }

        $plgOrderDetails = array();
        $plgShipmentItems = array();
        foreach($formOrderDetails as $formOrderDetail){
            $Options = unserialize($formOrderDetail->getSerial());
            $plgOrderDetails[$formOrderDetail->getIndex()] = $app['eccube.productoption.service.util']->getLabelFromOptions($Options);
        }
        
        foreach($formShipmentItems as $formShipmentItem){
            $Options = unserialize($formShipmentItem->getSerial());
            $plgShipmentItems[$formShipmentItem->getShippingIndex()][$formShipmentItem->getItemIndex()] = $app['eccube.productoption.service.util']->getLabelFromOptions($Options);
        }

        $source = $event->getSource();
        
        if(preg_match('/\{%\s*for\s*orderDetailForm\s*in\s*form\.OrderDetails\s*%\}/',$source, $result)){
            $search = $result[0];
            $snipet = file_get_contents($app['config']['plugin_realdir']. '/ProductOption/Resource/template/admin/Order/option_hidden_prototype.twig');
            $replace = $snipet . '{% for detail_idx , orderDetailForm in form.OrderDetails %}';
            $source = str_replace($search, $replace, $source);
        }
        
        if(preg_match('/\{\{\s*form_widget\(orderDetailForm\.ProductClass\)\s*\}\}/',$source, $result)){
            $search = $result[0];
            $snipet = file_get_contents($app['config']['plugin_realdir']. '/ProductOption/Resource/template/admin/Order/option_hidden_detail.twig');
            $replace = $search . $snipet;
            $source = str_replace($search, $replace, $source);
        }
        
        if(preg_match('/\{\{\s*form_widget\(shipmentItemForm\.ProductClass\)\s*\}\}/',$source, $result)){
            $search = $result[0];
            $snipet = file_get_contents($app['config']['plugin_realdir']. '/ProductOption/Resource/template/admin/Order/option_hidden_shipmentitem.twig');
            $replace = $search . $snipet;
            $source = str_replace($search, $replace, $source);
        }
        
        if(preg_match('/\{%\s*for\s*shippingForm\s*in\s*form\.Shippings\s*%\}/',$source, $result)){
            $search = $result[0];
            $replace = '{% set shipmentitem_idx = -1 %}{% for shipping_idx , shippingForm in form.Shippings %}';
            $source = str_replace($search, $replace, $source);
        }
        
        if(preg_match('/\{%\s*for\s*shipmentItemForm\s*in\s*shippingForm\.ShipmentItems\s*%\}/',$source, $result)){
            $search = $result[0];
            $replace = '{% for item_idx, shipmentItemForm in shippingForm.ShipmentItems %}{% set shipmentitem_idx = shipmentitem_idx + 1%}';
            $source = str_replace($search, $replace, $source);
        }

        if(preg_match('/<(.*)\s*id="product_info_list__class_category_name.*>/',$source, $result)){
            $start_tag = $result[0];
            $tag_name = trim($result[1]);
            $end_tag = '</' . $tag_name . '>';
            $start_index = strpos($source, $start_tag);
            $end_index = strpos($source, $end_tag, $start_index);
            $search = substr($source, $start_index, ($end_index - $start_index));
                
            $snipet = file_get_contents($app['config']['plugin_realdir']. '/ProductOption/Resource/template/admin/Order/detail_option.twig');
            $replace = $search.$snipet;
            $source = str_replace($search, $replace, $source);
        }
        
        if(preg_match('/<(.*)\s*id="shipment_item__class_category_name.*>n/',$source, $result)){
            $start_tag = $result[0];
            $tag_name = trim($result[1]);
            $end_tag = '</' . $tag_name . '>';
            $start_index = strpos($source, $start_tag);
            $end_index = strpos($source, $end_tag, $start_index);
            $search = substr($source, $start_index, ($end_index - $start_index));
                
            $snipet = file_get_contents($app['config']['plugin_realdir']. '/ProductOption/Resource/template/admin/Order/shipmentitem_option.twig');
            $replace = $search.$snipet;
            $source = str_replace($search, $replace, $source);
        }

        $builder = $app['form.factory']
            ->createBuilder('plg_productoption_order');
        $builder->get('OrderDetailOptions')->setData($formOrderDetails);
        $builder->get('ShipmentItemOptions')->setData($formShipmentItems);
        $form = $builder->getForm();        

        $event->setSource($source);
        $parameters['plgForm'] = $form->createView();
        $parameters['plgOrderDetails'] = $plgOrderDetails;
        $parameters['plgShipmentItems'] = $plgShipmentItems;
        $event->setParameters($parameters);
    }
    
    public function render(FilterResponseEvent $event)
    {
        $app = $this->app;
        if (!$app->isGranted('ROLE_ADMIN')) {
            return $app->redirect($app->url('admin_login'));
        }
        
        $request = $event->getRequest();
        $response = $event->getResponse();
        $BaseInfo = $app['eccube.repository.base_info']->get();
        
        $id = $request->get('id');
        
        if (is_null($id)) {
            // 空のエンティティを作成.
            $Order = $this->newOrder();
        } else {
            $Order = $app['eccube.repository.order']->find($id);
            if (is_null($Order)) {
                throw new NotFoundHttpException();
            }
        }        
        
        if ('POST' === $request->getMethod()) {
            $builder = $app['form.factory']
                    ->createBuilder('plg_productoption_order');

            $form = $builder->getForm();
            $form->handleRequest($request);

            if ($request->get('modal') === 'modal') {
                $refOrderDetails = $_POST['order']['OrderDetails'];
                if (count($refOrderDetails) > 0) {
                    foreach ($refOrderDetails as $idx => $refOrderDetail) {
                        if (isset($refOrderDetail['new'])) {
                            $target_detail_idx = $idx;
                        }
                    }
                }

                $addOptions = $form->get('AddProductOptions')->getData();
                $Options = array();
                if (count($addOptions) > 0) {
                    foreach ($addOptions as $option) {
                        $Option = $app['eccube.productoption.repository.option']->find($option['option_id']);
                        if ($Option) {
                            $add = true;
                            if ($Option->getType()->getId() == 1 || $Option->getType()->getId() == 2) {
                                if ($Option->getDisableCategory()) {
                                    if ($Option->getDisableCategory()->getId() == $option['value']) {
                                        $add = false;
                                    }
                                }
                            } elseif ($Option->getType()->getId() == 3 || $Option->getType()->getId() == 4) {
                                if (strlen($option['value']) == 0)
                                    $add = false;
                            }
                            if ($add) {
                                $Options['productoption' . $option['option_id']] = (string) $option['value'];
                            }
                        }
                    }
                }
                $option_price = $app['eccube.productoption.service.util']->getPriceFromOptions($Options);
                $deliv_free_flg = $app['eccube.productoption.service.util']->getDeliveryFreeFlgFromOptions($Options);
                
                $builder = $app['form.factory']
                    ->createBuilder('order');
                $form = $builder->getForm();
                $form->handleRequest($request);

                $OrderDetails = $form->get('OrderDetails')->getData();
                $price = $OrderDetails[$target_detail_idx]->getPrice();
                $OrderDetails[$target_detail_idx]->setPrice($price + $option_price);
                
                $detail_iteration = 1;
                foreach($OrderDetails as $key => $OrderDetail){
                    if($key == $target_detail_idx){
                        break;
                    }
                    $detail_iteration++;
                }

                if ($BaseInfo->getOptionMultipleShipping() == Constant::ENABLED) {
                    $refShippings = $_POST['order']['Shippings'];
                    foreach ($refShippings as $shipping_idx => $refShipping) {
                        foreach ($refShipping['ShipmentItems'] as $item_idx => $refShipmentItem) {
                            if (isset($refShipmentItem['new'])) {
                                $target_shipping_idx = $shipping_idx;
                                $target_item_idx = $item_idx;
                            }
                        }
                    }
                    
                    $Shippings = $form->get('Shippings')->getData();
                    $ShipmentItems = $Shippings[$target_shipping_idx]->getShipmentItems();
                    $price = $ShipmentItems[$target_item_idx]->getPrice();
                    $ShipmentItems[$target_item_idx]->setPrice($price + $option_price);
                    $ShipmentItems[$target_item_idx]->setPriceIncTax($price + $option_price + $app['eccube.service.tax_rule']->calcTax($price + $option_price, $OrderDetails[$target_detail_idx]->getTaxRate(), $OrderDetails[$target_detail_idx]->getTaxRule()));
                }
                
                $taxtotal = 0;
                $subtotal = 0;
                foreach ($OrderDetails as $OrderDetail) {
                    $tax = $app['eccube.service.tax_rule']
                        ->calcTax($OrderDetail->getPrice(), $OrderDetail->getTaxRate(), $OrderDetail->getTaxRule());
                    $OrderDetail->setPriceIncTax($OrderDetail->getPrice() + $tax);

                    $taxtotal += $tax;
                    $subtotal += $OrderDetail->getTotalPrice();
                }
                
                if($deliv_free_flg){
                    $Order->setDeliveryFeeTotal(0);
                    $shippings = $Order->getShippings();
                    foreach ($shippings as $Shipping) {
                        $Shipping->setShippingDeliveryFee(0);
                    }
                }
                
                $Order->setTax($taxtotal);
                $Order->setSubtotal($subtotal);
                $Order->setTotal($subtotal + $Order->getCharge() + $Order->getDeliveryFeeTotal() - $Order->getDiscount());
                $Order->setPaymentTotal($Order->getTotal());
                
                //値書き換え
                $html = $response->getContent();

                libxml_use_internal_errors(true);
                $dom = new \DOMDocument();
                $dom->loadHTML('<?xml encoding="UTF-8">' . mb_convert_encoding($html,'HTML-ENTITIES', 'UTF-8'));
                $dom->encoding = "UTF-8";
                $dom->formatOutput = true;

                //明細
                $this->setInputValue($dom, 'order[OrderDetails]['.$target_detail_idx.'][price]', $OrderDetails[$target_detail_idx]->getPrice());
                $formElement = $dom->getElementById('product_info_list__total_price--'.$detail_iteration);
                if($formElement){
                    $formElement->childNodes->item(2)->nodeValue = $this->priceFilter($OrderDetails[$target_detail_idx]->getTotalPrice());
                }
                
                //お届け先明細
                if ($BaseInfo->getOptionMultipleShipping() == Constant::ENABLED) {
                    $ShipmentItems = $Shippings[$target_shipping_idx]->getShipmentItems();
                    $this->setInputValue($dom, 'order[Shippings]['.$target_shipping_idx.'][ShipmentItems]['.$target_item_idx.'][price]', $ShipmentItems[$target_item_idx]->getPrice());
                }
                
                //小計
                $formElement = $dom->getElementById('product_info_result_box__body_sub_price');
                if($formElement){
                    $formElement->childNodes->item(2)->nodeValue = $this->priceFilter($Order->getSubTotal());
                }

                //合計・お支払合計
                $formElement = $dom->getElementById('product_info_result_box__body_summary');
                if($formElement){
                    $formElement->childNodes->item(2)->nodeValue = $this->priceFilter($Order->getTotal());
                    $formElement->childNodes->item(6)->nodeValue = $this->priceFilter($Order->getPaymentTotal());
                }

                //送料
                $this->setInputValue($dom, 'order[delivery_fee_total]', $Order->getDeliveryFeeTotal());

                $response->setContent(mb_convert_encoding($dom->saveHTML(), 'UTF-8', 'HTML-ENTITIES'));
                $event->setResponse($response);
            }
        }
    }
    
    public function save(EventArgs $event)
    {
        $app = $this->app;
        $request = $app['request'];
        
        $TargetOrder = $event->getArgument('TargetOrder');
        
        $builder = $app['form.factory']
                ->createBuilder('plg_productoption_order');

        $form = $builder->getForm();
        $form->handleRequest($request);        

        $orderDetailOptions = $form->get('OrderDetailOptions')->getData();
        $shipmentItemOptions = $form->get('ShipmentItemOptions')->getData();
        $OrderDetails = $TargetOrder->getOrderDetails();
        foreach($OrderDetails as $idx => $orderDetail){
            $Serial = '';
            foreach($orderDetailOptions as $orderDetailOption){
                if($orderDetailOption->getIndex() == $idx){
                    $Serial = $orderDetailOption->getSerial();
                    break;
                }
            }
            if($Serial != ''){
                $plgOrderDetail = $app['eccube.productoption.repository.order_detail']->findOneBy(array('order_detail_id' => $orderDetail->getId()));
                if(!$plgOrderDetail){
                    $plgOrderDetail = new \Plugin\ProductOption\Entity\OrderDetail();
                    $plgOrderDetail->setOrderDetailId($orderDetail->getId())
                                   ->setOrderDetail($orderDetail);

                    $plgOrderOption = new \Plugin\ProductOption\Entity\OrderOption();
                    $plgOrderOption->setOrder($TargetOrder)
                                   ->setOrderId($TargetOrder->getId());
                    $plgOrderOption->setSerial($Serial);
                    $plgOrderOption = $app['eccube.productoption.repository.order_option']->save($plgOrderOption);

                    $plgOrderDetail->setOrderOptionId($plgOrderOption->getId())
                                   ->setOrderOption($plgOrderOption);

                    $app['orm.em']->persist($plgOrderDetail);
                }else{
                    $plgOrderOption = $plgOrderDetail->getOrderOption();
                    $plgOrderOption->setSerial($Serial);
                    $app['eccube.productoption.repository.order_option']->save($plgOrderOption);
                }
            }
        }
        
        $Shippings = $TargetOrder->getShippings();
        foreach($Shippings as $shipping_idx => $shipping){
            $ShipmentItems = $shipping->getShipmentItems();
            foreach($ShipmentItems as $item_idx => $shipmentItem){
                $Serial = '';
                foreach($shipmentItemOptions as $shipmentItemOption){
                    if($shipmentItemOption->getShippingIndex() == $shipping_idx 
                            && $shipmentItemOption->getItemIndex() == $item_idx ){
                        $Serial = $shipmentItemOption->getSerial();
                        break;
                    }
                }
                if($Serial != ''){
                    $plgShipmentItem = $app['eccube.productoption.repository.shipment_item']->findOneBy(array('item_id' => $shipmentItem->getId()));
                    if(!$plgShipmentItem){
                        $plgShipmentItem = new \Plugin\ProductOption\Entity\ShipmentItem();
                        $plgShipmentItem->setItemId($shipmentItem->getId())
                                        ->setShipmentItem($shipmentItem);

                        $plgOrderOption = new \Plugin\ProductOption\Entity\OrderOption();
                        $plgOrderOption->setOrder($TargetOrder)
                                       ->setOrderId($TargetOrder->getId());
                        $plgOrderOption->setSerial($Serial);
                        $plgOrderOption = $app['eccube.productoption.repository.order_option']->save($plgOrderOption);

                        $plgShipmentItem->setOrderOptionId($plgOrderOption->getId())
                                       ->setOrderOption($plgOrderOption);

                        $app['orm.em']->persist($plgShipmentItem);
                    }else{
                        $plgOrderOption = $plgShipmentItem->getOrderOption();
                        $plgOrderOption->setSerial($Serial);
                        $app['eccube.productoption.repository.order_option']->save($plgOrderOption);
                    }
                }                
            }
        }
        
        $app['orm.em']->flush();
    }

    private function setInputValue($dom, $name, $value)
    {
        $xp = new \DOMXpath($dom);
        $nodes = $xp->query('//input[@name="'.$name.'"]');
        $node = $nodes->item(0);
        $node->setAttribute('value',$value);
    }
    
    private function priceFilter($value)
    {
        return '\ '. number_format($value);
    }
    
    protected function newOrder()
    {
        $Order = new \Eccube\Entity\Order();
        $Shipping = new \Eccube\Entity\Shipping();
        $Shipping->setDelFlg(0);
        $Order->addShipping($Shipping);
        $Shipping->setOrder($Order);

        return $Order;
    }
}
