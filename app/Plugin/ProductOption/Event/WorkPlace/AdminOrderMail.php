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


class AdminOrderMail extends AbstractWorkPlace
{
    public function createTwig(TemplateEvent $event)
    {
        $app = $this->app;
        $parameters = $event->getParameters();
        
        $Order = $parameters['Order'];
        $form = $parameters['form'];

        $orderDetails = $Order->getOrderDetails();
        $plgOrderDetails = $app['eccube.productoption.service.util']->getPlgOrderDetails($orderDetails);
        
        $Shippings = $Order->getShippings();
        $plgShipmentItems = $app['eccube.productoption.service.util']->getPlgShipmentItems($Shippings);

        $body = $app->renderView('Mail/order.twig', array(
            'header' => $form['header']->vars['value'],
            'footer' => $form['footer']->vars['value'],
            'Order' => $Order,
            'plgOrderDetails' => $plgOrderDetails,
            'plgShipmentItems' => $plgShipmentItems,
        ));

        $parameters['body'] = $body;

        $event->setParameters($parameters);
    }
    
    public function save(EventArgs $event)
    {
        $app = $this->app;
        
        $MailHistory = $event->getArgument('MailHistory');
        $Order = $event->getArgument('Order');
        $form = $event->getArgument('form');
        
        $orderDetails = $Order->getOrderDetails();
        $plgOrderDetails = $app['eccube.productoption.service.util']->getPlgOrderDetails($orderDetails);
        
        $Shippings = $Order->getShippings();
        $plgShipmentItems = $app['eccube.productoption.service.util']->getPlgShipmentItems($Shippings);

        $body = $app->renderView('Mail/order.twig', array(
            'header' => $form['header']->getData(),
            'footer' => $form['footer']->getData(),
            'Order' => $Order,
            'plgOrderDetails' => $plgOrderDetails,
            'plgShipmentItems' => $plgShipmentItems,
        ));
        
        $MailHistory->setMailBody($body);
        $app['orm.em']->persist($MailHistory);
        $app['orm.em']->flush();
    }
}
