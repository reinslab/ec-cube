<?php
/*
 * Copyright(c) 2015 GMO Payment Gateway, Inc. All rights reserved.
 * http://www.gmo-pg.com/
 */

namespace Plugin\GmoPaymentGateway\Service\client;

require_once(__DIR__ . "/../../vendor/autoload.php");

use Guzzle\Service\Client;
use Plugin\GmoPaymentGateway\Controller\DataObj\OrderExtension;
use Plugin\GmoPaymentGateway\Controller\Util\ErrorUtil;
use Plugin\GmoPaymentGateway\Controller\Util\PaymentUtil;
use Plugin\GmoPaymentGateway\Controller\Util\CommonUtil;
use Plugin\GmoPaymentGateway\Controller\Util\PluginUtil;

use Symfony\Component\Yaml\Yaml;

/**
 * 決済モジュール 決済処理 基底クラス
 */
class PG_MULPAY_Client_Base
{

    public $error = array();
    public $results = null;
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

    function getSendData($sendKey, OrderExtension $orderInfo, $listParam, \Plugin\GmoPaymentGateway\Controller\DataObj\PaymentExtension $paymentInfo, $gmoSetting)
    {   
    
        $objUtilPg = new PaymentUtil($this->app);
        $dataSend = array();
        $memo03 = '';
        // Payment configuration (dtb_gmo_payment_method#memo05)
        if(!is_null($paymentInfo)){
            $paymentConfig = $paymentInfo->getArrPaymentConfig();
            $gmoPaymentMethod = $paymentInfo->getGmoPaymentMethod();
            if (!is_null($gmoPaymentMethod)) {
                $memo03 = $gmoPaymentMethod->getMemo03();
            }
        }
        
        $order = $orderInfo->getOrder();
        $customerId = '';
        if(is_null($order)){
            $customerId = $orderInfo->getCustomer()->getId();
        } else if (!is_null($order->getCustomer())) {
            $customerId = $order->getCustomer()->getId();
        }
        $paymentData = $orderInfo->getPaymentData();
        
        foreach ($sendKey as $key) {
            switch ($key) {
                case 'ShopID':
                case 'ShopPass':
                    $dataSend[$key] = $gmoSetting[$key];
                    break;
                case 'SiteID':
                    $dataSend[$key] = $gmoSetting['site_id'];
                    break;
                case 'SitePass':
                    $dataSend[$key] = $gmoSetting['site_pass'];
                    break;
                case 'ClientField3':
                    // ※この部分の表記などについて修正・削除等、一切の変更は絶対に行わないで下さい。
                    // 問題発生時の調査や解決などに支障が出るため、変更された場合はサポート等が
                    // 出来ない場合がございます。
                    
                    // Get plugin version
                    $plugin = Yaml::parse(__DIR__ . '/../../config.yml');
                    $version = $plugin['version'];
        
                    $dataSend[$key] = 'EC-CUBE3(' . $version . ')';
                    // 修正不可ここまで
                    break;
                case 'CancelAmount':
                case 'Amount':
                case 'FirstAmount':
                    $dataSend[$key] = $order->getPaymentTotal();
                    break;
                case 'TdTenantName':
                    $dataSend[$key] = $objUtilPg->convTdTenantName($paymentConfig[$key]);
                    break;
                case 'Expire':
                    $dataSend[$key] = $listParam['expire_year'] . $listParam['expire_month'];
                    break;
                case 'Method':
                    if (strpos($listParam[$key], '-') !== false) {
                        list($id, $num) = explode('-', $listParam[$key]);
                        $dataSend[$key] = $id;
                        if ($num > 0) {
                            $dataSend['PayTimes'] = $num;
                        }
                    } else {
                        $dataSend[$key] = $listParam[$key];
                    }
                    break;
                case 'ClientFieldFlag':
                    $dataSend[$key] = '1';
                    break;
                case 'TdFlag':
                    $dataSend[$key] = $paymentConfig[$key];
                    break;
                case 'HttpAccept':
                    if (isset($_SERVER['HTTP_ACCEPT'])) {
                        $dataSend[$key] = $_SERVER['HTTP_ACCEPT'];
                    }
                    break;
                case 'HttpUserAgent':
                    if (isset($_SERVER['HTTP_USER_AGENT'])) {
                        $dataSend[$key] = $_SERVER['HTTP_USER_AGENT'];
                    }
                    break;
                case 'DeviceCategory':
                    $dataSend[$key] = '0';
                    break;
                case 'MemberName':
                    if(is_null($order)){
                        if (!is_null($orderInfo->getCustomer()->getSecretKey())) {
                        $dataSend[$key] = $orderInfo->getCustomer()->getSecretKey();
                        } else if (!is_null($orderInfo->getCustomer()->getId()) && $orderInfo->getCustomer()->getId() != '0') {
                            $Customer = $this->app['orm.em']->getRepository('Eccube\\Entity\\Customer')
                                ->findOneBy(array(
                                    'id' => $orderInfo->getCustomer()->getId(),
                                    'del_flg' => 0,
                                ));
                            $dataSend[$key] = $Customer->getSecretKey();
                        }    
                    }
                    else{
                        if (!is_null($order->getCustomer()->getSecretKey())) {
                            $dataSend[$key] = $order->getCustomer()->getSecretKey();
                        } else if (!is_null($order->getCustomer()->getId()) && $order->getCustomer()->getId() != '0') {
                            $Customer = $this->app['orm.em']->getRepository('Eccube\\Entity\\Customer')
                                ->findOneBy(array(
                                    'id' => $order->getCustomer()->getId(),
                                    'del_flg' => 0,
                                ));
                            $dataSend[$key] = $Customer->getSecretKey();
                        }
                    }
                    break;
                case 'CustomerName':
                    $dataSend[$key] = $objUtilPg->convCVSText($order->getName01() . $order->getName02());
                    break;
                case 'CustomerKana':
                    $dataSend[$key] = $objUtilPg->convCVSText($order->getKana01() . $order->getKana02());
                    break;
                case 'TelNo':
                    $dataSend[$key] = $order->getTel01() . '' . $order->getTel02() . '' . $order->getTel03();
                    break;
                case 'MailAddress':
                    
                    if ($memo03 == $this->const['PG_MULPAY_PAYID_CVS'] && $paymentConfig['enable_mail'] == '1' && !is_null($paymentConfig['enable_cvs_mails']) && array_search($listParam['Convenience'], $paymentConfig['enable_cvs_mails']) !== FALSE) {
                        // CVS決済の場合は、個別のコンビニ設定も確認する。
                        $dataSend[$key] = $order->getEmail();
                    } else if ($memo03 == $this->const['PG_MULPAY_PAYID_MOBILESUICA'] || $memo03 == $this->const['PG_MULPAY_PAYID_MOBILEEDY']) {
                        if (!is_null($listParam['MailAddress'])) {
                            $dataSend[$key] = $listParam['MailAddress'];
                        } else {
                            $dataSend[$key] = $order->getEmail();
                        }
                    } else if ($paymentConfig['enable_mail'] == '1' && $memo03 != $this->const['PG_MULPAY_PAYID_CVS']) {
                        if (isset($listParam['MailAddress']) && !is_null($listParam['MailAddress'])) {
                            $dataSend[$key] = $listParam['MailAddress'];
                        } else {
                            $dataSend[$key] = $order->getEmail();
                        }
                    }
                    break;
                case 'ShopMailAddress':
                    //$arrSiteInfo = Helper_DB_Ex::sfGetBasisData();
                    $arrSiteInfo = $this->app['eccube.repository.base_info']->get();
                    $dataSend[$key] = $arrSiteInfo->getEmail01();
                    break;
                case 'ReserveNo':
                    $dataSend[$key] = $order->getId(); //['order_id'];
                    break;
                case 'MemberNo':
                    if(empty($customerId)){
                        $dataSend[$key] = '';
                    } else {
                        $dataSend[$key] = $customerId;
                    }
                    break;
                case 'MemberID':
                    if(empty($customerId)){
//                        $dataSend[$key] = 0;
                        $dataSend[$key] = '';
                    } else {
                        /* Create Gmo memeber id from customer id
                         * Only apply for payment method RegistCredit and CVS
                         */

                        $GmoMemberId = CommonUtil::getGmoMemberId($customerId, $this->app);
                        if (is_null($GmoMemberId)) {
                            // Create new member id
                            $GmoMemberId = CommonUtil::createGmoMemberId($customerId, $this->app, true);
                            $Customer = $this->app['eccube.repository.customer']->findOneBy(array('id'=>$customerId));
                            // Save member id into dtb_gmo_member
                            $this->app['eccube.plugin.gmo_pg.repository.gmo_member']->updateOrCreate($this->app, $Customer, $GmoMemberId);
                            $this->app['orm.em']->flush();
                        }

                        $dataSend[$key] = $GmoMemberId;
                        
                    }
                    break;
                case 'ServiceTel':
                    $dataSend[$key] = $paymentConfig['ServiceTel_1'] . '' . $paymentConfig['ServiceTel_2'] . '' . $paymentConfig['ServiceTel_3'];
                    break;
                case 'ReceiptsDisp12':
                    $dataSend[$key] = $paymentConfig['ReceiptsDisp12_1'] . '' . $paymentConfig['ReceiptsDisp12_2'] . '' . $paymentConfig['ReceiptsDisp12_3'];
                    break;
                case 'ReceiptsDisp13':
                    $dataSend[$key] = sprintf('%02d', $paymentConfig['ReceiptsDisp13_1']) . ':' . sprintf('%02d', $paymentConfig['ReceiptsDisp13_2']) . '-' . sprintf('%02d', $paymentConfig['ReceiptsDisp13_3']) . ':' . sprintf('%02d', $paymentConfig['ReceiptsDisp13_4']);
                    break;
                case 'ItemName':
                case 'Commodity':
                    if ($memo03 == $this->const['PG_MULPAY_PAYID_AUCONTINUANCE']) {
                        $dataSend[$key] = $paymentConfig[$key];
                    } else {
                        $dataSend[$key] = $this->getItemName($order->getId());
                    }
                    break;
                case 'RedirectURL':
                   
//                if (Display_Ex::detectDevice() === DEVICE_TYPE_MOBILE
//                        || $paymentInfo[PG_MULPAY_PAYMENT_COL_PAYID] == PG_MULPAY_PAYID_DOCOMO) {
//                    // PC→携帯があるためドコモの場合セッションＩＤ付与 PCと携帯でセッション名が異なる問題がある
////                    $arrSendData[$key] .= '&' . session_name() . '=' . session_id();
//                     $arrSendData[$key] .= '&' . ini_get("session.name") . '=' . session_id();
//                }
                    break;
                case 'CreateMember':
                    $dataSend[$key] = '1';
                    break;
                case 'HolderName':
                    if (!isset($listParam['card_name1']) && isset($listParam['HolderName'])) {
                        $dataSend[$key] = $listParam['HolderName'];
                    } else {
                            $dataSend[$key] = $listParam['card_name1'] . ' ' . $listParam['card_name2'];
                        }
                    break;
                case 'FirstAccountDate':
                    $arrPluginConfig = $objUtilPg->getPluginConfig('PgCarrierSubs');
                    if (!is_null($arrPluginConfig)) {
                        if ($arrPluginConfig['is_first_free'] == '1') {
                            $term = '+1 months';
                            if (date('d') > 28) {
                                $term .= ' -' . (date('d') - 28) . 'days';
                            }
                            $dataSend[$key] = date('Ymd', strtotime($term));
                        } else {
                            $dataSend[$key] = date('Ymd');
                        }
                    }
                case 'OrderID':
                    $dataSend[$key] = $orderInfo->getOrderID();
                    break;
                case 'Tax':
                    $dataSend[$key] = '';
                    break;
                case 'Version':
                case 'VerSion':
                    $dataSend[$key] = '';
                    break;
                case 'ItemId':
                    $OrderDetail = $order->getOrderDetails();
                    $ItemId = $OrderDetail[0]->getProduct()->getId();
                    $dataSend[$key] = $ItemId;
                    break;
                case 'ItemSubId':
                    $dataSend[$key] = '';
                    break;
                case 'PaymentTermSec':
                    $dataSend[$key] = '';
                    break;
                case 'Token':
                    $dataSend[$key] = $listParam['token'];
                    break;
                case 'RetURL':
                    if ($memo03 == $this->const['PG_MULPAY_PAYID_RAKUTEN_ID']) {
                        if (isset($this->app['env']) && $this->app['env'] == 'test') {
                            $dataSend[$key] = $this->app['config']['root_urlpath'].'/shopping/rakutenResult/1';
                        } else {
                            $redirectUrl = \Plugin\GmoPaymentGateway\Controller\Util\CommonUtil::getUrl().'/shopping/rakutenResult/1';
                            $dataSend[$key] = \Plugin\GmoPaymentGateway\Controller\Util\CommonUtil::rmDupSlash($redirectUrl);
                        }
                    }
                    break;
                case 'ErrorRcvURL':
                    if ($memo03 == $this->const['PG_MULPAY_PAYID_RAKUTEN_ID']) {
                        if (isset($this->app['env']) && $this->app['env'] == 'test') {
                            $dataSend[$key] = $this->app['config']['root_urlpath'].'/shopping/rakutenResult/0';
                        } else {
                            $redirectUrl = \Plugin\GmoPaymentGateway\Controller\Util\CommonUtil::getUrl().'/shopping/rakutenResult/0';
                            $dataSend[$key] = \Plugin\GmoPaymentGateway\Controller\Util\CommonUtil::rmDupSlash($redirectUrl);
                        }
                    }
                    break;
                case 'SeqMode':
                    $dataSend[$key] = '1';
                    break;
                case 'CardSeq':
                    if (isset($listParam['CardSeq'])) {
                        $dataSend[$key] = $listParam['CardSeq'];
                    }
                    break;
                default:
                    if (isset($listParam[$key])) {
                        $dataSend[$key] = $listParam[$key];
                    } elseif (isset($paymentData[$key])) {
                        $dataSend[$key] = $paymentData[$key];
                    } elseif (isset($paymentConfig[$key])) {
                        $dataSend[$key] = $paymentConfig[$key];
                    } elseif (isset($gmoSetting[$key])) {
                        $dataSend[$key] = $gmoSetting[$key];
                    } elseif (isset($this->results[0][$key])) {
                        $dataSend[$key] = $this->results[0][$key];
                    }
            }
        }
        return $dataSend;
    }

