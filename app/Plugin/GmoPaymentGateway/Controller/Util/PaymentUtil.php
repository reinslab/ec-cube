<?php
/*
 * Copyright(c) 2015 GMO Payment Gateway, Inc. All rights reserved.
 * http://www.gmo-pg.com/
 */

namespace Plugin\GmoPaymentGateway\Controller\Util;

/**
 * 決済モジュール用 汎用関数クラス
 */
class PaymentUtil
{

    private $app;

    public function __construct(\Eccube\Application $app)
    {
        $this->app = $app;
    }

    function getJobCds($pay_id = PG_MULPAY_PAYID_CREDIT)
    {
        $arrJobCds = array(
            'CAPTURE' => '即時売上',
            'AUTH' => '仮売上',
            'SAUTH' => '簡易オーソリ',
            'CHECK' => '有効性チェック'
        );
        if ($pay_id != $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_CREDIT']
            && $pay_id != $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_REGIST_CREDIT']
        ) { // クレジットカード以外
            unset($arrJobCds['SAUTH']);
            unset($arrJobCds['CHECK']);
        }
        return $arrJobCds;
    }

    function getCreditPayMethod()
    {
        $arrPayMethod = array(
            '1-0' => '一括払い',
            '2-2' => '分割2回払い',
            '2-3' => '分割3回払い',
            '2-4' => '分割4回払い',
            '2-5' => '分割5回払い',
            '2-6' => '分割6回払い',
            '2-7' => '分割7回払い',
            '2-8' => '分割8回払い',
            '2-9' => '分割9回払い',
            '2-10' => '分割10回払い',
            '2-11' => '分割11回払い',
            '2-12' => '分割12回払い',
            '2-13' => '分割13回払い',
            '2-14' => '分割14回払い',
            '2-15' => '分割15回払い',
            '2-16' => '分割16回払い',
            '2-17' => '分割17回払い',
            '2-18' => '分割18回払い',
            '2-19' => '分割19回払い',
            '2-20' => '分割20回払い',
            '2-21' => '分割21回払い',
            '2-22' => '分割22回払い',
            '2-23' => '分割23回払い',
            '2-24' => '分割24回払い',
            '2-26' => '分割26回払い',
            '2-30' => '分割30回払い',
            '2-32' => '分割32回払い',
            '2-34' => '分割34回払い',
            '2-36' => '分割36回払い',
            '2-37' => '分割37回払い',
            '2-40' => '分割40回払い',
            '2-42' => '分割42回払い',
            '2-48' => '分割48回払い',
            '2-50' => '分割50回払い',
            '2-54' => '分割54回払い',
            '2-60' => '分割60回払い',
            '2-72' => '分割72回払い',
            '2-84' => '分割84回払い',
            '3-0' => 'ボーナス一括',
            '4-2' => 'ボーナス分割2回払い',
            '5-0' => 'リボ払い',
        );
        return $arrPayMethod;
    }

    function getConveni()
    {
        $arrCONVENI = array(
            $this->app['config']['GmoPaymentGateway']['const']['CONVENI_LOSON'] => 'ローソン',
            $this->app['config']['GmoPaymentGateway']['const']['CONVENI_FAMILYMART'] => 'ファミリーマート',
            $this->app['config']['GmoPaymentGateway']['const']['CONVENI_CIRCLEK'] => 'サークルKサンクス',
            $this->app['config']['GmoPaymentGateway']['const']['CONVENI_MINISTOP'] => 'ミニストップ',
            $this->app['config']['GmoPaymentGateway']['const']['CONVENI_DAILYYAMAZAKI'] => 'デイリーヤマザキ',
            $this->app['config']['GmoPaymentGateway']['const']['CONVENI_SEVENELEVEN'] => 'セブンイレブン'
        );
        return $arrCONVENI;
    }

