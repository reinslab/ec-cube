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


class FrontShoppingComplete extends AbstractWorkPlace
{
    public function save(EventArgs $event)
    {
        $app = $this->app;
        
        $MailHistory = $event->getArgument('MailHistory');
        $Order = $event->getArgument('Order');
        $MailTemplate = $MailHistory->getMailTemplate();
        
        $orderDetails = $Order->getOrderDetails();
        $plgOrderDetails = $app['eccube.productoption.service.util']->getPlgOrderDetails($orderDetails);
        
        $Shippings = $Order->getShippings();
        $plgShipmentItems = $app['eccube.productoption.service.util']->getPlgShipmentItems($Shippings);

        $body = $app->renderView('Mail/order.twig', array(
            'header' => $MailTemplate->getHeader(),
            'footer' => $MailTemplate->getFooter(),
            'Order' => $Order,
            'plgOrderDetails' => $plgOrderDetails,
            'plgShipmentItems' => $plgShipmentItems,
        ));
        
        $MailHistory->setMailBody($body);
        $app['orm.em']->persist($MailHistory);
        $app['orm.em']->flush();
    }
    
    public function execute(EventArgs $event)
    {
        $this->app['eccube.productoption.service.cart']->clear()->save();
    }    
}
