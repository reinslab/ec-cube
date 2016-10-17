<?php
/*
 * Copyright(c) 2015 GMO Payment Gateway, Inc. All rights reserved.
 * http://www.gmo-pg.com/
 */

namespace Plugin\GmoPaymentGateway\Service\client;

use Plugin\GmoPaymentGateway\Controller\Util\PluginUtil;
use Plugin\GmoPaymentGateway\Controller\Util\PaymentUtil;
/**
 * 決済モジュール 決済処理: 登録クレジットカード
 */
class PG_MULPAY_Client_RegistCredit extends PG_MULPAY_Client_Credit
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
                              \Plugin\GmoPaymentGateway\Controller\DataObj\PaymentExtension $PaymentExtension, $physical_seq = false)
    {
        $objMdl =& PluginUtil::getInstance($this->app);
        $gmoSetting = $objMdl->getUserSettings();

        $server_url = $gmoSetting['server_url'] . 'EntryTran.idPass';
        $Order = $OrderExtension->getOrder();
        $orderId = $Order->getId();
        $sendKey = array(
            'ShopID',
            'ShopPass',
            'OrderID',
            'JobCd',
            'Amount',
            'TdFlag',
            'TdTenantName',
            'Method',
        );


        $listParam['action_status'] = $this->const['PG_MULPAY_ACTION_STATUS_ENTRY_REQUEST'];
        $listParam['pay_status'] = $this->const['PG_MULPAY_PAY_STATUS_UNSETTLED'];
        $listParam['success_pay_status'] = '';
        $listParam['fail_pay_status'] = $this->const['PG_MULPAY_PAY_STATUS_FAIL'];
        $OrderID = $orderId . '-' . date('dHis');
        $ret = $this->sendOrderRequest($server_url, $sendKey, $OrderID, $listParam, $PaymentExtension, $gmoSetting);

        if (!$ret) {
            return $ret;
        }

        $server_url = $gmoSetting['server_url'] . 'ExecTran.idPass';
        $sendKey = array(
            'AccessID',
            'AccessPass',
            'OrderID',
            'Method',
            'PayTimes',
            'SiteID',
            'SitePass',
            'MemberID',
            'CardSeq',
            'ClientField1',
            'ClientField2',
            'ClientField3',
            'ClientFieldFlag'
        );

        $sendKey[] = 'HttpAccept';
        $sendKey[] = 'HttpUserAgent';
        $sendKey[] = 'DeviceCategory';
        if($physical_seq){
            // get card with SeqCard is physical number, not logical number
            $sendKey[] = 'SeqMode';
        }
        $listParam['action_status'] = $this->const['PG_MULPAY_ACTION_STATUS_EXEC_REQUEST'];
        $listParam['pay_status'] = '';

        $arrPaymentConfig = $PaymentExtension->getArrPaymentConfig();
        $paymentUtil = new PaymentUtil($this->app);
        if ($paymentUtil->isSubscription()) { // Check purchase product with regular type
            if (isset($arrPaymentConfig['JobCd']) && !is_null($arrPaymentConfig['JobCd'])) {
                $arrPaymentConfig['JobCd'] = 'CHECK';
            }
        }
        if (isset($arrPaymentConfig['JobCd']) && !is_null($arrPaymentConfig['JobCd'])) {
            $status_action = 'PG_MULPAY_PAY_STATUS_' . $arrPaymentConfig['JobCd'];
            $listParam['success_pay_status'] = $this->const[$status_action];
        } else {
            $listParam['success_pay_status'] = $this->const['PG_MULPAY_PAY_STATUS_AUTH'];
        }
        $listParam['fail_pay_status'] = $this->const['PG_MULPAY_PAY_STATUS_FAIL'];
        if (!$paymentUtil->isSubscription()) {
            if ($arrPaymentConfig['TdFlag'] == '1') {
                $listParam['success_pay_status'] = $this->const['PG_MULPAY_PAY_STATUS_UNSETTLED'];
            }
        }
        $ret = $this->sendOrderRequest($server_url, $sendKey, $orderId, $listParam, $PaymentExtension, $gmoSetting);
        return $ret;

    }

}
