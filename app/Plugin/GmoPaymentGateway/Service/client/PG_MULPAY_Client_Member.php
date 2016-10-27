<?php
/*
 * Copyright(c) 2015 GMO Payment Gateway, Inc. All rights reserved.
 * http://www.gmo-pg.com/
 */


namespace Plugin\GmoPaymentGateway\Service\client;

use Plugin\GmoPaymentGateway\Controller\Util\PluginUtil;

/**
 * 決済モジュール 決済処理: 会員処理
 */
class PG_MULPAY_Client_Member extends PG_MULPAY_Client_Base
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


    function checkMember($OrderExtension)
    {
        $orderInfo = $OrderExtension->getOrder();
        $objUtil = new \Plugin\GmoPaymentGateway\Controller\Util\PaymentUtil($this->app);
        $ret = $this->getMember($OrderExtension);

        if (!$ret) {
            // エラーなら無視
            return true;
        }
        $arrResult = $this->getResults();

        if (empty($arrResult)) {
            return true;
        }

        if (isset($arrResult['MemberName']) && $arrResult['MemberName'] != null) {
            $this->updateMember($OrderExtension);
            return true;
        }

        if (is_null($orderInfo->getCustomer()->getSecretKey())) {
            $orderInfo = $this->app['orm.em']->getRepository('Eccube\\Entity\\Order')
                ->findOneBy(array(
                    'id' => $orderInfo->getId(),
                ));
        }
        if ($orderInfo->getCustomer()->getSecretKey() == $arrResult['MemberName']) {
            return true;
        }
        $serverName = mb_convert_encoding($arrResult['MemberName'], 'UTF-8', 'SJIS-win');
        $checkName = $objUtil->convCVSText($orderInfo->getCustomer()->getName01() . $orderInfo->getCustomer()->getName02());
        if ($serverName == $checkName) {
            $this->updateMember($OrderExtension);
            return true;
        }

        $this->updateMember($OrderExtension);
        return $this->deleteCardAll($OrderExtension);

    }

    function searchCard($OrderExtension, $listParam = array(), $is_check = true, $physical_seq = false)
    {
        $orderInfo = $OrderExtension->getOrder();
        if(!is_null($orderInfo))
        {
            $customer =  $orderInfo->getCustomer();
            
            if (is_null($customer)){
                return false;
            }

            if (is_null($customer->getId()) || $orderInfo->getCustomer()->getId() == '0') {
                return false;
            }

            if ($is_check) {
                $this->checkMember($OrderExtension);
            }
        }
        $objMdl =& PluginUtil::getInstance($this->app);

        $gmoSetting = $objMdl->getUserSettings();

        $server_url = $gmoSetting['server_url'] . 'SearchCard.idPass';

        $sendKey = array(
            'SiteID',
            'SitePass',
            'MemberID',
            'CardSeq',
        );
        $paymentInfo = new \Plugin\GmoPaymentGateway\Controller\DataObj\PaymentExtension();
        $arrSendData = $this->getSendData($sendKey, $OrderExtension, $listParam, $paymentInfo, $gmoSetting);
        if($physical_seq){
            // get card with SeqCard is physical number, not logical number
            $arrSendData['SeqMode'] = 1;
        }

        $ret = $this->sendRequest($server_url, $arrSendData);

        if (!$ret) {
            return false;
        }


        $error = $this->getError();
        if (!empty($error)) {
            return false;
        }
        return true;
    }

    function deleteCardAll($OrderExtension)
    {

        $ret = $this->searchCard($OrderExtension, array(), false);
        if (!$ret) {
            return false;
        }
        // 逆順並び替え
        $arrCardSeq = array();
        foreach ($this->results as $listData) {
            $arrCardSeq[] = $listData['CardSeq'];
        }
        rsort($arrCardSeq);
        foreach ($arrCardSeq as $seq) {
            $this->deleteCard($OrderExtension, $seq);
        }
        return true;
    }

    function deleteCard($OrderExtension, $listParam = array())
    {
        $objMdl =& PluginUtil::getInstance($this->app);
        
        $gmoSetting = $objMdl->getUserSettings();

        $server_url = $gmoSetting['server_url'] . 'DeleteCard.idPass';

        $sendKey = array(
            'SiteID',
            'SitePass',
            'MemberID',
            'CardSeq',
        );
        $paymentInfo = new \Plugin\GmoPaymentGateway\Controller\DataObj\PaymentExtension();
        $arrSendData = $this->getSendData($sendKey, $OrderExtension, $listParam, $paymentInfo, $gmoSetting);

        //delete card with SeqCard is physical number, not logical
        $arrSendData['SeqMode'] = 1;
        
        $ret = $this->sendRequest($server_url, $arrSendData);
        
        if (!$ret) {
            return false;
        }

        if (!is_null($this->getError())) {
            return false;
        }
        return true;
    }
    function saveCard($OrderExtension, $listParam, $CardSeq = null)
    {
        $objMdl =& PluginUtil::getInstance($this->app);
        // $objMdl = new PluginUtil($this->app);
        $gmoSetting = $objMdl->getUserSettings();

        $server_url = $gmoSetting['server_url'] . 'SaveCard.idPass';

        $sendKey = array(
            'SiteID',
            'SitePass',
            'MemberID',
            'DefaultFlag',
            'CardNo',
            'Expire',
            'HolderName',
        );

        if(!array_key_exists('CardSeq', $listParam))
        {
                $sendKey[] = 'CardSeq';
        }
        if(!array_key_exists('DefaultFlag', $listParam))
        {
                $listParam['DefaultFlag'] = '1';
        }
        $paymentInfo = new \Plugin\GmoPaymentGateway\Controller\DataObj\PaymentExtension();

        $arrSendData = $this->getSendData($sendKey, $OrderExtension, $listParam, $paymentInfo, $gmoSetting);
        
        // if add new card, $CardSeq will null
        if(is_null($CardSeq))
        {   
            $ret = $this->sendRequest($server_url, $arrSendData);
        }
        // if update card, $CardSeq will not null and add it to arrSendData array
        else
        {
            $arrSendData['SeqMode'] = 1;
            $arrSendData['CardSeq'] = $CardSeq;
            $ret = $this->sendRequest($server_url, $arrSendData);
        }

        
        if (!$ret) {
            return false;
        }

        if (!is_null($this->getError())) {
            return false;
        }
        return true;
    }

    function deleteMember($orderInfo)
    {
        if (is_null($orderInfo->getCustomer()->getId()) || $orderInfo->getCustomer()->getId() == '0') {
            return true;
        }

        $objMdl = new PluginUtil();
        $gmoSetting = $objMdl->getUserSettings();

        $server_url = $gmoSetting['server_url'] . 'DeleteMember.idPass';

        $sendKey = array(
            'SiteID',
            'SitePass',
            'MemberID',
        );

        $arrSendData = $this->getSendData($sendKey, $orderInfo, $listParam, $paymentInfo, $gmoSetting);

        $ret = $this->sendRequest($server_url, $arrSendData);
        if (!$ret) {
            return false;
        }

        if (!is_null($this->getError())) {
            return false;
        }
        return true;
    }
    
    /**
     * Delete member from gmo
     * @param type $gmoMemberId
     * @return boolean
     */
    function deleteGmoMember($gmoMemberId)
    {
        if (is_null($gmoMemberId)) {
           return false;
        }
        $objMdl =& PluginUtil::getInstance($this->app);
        $gmoSetting = $objMdl->getUserSettings();

        $server_url = $gmoSetting['server_url'] . 'DeleteMember.idPass';

        $sendKey = array(
            'SiteID',
            'SitePass',
            'MemberID',
        );

        $arrSendData = $this->createSendData($sendKey, $gmoSetting, $gmoMemberId);
        
        $ret = $this->sendRequest($server_url, $arrSendData);
        if (!$ret) {
            return false;
        }

        if (!is_null($this->getError())) {
            return false;
        }
        return true;
    }
    
    /**
     * Regist member to gmo
     * @param type $gmoMemberId
     * @return boolean true if regist successful
     */
    public function saveGmoMember($gmoMemberId) 
    {
        $objMdl =& PluginUtil::getInstance($this->app);
        $gmoSetting = $objMdl->getUserSettings();

        $server_url = $gmoSetting['server_url'] . 'SaveMember.idPass';

        $sendKey = array(
            'SiteID',
            'SitePass',
            'MemberID',
            'MemberName',
        );
       
        $arrSendData = $this->createSendData($sendKey, $gmoSetting, $gmoMemberId);
        $ret = $this->sendRequest($server_url, $arrSendData);
        if (!$ret) {
            return false;
        }

        $error = $this->getError();
        if (!empty($error)) {
            return false;
        }
        return true;
    }
    
    /**
     * Prepare data to delete member at gmo
     * @param type $sendKey
     * @param type $gmoSetting
     * @param type $gmoMemberId
     * @return type $dataSend
     */
    public function createSendData($sendKey, $gmoSetting, $gmoMemberId) 
    {
        $dataSend = array();
        foreach ($sendKey as $key) {
            switch ($key) {
                case 'SiteID':
                    $dataSend[$key] = $gmoSetting['site_id'];
                    break;
                case 'SitePass':
                    $dataSend[$key] = $gmoSetting['site_pass'];
                    break;
                case 'MemberID':
                    $dataSend[$key] = $gmoMemberId;
                    break;
                case 'MemberName':
                    // Find customer base on gmo_new_meber_id
                    $gmoMember = $this->app['eccube.plugin.gmo_pg.repository.gmo_member']->findOneBy(array('new_member_id'=>$gmoMemberId));
                    if (!is_null($gmoMember)) {
                        $dataSend[$key] = $gmoMember->getCustomer()->getSecretKey();
                    } else {
                        $dataSend[$key] = '';
                    }
                    
                    break;
            }
        }
        return $dataSend;
    }

    function updateMember($OrderExtension)
    {
        $orderInfo = $OrderExtension->getOrder();
        if (is_null($orderInfo->getId()) || $orderInfo->getId() == '0') {
            return true;
        }

        $objMdl =& PluginUtil::getInstance($this->app);
        $gmoSetting = $objMdl->getUserSettings();

        $server_url = $gmoSetting['server_url'] . 'UpdateMember.idPass';

        $sendKey = array(
            'SiteID',
            'SitePass',
            'MemberID',
            'MemberName',
        );
        $paymentInfo = new \Plugin\GmoPaymentGateway\Controller\DataObj\PaymentExtension();
        $arrSendData = $this->getSendData($sendKey, $OrderExtension, array(), $paymentInfo, $gmoSetting);

        $ret = $this->sendRequest($server_url, $arrSendData);

        if (!$ret) {
            return false;
        }

        $error = $this->getError();
        if (!empty($error)) {
            return false;
        }
        return true;
    }

    function saveMember($orderExtension, $is_check = true)
    {
        $orderInfo = $orderExtension->getOrder();
        // if (is_null($orderInfo->getCustomer()->getId()) || $orderInfo->getCustomer()->getId() == '0') {
        //     return true;
        // }

        // if(!is_null($orderInfo))
        // {
        //     if (is_null($orderInfo)) {
        //         return true;
        //     }
            
        //     if (is_null($orderInfo->getCustomer()->getId()) || $orderInfo->getCustomer()->getId() == '0') {
        //         return true;
        //     }
        // }

        if(!is_null($orderInfo))
        {
            $customer =  $orderInfo->getCustomer();
            if (is_null($customer)){
                return false;
            }
            if (is_null($customer->getId()) || $orderInfo->getCustomer()->getId() == '0') {
                return false;
            }
        }

        $objMdl =& PluginUtil::getInstance($this->app);
        $gmoSetting = $objMdl->getUserSettings();

        $server_url = $gmoSetting['server_url'] . 'SaveMember.idPass';

        $sendKey = array(
            'SiteID',
            'SitePass',
            'MemberID',
            'MemberName',
        );
        $PaymentExtension = new \Plugin\GmoPaymentGateway\Controller\DataObj\PaymentExtension();
        $arrSendData = $this->getSendData($sendKey, $orderExtension, array(), $PaymentExtension, $gmoSetting);
        $ret = $this->sendRequest($server_url, $arrSendData);
        if (!$ret) {
            return false;
        }

        $error = $this->getError();
        if (!empty($error)) {
            return false;
        }
        return true;
    }

    function getMember($orderExtension)
    {
        $orderInfo = $orderExtension->getOrder();
        if(!is_null($orderInfo)){
            if (is_null($orderInfo->getCustomer()->getId()) || $orderInfo->getCustomer()->getId() == '0') {
                return false;
            }
        }

        // if (is_null($orderInfo) || is_null($orderInfo->getCustomer())) {
        //      return true;
        // }

        // if (is_null($orderInfo->getCustomer()->getId()) || $orderInfo->getCustomer()->getId() == '0') {
        //     return true;
        // }
        

        $objMdl =& PluginUtil::getInstance($this->app);
        $gmoSetting = $objMdl->getUserSettings();

        $server_url = $gmoSetting['server_url'] . 'SearchMember.idPass';

        $sendKey = array(
            'SiteID',
            'SitePass',
            'MemberID',
        );

        $PaymentExtension = new \Plugin\GmoPaymentGateway\Controller\DataObj\PaymentExtension();
        $arrSendData = $this->getSendData($sendKey, $orderExtension, array(), $PaymentExtension, $gmoSetting);


        $ret = $this->sendRequest($server_url, $arrSendData);

        if (!$ret) {
            return false;
        }

        $error = $this->getError();
        if (!empty($error)) {
            return false;
        }
        return true;
    }

    function doPaymentRequest($arrOrder, $listParam, $paymentInfo)
    {
    }

    function getTargetPoint()
    {
        return '';
    }

    function getSendParam($listData)
    {

    }

}