    function getItemName($order_id)
    {
        $objUtilPg = new PaymentUtil($this->app);
        $OrderDetails = $this->app['eccube.repository.order']->findOneBy(array('id' => $order_id))->getOrderDetails();
        $ret = '';
        $ret = $OrderDetails[0]->getProductName();
        $ret = $objUtilPg->convertProhibitedKigo($ret);
        $ret = $objUtilPg->convertProhibitedChar($ret);
        $ret = mb_convert_kana($ret, 'KVSA', 'UTF-8');

        $ret = $objUtilPg->subString($ret, $this->const['SUICA_ITEM_NAME_LEN']);
        return $ret;
    }

    function sendOrderRequest($url, $sendKey, $order_id, $listParam, \Plugin\GmoPaymentGateway\Controller\DataObj\PaymentExtension $paymentInfo, $gmoSetting)
    {

        $reqParam = $listParam;
        $objUtil = new PaymentUtil($this->app);

        // Get order
        if (false !== strpos($order_id, "-")) {
            $OrderID = $order_id;
            list($split_orderId, $split_orderTime) = explode("-", $OrderID);
            $OrderExtension = $objUtil->getOrderPayData($split_orderId);
        } else {
            // Get payment authen data if existings
            $OrderID = '';
            $OrderExtension = $objUtil->getOrderPayData($order_id);
            $paymentData = $OrderExtension->getPaymentData();
            if (!isset($paymentData['OrderID']) || is_null($paymentData['OrderID'] || empty($paymentData['OrderID']))) {
                $OrderID = $OrderExtension->getOrder()->getId() . '-' . date('dHis');
            } else { // In case existed OrderID
                $OrderID = $paymentData['OrderID'];
            }
        }

        $OrderExtension->setOrderID($OrderID);
        $paymentConfig = $paymentInfo->getArrPaymentConfig();
        $arrSendData = $this->getSendData($sendKey, $OrderExtension, $listParam, $paymentInfo, $gmoSetting);
        if ($objUtil->isSubscription()) { // Check purchase product with regular type
            if (isset($paymentConfig['JobCd']) && !is_null($paymentConfig['JobCd'])) {
                $paymentConfig['JobCd'] = 'CHECK';
            }
            if (array_key_exists('JobCd', $arrSendData) !== FALSE) { // find out jobCd keys
                $arrSendData['JobCd'] = 'CHECK';
            }
        }
        $ret = $this->sendRequest($url, $arrSendData);
        if ($ret) {
            $listParam = $this->getResults();
        } else {
            $listParam = array();
            $listParam[0]['request_error'] = $this->getError();
        }

        $listParam[0]['OrderID'] = $OrderID;
        $listParam[0]['Amount'] = $OrderExtension->getOrder()->getPaymentTotal();

        if (isset($paymentConfig['JobCd']) && !is_null($paymentConfig['JobCd'])) {
            $listParam[0]['JobCd'] = $paymentConfig['JobCd'];
        }

        if (isset($reqParam['CardSeq']) && !is_null($reqParam['CardSeq'])) {
            $listParam[0]['CardSeq'] = $reqParam['CardSeq'];
        }

        if (!is_null($reqParam['action_status'])) {
            $listParam[0]['action_status'] = $reqParam['action_status'];
        }
        if (isset($reqParam['pay_status']) && !is_null($reqParam['pay_status'])) {
            $listParam[0]['pay_status'] = $reqParam['pay_status'];
        }

        $error = $this->getError();
        if (!is_null($reqParam['success_pay_status']) && empty($error)) {
            $listParam[0]['pay_status'] = $reqParam['success_pay_status'];
        } else if (!is_null($reqParam['fail_pay_status']) && !empty($error)) {
            $listParam[0]['pay_status'] = $reqParam['fail_pay_status'];
        }

        $objUtil->setOrderPayData($OrderExtension->getGmoOrderPayment(), $listParam);

        if (!empty($this->error)) {
            return false;
        }
        // 成功時のみ表示用データの構築
        $this->setOrderPaymentViewData($OrderExtension, $listParam, $paymentInfo);

        return true;
    }

