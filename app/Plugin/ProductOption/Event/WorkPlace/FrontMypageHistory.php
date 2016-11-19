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


class FrontMypageHistory extends AbstractWorkPlace
{
    public function createTwig(TemplateEvent $event)
    {
        $app = $this->app;
        $parameters = $event->getParameters();

        $Order = $parameters['Order'];
                
        $plgOrderDetails = array();
        
        $orderDetails = $Order->getOrderDetails();
        $plgOrderDetails['label'] = $app['eccube.productoption.service.util']->getPlgOrderDetails($orderDetails);
        $plgOrderDetails['price'] = $app['eccube.productoption.service.util']->getPlgOrderDetailPrice($orderDetails);
   
        $Shippings = $Order->getShippings();
        $plgShipmentItems = $app['eccube.productoption.service.util']->getPlgShipmentItems($Shippings);

        $source = $event->getSource();
        if(preg_match('/<(.*)\s*id="detail_list__classcategory_name.*>\n/',$source, $result)){
            $start_tag = $result[0];
            $tag_name = trim($result[1]);
            $end_tag = '</' . $tag_name . '>';
            $start_index = strpos($source, $start_tag);
            $end_index = strpos($source, $end_tag, $start_index);
            $search = substr($source, $start_index, ($end_index - $start_index));
                
            $snipet = file_get_contents($app['config']['plugin_realdir']. '/ProductOption/Resource/template/default/Mypage/history_detail_option.twig');
            $replace = $search.$snipet;
            $source = str_replace($search, $replace, $source);
        }
        
        if(preg_match('/<(.*)\s*id="shipping_list__shipment_class_category.*>\n/',$source, $result)){
            $start_tag = $result[0];
            $tag_name = trim($result[1]);
            $end_tag = '</' . $tag_name . '>';
            $start_index = strpos($source, $start_tag);
            $end_index = strpos($source, $end_tag, $start_index);
            $search = substr($source, $start_index, ($end_index - $start_index));
                
            $snipet = file_get_contents($app['config']['plugin_realdir']. '/ProductOption/Resource/template/default/Mypage/history_shipmentitem_option.twig');
            $replace = $search.$snipet;
            $source = str_replace($search, $replace, $source);
        }

        $search = '{% if OrderDetail.product and OrderDetail.price_inc_tax != OrderDetail.productClass.price02IncTax %}';
        $replace = file_get_contents($app['config']['plugin_realdir']. '/ProductOption/Resource/template/default/Mypage/history_detail_price.twig');
        $source = str_replace($search,$replace, $source);
        
        $search = '{{ OrderDetail.productClass.price02IncTax|price }}';
        $replace = '{% set price = OrderDetail.productClass.price02IncTax + option_price %}{{ price|price }}';
        $source = str_replace($search,$replace, $source);
        
        $event->setSource($source);
        $parameters['plgOrderDetails'] = $plgOrderDetails;
        $parameters['plgShipmentItems'] = $plgShipmentItems;
        $event->setParameters($parameters);
    }
    
    public function execute(EventArgs $event)
    {
        $Order = $event->getArgument('Order');
        
        $app = $this->app;
        
        foreach ($Order->getOrderDetails() as $OrderDetail) {
            try {
                if ($OrderDetail->getProduct()) {
                    if(version_compare(Constant::VERSION,'3.0.10','<')){
                        $app['eccube.service.cart']->addProduct($OrderDetail->getProductClass()->getId(), $OrderDetail->getQuantity())->save();
                    }
                    $plgOrderDetail = $app['eccube.productoption.repository.order_detail']->findOneBy(array('order_detail_id' => $OrderDetail->getId()));
                    if($plgOrderDetail){
                        $Options = $plgOrderDetail->getOrderOption()->getSerial();
                        foreach($Options as $option_key => $value){
                            $option_id = str_replace('productoption', '', $option_key);
                            $ProductOption = $app['eccube.productoption.repository.product_option']->findOneBy(array('option_id' => $option_id, 'product_id' => $OrderDetail->getProduct()->getId() ));
                            if(!$ProductOption)unset($Options[$option_key]);
                        }
                        $app['eccube.productoption.service.cart']->addProductOption($OrderDetail->getProductClass()->getId(), $Options, $OrderDetail->getQuantity());
                    }
                } else {
                    $app->addRequestError('cart.product.delete');
                }
            } catch (CartException $e) {
                $app->addRequestError($e->getMessage());
            }
        }
        
        if(version_compare(Constant::VERSION,'3.0.10','<')){
            $app->redirect($app->url('cart'))->send();
            exit;
        }
    }
}
