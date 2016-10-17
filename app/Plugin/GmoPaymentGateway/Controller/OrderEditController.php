<?php
/*
 * Copyright(c) 2015 GMO Payment Gateway, Inc. All rights reserved.
 * http://www.gmo-pg.com/
 */

namespace Plugin\GmoPaymentGateway\Controller;

use Eccube\Application;
use Plugin\GmoPaymentGateway\Controller\Util\PaymentUtil;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception as HttpException;


class OrderEditController
{
    private $main_title;
    private $sub_title;

    public function __construct()
    {
        $this->main_title = '受注管理';
        $this->sub_title = '受注編集';
    }

    public function index(Application $app, Request $request)
    {
        $orderId = $request->get('orderId');
        if ($orderId == 0) {
            $Order = $app['eccube.service.order']->newOrder();
        } else {
            $Order = $app['orm.em']
                ->getRepository('Eccube\Entity\Order')
                ->find($orderId);
        }
        if (empty($Order)) {
            throw new HttpException\NotFoundHttpException('Order is not found.');
        }

        $form = $app['form.factory']
            ->createBuilder('order', $Order)
            ->getForm();

        /* get $orderExtGetPaymentData */
        $objUtil = new PaymentUtil($app);
        $orderExt = $objUtil->getOrderPayData($orderId);
        $orderExtGetPaymentData = $orderExt->getPaymentData();
        /* end get $orderExtGetPaymentData */

        return $app['view']->render('GmoPaymentGateway/View/order.twig', array(
            'form' => $form->createView(),
            'title' => $this->main_title,
            'sub_title' => $this->sub_title,
            'Order' => $Order,
            'orderId' => $orderId,
            'orderExtGetPaymentData' => $orderExtGetPaymentData,
            'appConst' => $app['config']['GmoPaymentGateway']['const'],
            'tpl_mode' => 'edit'
        ));
    }

    public function commit(Application $app, Request $request)
    {


        /*if ('POST' === $app['request']->getMethod()) {
            $form->handleRequest($app['request']);
            if ($form->isValid()) {
                $Order = $form->getData();
//                $OrderDetails = $Order->getOrderDetails();
//                $Shippings = $Order->getShippings();

                $app['orm.em']->persist($Order);
                $app['orm.em']->flush();
                // TODO: リダイレクトすると検索条件が消える
                // return $app->redirect($app['url_generator']->generate('admin_order'));
            }
        }*/
    }
}