    /**
     * 旧コンビニコードを新コンビニコードに変換する
     *
     * @return array 配列データ
     */
    function convOld2NewCVSCD($data) {
        if (isset($data['Convenience']) &&
            !is_null($data['Convenience'])) {
            switch ($data['Convenience']) {
            case $this->const['CONVENI_LOSON']:
                $data['Convenience'] = $this->const['CONVENI_LOSON_NEW'];
                break;

            case $this->const['CONVENI_FAMILYMART']:
                $data['Convenience'] = $this->const['CONVENI_FAMILYMART_NEW'];
                break;

            case $this->const['CONVENI_MINISTOP']:
                $data['Convenience'] = $this->const['CONVENI_MINISTOP_NEW'];
                break;

            default:
                break;
            }
        }

        return $data;
    }

    /**
     * 新コンビニコードを旧コンビニコードに変換する
     *
     * @return array 配列データ
     */
    function convNew2OldCVSCD($data) {
        for ($i = 0; $i < count($data); ++$i) {
            if (isset($data[$i]['Convenience']) &&
                !is_null($data[$i]['Convenience'])) {
                switch ($data[$i]['Convenience']) {
                case $this->const['CONVENI_LOSON_NEW']:
                    $data[$i]['Convenience'] = $this->const['CONVENI_LOSON'];
                    break;

                case $this->const['CONVENI_FAMILYMART_NEW']:
                    $data[$i]['Convenience'] =
                        $this->const['CONVENI_FAMILYMART'];
                    break;

                case $this->const['CONVENI_MINISTOP_NEW']:
                    $data[$i]['Convenience'] = $this->const['CONVENI_MINISTOP'];
                    break;

                default:
                    break;
                }
            }
        }

        return $data;
    }

