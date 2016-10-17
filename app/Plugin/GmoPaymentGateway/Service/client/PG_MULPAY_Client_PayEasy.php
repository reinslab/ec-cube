<?php
/*
 * Copyright(c) 2015 GMO Payment Gateway, Inc. All rights reserved.
 * http://www.gmo-pg.com/
 */

namespace Plugin\GmoPaymentGateway\Service\client;

/**
 * 決済モジュール 決済処理: PayEasy
 */
class PG_MULPAY_Client_PayEasy extends PG_MULPAY_Client_Base
{

    protected $app;
    protected $const;

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


    function doPaymentRequest(\Eccube\Entity\Order $order, $listParam,
                              \Plugin\GmoPaymentGateway\Controller\DataObj\PaymentExtension $paymentInfo)
    {

        $objMdl =& \Plugin\GmoPaymentGateway\Controller\Util\PluginUtil::getInstance($this->app);
        $gmoSetting = $objMdl->getUserSettings();

        $server_url = $gmoSetting['server_url'] . 'EntryTranPayEasy.idPass';

        $sendKey = array(
            'ShopID',
            'ShopPass',
            'OrderID',
            'Amount',
        );

        $listParam['action_status'] = $this->const['PG_MULPAY_ACTION_STATUS_ENTRY_REQUEST'];
        $listParam['pay_status'] = $this->const['PG_MULPAY_PAY_STATUS_UNSETTLED'];
        $listParam['success_pay_status'] = '';
        $listParam['fail_pay_status'] = $this->const['PG_MULPAY_PAY_STATUS_FAIL'];

        $OrderID = $order->getId() . '-' . date('dHis');

        $ret = $this->sendOrderRequest($server_url, $sendKey, $OrderID, $listParam, $paymentInfo, $gmoSetting);

        if (!$ret) {
            return $ret;
        }

        $server_url = $gmoSetting['server_url'] . 'ExecTranPayEasy.idPass';
        $sendKey = array(
            'AccessID',
            'AccessPass',
            'OrderID',
            'CustomerName',
            'CustomerKana',
            'TelNo',
            'PaymentTermDay',
            'MailAddress',
            'ShopMailAddress',
            'RegisterDisp1',
            'RegisterDisp2',
            'RegisterDisp3',
            'RegisterDisp4',
            'RegisterDisp5',
            'RegisterDisp6',
            'RegisterDisp7',
            'RegisterDisp8',
            'ReceiptsDisp1',
            'ReceiptsDisp2',
            'ReceiptsDisp3',
            'ReceiptsDisp4',
            'ReceiptsDisp5',
            'ReceiptsDisp6',
            'ReceiptsDisp7',
            'ReceiptsDisp8',
            'ReceiptsDisp9',
            'ReceiptsDisp10',
            'ReceiptsDisp11',
            'ReceiptsDisp12',
            'ReceiptsDisp13',
            'ClientField1',
            'ClientField2',
            'ClientField3',
            'ClientFieldFlag'
        );

        $listParam['action_status'] = $this->const['PG_MULPAY_ACTION_STATUS_EXEC_REQUEST'];
        $listParam['pay_status'] = '';
        $listParam['success_pay_status'] = $this->const['PG_MULPAY_PAY_STATUS_REQUEST_SUCCESS'];
        $listParam['fail_pay_status'] = $this->const['PG_MULPAY_PAY_STATUS_FAIL'];

        $ret = $this->sendOrderRequest($server_url, $sendKey, $OrderID, $listParam, $paymentInfo, $gmoSetting);

        return $ret;
    }
}
