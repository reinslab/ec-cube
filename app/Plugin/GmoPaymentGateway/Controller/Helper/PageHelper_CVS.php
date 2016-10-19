<?php
/*
 * Copyright(c) 2015 GMO Payment Gateway, Inc. All rights reserved.
 * http://www.gmo-pg.com/
 */

namespace Plugin\GmoPaymentGateway\Controller\Helper;

/**
 * 決済モジュール 決済画面ヘルパー：コンビニ決済
 */
class PageHelper_CVS
{

    public $isComplete = false;
    public $error;
    public $orderId;

    /**
     * 画面モード毎のアクションを行う
     *
     * @param text $mode Mode値
     * @param FormParam $objFormParam FormParam インスタンス
     * @param array $arrOrder 受注情報
     * @param Page $objPage 呼出元ページオブジェクト
     * @return void
     */
    function modeAction($mode, $listParam, \Eccube\Entity\Order $order, \Plugin\GmoPaymentGateway\Controller\DataObj\PaymentExtension $paymentInfo, \Eccube\Application $app)
    {
        $this->isComplete = false;
        $this->error['payment'] = '';
        $this->error['conveni'] = '';

        switch ($mode) {
            case 'next':

                $objClient = new \Plugin\GmoPaymentGateway\Service\client\PG_MULPAY_Client_CVS($app);
                $result = $objClient->doPaymentRequest($order, $listParam, $paymentInfo);

                if ($result) {
                    $order_status = $app['config']['order_pay_wait'];
                    $order->setOrderStatus($app['eccube.repository.order_status']->find($order_status));
                    $app['orm.em']->persist($order);
                    $app['orm.em']->flush();
                    $this->isComplete = true;
                    $app['eccube.service.cart']->clear()->save();
                    $this->orderId = $order->getId();
                } else {
                    $error = $objClient->getError();
                    $this->error['payment'] = '※ 決済でエラーが発生しました。<br />' . implode('<br />', $error);
                }

                break;

            default:

                break;
        }
    }

}

?>