    function getPayTypeFromPayId($paytype)
    {
        switch ($paytype) {
            case $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_MOBILESUICA']:
                return $this->app['config']['GmoPaymentGateway']['const']['MULPAY_PAYTYPE_MOBILESUICA'];
            case $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_MOBILEEDY']:
                return $this->app['config']['GmoPaymentGateway']['const']['MULPAY_PAYTYPE_MOBILEEDY'];
            case $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_CVS']:
                return $this->app['config']['GmoPaymentGateway']['const']['MULPAY_PAYTYPE_CVS'];
            case $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_PAYEASY']:
                return $this->app['config']['GmoPaymentGateway']['const']['MULPAY_PAYTYPE_PAYEASY'];
            case $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_PAYPAL']:
                return $this->app['config']['GmoPaymentGateway']['const']['MULPAY_PAYTYPE_PAYPAL'];
            case $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_IDNET']:
                return $this->app['config']['GmoPaymentGateway']['const']['MULPAY_PAYTYPE_IDNET'];
            case $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_WEBMONEY']:
                return $this->app['config']['GmoPaymentGateway']['const']['MULPAY_PAYTYPE_WEBMONEY'];
            case $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_AU']:
                return $this->app['config']['GmoPaymentGateway']['const']['MULPAY_PAYTYPE_AU'];
            case $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_DOCOMO']:
                return $this->app['config']['GmoPaymentGateway']['const']['MULPAY_PAYTYPE_DOCOMO'];
            case $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_SB']:
                return $this->app['config']['GmoPaymentGateway']['const']['MULPAY_PAYTYPE_SB'];
            case $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_RAKUTEN_ID']:
                return $this->app['config']['GmoPaymentGateway']['const']['MULPAY_PAYTYPE_RAKUTEN_ID'];
            case $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_CREDIT']:
            case $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_REGIST_CREDIT']:
            case $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_CREDIT_CHECK']:
            case $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_CREDIT_SAUTH']:
            case $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_TOKEN']:
            default:
                return $this->app['config']['GmoPaymentGateway']['const']['MULPAY_PAYTYPE_CREDIT'];
        }
    }

    function convCVSText($txt)
    {
        return mb_convert_kana($txt, 'KASV', 'UTF-8');
    }

    function convTdTenantName($shop_name)
    {
        if (empty($shop_name)) return '';
        $shop_name = mb_convert_encoding($shop_name, "EUC-JP", "UTF-8");
        $enc_name = base64_encode($shop_name);
        if (strlen($enc_name) <= 25) {
            return $enc_name;
        }
        return '';
    }

    function setOrderPayData(\Plugin\GmoPaymentGateway\Entity\GmoOrderPayment $gmoOrderPaymentData,
                             $listData, $is_only_log = false)
    {

        if (isset($listData[0]) and is_array($listData[0])) {
            $arrTemp = $listData[0];
            unset($listData[0]);
            $listData = array_merge((array)$listData, (array)$arrTemp);
        }
        foreach ($listData as $key => $val) {
            if (!$val || is_array($val) || preg_match('/^[\w\s]+$/i', $val)) {
                continue;
            }
            $char_code = $this->app['config']['char_code'];
            $temp = mb_convert_encoding($val, 'sjis-win', $char_code);
            $temp = mb_convert_encoding($temp, $char_code, 'sjis-win');
            if ($val !== $temp) {
                $temp = mb_convert_encoding($val, $char_code, 'sjis-win');
                $temp = mb_convert_encoding($temp, 'sjis-win', $char_code);
                if ($val === $temp) {
                    $listData[$key] = mb_convert_encoding($val, $char_code, 'sjis-win');
                } else {
                    $listData[$key] = 'unknown encoding strings';
                }
            }
        }

        $memo09 = $gmoOrderPaymentData->getMemo09();
        if (!empty($memo09)) {
            $arrLog = unserialize($memo09);
        } else {
            $arrLog = array();
        }
        $arrLog[] = array(date('Y-m-d H:i:s') => $listData);
        $gmoOrderPaymentData->setMemo09(serialize($arrLog));

        if (!$is_only_log) {
            $memo05 = $gmoOrderPaymentData->getMemo05();
            if (!empty($memo05)) {
                $arrPayData = unserialize($memo05);
            } else {
                $arrPayData = array();
            }
            foreach ($listData as $key => $val) {
                if (empty($val) && !empty($arrPayData[$key])) {
                    unset($listData[$key]);
                }
            }

            $arrPayData = array_merge($arrPayData, (array)$listData);

            $gmoOrderPaymentData->setMemo05(serialize($arrPayData));

            if (isset($listData['pay_status'])) {
                $gmoOrderPaymentData->setMemo04($listData['pay_status']);
            }
        }

        $this->app['orm.em']->persist($gmoOrderPaymentData);
        $this->app['orm.em']->flush();
    }

