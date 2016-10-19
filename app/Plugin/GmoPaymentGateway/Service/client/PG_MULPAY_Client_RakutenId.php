<?php
/**
 * 決済モジュール 決済処理: 楽天ID
 */
namespace Plugin\GmoPaymentGateway\Service\client;
use Plugin\GmoPaymentGateway\Controller\Util\PluginUtil ;

class PG_MULPAY_Client_RakutenId extends PG_MULPAY_Client_Base {
    
    /**
     * コンストラクタ
     *
     * @return void
     */
    function __construct(\Eccube\Application $app) {
        parent::__construct($app);
        $this->app = $app;
        $this->const = $app['config']['GmoPaymentGateway']['const'];
    }

    function doPaymentRequest(\Plugin\GmoPaymentGateway\Controller\DataObj\OrderExtension $OrderExtension, $listParam = array(),
                              \Plugin\GmoPaymentGateway\Controller\DataObj\PaymentExtension $PaymentExtension) {
        
      
        $pluginUtil =& PluginUtil::getInstance($this->app);
        $order = $OrderExtension->getOrder();
        $gmoSetting = $pluginUtil->getUserSettings();
        $server_url = $gmoSetting['server_url'] . 'EntryTranRakutenId.idPass';
        $sendKeys = array(
            'Version',
            'ShopID',
            'ShopPass',
            'OrderID',
            'JobCd',
            'Amount',
            'Tax',
        );

        $listParam['action_status'] = $this->const['PG_MULPAY_ACTION_STATUS_ENTRY_REQUEST'];
        $listParam['pay_status'] = $this->const['PG_MULPAY_PAY_STATUS_UNSETTLED'];
        $listParam['success_pay_status'] = '';
        $listParam['fail_pay_status'] = $this->const['PG_MULPAY_PAY_STATUS_FAIL'];
        $OrderID = $order->getId() . '-' . date('dHis');
        $ret = $this->sendOrderRequest($server_url, $sendKeys, $OrderID, $listParam, $PaymentExtension, $gmoSetting);
        if (!$ret) {
            return $ret;
        }
        $server_url = $gmoSetting['server_url'] . 'ExecTranRakutenId.idPass';
        $sendKeys = array(
            'Version',
            'ShopID',
            'ShopPass',
            'AccessID',
            'AccessPass',
            'OrderID',
            'ClientField1',
            'ClientField2',
            'ClientField3',
            'ItemId',
            'ItemSubId',
            'ItemName',
            'RetURL',
            'ErrorRcvURL',
            'PaymentTermSec',
        );
        $sendKeys[] = 'HttpAccept';
        $sendKeys[] = 'HttpUserAgent';
        $sendKeys[] = 'DeviceCategory';

        $listParam['action_status'] = $this->const['PG_MULPAY_ACTION_STATUS_EXEC_REQUEST'];
        $listParam['pay_status'] = '';
        $arrPaymentConfig = $PaymentExtension->getArrPaymentConfig();
        if (isset($arrPaymentConfig['JobCd'])) {
            $status_action = 'PG_MULPAY_PAY_STATUS_' . $arrPaymentConfig['JobCd'];            
            $listParam['success_pay_status'] = $this->const[$status_action];
        } else {
            $listParam['success_pay_status'] = $this->const['PG_MULPAY_PAY_STATUS_AUTH'];
        }
        $listParam['fail_pay_status'] = $this->const['PG_MULPAY_PAY_STATUS_FAIL'];

        if (isset($arrPaymentConfig['TdFlag']) && $arrPaymentConfig['TdFlag'] == '1') {
            $listParam['success_pay_status'] = $this->const['PG_MULPAY_PAY_STATUS_UNSETTLED'];
        }
      
        $ret = $this->sendOrderRequest($server_url, $sendKeys, $OrderExtension->getOrder()->getId(), $listParam, $PaymentExtension, $gmoSetting);
        return $ret;
    }

}