    function sendRequest($url, $dataSend)
    {
        // コンビニコードの新旧コード変換を行う
        $dataSend = $this->convOld2NewCVSCD($dataSend);

        $objMdl = new PaymentUtil($this->app);

        $listData = array();
        foreach ($dataSend as $key => $value) {
            $listData[$key] = mb_convert_encoding($value, 'SJIS-win', 'UTF-8');
        }
      
        //$client = new Client();
        $client = new Client(null, array('curl.options' => array('CURLOPT_SSLVERSION' => 6)));
        $request = $client->post($url, array(), $listData);
        $response = $request->send();

        $r_code = $response->getStatusCode();
        switch ($r_code) {
            case 200:
                break;
            case 404:
                $msg = 'レスポンスエラー:RCODE:' . $r_code;
                $this->setError($msg);
                return false;
                break;
            case 500:
            default:
                $msg = '決済サーバーエラー:RCODE:' . $r_code;
                $this->setError($msg);
                return false;
                break;
        }

        $response_body = $response->getBody(true);

        if (is_null($response_body)) {
            $msg = 'レスポンスデータエラー: レスポンスがありません。';
            $this->setError($msg);
            return false;
        }


        $arrRet = $this->parseResponse($response_body);
        $this->setResults($arrRet);
        if (!empty($this->error)) {
            return false;
        }
        return true;
    }

