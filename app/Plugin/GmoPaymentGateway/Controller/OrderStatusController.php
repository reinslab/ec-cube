<?php
/*
 * Copyright(c) 2015 GMO Payment Gateway, Inc. All rights reserved.
 * http://www.gmo-pg.com/
 */

namespace Plugin\GmoPaymentGateway\Controller;

use Eccube\Application;
use Plugin\GmoPaymentGateway\Controller\Util\PaymentUtil;
use Plugin\GmoPaymentGateway\Service\client\PG_MULPAY_Client_Util;

class OrderStatusController
{

    protected $title;
    protected $subtitle;
    protected $app;

    public function __construct()
    {
        $this->title = '受注管理';
        $this->subtitle = '決済状況管理';
    }

    public function index(Application $app, $paymentStatus, $paymentType)
    {
        $this->app = $app;

        $objUtilPgMulPay = new PaymentUtil($app);
        $orderRepo = $app['orm.em']->getRepository('\Eccube\Entity\Order');

        $form = $app['form.factory']
            ->createBuilder()
            ->add('move', 'collection', array(
                'type' => 'checkbox',
                'prototype' => true,
                'allow_add' => true,
            ))
            ->add('status', 'order_status', array(
                'expanded' => false,
                'multiple' => false,
                'placeholder' => '選択してください'
            ))
            ->add('payment_status', 'choice', array(
                'choices' => array(
                    'commit' => '一括売上',
                    'cancel' => '一括取消',
                    'reauth' => '一括再オーソリ'
                ),
                'placeholder' => '選択してください',
                'required' => false,
                'expanded' => false,
                'multiple' => false
            ))
            ->add('mode', 'hidden')
            ->getForm();

        $changePaymentStatusErrors = array();
        if ('POST' === $app['request']->getMethod()) {

            $form->handleRequest($app['request']);
            if ($form->isValid()) {
                $data = $form->getData();

                if ($data['mode'] === 'status') {

                    foreach ($data['move'] as $orderId => $value) {
                        $orderRepo->changeStatus($orderId, $data['status']);
                    }
                    $app->addSuccess('admin.register.complete', 'admin');
                    return $app->redirect($app['url_generator']->generate('gmo_admin_order_status', array('paymentStatus' => $paymentStatus, 'paymentType' => $paymentType)));

                } else if ($data['mode'] === 'delete') {

                    $this->delete($data['move']);
                    $app->addSuccess('admin.delete.complete', 'admin');
                    return $app->redirect($app['url_generator']->generate('gmo_admin_order_status', array('paymentStatus' => $paymentStatus, 'paymentType' => $paymentType)));

                } else if ($data['mode'] === 'payment_status') {
                    $error_message = null;
                    foreach ($data['move'] as $orderId => $value) {
                        $arrOrder = $objUtilPgMulPay->getOrderPayData($orderId);
                        $objClient = new PG_MULPAY_Client_Util($app);
                        $ret = null;
                        if ($data['payment_status'] === 'commit') {
                            $ret = $objClient->commitOrder($arrOrder);
                        } else if ($data['payment_status'] === 'cancel') {
                            $ret = $objClient->cancelOrder($arrOrder);
                        } else if ($data['payment_status'] === 'reauth') {
                            $ret = $objClient->reauthOrder($arrOrder);
                        }

                        if (!$ret) {
                            $error = $objClient->getError();
                            $error_message = '注文番号:' . $orderId . 'の決済でエラーが発生しました。';
                            if (empty($error)) {
                                $error_message .= '対象の変更は出来ない決済です。';
                            } else {
                                foreach ($error as $errMess) {
                                    $error_message .= $errMess;
                                }
                            }

                            $app->addDanger($error_message, 'admin');
                        }
                    }
                    if (is_null($error_message)) {
                        $app->addSuccess('admin.register.complete', 'admin');
                    }
                    // return $app->redirect($app['url_generator']->generate('gmo_admin_order_status', array('paymentStatus' => $paymentStatus, 'paymentType' => $paymentType)));
                }
            }
        }

        $arrPaymentStatuses = $objUtilPgMulPay->getPaymentStatus();

        $orderStatuses = $app['orm.em']
            ->getRepository('\Eccube\Entity\Master\OrderStatus')
            ->findAllArray();


        $gmoOrderPaymentRepo = $app['orm.em']->getRepository('\Plugin\GmoPaymentGateway\Entity\GmoOrderPayment');
        $gmoOrderPaymentRepo->setConfig($app['config']['GmoPaymentGateway']['const']);
        $orders = $gmoOrderPaymentRepo->getOrderByPaymentStatusAndType($paymentStatus, $paymentType);
        $gmoPaymentRepo = $app['orm.em']->getRepository('\Plugin\GmoPaymentGateway\Entity\GmoPaymentMethod');
        $gmoPaymentRepo->setConfig($app['config']['GmoPaymentGateway']['const']);
        $arrMulpayPayments = $gmoPaymentRepo->findMulPayPayments();
        $paymentTypes = array();
        foreach ($arrMulpayPayments as $payment) {
            $paymentTypes[$payment[$app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYMENT_COL_PAYID']]] = $payment['method'];
        }

        return $app['view']->render('GmoPaymentGateway/View/admin/gmo_order_status.twig', array(
            'form' => $form->createView(),
            'maintitle' => $this->title,
            'subtitle' => $this->subtitle,
            'Orders' => $orders,
            'PaymentTypes' => $paymentTypes,
            'PaymentStatuses' => $arrPaymentStatuses,
            'OrderStatuses' => $orderStatuses,
            'CurrentStatus' => $paymentStatus,
            'CurrentType' => $paymentType,
            'changePaymentStatusErrors' => $changePaymentStatusErrors
        ));
    }

    /**
     * 受注テーブルの論理削除
     */
    function delete($arrOrderId)
    {


        if (!isset($arrOrderId) || !is_array($arrOrderId)) {
            return false;
        }

        $this->app['orm.em']->getConnection()->beginTransaction();
        try {
            foreach ($arrOrderId as $orderId => $value) {
                $Order = $this->app['orm.em']->getRepository('Eccube\Entity\Order')
                    ->find($orderId);

                if ($Order) {
                    $Order->setDelFlg(1);
                    //$Order->setUpdateDate('CURRENT_TIMESTAMP');
                    $this->app['orm.em']->persist($Order);
                    $this->app['orm.em']->flush();
                }
            }
        } catch (Exception $ex) {
            $this->app['orm.em']->getConnection()->rollback();
            return false;
        }

        $this->app['orm.em']->getConnection()->commit();

        return true;
    }

}
