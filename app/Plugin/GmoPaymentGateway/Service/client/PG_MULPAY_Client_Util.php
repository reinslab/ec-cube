<?php
/*
 * Copyright(c) 2015 GMO Payment Gateway, Inc. All rights reserved.
 * http://www.gmo-pg.com/
 */

namespace Plugin\GmoPaymentGateway\Service\client;

use Plugin\GmoPaymentGateway\Controller\DataObj\PaymentExtension;
use Plugin\GmoPaymentGateway\Controller\Util\PaymentUtil;
use Plugin\GmoPaymentGateway\Controller\Util\PluginUtil;


/**
 * 決済モジュール 決済処理: 各種取引処理
 */
class PG_MULPAY_Client_Util extends PG_MULPAY_Client_Base
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
        $this->app = $app;
        $this->const = $app['config']['GmoPaymentGateway']['const'];
    }


    function saveOrderCard($orderExtension, $listParam = array(), $seqMode=false)
    {

        //$arrCustomer = Helper_Customer_Ex::sfGetCustomerData($orderExtension['customer_id']);
        $objClientMember = new PG_MULPAY_Client_Member($this->app);
        $ret = $objClientMember->getMember($orderExtension);

        if (!$ret) {
            $objClientMember->saveMember($orderExtension);
        }
        $objMdl =& PluginUtil::getInstance($this->app);
        //$objMdl =& PG_MULPAY_Ex::getInstance();

        $gmoSetting = $objMdl->getUserSettings();

        $server_url = $gmoSetting['server_url'] . 'TradedCard.idPass';

        // Use logical card seq
        if (!$seqMode){
            $sendKey = array(
                'ShopID',
                'ShopPass',
                'OrderID',
                'SiteID',
                'SitePass',
                'MemberID',                
                'DefaultFlag',
                'HolderName',
            );
        } else { // Use physical card seq
            $sendKey = array(
                'ShopID',
                'ShopPass',
                'OrderID',
                'SiteID',
                'SitePass',
                'MemberID',
                'SeqMode',
                'DefaultFlag',
                'HolderName',
            );            
        }
        

        $listParam['DefaultFlag'] = '1'; // 最終登録したカードをデフォルトに
        $PaymentExtension = new \Plugin\GmoPaymentGateway\Controller\DataObj\PaymentExtension();
        $orderID = '';
        $getPaymentData = $orderExtension->getPaymentData();
        if (isset($getPaymentData['OrderID']) && !empty($getPaymentData['OrderID'])) {
            $orderID = $getPaymentData['OrderID'];
        }
        $orderExtension->setOrderID($orderID);

        $arrSendData = $this->getSendData($sendKey, $orderExtension, $listParam, $PaymentExtension, $gmoSetting);

        $ret = $this->sendRequest($server_url, $arrSendData);

        $error = $this->getError();
        if (!empty($error)) {
            return false;
        }
        return true;
    }

    function getOrderInfo($orderExtension)
    {
        $orderGmoInfo = $orderExtension->getGmoOrderPayment();

        $objMdl =& PluginUtil::getInstance($this->app);
        $gmoSetting = $objMdl->getUserSettings();

        $objUtil = new PaymentUtil($this->app);

        $memo03 = $orderGmoInfo->getMemo03();
        if ($memo03 == $this->const['PG_MULPAY_PAYID_CREDIT']
            || $memo03 == $this->const['PG_MULPAY_PAYID_REGIST_CREDIT']
            || $memo03 == $this->const['PG_MULPAY_PAYID_CREDIT_CHECK']
            || $memo03 == $this->const['PG_MULPAY_PAYID_CREDIT_SAUTH']
            //Token
            || $memo03 == $this->const['PG_MULPAY_PAYID_TOKEN']
        ) {

            $server_url = $gmoSetting['server_url'] . 'SearchTrade.idPass';
            $sendKey = array(
                'ShopID',
                'ShopPass',
                'OrderID',
            );
        } else {
            $server_url = $gmoSetting['server_url'] . 'SearchTradeMulti.idPass';
            $sendKey = array(
                'Version',
                'ShopID',
                'ShopPass',
                'OrderID',
                'PayType',
            );

        }

        $listParam = array();
        if (is_null($orderGmoInfo->getId())) {
            $msg = '決済履歴がありません。';
            $objMdl->printLog($msg);
            $this->setError($msg);
            return false;
        }
        if (array_search('PayType', $sendKey) !== FALSE && !empty($memo03)) {
            $listParam['PayType'] = $objUtil->getPayTypeFromPayId($memo03);
        }

        $paymentInfo = $objUtil->getPaymentTypeConfig($orderExtension->getOrder()->getPayment()->getId());
        $arrSendData = $this->getSendData($sendKey, $orderExtension, $listParam, $paymentInfo, $gmoSetting);

        $ret = $this->sendRequest($server_url, $arrSendData);
        
        $error = $this->getError();
        if (!empty($error)) {
            return false;
        }
        
        $results = $this->getResults();
        $results['action_status'] = $this->const['PG_MULPAY_ACTION_STATUS_EXEC_SUCCESS'];
        
        if (isset($this->const['PG_MULPAY_PAY_STATUS_' . $results['Status']])) {
            $results['pay_status'] = $this->const['PG_MULPAY_PAY_STATUS_' . $results['Status']];
        } else if (!is_null($results['Status'])) {
            $results['pay_status'] = $this->const['PG_MULPAY_PAY_STATUS_EXCEPT'];
        }
        
        $objUtil->setOrderPayData($orderGmoInfo, $results);
        return true;
    }

    function changeOrder($orderExtension)
    {
        $order = $orderExtension->getOrder();
        $orderGmoInfo = $orderExtension->getGmoOrderPayment();
        $getPaymentData = $orderExtension->getPaymentData();

        $objMdl =& PluginUtil::getInstance($this->app);
        $gmoSetting = $objMdl->getUserSettings();

        $objUtil = new PaymentUtil($this->app);
        $memo03 = $orderGmoInfo->getMemo03();
        if ($memo03 == $this->const['PG_MULPAY_PAYID_CREDIT']
            || $memo03 == $this->const['PG_MULPAY_PAYID_REGIST_CREDIT']
            || $memo03 == $this->const['PG_MULPAY_PAYID_CREDIT_CHECK']
            || $memo03 == $this->const['PG_MULPAY_PAYID_CREDIT_SAUTH']
            //Token    
            || $memo03 == $this->const['PG_MULPAY_PAYID_TOKEN']
        ) {

            $term = '180';

            $server_url = $gmoSetting['server_url'] . 'ChangeTran.idPass';

            $sendKey = array(
                'ShopID',
                'ShopPass',
                'AccessID',
                'AccessPass',
                'JobCd',
                'Amount',
            );

        } else if ($memo03 == $this->const['PG_MULPAY_PAYID_RAKUTEN_ID']) {

            // Check various conditions for rakutenID
            if (!$this->checkConditionForRakutenID($getPaymentData, $orderGmoInfo, $objMdl)) return false;

            $server_url = $gmoSetting['server_url'] . 'RakutenIdChange.idPass';
            $sendKey = array(
                'VerSion',
                'ShopID',
                'ShopPass',
                'AccessID',
                'AccessPass',
                'OrderID',
                'Amount',
                'Tax'
            );

        } else {
            $msg = '決済金額変更エラー：金額変更処理に対応していない決済です。';
            $objMdl->printLog($msg);
            $this->setError($msg);
            return false;
        }

        // declare listParam to set data
        $listParam = array();

        // get tranDate
        $getPaymentData = $orderExtension->getPaymentData();
        if ($memo03 != $this->const['PG_MULPAY_PAYID_RAKUTEN_ID']) {
            if (empty($getPaymentData['TranDate'])) {
                $msg = '決済処理エラー：処理日付が不足しています。';
                $objMdl->printLog($msg);
                return false;
            }
            sscanf($getPaymentData['TranDate'], '%04d%02d%02d%02d%02d%02d', $year, $month, $day, $hour, $min, $sec);
            // compare TranDate with term
            if (strtotime('+' . $term . ' days', mktime($hour, $min, $sec, $month, $day, $year)) < time()) {
                $msg = '決済処理エラー：金額変更期限を越えています。(決済日から' . $term . '日以内)';
                $objMdl->printLog($msg);
                $this->setError($msg);
                return false;
            }
        }

        // get jobCd
        $paymentInfo = $objUtil->getPaymentTypeConfig($orderExtension->getOrder()->getPayment()->getId());
        $getArrPaymentConfig = $paymentInfo->getArrPaymentConfig();
        if (array_search('JobCd', $sendKey) !== FALSE) {
            $listParam['JobCd'] = $getArrPaymentConfig['JobCd'];
        }

        $objUtil->setOrderPayData($orderGmoInfo, array('no_update_status_flg' => '1'));


        $arrSendData = $this->getSendData($sendKey, $orderExtension, $listParam, $paymentInfo, $gmoSetting);
        $ret = $this->sendRequest($server_url, $arrSendData);

        $error = $this->getError();
        if (!empty($error)) {
            return false;
        }

        $results = $this->getResults();

        $results['action_status'] = $this->const['PG_MULPAY_ACTION_STATUS_EXEC_SUCCESS'];

        if (!empty($listParam['JobCd'])) {
            $results['pay_status'] = $this->const['PG_MULPAY_PAY_STATUS_' . $listParam['JobCd']];
        } else {
            $results['pay_status'] = $this->const['PG_MULPAY_PAY_STATUS_AUTH'];
        }

        if (array_search('JobCd', $sendKey) !== FALSE) {
            $results['JobCd'] = $listParam['JobCd'];
        }

        if (empty($results['Amount'])) {
            $results['Amount'] = $orderExtension->getOrder()->getPaymentTotal();
        }

        $objUtil->setOrderPayData($orderGmoInfo, $results);

        return true;
    }

    function commitOrder($orderExtension)
    {

        $objMdl =& PluginUtil::getInstance($this->app);
        $objUtil = new PaymentUtil($this->app);
        $gmoSetting = $objMdl->getUserSettings();

        $order = $orderExtension->getOrder();
        $orderGmoInfo = $orderExtension->getGmoOrderPayment();
        $getPaymentData = $orderExtension->getPaymentData();
        $paymentInfo = $objUtil->getPaymentTypeConfig($order->getPayment()->getId());
        $target_term_days = '90';

        $memo03 = $orderGmoInfo->getMemo03();
        if ($memo03 == $this->const['PG_MULPAY_PAYID_CREDIT']
            || $memo03 == $this->const['PG_MULPAY_PAYID_REGIST_CREDIT']
            //Token
            || $memo03 == $this->const['PG_MULPAY_PAYID_TOKEN']
        ) {

            $server_url = $gmoSetting['server_url'] . 'AlterTran.idPass';

            $sendKey = array(
                'ShopID',
                'ShopPass',
                'AccessID',
                'AccessPass',
                'JobCd',
                'Amount',
            );
        } else if ($memo03 == $this->const['PG_MULPAY_PAYID_RAKUTEN_ID']) {

            // Check various conditions for rakutenID
            if ($orderGmoInfo->getMemo04() != $this->const['PG_MULPAY_PAY_STATUS_AUTH']) {
                $msg = '決済処理エラー：取引の現状態に対して、処理可能な操作ではありません。';
                $objMdl->printLog($msg);
                $this->setError($msg);
                return false;
            }
            if (!$this->checkConditionForRakutenID($getPaymentData, $orderGmoInfo, $objMdl)) return false;
            $server_url = $gmoSetting['server_url'] . 'RakutenIdSales.idPass';
            // $target_term_days = '60';
            $sendKey = array(
                'VerSion',
                'ShopID',
                'ShopPass',
                'AccessID',
                'AccessPass',
                'OrderID'
            );

        } 
        else {
            $msg = '決済確定エラー：確定処理に対応していない決済です。';
            $objMdl->printLog($msg);
            $this->setError($msg);
            return false;
        }

        $listParam = array();
        $getPaymentData = $orderExtension->getPaymentData();
        if ($memo03 != $this->const['PG_MULPAY_PAYID_RAKUTEN_ID']) {
            if (empty($getPaymentData['TranDate'])) {
                $msg = '決済処理エラー：処理日付が不足しています。';
                $objMdl->printLog($msg);
                return false;
            }
            sscanf($getPaymentData['TranDate'], '%04d%02d%02d%02d%02d%02d', $year, $month, $day, $hour, $min, $sec);

            if (strtotime('+' . $target_term_days . ' days', mktime($hour, $min, $sec, $month, $day, $year)) > time()) {
                if (array_search('JobCd', $sendKey) !== FALSE) {
                    $listParam['JobCd'] = 'SALES';
                }
            } else {
                $msg = '決済確定前エラー：確定期限を越えています。(決済日から' . $target_term_days . '日以内)';
                $objMdl->printLog($msg);
                $this->setError($msg);
                return false;
            }
        }

        $arrSendData = $this->getSendData($sendKey, $orderExtension, $listParam, $paymentInfo, $gmoSetting);

        $ret = $this->sendRequest($server_url, $arrSendData);

        $error = $this->getError();
        if (!empty($error)) {
            return false;
        }

        $results = $this->getResults();
        $results['action_status'] = $this->const['PG_MULPAY_ACTION_STATUS_EXEC_SUCCESS'];
        $results['pay_status'] = $this->const['PG_MULPAY_PAY_STATUS_COMMIT'];

        $objUtil->setOrderPayData($orderGmoInfo, $results);

        return true;
    }

    function reauthOrder($orderExtension)
    {
        $objMdl =& PluginUtil::getInstance($this->app);
        $objUtil = new PaymentUtil($this->app);
        $gmoSetting = $objMdl->getUserSettings();
        $server_url = $gmoSetting['server_url'] . 'AlterTran.idPass';

        $order = $orderExtension->getOrder();
        $orderGmoInfo = $orderExtension->getGmoOrderPayment();
        $paymentInfo = $objUtil->getPaymentTypeConfig($order->getPayment()->getId());
        $getPaymentData = $orderExtension->getPaymentData();
        if ($getPaymentData['pay_status'] != $this->const['PG_MULPAY_PAY_STATUS_VOID']
            && $getPaymentData['pay_status'] != $this->const['PG_MULPAY_PAY_STATUS_RETURN']
            && $getPaymentData['pay_status'] != $this->const['PG_MULPAY_PAY_STATUS_RETURNX']
        ) {
            $msg = '決済エラー：取り消されていない注文は再オーソリ出来ません。';
            $objMdl->printLog($msg);
            $this->setError($msg);
            return false;
        }

        $memo03 = $orderGmoInfo->getMemo03();
        if (!($memo03 == $this->const['PG_MULPAY_PAYID_CREDIT']
            || $memo03 == $this->const['PG_MULPAY_PAYID_REGIST_CREDIT']
            || $memo03 == $this->const['PG_MULPAY_PAYID_CREDIT_CHECK']
            || $memo03 == $this->const['PG_MULPAY_PAYID_CREDIT_SAUTH']
            //Token
            || $memo03 == $this->const['PG_MULPAY_PAYID_TOKEN'])
        ) {
            $msg = '決済エラー：再オーソリはクレジットカード決済のみ対応しています。';
            $objMdl->printLog($msg);
            $this->setError($msg);
            return false;
        }

        $sendKey = array(
            'ShopID',
            'ShopPass',
            'AccessID',
            'AccessPass',
            'JobCd',
            'Amount',
            'Method',
            'PayTimes'
        );

        $listParam = array();
        $getArrPaymentConfig = $paymentInfo->getArrPaymentConfig();
        $getPaymentData = $orderExtension->getPaymentData();
        $listParam['JobCd'] = $getArrPaymentConfig['JobCd'];
        $listParam['Method'] = $getPaymentData['Method'];
        $listParam['PayTimes'] = $getPaymentData['PayTimes'];
        $objUtil->setOrderPayData($orderGmoInfo, array('no_update_status_flg' => '1'));

        $arrSendData = $this->getSendData($sendKey, $orderExtension, $listParam, $paymentInfo, $gmoSetting);

        $ret = $this->sendRequest($server_url, $arrSendData);

        $error = $this->getError();
        if (!empty($error)) {
            return false;
        }

        $results = $this->getResults();

        $results['action_status'] = $this->const['PG_MULPAY_ACTION_STATUS_EXEC_SUCCESS'];
        $getArrPaymentConfig = $paymentInfo->getArrPaymentConfig();
        if (!empty($getArrPaymentConfig['JobCd'])) {
            $constantCode = 'PG_MULPAY_PAY_STATUS_' . $getArrPaymentConfig['JobCd'];
            $results['pay_status'] = $this->const[$constantCode];
        } else {
            $results['pay_status'] = $this->const['PG_MULPAY_PAY_STATUS_AUTH'];
        }

        $results['JobCd'] = $getArrPaymentConfig['JobCd'];
        if (!isset($results['Amount'])) {
            $results['Amount'] = $order->getPaymentTotal();
        }

        //PluginUtil_Ex::setOrderPayData($orderExtension, $results);
        $objUtil->setOrderPayData($orderGmoInfo, $results);
        return true;
    }

    // 返品
    function cancelOrder($orderExtension)
    {
        //$objMdl =& PG_MULPAY_Ex::getInstance();
        $objMdl =& PluginUtil::getInstance($this->app);
        $objUtil = new PaymentUtil($this->app);
        $gmoSetting = $objMdl->getUserSettings();

        // $orderExtension = $objUtil->getOrderPayData($orderExtension['order_id']);
        $order = $orderExtension->getOrder();
        $orderGmoInfo = $orderExtension->getGmoOrderPayment();
        $getPaymentData = $orderExtension->getPaymentData();
        $paymentInfo = $objUtil->getPaymentTypeConfig($order->getPayment()->getId());

        $memo03 = $orderGmoInfo->getMemo03();
        if ($memo03 == $this->const['PG_MULPAY_PAYID_CREDIT']
            || $memo03 == $this->const['PG_MULPAY_PAYID_REGIST_CREDIT']
            || $memo03 == $this->const['PG_MULPAY_PAYID_CREDIT_CHECK']
            || $memo03 == $this->const['PG_MULPAY_PAYID_CREDIT_SAUTH']
            //Token
            || $memo03 == $this->const['PG_MULPAY_PAYID_TOKEN']
        ) {

            $term = '180';

            $server_url = $gmoSetting['server_url'] . 'AlterTran.idPass';

            $sendKey = array(
                'ShopID',
                'ShopPass',
                'AccessID',
                'AccessPass',
                'JobCd',
            );
        } else if ($memo03 == $this->const['PG_MULPAY_PAYID_RAKUTEN_ID']) {

            // Check various conditions for rakutenID
            if (!$this->checkConditionForRakutenID($getPaymentData, $orderGmoInfo, $objMdl)) return false;

            $server_url = $gmoSetting['server_url'] . 'RakutenIdCancel.idPass';

            $sendKey = array(
                'VerSion',
                'ShopID',
                'ShopPass',
                'AccessID',
                'AccessPass',
                'OrderID'
            );

        } 
        else {
            $msg = '決済キャンセル・返品エラー：キャンセル・返品処理に対応していない決済です。';
            $this->setError($msg);
            $objMdl->printLog($msg);
            return false;
        }

        $listParam = array();
        if ($memo03 != $this->const['PG_MULPAY_PAYID_RAKUTEN_ID']) {
            if (empty($getPaymentData['TranDate'])) {
                $msg = '決済処理エラー：処理日付が不足しています。';
                $objMdl->printLog($msg);
                return false;
            }
            sscanf($getPaymentData['TranDate'], '%04d%02d%02d%02d%02d%02d', $year, $month, $day, $hour, $min, $sec);
            if (date('Ymd') == sprintf('%04d%02d%02d', $year, $month, $day)) {
                if (array_search('JobCd', $sendKey) !== FALSE) {
                    $listParam['JobCd'] = 'VOID';
                }
            } else if (date('Ym') == sprintf('%04d%02d', $year, $month)) {
                if (array_search('JobCd', $sendKey) !== FALSE) {
                    $listParam['JobCd'] = 'RETURN';
                }
            } else {
                if (strtotime('+' . $term . ' days', mktime($hour, $min, $sec, $month, $day, $year)) > time()) {
                    if (array_search('JobCd', $sendKey) !== FALSE) {
                        $listParam['JobCd'] = 'RETURNX';
                        if (isset($getPaymentData['Status']) && $getPaymentData['Status'] == 'AUTH') {
                            $listParam['JobCd'] = 'RETURN';
                        }
                    }
                } else {
                    $msg = '決済変更エラー：取消期限を越えています。(決済日から' . $term . '日以内)';
                    $objMdl->printLog($msg);
                    $this->setError($msg);
                    return false;
                }
            }
            if ($listParam['JobCd'] == 'RETURNX' && $orderGmoInfo->getMemo04() == $this->const['PG_MULPAY_PAY_STATUS_AUTH']) {
                $listParam['JobCd'] = 'RETURN';
            }
        }

        $arrSendData = $this->getSendData($sendKey, $orderExtension, $listParam, $paymentInfo, $gmoSetting);

        $ret = $this->sendRequest($server_url, $arrSendData);

        $error = $this->getError();
        if (!empty($error)) {
            return false;
        }

        $results = $this->getResults();

        if (array_search('JobCd', $sendKey) !== FALSE) {
            $results['JobCd'] = $listParam['JobCd'];
        }

        $results['action_status'] = $this->const['PG_MULPAY_ACTION_STATUS_EXEC_SUCCESS'];
        if (array_search('JobCd', $sendKey) === FALSE && empty($listParam['JobCd'])) {
            if ($memo03 == $this->const['PG_MULPAY_PAYID_RAKUTEN_ID']){
                $listParam['JobCd'] = 'REQCANCEL';
            } else {
                $listParam['JobCd'] = 'CANCEL';
            }
        }
        $constantCode = 'PG_MULPAY_PAY_STATUS_' . $listParam['JobCd'];
        $results['pay_status'] = $this->const[$constantCode];

        $objUtil->setOrderPayData($orderGmoInfo, $results);
        return true;
    }

    /**
     * Check some conditions for RakutenID payment
     *
     * @param $paymentData   paymentInfo of the order(memo05)
     * @param $orderGmoInfo  the order's memos
     * @param $objMdl        Instance of PluginUtil, to print log if there is error
     *
     * @return boolean
     */
    public function checkConditionForRakutenID($paymentData, $orderGmoInfo, $objMdl) {
        //Check if the transaction is registered as temporary sale
        if ($paymentData['JobCd'] !== 'AUTH') {
            $msg = '決済処理エラー：指定した処理区分の処理は実行出来ません（仮売として登録した取引であること）。';
            $objMdl->printLog($msg);
            $this->setError($msg);
            return false;
        }

        // Base on the order's status, validate the datetime appropriately
        $paymentStatus = $orderGmoInfo->getMemo04();
        if ($paymentStatus == $this->const['PG_MULPAY_PAY_STATUS_AUTH']) {
            // The order is not finalized yet

            // Extract the OrderDate
            if (empty($paymentData['OrderDate'])) {
                $msg = '決済処理エラー：注文日が不足しています。';
                $objMdl->printLog($msg);
                return false;
            }
            sscanf($paymentData['OrderDate'], '%04d%02d%02d%02d%02d%02d', $year, $month, $day, $hour, $min, $sec);

            if (strtotime('last day of fifth month', mktime(23, 59, 59, $month, $day, $year)) < time()) {
                $msg = '決済処理エラー：取消期限を越えています(注文確定前の取引の場合、注文日の５ヵ月後の末日迄であること)。';
                $objMdl->printLog($msg);
                $this->setError($msg);
                return false;
            }
        } else if ($paymentStatus == $this->const['PG_MULPAY_PAY_STATUS_COMMIT'] or 
                   $paymentStatus == $this->const['PG_MULPAY_PAY_STATUS_SALES'] or 
                   $paymentStatus == $this->const['PG_MULPAY_PAY_STATUS_CAPTURE']) {
            // The order is finalized

            // Extract the CompletionDate
            if (empty($paymentData['CompletionDate'])) {
                $msg = '決済処理エラー：注文確定の処理完了日が不足しています。';
                $objMdl->printLog($msg);
                return false;
            }
            sscanf($paymentData['CompletionDate'], '%04d%02d%02d', $year, $month, $day);

            if (strtotime('last day of next month', mktime(23, 59, 59, $month, $day, $year)) < time()) {
                $msg = '決済処理エラー：取消期限を越えています(注文確定後の取引の場合、注文確定の処理完了日から翌月末日迄であること)。';
                $objMdl->printLog($msg);
                $this->setError($msg);
                return false;
            }
        } else {
            $msg = '決済処理エラー：取引の現状態に対して、処理可能な操作ではありません。';
            $objMdl->printLog($msg);
            $this->setError($msg);
            return false;
        }

        return true;
    }

}
