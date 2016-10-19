<?php
/*
 * Copyright(c) 2015 GMO Payment Gateway, Inc. All rights reserved.
 * http://www.gmo-pg.com/
 */


/**
 * 決済モジュール 決済処理: クレジットカード
 */
namespace Plugin\GmoPaymentGateway\Service\client;

use Plugin\GmoPaymentGateway\Controller\Util\PaymentUtil;
use Plugin\GmoPaymentGateway\Controller\Util\PluginUtil;

class PG_MULPAY_Client_Token extends PG_MULPAY_Client_Base
{

    /**
     * コンストラクタ
     *
     * @return void
     */
    function __construct(\Eccube\Application $app)
    {
        parent::__construct($app);
        $this->app = $app;
        $this->const = $app['config']['GmoPaymentGateway']['const'];
    }

    function doPaymentRequest(\Plugin\GmoPaymentGateway\Controller\DataObj\OrderExtension $OrderExtension, $listParam,
                              \Plugin\GmoPaymentGateway\Controller\DataObj\PaymentExtension $PaymentExtension)
    {
        $objMdl =& PluginUtil::getInstance($this->app);
        $orderGmoInfo = $OrderExtension->getGmoOrderPayment();
        $gmoSetting = $objMdl->getUserSettings();
        $is_pass = false;
        $mdl_pg_paydata = null;
        if (!empty($orderGmoInfo)) {
            $mdl_pg_paydata = $orderGmoInfo->getMemo05();
        }
        if (!is_null($mdl_pg_paydata)) {
            $arrPayData = unserialize($mdl_pg_paydata);

            if (isset($arrPayData['AccessID']) && isset($arrPayData['AccessPass'])) {
                $is_pass = true;
                $OrderExtension->setPaymentData((array)$arrPayData);
            }
        }

        $server_url = $gmoSetting['server_url'] . 'EntryTran.idPass';
        $sendKey = array(
            'ShopID',
            'ShopPass',
            'OrderID',
            'JobCd',
            'Amount',
            'TdFlag',
            'TdTenantName',
        );

        $listParam['action_status'] = $this->const['PG_MULPAY_ACTION_STATUS_ENTRY_REQUEST'];
        $listParam['pay_status'] = $this->const['PG_MULPAY_PAY_STATUS_UNSETTLED'];
        $listParam['success_pay_status'] = '';
        $listParam['fail_pay_status'] = $this->const['PG_MULPAY_PAY_STATUS_FAIL'];

        if (!$is_pass) {
            $ret = $this->sendOrderRequest($server_url, $sendKey, $OrderExtension->getOrder()->getId(), $listParam, $PaymentExtension, $gmoSetting);
            if (!$ret) {
                return $ret;
            }
        }
        $server_url = $gmoSetting['server_url'] . 'ExecTran.idPass';
        $sendKey = array(
            'AccessID',
            'AccessPass',
            'OrderID',
            'Method',
            'PayTimes',
            'Token',
            'ClientField1',
            'ClientField2',
            'ClientField3',
        );

        $sendKey[] = 'HttpAccept';
        $sendKey[] = 'HttpUserAgent';
        $sendKey[] = 'DeviceCategory';

        $listParam['action_status'] = $this->const['PG_MULPAY_ACTION_STATUS_EXEC_REQUEST'];
        $listParam['pay_status'] = '';
        $arrPaymentConfig = $PaymentExtension->getArrPaymentConfig();
        if (!is_null($arrPaymentConfig['JobCd'])) {
            $status_action = 'PG_MULPAY_PAY_STATUS_' . $arrPaymentConfig['JobCd'];
            $listParam['success_pay_status'] = $this->const[$status_action];
        } else {
            $listParam['success_pay_status'] = $this->const['PG_MULPAY_PAY_STATUS_AUTH'];
        }
        $listParam['fail_pay_status'] = $this->const['PG_MULPAY_PAY_STATUS_FAIL'];

        if ($arrPaymentConfig['TdFlag'] == '1') {
            $listParam['success_pay_status'] = $this->const['PG_MULPAY_PAY_STATUS_UNSETTLED'];
        }

        $ret = $this->sendOrderRequest($server_url, $sendKey, $OrderExtension->getOrder()->getId(), $listParam, $PaymentExtension, $gmoSetting);
        
        return $ret;
    }

    function doSecureTran($order_id, $listParam, $PaymentExtension)
    {

        $this->setResults($listParam);
        $objMdl =& PluginUtil::getInstance($this->app);
        $objUtil = new PaymentUtil($this->app);
        $OrderExtension = $objUtil->getOrderPayData($order_id);
        $orderGmoInfo = $OrderExtension->getGmoOrderPayment();
        $mdl_pg_paydata = null;

        if (!empty($orderGmoInfo)) {
            $mdl_pg_paydata = $orderGmoInfo->getMemo05();
        }
        if (!is_null($mdl_pg_paydata)) {
            $arrPayData = unserialize($mdl_pg_paydata);
        } else {
            $error_message = "3Dセキュア認証遷移エラー:決済データが受注情報に見つかりませんでした.";
            return $this->app['view']->render('error.twig', array("error_message" => $error_message));
        }

        if (isset($arrPayData['MD']) && $arrPayData['MD'] != $listParam['MD']) {
            $error_message = '3Dセキュア認証遷移エラー:取引ID(MD)が一致しませんでした。(' . $listParam['MD'] . ':' . $arrPayData['MD'] . ')';
            return $this->app['view']->render('error.twig', array("error_message" => $error_message));
        }

        if (!isset($listParam['PaRes']) || is_null($listParam['PaRes'])) {
            return false;
        }

        $gmoSetting = $objMdl->getUserSettings();

        $server_url = $gmoSetting['server_url'] . 'SecureTran.idPass';

        $sendKey = array(
            'PaRes',
            'MD',
        );

        $listParam['action_status'] = $this->const['PG_MULPAY_ACTION_STATUS_RECV_NOTICE'];
        if (isset($PaymentExtension->getArrPaymentConfig['JobCd']) && !is_null($PaymentExtension->getArrPaymentConfig['JobCd'])) {
            $listParam['success_pay_status'] = constant('PG_MULPAY_PAY_STATUS_' . $PaymentExtension->getArrPaymentConfig['JobCd']);
        } else {
            $listParam['success_pay_status'] = $this->const['PG_MULPAY_PAY_STATUS_AUTH'];
        }
        $listParam['fail_pay_status'] = $this->const['PG_MULPAY_PAY_STATUS_FAIL'];

        $ret = $this->sendOrderRequest($server_url, $sendKey, $order_id, $listParam, $PaymentExtension, $gmoSetting);

        return $ret;
    }

    

}
