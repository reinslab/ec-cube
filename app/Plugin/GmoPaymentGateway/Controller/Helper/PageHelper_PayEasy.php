<?php
/*
 * Copyright(c) 2015 GMO Payment Gateway, Inc. All rights reserved.
 * http://www.gmo-pg.com/
 */

namespace Plugin\GmoPaymentGateway\Controller\Helper;

/**
 * 決済モジュール 決済画面ヘルパー：pay-easy決済
 */
class PageHelper_PayEasy
{

    /**
     * array of errors
     * @var array
     */
    private $errors;
    public $orderId;
    public $isComplete = false;
    /**
     * the encrypted receipt number
     * @var string
     */
    private $receiptNo;

    public function __construct()
    {
        $this->errors = array();
    }

    /**
     * 画面モード毎のアクションを行う
     *
     * @param text $mode Mode値
     * @param FormParam $objFormParam FormParam インスタンス
     * @param array $arrOrder 受注情報
     * @param Page $objPage 呼出元ページオブジェクト
     * @return void
     */
    function modeAction($mode, $listParam,
                        \Eccube\Entity\Order $order,
                        \Plugin\GmoPaymentGateway\Controller\DataObj\PaymentExtension $paymentInfo,
                        \Eccube\Application $app)
    {

        switch ($mode) {
            case 'next':
                // 決済実行
                $objClient = new \Plugin\GmoPaymentGateway\Service\client\PG_MULPAY_Client_PayEasy($app);
                //$objClient = new PG_MULPAY_Client_PayEasy_Ex();
                $result = $objClient->doPaymentRequest($order, $listParam, $paymentInfo);
                if ($result) {
                    $results = $objClient->getResults();
                    $order_status = $app['config']['order_pay_wait'];
                    $OrderStatus = $app['eccube.repository.order_status']->find($order_status);
                    $order->setOrderStatus($OrderStatus);
                    $app['orm.em']->persist($order);
                    $app['orm.em']->flush();
                    $app['eccube.service.cart']->clear()->save();
                    $this->setReceiptNo($results['EncryptReceiptNo']);
                    $this->orderId = $order->getId();
                    $this->isComplete = true;
                    return true;

                } else {
                    $error = $objClient->getError();
                    $this->setErrors($error);
                    return false;
                }

                break;
            case 'return':
                $order->setDelFlg(1);
                $order_status = $app['config']['order_cancel'];
                $OrderStatus = $app['eccube.repository.order_status']->find($order_status);
                $app['orm.em']->getRepository('Eccube\Entity\Order')->changeStatus($order->getId(), $OrderStatus);
                break;
            default:
                break;
        }

        return false;
    }

    /**
     * get array of errors
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * set errors
     * @param  $errors
     */
    public function setErrors($errors)
    {
        return $this->errors = $errors;
    }

    /**
     * get the encrypted receipt number
     * @return string
     */
    public function getReceiptNo()
    {
        return $this->receiptNo;
    }

    /**
     * set the encrypted receipt number
     * @param  $number
     */
    public function setReceiptNo($number)
    {
        return $this->receiptNo = $number;
    }

}

?>