    function getOrderPayData($orderId)
    {
        $Order = $this->app['orm.em']->getRepository('Eccube\Entity\Order')
            ->find($orderId);

        if (is_null($Order)) {
            return false;
        }
        if ($Order->getDelFlg() == '1') {
            //TODO
//            $objMdl =& PG_MULPAY_Ex::getInstance();
//            $objMdl->printLog('getOrderPayData Error: deleted order. order_id = ' . $order_id);
            return false;
        }

        $gmoOrderPaymentRepo = $this->app['orm.em']->getRepository('\Plugin\GmoPaymentGateway\Entity\GmoOrderPayment');
        $orderGmoInfo = $gmoOrderPaymentRepo->findOneBy(array('id' => $orderId));

        if (is_null($orderGmoInfo)) {
            $orderGmoInfo = new \Plugin\GmoPaymentGateway\Entity\GmoOrderPayment();
            $orderGmoInfo->setId($orderId);
        }

        $getMemo05 = $orderGmoInfo->getMemo05();
        if (empty($getMemo05)) {
            $arrPayData = array();
        } else {
            $arrPayData = CommonUtil::unSerializeData($getMemo05);
        }

        $getMemo09 = $orderGmoInfo->getMemo09();
        if (empty($getMemo09)) {
            $arrPayData['payment_log'] = array();
        } else {
            $arrPayData['payment_log'] = CommonUtil::unSerializeData($getMemo09);
        }
        if (isset($arrPayData[0]) and is_array($arrPayData[0])) {
            $arrTemp = $arrPayData[0];
            unset($arrPayData[0]);
            $listData = array_merge((array)$listData, (array)$arrTemp);
        }

        $preOrderId = $Order->getPreOrderId();
        $arrPayData['preOrderId'] = array();
        if (!empty($preOrderId)) {
            $arrPayData['preOrderId'] = $preOrderId;
        }

        if (!isset($arrPayData['register_card'])) {
            $arrPayData['register_card'] = "";
        }

        $OrderExtension = new \Plugin\GmoPaymentGateway\Controller\DataObj\OrderExtension();
        $OrderExtension->setOrder($Order);
        $OrderExtension->setPaymentData($arrPayData);
        $OrderExtension->setGmoOrderPayment($orderGmoInfo);
        if (isset($arrPayData['OrderID'])) {
            $OrderExtension->setOrderID($arrPayData['OrderID']);
        }
        return $OrderExtension;
    }

    /**
     * 支払方法情報を取得する
     *
     * @param integer $paymentId 支払いID
     * @return array 支払方法情報。決済モジュール管理対象である場合、内部識別コードを同時に設定する
     */
    function getPaymentInfo($paymentId)
    {
        $Payment = $this->app['orm.em']->getRepository('Plugin\GmoPaymentGateway\Entity\GmoPaymentMethod')
            ->findOneBy(array('id' => $paymentId));
        if (empty($Payment)) {
            return false;
        }
       
        $PaymentExtension = new \Plugin\GmoPaymentGateway\Controller\DataObj\PaymentExtension();
        $PaymentExtension->setGmoPaymentMethod($Payment);

        // 決済モジュールの対象決済であるかの判断と内部識別コードの設定を同時に行う。
        $arrPaymentCode = $this->getPaymentTypeCodes();
        
        $PaymentExtension->setPaymentCode($arrPaymentCode[$Payment->getMemo03()]);

        return $PaymentExtension;
    }

    function getPaymentTypeConfig($payment_id)
    {
        $PaymentExtension = $this->getPaymentInfo($payment_id);
        $Payment = $PaymentExtension->getGmoPaymentMethod();
        $memo05 = $Payment->getMemo05();
        if (!empty($memo05)) {
            $arrTemp = unserialize($memo05);
            if ($arrTemp !== false) {
                $PaymentExtension->setArrPaymentConfig($arrTemp);

            }
        }
        return $PaymentExtension;
    }

    /**
     * Check enable regist credit
     * card_regist_flg = 1 利用する
     * card_regist_flg = 0 利用しない
     * @return boolean
     */
    function isRegistCardPaymentEnable()
    {
        $objMdl =& PluginUtil::getInstance($this->app);
        $subData = $objMdl->getUserSettings();
        if(isset($subData['card_regist_flg']) && $subData['card_regist_flg'] == $this->app['config']['GmoPaymentGateway']['const']['GMO_REGIST_FLG_ON']){
            return true;
        }
        return false;

    }

