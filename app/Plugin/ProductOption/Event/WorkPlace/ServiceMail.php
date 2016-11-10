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


class ServiceMail extends AbstractWorkPlace
{
    public function execute(EventArgs $event)
    {
        $app = $this->app;
        
        $MailTemplate = $event['MailTemplate'];
        $Order = $event['Order'];
        $message = $event['message'];
        
        $orderDetails = $Order->getOrderDetails();
        $plgOrderDetails = $app['eccube.productoption.service.util']->getPlgOrderDetails($orderDetails);
        
        $Shippings = $Order->getShippings();
        $plgShipmentItems = $app['eccube.productoption.service.util']->getPlgShipmentItems($Shippings);
        
// A => NSS 
        //印刷商品判定
        $flgPrintItem = $app['eccube.service.product']->isPrintProductByOrder($Order);
// A => NSS 

        $body = $app->renderView('Mail/order.twig', array(
            'header' => $MailTemplate->getHeader(),
            'footer' => $MailTemplate->getFooter(),
            'Order' => $Order,
            'plgOrderDetails' => $plgOrderDetails,
            'plgShipmentItems' => $plgShipmentItems,
// A => NSS 印刷商品フラグ
            'flgPrintItem' => $flgPrintItem,
// A => NSS 印刷商品フラグ
        ));
        
        $message->setBody($body);
        
        $event['message'] = $message;
    }
}
