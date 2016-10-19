<?php
/*
 * Copyright(c) 2015 GMO Payment Gateway, Inc. All rights reserved.
 * http://www.gmo-pg.com/
 */

namespace Plugin\GmoPaymentGateway\Controller\Helper;

use Plugin\GmoPaymentGateway\Controller\Util\PaymentUtil;
use Plugin\GmoPaymentGateway\Controller\Util\PluginUtil;
use Plugin\GmoPaymentGateway\Service\client\PG_MULPAY_Client_Credit;
use Plugin\GmoPaymentGateway\Service\client\PG_MULPAY_Client_RegistCredit;

/**
 * 決済モジュール 決済画面ヘルパー：登録クレジット決済
 */
class PageHelper_RegistCredit
{
    public $arrTdData;
    public $tpl_url;
    public $tpl_is_td_tran;
    public $tpl_is_loding;
    public $tpl_btn_next;
    public $tpl_payment_onload;
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
    function modeAction($mode, $listParam,
                        \Eccube\Entity\Order $Order,
                        \Plugin\GmoPaymentGateway\Controller\DataObj\PaymentExtension $PaymentExtension,
                        \Eccube\Application $app)
    {
        $this->app = $app;

        $objUtil = new PaymentUtil($app);
        $this->dataReturn = array();
        $this->dataReturn['tpl_url'] = '';
        $this->dataReturn['tpl_is_td_tran'] = false;
        $this->dataReturn['tpl_is_loding'] = true;
        $this->dataReturn['tpl_btn_next'] = false;
        $this->dataReturn['tpl_payment_onload'] = '';
        $this->isComplete = false;
        $this->dataReturn['arrTdData']['TermUrl'] = '';
        $this->dataReturn['tpl_pg_regist_card_max'] = false;
        $this->dataReturn['arrTdData'] = array();
        $this->dataReturn['arrTdData']['TermUrl'] = '';
        $this->dataReturn['arrTdData']['PaReq'] = '';
        $this->dataReturn['arrTdData']['PaRes'] = '';
        $this->error = array();
        $this->error['payment'] = '';
        $this->error['CardSeq'] = '';
        $this->dataReturn['result'] = array();
        $this->dataReturn['registerCardFlg'] = false;
        $OrderExtension = $objUtil->getOrderPayData($Order->getId());
        
        $objMdl = &PluginUtil::getInstance($app);
        // In case of subscription, mark order mem06 = suscription
        $is_subscription = false;
        if ($objUtil->isSubscription()) {
            $is_subscription = true;
        }
        switch ($mode) {
            case 'next':
                $objClient = new PG_MULPAY_Client_RegistCredit($this->app);
                $result = $objClient->doPaymentRequest($OrderExtension, $listParam, $PaymentExtension, true);
                if ($result) {
                    $results = $objClient->getResults();
                    $this->dataReturn['arrTdData'] = $results;
                    if (isset($results['ACS']) && $results['ACS'] == '1') {
                        $this->dataReturn['arrTdData'] = $results;
                        $this->dataReturn['arrTdData']['PaRes'] = '';
                        $this->dataReturn['tpl_is_td_tran'] = true;
                        $this->dataReturn['tpl_is_loding'] = true;
                        $this->dataReturn['tpl_btn_next'] = true;
                        $this->dataReturn['tpl_payment_onload'] = true;

                    } else {
                        $order_status = $app['config']['order_new'];
                        $Order->setOrderStatus($app['eccube.repository.order_status']->find($order_status));
                        $app['orm.em']->persist($Order);
                        $app['orm.em']->flush();
                        // In case use scription get save subs order
                        if ($is_subscription) {
                            $cardSeq = $app['session']->get('eccube.plugin.gmo_pg.subs.card_seq');
                            $app['session']->remove('eccube.plugin.gmo_pg.subs.card_seq');
                            $objMdl->printLog("Get card_seq:".$cardSeq."\n");
                            if (!is_null($cardSeq)) {
                                $helperCommon = new \Plugin\GmoPaymentGatewaySubscription\Controller\Helper\Helper_Common($app);
                                $helperCommon->saveGmoData($OrderExtension, $cardSeq);
                                $helperCommon->createSubsData($OrderExtension);
                            }
                        }
                        $app['eccube.service.cart']->clear()->save();
                        $this->orderId = $Order->getId();
                        $this->isComplete = true;
                    }
                } else {
                    $error = $objClient->getError();
                    $this->error['payment'] = '※ 決済でエラーが発生しました。<br />' . implode('<br />', $error);
                }
                break;

            case 'SecureTran':
                $objClient = new PG_MULPAY_Client_Credit($app);
                $result = $objClient->doSecureTran($Order->getId(), $listParam, $PaymentExtension);
                if ($result) {
                    $order_status = $app['config']['order_new'];
                    $Order->setOrderStatus($app['eccube.repository.order_status']->find($order_status));
                    $app['orm.em']->persist($Order);
                    $app['orm.em']->flush();

                    // In case use scription get card_seq base on newest card
                    if ($is_subscription) {
                        // Regist newest card_seq into dtb_gmo_payment_order
                        $cardSeq = $app['session']->get('eccube.plugin.gmo_pg.subs.card_seq');
                        $app['session']->remove('eccube.plugin.gmo_pg.subs.card_seq');
                        $objMdl->printLog("Get card_seq:".$cardSeq."\n");
                        if (!is_null($cardSeq)) {
                            $helperCommon = new \Plugin\GmoPaymentGatewaySubscription\Controller\Helper\Helper_Common($app);
                            $helperCommon->saveGmoData($OrderExtension, $cardSeq);
                            // Create subscription data
                            $helperCommon->createSubsData($OrderExtension);
                        }
                    }
                    $app['eccube.service.cart']->clear()->save();
                    $this->orderId = $Order->getId();
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
