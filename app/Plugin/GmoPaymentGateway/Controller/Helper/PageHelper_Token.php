<?php
/*
 * Copyright(c) 2015 GMO Payment Gateway, Inc. All rights reserved.
 * http://www.gmo-pg.com/
 */

namespace Plugin\GmoPaymentGateway\Controller\Helper;

use Plugin\GmoPaymentGateway\Controller\Util\PaymentUtil;
use Plugin\GmoPaymentGateway\Service\client\PG_MULPAY_Client_Token;
use Plugin\GmoPaymentGateway\Service\client\PG_MULPAY_Client_Util;

/**
 * 決済モジュール 決済画面ヘルパー：クレジット決済
 */
class PageHelper_Token
{

    protected $arrTdData;
    protected $tpl_url;
    protected $tpl_is_td_tran;
    protected $tpl_is_loding;
    protected $tpl_btn_next;
    protected $tpl_payment_onload;
    public $dataReturn;
    public $error;
    public $isComplete = false;
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
    function modeAction($mode, $listParam, \Eccube\Entity\Order $order, \Plugin\GmoPaymentGateway\Controller\DataObj\PaymentExtension $PaymentExtension, \Eccube\Application $app)
    {
        $this->app = $app;
        $objClient = new PG_MULPAY_Client_Token($app);
        $objUtil = new PaymentUtil($app);
        $this->dataReturn = array();
        $this->dataReturn['tpl_url'] = '';
        $this->dataReturn['tpl_is_td_tran'] = false;
        $this->dataReturn['tpl_is_loding'] = true;
        $this->dataReturn['tpl_btn_next'] = false;
        $this->dataReturn['tpl_payment_onload'] = '';
        $this->isComplete = false;
        $this->dataReturn['term_url'] = '';
        $this->dataReturn['arrTdData']['TermUrl'] = '';
        $this->dataReturn['tpl_pg_regist_card_max'] = false;
        $this->dataReturn['arrTdData'] = array();
        $this->dataReturn['arrTdData']['TermUrl'] = '';
        $this->dataReturn['arrTdData']['PaReq'] = '';
        $this->dataReturn['arrTdData']['PaRes'] = '';
        $this->error = array();
        $this->dataReturn['result'] = array();
        $this->dataReturn['registerCardFlg'] = false;
        $this->dataReturn['tpl_plg_pg_mulpay_is_subscription'] = false;
        $this->dataReturn['tpl_plg_pg_mulpay_subscription_name'] = '';
        $OrderExtension = $objUtil->getOrderPayData($order->getId());
        switch ($mode) {
            case 'next':
                // $result = $objClient->doTokenRequest($OrderExtension, $listParam, $PaymentExtension);
                $listParam['Method'] = $listParam['method'];
                $result = $objClient->doPaymentRequest($OrderExtension, $listParam, $PaymentExtension);
                if ($result) {
                    $results = $objClient->getResults();
                    $this->dataReturn['arrTdData'] = $results;

                    if (isset($results['ACS']) && $results['ACS'] == '1') {
                        $this->dataReturn['arrTdData'] = $results;
                        $this->dataReturn['arrTdData']['PaRes'] = '';
                        $this->dataReturn['tpl_url'] = $results['ACSUrl'];
                        $this->dataReturn['tpl_is_td_tran'] = true;
                        $this->dataReturn['tpl_is_loding'] = true;
                        $this->dataReturn['tpl_btn_next'] = true;
                        $this->dataReturn['tpl_payment_onload'] = true;
                    } else {
                        $order_status = $app['config']['order_new'];
                        $order->setOrderStatus($app['eccube.repository.order_status']->find($order_status));
                        $app['orm.em']->persist($order);
                        $app['orm.em']->flush();
                        
                        $this->orderId = $order->getId();
                        $app['eccube.service.cart']->clear()->save();

                        $this->isComplete = true;
                    }
                }
                else {
                    $error = $objClient->getError();
                    $this->error['payment'] = '※ 決済でエラーが発生しました。<br />' . implode('<br />', $error);
                }
                break;
            case 'tokenSecureTran':
                $objClient = new PG_MULPAY_Client_Token($app);

                $result = $objClient->doSecureTran($order->getId(), $listParam, $PaymentExtension);
                if ($result) {
                    $order_status = $app['config']['order_new'];
                    $order->setOrderStatus($app['eccube.repository.order_status']->find($order_status));
                    $app['orm.em']->persist($order);
                    $app['orm.em']->flush();
                    $app['eccube.service.cart']->clear()->save();
                    $this->orderId = $order->getId();
                    $this->isComplete = true;
                } else {
                    $error = $objClient->getError();
                    if (!empty($error)) {
                        $this->error['payment'] = '※ 決済でエラーが発生しました。<br />' . implode('<br />', $error);
                    }
                }
                break;
            
            default:
                break;
        }
    }
}
