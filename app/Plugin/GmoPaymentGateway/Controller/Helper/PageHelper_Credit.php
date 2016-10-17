<?php
/*
 * Copyright(c) 2015 GMO Payment Gateway, Inc. All rights reserved.
 * http://www.gmo-pg.com/
 */

namespace Plugin\GmoPaymentGateway\Controller\Helper;

use Plugin\GmoPaymentGateway\Controller\Util\PaymentUtil;
use Plugin\GmoPaymentGateway\Controller\Util\PluginUtil;
use Plugin\GmoPaymentGateway\Service\client\PG_MULPAY_Client_Credit;
use Plugin\GmoPaymentGateway\Service\client\PG_MULPAY_Client_Util;

/**
 * 決済モジュール 決済画面ヘルパー：クレジット決済
 */
class PageHelper_Credit
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
        $objClient = new PG_MULPAY_Client_Credit($app);
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
        $is_subscription = false;
        $objMdl = &PluginUtil::getInstance($app);
        if ($objUtil->isSubscription()) {
            $is_subscription = true;
        }
        switch ($mode) {
            case 'next':
                $listParam['CardNo'] = $listParam['card_no'];
                $listParam['Method'] = $listParam['method'];
                // 決済実行
                $result = $objClient->doPaymentRequest($OrderExtension, $listParam, $PaymentExtension);
                if ($result) {
                    if (isset($listParam['register_card']) && $listParam['register_card'] == '1') {
                        $listData = array();
                        $listData[0]['register_card'] = '1';
                        $listData[0]['HolderName'] = $listParam['card_name1'] . ' ' . $listParam['card_name2'];
                        $gmoOrderPayment = $app['orm.em']->getRepository('\Plugin\GmoPaymentGateway\Entity\GmoOrderPayment')->findOneBy(array('id' => $order->getId()));
                        $this->dataReturn['registerCardFlg'] = true;
                        $objUtil->setOrderPayData($gmoOrderPayment, $listData);
                    }
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
                        if (isset($this->dataReturn['registerCardFlg']) && $this->dataReturn['registerCardFlg']) {
                            $OrderExtension = $objUtil->getOrderPayData($order->getId());
                            if ($is_subscription) {
                                $cardSeq = $this->registCard($OrderExtension, $listParam, true);
                                $objMdl->printLog("Get card_seq:".$cardSeq."\n");
                                if (!is_null($cardSeq)) {
                                    $helperCommon = new \Plugin\GmoPaymentGatewaySubscription\Controller\Helper\Helper_Common($app);
                                    $helperCommon->saveGmoData($OrderExtension, $cardSeq);
                                    // Create subscription data
                                    $helperCommon->createSubsData($OrderExtension);
                                }
                                
                            } else {
                                $cardSeq = $this->registCard($OrderExtension, $listParam, false);
                            }
                        }
                        $this->orderId = $order->getId();
                        $app['eccube.service.cart']->clear()->save();
                        $this->isComplete = true;
                    }
                } else {
                    $error = $objClient->getError();
                    $this->error['payment'] = '※ 決済でエラーが発生しました。<br />' . implode('<br />', $error);
                }
                break;
            case 'SecureTran':
                $objClient = new PG_MULPAY_Client_Credit($app);

                $result = $objClient->doSecureTran($order->getId(), $listParam, $PaymentExtension);
                if ($result) {
                    $order_status = $app['config']['order_new'];
                    $order->setOrderStatus($app['eccube.repository.order_status']->find($order_status));
                    $app['orm.em']->persist($order);
                    $app['orm.em']->flush();
                    $OrderExtension = $objUtil->getOrderPayData($order->getId());
                    $arrPayData = $OrderExtension->getPaymentData();
                    if ($arrPayData['register_card'] == '1') {
                        $cardSeq = $this->registCard($OrderExtension, $arrPayData, $is_subscription); 
                    }
                    // In case use scription get card_seq base on newest card
                    if ($is_subscription) {
                        $selected_card_seq = null;
                        if ($arrPayData['register_card'] == '1') {
                            $selected_card_seq = $cardSeq;
                        } else {
                            $selected_card_seq = $app['session']->get('eccube.plugin.gmo_pg.subs.card_seq');
                            $app['session']->remove('eccube.plugin.gmo_pg.subs.card_seq');
                        }
                        $objMdl->printLog("Get card_seq:".$selected_card_seq."\n");
                        if (!is_null($selected_card_seq)) {
                            $helperCommon = new \Plugin\GmoPaymentGatewaySubscription\Controller\Helper\Helper_Common($app);
                            $helperCommon->saveGmoData($OrderExtension, $selected_card_seq);
                            // Create subscription data
                            $helperCommon->createSubsData($OrderExtension);
                        }
                    }
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

    /**
     * Register Card
     * @param type $orderExtension
     * @param type $listParam
     * @param type $isSubscription
     * @return cardSeq Card sequence number
     */
    function registCard($orderExtension, $listParam = array(), $isSubscription = false)
    {
        $carSeq = null;
        $objClient = new PG_MULPAY_Client_Util($this->app);
        $objClient->saveOrderCard($orderExtension, $listParam, $isSubscription);
        $result = $objClient->getResults();
        if (isset($result['CardSeq'])){
            $carSeq = $result['CardSeq'];
        }
        return $carSeq;
    }

}