    function setError($msg)
    {
        $this->error[] = $msg;
    }

    function getError()
    {

        return $this->error;
    }

    /**
     * レスポンスを解析する
     *
     * @param string $string レスポンス
     * @return array 解析結果
     */
    function parseResponse($string)
    {
        $arrRet = array();
        $string = trim($string);
        $objMdl = new \Plugin\GmoPaymentGateway\Controller\Util\PluginUtil($this->app);

        if (strpos($string, 'ACS=1') === 0) {
            $regex = '|^ACS=1&ACSUrl\=(.+?)&PaReq\=(.+?)&MD\=(.+?)$|';
            $ret = preg_match_all($regex, $string, $matches);
            if ($ret !== false && $ret > 0) {
                $arrRet[0]['ACS'] = '1';
                $arrRet[0]['ACSUrl'] = $matches[1][0];
                $arrRet[0]['PaReq'] = $matches[2][0];
                $arrRet[0]['MD'] = $matches[3][0];
            } else {
                $this->setError('本人認証サービスの実行に失敗しました。');
                $msg = '-> 3D response failed: ' . $string;
            }
        } else {
            $arrTmpAnd = explode('&', $string);
            foreach ($arrTmpAnd as $eqString) {
                // $eqString -> CardSeq=2|0|1, DefaultFlag=0|0|0...
                list($key, $val) = explode('=', $eqString);
                if (strpos($key, '<') !== FALSE || strpos($key, '>') !== FALSE) {
                    $this->setError('不正な返答が返されました。接続先を確認して下さい。');
                    continue;
                }

                // $val -> 2|0|1, 0|0|0, ...
                if (preg_match('/|/', $val)) {
                    $arrTmpl = explode('|', $val);
                    $max = count($arrTmpl);
                    for ($i = 0; $i < $max; $i++) {
                        $arrRet[$i][$key] = trim($arrTmpl[$i]);
                    }
                    // $val -> 2, 0, 1...
                } else {
                    $arrRet[0][$key] = trim($val);
                }
            }
        }
        if (isset($arrRet[0]['ErrCode'])) {
            $this->setError($this->createErrCode($arrRet));
        }
        return $arrRet;
    }