    function getPaymentStatus()
    {
        return array(
            $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAY_STATUS_UNSETTLED'] => '未決済',
            $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAY_STATUS_REQUEST_SUCCESS'] => '要求成功',
            $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAY_STATUS_REQSALES'] => '注文確定受付',
            $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAY_STATUS_REQCANCEL'] => 'キャンセル受付',
            $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAY_STATUS_REQCHANGE'] => '注文金額変更受付',
            $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAY_STATUS_PAY_SUCCESS'] => '支払い完了',            
            $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAY_STATUS_PAYSTART'] => '決済開始',
            $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAY_STATUS_EXPIRE'] => '期限切れ',
            $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAY_STATUS_CANCEL'] => 'キャンセル',
            $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAY_STATUS_FAIL'] => '決済失敗',
            $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAY_STATUS_AUTH'] => '仮売上済み',
            $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAY_STATUS_COMMIT'] => '実売上済み',
            $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAY_STATUS_SALES'] => '実売上済み',
            $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAY_STATUS_CAPTURE'] => '即時売上済み',
            $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAY_STATUS_VOID'] => '取消済み',
            $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAY_STATUS_RETURN'] => '返品済み',
            $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAY_STATUS_RETURNX'] => '月跨ぎ返品済み',
            $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAY_STATUS_SAUTH'] => '簡易オーソリ済み',
            $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAY_STATUS_CHECK'] => '有効性チェック済み',
            $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAY_STATUS_EXCEPT'] => '例外エラー',
        );
    
    }

