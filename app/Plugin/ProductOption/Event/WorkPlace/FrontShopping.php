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

use Eccube\Event\EventArgs;
use Eccube\Event\TemplateEvent;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;


class FrontShopping extends AbstractWorkPlace
{
    public function createTwig(TemplateEvent $event)
    {
        $app = $this->app;
        $parameters = $event->getParameters();
        
        $Order = $parameters['Order'];
        $orderDetails = $Order->getOrderDetails();
        $plgOrderDetails = $app['eccube.productoption.service.util']->getPlgOrderDetails($orderDetails);
        
        $Shippings = $Order->getShippings();
        $plgShipmentItems = $app['eccube.productoption.service.util']->getPlgShipmentItems($Shippings);

        $source = $event->getSource();
        if(preg_match('/<.*id="cart_box_list__price/',$source, $result)){
            $search = $result[0];
            $snipet = file_get_contents($app['config']['plugin_realdir']. '/ProductOption/Resource/template/default/Shopping/detail_option.twig');
            $replace = $snipet.$search;
            $source = str_replace($search, $replace, $source);
        }
        
        if(preg_match('/<.*id="shipping_box__price/',$source, $result)){
            $search = $result[0];
            $snipet = file_get_contents($app['config']['plugin_realdir']. '/ProductOption/Resource/template/default/Shopping/shipmentitem_option.twig');
            $replace = $snipet.$search;
            $source = str_replace($search, $replace, $source);
        }

        $event->setSource($source);
        $parameters['plgOrderDetails'] = $plgOrderDetails;
        $parameters['plgShipmentItems'] = $plgShipmentItems;
        $event->setParameters($parameters);
    }
    
    public function execute(EventArgs $event)
    {
        $Order = $event->getArgument('Order');
        
        $app = $this->app;
        
        $Order = $app['eccube.productoption.service.shopping']->customOrder($Order);

        $app['orm.em']->persist($Order);
        $app['orm.em']->flush();
    }    
}