    /**
     * エラーコード文字列を構築する
     *
     * @param array $arrRet
     * @return string
     */
    function createErrCode($arrRet)
    {

        $objErrMsg = new ErrorUtil($this->app);
        $msg = '';
        foreach ($arrRet as $key => $ret) {
            if (is_array($ret)) {
                $errorMsg = $objErrMsg->lfGetErrorInformation($ret['ErrInfo']);
                $error_text = empty($errorMsg['message']) ? $errorMsg['context'] : $errorMsg['message'];
                $msg .= $error_text . '(' . sprintf('%s-%s', $ret['ErrCode'], $ret['ErrInfo']) . '),';
            } else if ($key == 'ErrInfo') {
                if (preg_match('/|/', $ret)) {
                    $arrTmplInfo = explode('|', $ret);
                    $arrTmplCode = explode('|', $arrRet['ErrCode']);
                } else {
                    $arrTmplInfo = array($ret);
                    $arrTmplCode = array($ret['ErrCode']);
                }
                foreach ($arrTmplInfo as $key2 => $err) {
                    $errorMsg = $objErrMsg->lfGetErrorInformation($err);
                    $error_text = Utils_Ex::isBlank($errorMsg['message']) ? $errorMsg['context'] : $errorMsg['message'];
                    $msg .= $error_text . '(' . sprintf('%s-%s', $arrTmplCode[$key2], $arrTmplInfo[$key2]) . '),';
                }
            }
        }
        $msg = substr($msg, 0, strlen($msg) - 1); // 最後の,をカット
        return $msg;
    }

    function setResults($results)
    {
        // コンビニコードの新旧コード変換を行う
        $results = $this->convNew2OldCVSCD($results);

        $this->results = $results;
    }

    function getResults()
    {
        if (is_null($this->results[0]) && !is_null($this->results)) {
            return $this->results;
        }
        return $this->results[0];
    }