    /**
     * 決済モジュールで利用出来る決済方式の名前一覧を取得する
     *
     * @param integer $tokenOption : 1 = use token, 0 = use credit
     * @return array 支払方法
     */
    function getPaymentTypeNames($tokenOption)
    {
        $payments =  array(
            $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_CREDIT'] => $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYNAME_CREDIT'],
            $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_TOKEN'] => $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYNAME_TOKEN'],
            $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_CVS'] => $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYNAME_CVS'],
            $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_PAYEASY'] => $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYNAME_PAYEASY'],
            $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_ATM'] => $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYNAME_ATM'],
            $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_RAKUTEN_ID'] => $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYNAME_RAKUTEN_ID'],            
            //$this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_REGIST_CREDIT'] => $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYNAME_REGIST_CREDIT'],
            // $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_CREDIT_CHECK'] => $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYNAME_CREDIT_CHECK'],
            // $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_CREDIT_SAUTH'] => $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYNAME_CREDIT_SAUTH'],
            // $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_MOBILEEDY'] => $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYNAME_MOBILEEDY'],
            // $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_MOBILESUICA'] =>  $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYNAME_MOBILESUICA'],
            // $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_PAYPAL'] => $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYNAME_PAYPAL'],
            // $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_IDNET'] => $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYNAME_IDNET'],
            // $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_WEBMONEY'] => $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYNAME_WEBMONEY'],
            // $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_AU'] => $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYNAME_AU'],
            // $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_DOCOMO'] => $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYNAME_DOCOMO'],
            // $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_SB'] => $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYNAME_SB'],
            // $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_COLLECT'] => $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYNAME_COLLECT'],
        );
        // If using Credit
        if ($tokenOption === 1){            
            unset ($payments[$this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_CREDIT']]);
        } else if ($tokenOption === 0) { // using token
            unset ($payments[$this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_TOKEN']]);
        }
        return $payments;
    }

    /**
     * 決済モジュールで利用出来る決済方式の内部名一覧を取得する
     *
     * @return array 支払方法コード
     */
    function getPaymentTypeCodes()
    {
        $constGmoPG = $this->app['config']['GmoPaymentGateway']['const'];

        return array(
            $constGmoPG['PG_MULPAY_PAYID_CREDIT'] => $constGmoPG['PG_MULPAY_PAYCODE_CREDIT'],
            $constGmoPG['PG_MULPAY_PAYID_CVS'] => $constGmoPG['PG_MULPAY_PAYCODE_CVS'],
            $constGmoPG['PG_MULPAY_PAYID_PAYEASY'] => $constGmoPG['PG_MULPAY_PAYCODE_PAYEASY'],
            $constGmoPG['PG_MULPAY_PAYID_ATM'] => $constGmoPG['PG_MULPAY_PAYCODE_ATM'],
            $constGmoPG['PG_MULPAY_PAYID_RAKUTEN_ID'] => $constGmoPG['PG_MULPAY_PAYCODE_RAKUTEN_ID'],
            $constGmoPG['PG_MULPAY_PAYID_TOKEN'] => $constGmoPG['PG_MULPAY_PAYCODE_TOKEN'],
            $constGmoPG['PG_MULPAY_PAYID_REGIST_CREDIT'] => $constGmoPG['PG_MULPAY_PAYCODE_REGIST_CREDIT'],
            // $constGmoPG['PG_MULPAY_PAYID_CREDIT_CHECK'] => $constGmoPG['PG_MULPAY_PAYCODE_CREDIT_CHECK'],
            // $constGmoPG['PG_MULPAY_PAYID_CREDIT_SAUTH'] => $constGmoPG['PG_MULPAY_PAYCODE_CREDIT_SAUTH'],
            // $constGmoPG['PG_MULPAY_PAYID_MOBILEEDY'] => $constGmoPG['PG_MULPAY_PAYCODE_MOBILEEDY'],
            // $constGmoPG['PG_MULPAY_PAYID_MOBILESUICA'] =>  $constGmoPG['PG_MULPAY_PAYCODE_MOBILESUICA'],
            // $constGmoPG['PG_MULPAY_PAYID_PAYPAL'] => $constGmoPG['PG_MULPAY_PAYCODE_PAYPAL'],
            // $constGmoPG['PG_MULPAY_PAYID_IDNET'] => $constGmoPG['PG_MULPAY_PAYCODE_IDNET'],
            // $constGmoPG['PG_MULPAY_PAYID_WEBMONEY'] => $constGmoPG['PG_MULPAY_PAYCODE_WEBMONEY'],
            // $constGmoPG['PG_MULPAY_PAYID_AU'] => $constGmoPG['PG_MULPAY_PAYCODE_AU'],
            // $constGmoPG['PG_MULPAY_PAYID_DOCOMO'] => $constGmoPG['PG_MULPAY_PAYCODE_DOCOMO'],
            // $constGmoPG['PG_MULPAY_PAYID_SB'] => $constGmoPG['PG_MULPAY_PAYCODE_SB'],
            // $constGmoPG['PG_MULPAY_PAYID_COLLECT'] => $constGmoPG['PG_MULPAY_PAYCODE_COLLECT'],
        );

    }

    /**
     * 禁止文字か判定を行う。
     *
     * @param string $value 判定対象
     * @return boolean 結果
     */
    function isProhibitedChar($value)
    {
        $check_char = mb_convert_encoding($value, "SJIS-win", "UTF-8");
        if (hexdec('8740') <= hexdec(bin2hex($check_char)) && hexdec('879E') >= hexdec(bin2hex($check_char))) {
            return true;
        }
        if ((hexdec('ED40') <= hexdec(bin2hex($check_char)) && hexdec('ED9E') >= hexdec(bin2hex($check_char))) ||
            (hexdec('ED9F') <= hexdec(bin2hex($check_char)) && hexdec('EDFC') >= hexdec(bin2hex($check_char))) ||
            (hexdec('EE40') <= hexdec(bin2hex($check_char)) && hexdec('EE9E') >= hexdec(bin2hex($check_char))) ||
            (hexdec('FA40') <= hexdec(bin2hex($check_char)) && hexdec('FA9E') >= hexdec(bin2hex($check_char))) ||
            (hexdec('FA9F') <= hexdec(bin2hex($check_char)) && hexdec('FAFC') >= hexdec(bin2hex($check_char))) ||
            (hexdec('FB40') <= hexdec(bin2hex($check_char)) && hexdec('FB9E') >= hexdec(bin2hex($check_char))) ||
            (hexdec('FB9F') <= hexdec(bin2hex($check_char)) && hexdec('FBFC') >= hexdec(bin2hex($check_char))) ||
            (hexdec('FC40') <= hexdec(bin2hex($check_char)) && hexdec('FC4B') >= hexdec(bin2hex($check_char)))
        ) {
            return true;
        }
        if ((hexdec('EE9F') <= hexdec(bin2hex($check_char)) && hexdec('EEFC') >= hexdec(bin2hex($check_char))) ||
            (hexdec('F040') <= hexdec(bin2hex($check_char)) && hexdec('F9FC') >= hexdec(bin2hex($check_char)))
        ) {
            return true;
        }

        return false;
    }

    /**
     * 禁止文字を全角スペースに置換する。
     *
     * @param string $value 対象文字列
     * @return string 結果
     */
    function convertProhibitedChar($value)
    {
        $ret = $value;
        for ($i = 0; $i < mb_strlen($value); $i++) {
            $tmp = mb_substr($value, $i, 1);
            if ($this->isProhibitedChar($tmp)) {
                $ret = str_replace($tmp, "　", $value);
            }
        }
        return $ret;
    }

    /**
     * 禁止半角記号を半角スペースに変換する。
     *
     * @param string $value
     * @return string 変換した値
     */
    function convertProhibitedKigo($value)
    {
        $prohiditedKigos = array('^','`','{','|','}','~','&','<','>','"','\'');
        foreach ($prohiditedKigos as $prohidited_kigo) {
            if (strstr($value, $prohidited_kigo)) {
                $value = str_replace($prohidited_kigo, " ", $value);
            }
        }
        return $value;
    }

    /**
     * 文字列から指定バイト数を切り出す。
     *
     * @param string $value
     * @param integer $len
     * @return string 結果
     */
    function subString($value, $len)
    {
        $value = mb_convert_encoding($value, "SJIS", "UTF-8");
        for ($i = 1; $i <= mb_strlen($value); $i++) {
            $tmp = mb_substr($value, 0, $i);
            if (strlen($tmp) <= $len) {
                $ret = mb_convert_encoding($tmp, "UTF-8", "SJIS");
            } else {
                break;
            }
        }
        return $ret;
    }

    /**
     * 日付をISO8601形式にフォーマットする
     *
     * @param string $date
     * @return string ISO8601 format date
     **/
    function formatISO8601($date)
    {
        $n = sscanf($date, '%4s%2s%2s%2s%2s%2s', $year, $month, $day, $hour, $min, $sec);
        return sprintf('%s-%s-%s %s:%s:%s', $year, $month, $day, $hour, $min, $sec);
    }

    /**
     * 配列データからログに記録しないデータをマスクする
     *
     * @param array $listData
     * @return array マスク後データ
     */
    function setMaskData($listData)
    {
        foreach ($listData as $key => $val) {
            switch ($key) {
                case 'CardNo':
                    $listData[$key] = str_repeat('*', 13) . substr($val, -3);
                    break;
                case 'SecurityCode':
                    $listData[$key] = str_repeat('*', 4);
                    break;
                case 'MemberName':
                case 'CustomerName':
                case 'CustomerKana':
                case 'ShopPass':
                case 'SitePass':
                case 'MemberName':
                case 'MailAddress':
                    $listData[$key] = str_repeat('*', 6);
                    break;
                default:
                    break;
            }
        }
        return $listData;
    }

    function printLog($msg)
    {
        if (is_array($msg) || is_object($msg)) {
            $msg = print_r($msg, true);
        }
        $objMdl =& PG_MULPAY_Ex::getInstance();
        $objMdl->printLog($msg);
    }
    
    /**
     * Check if user is currently purchasing a subscription
     * @return boolean : true if use subscription
     */
    public function isSubscription() {
        // Set JobCd = CHECK in case purchase regular product that use scription
        $pluginRepo = $this->app['orm.em']->getRepository('\Eccube\Entity\Plugin');
        $pluginSubs = $pluginRepo->findOneBy(array('code' => 'GmoPaymentGatewaySubscription','del_flg' => 0, 'enable' => 1));
        if (!is_null($pluginSubs)) { // If setup subscription plugin
            // Get config of subscription
            $repoConfig = $this->app['orm.em']->getRepository('\Plugin\GmoPaymentGatewaySubscription\Entity\GmoSubsConfig');
            $subConfig = $repoConfig->getSubsConfig($this->app);
            $helperCommon = new \Plugin\GmoPaymentGatewaySubscription\Controller\Helper\Helper_Common($this->app);
            // If use subscription and exist JobCd keys
            if ($helperCommon->checkUsingSubscription($subConfig)) {
                return true;
            }
        }
        return false;
    }
}
