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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;


class ServiceAdminMail extends AbstractWorkPlace
{
    public function execute(EventArgs $event)
    {
        $app = $this->app;
        
        $formData = $event['formData'];
        $Order = $event['Order'];
        $message = $event['message'];
        
        $orderDetails = $Order->getOrderDetails();
        $plgOrderDetails = $app['eccube.productoption.service.util']->getPlgOrderDetails($orderDetails);
        
        $Shippings = $Order->getShippings();
        $plgShipmentItems = $app['eccube.productoption.service.util']->getPlgShipmentItems($Shippings);
        
        $body = $app->renderView('Mail/order.twig', array(
            'header' => $formData['header'],
            'footer' => $formData['footer'],
            'Order' => $Order,
            'plgOrderDetails' => $plgOrderDetails,
            'plgShipmentItems' => $plgShipmentItems,
        ));
        
        $message->setBody($body);
        
        $event['message'] = $message;
    }
}