    function setOrderPaymentViewData(\Plugin\GmoPaymentGateway\Controller\DataObj\OrderExtension &$orderInfo, $listParam, \Plugin\GmoPaymentGateway\Controller\DataObj\PaymentExtension $paymentInfo)
    {

        $arrPaymentConfig = $paymentInfo->getArrPaymentConfig();
        $gmoOrderPaymentData = $orderInfo->getGmoOrderPayment();


        $listData = array();
        $results = $this->getResults();

        if (isset($results['Approve']) && !is_null($results['Approve'])) {
            $listData['Approve']['name'] = '承認番号';
            $listData['Approve']['value'] = $results['Approve'];
        }

        if (isset($results['CustID']) && !is_null($results['CustID'])) {
            $listData['CustID']['name'] = 'お客様番号';
            $listData['CustID']['value'] = $results['CustID'];
        }

        if (isset($results['BkCode']) && !is_null($results['BkCode'])) {
            $listData['BkCode']['name'] = '収納機関番号';
            $listData['BkCode']['value'] = $results['BkCode'];
        }

        if (isset($results['ConfNo']) && !is_null($results['ConfNo'])) {
            $listData['ConfNo']['name'] = '確認番号';
            $listData['ConfNo']['value'] = $results['ConfNo'];
        }

        if (isset($results['ReceiptNo']) && !is_null($results['ReceiptNo'])) {
            $listData['ReceiptNo']['name'] = '受付番号';
            $listData['ReceiptNo']['value'] = $results['ReceiptNo'];
        }

        if (isset($results['EdyOrderNo']) && !is_null($results['EdyOrderNo'])) {
            $listData['EdyOrderNo']['name'] = 'Edy注文番号';
            $listData['EdyOrderNo']['value'] = $results['EdyOrderNo'];
        }

        if (isset($results['ManagementNo']) && !is_null($results['ManagementNo'])) {
            $listData['ManagementNo']['name'] = '管理番号';
            $listData['ManagementNo']['value'] = $results['ManagementNo'];
        }

        if (isset($results['PayInfoNo']) && !is_null($results['PayInfoNo'])) {
            $listData['PayInfoNo']['name'] = '決済情報番号';
            $listData['PayInfoNo']['value'] = $results['PayInfoNo'];
        }

        if (isset($results['ReceiptUrl']) && !is_null($results['ReceiptUrl'])) {
            $listData['ReceiptUrl']['name'] = '払込票URL';
            $listData['ReceiptUrl']['value'] = $results['ReceiptUrl'];
        }

        if (isset($results['PaymentTerm']) && !is_null($results['PaymentTerm'])) {
            $listData['PaymentTerm']['name'] = 'お支払い期限';
            $year = substr($results['PaymentTerm'], 0, 4);
            $month = substr($results['PaymentTerm'], 4, 2);
            $day = substr($results['PaymentTerm'], 6, 2);
            $hour = substr($results['PaymentTerm'], 8, 2);
            $min = substr($results['PaymentTerm'], 10, 2);
            $sec = substr($results['PaymentTerm'], 12, 2);
            $listData['PaymentTerm']['value'] = $year . '年' . $month . '月' . $day . '日 ' . $hour . '時' . $min . '分' . "\n\n";
        }

        if (isset($arrPaymentConfig['order_mail_title1']) && isset($arrPaymentConfig['order_mail_body1'])) {
            $listData['order_mail_title1']['name'] = $arrPaymentConfig['order_mail_title1'];
            $listData['order_mail_title1']['value'] = $arrPaymentConfig['order_mail_body1'];
        }

        if (isset($results['Convenience']) && !is_null($results['Convenience'])) {
            $title_key = 'order_mail_title_' . $results['Convenience'];
            $body_key = 'order_mail_body_' . $results['Convenience'];
            if (!is_null($arrPaymentConfig[$title_key]) && !is_null($arrPaymentConfig[$body_key])) {
                $listData[$title_key]['name'] = $arrPaymentConfig[$title_key];
                $listData[$title_key]['value'] = "\n\n" . $arrPaymentConfig[$body_key];
            }
        }

        if (!empty($listData)) {
            $listData['title']['value'] = '1';
            $listData['title']['name'] = $paymentInfo->getGmoPaymentMethod()->getMethod();
            $gmoOrderPaymentData->setMemo02(serialize($listData));

            $this->app['orm.em']->persist($gmoOrderPaymentData);
            $this->app['orm.em']->flush();
        }
    }

}
