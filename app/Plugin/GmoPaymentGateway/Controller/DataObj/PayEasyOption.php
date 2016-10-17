<?php
/*
 * Copyright(c) 2015 GMO Payment Gateway, Inc. All rights reserved.
 * http://www.gmo-pg.com/
 */

namespace Plugin\GmoPaymentGateway\Controller\DataObj;

/**
 * Additional options for PayEasy(NetBank) payment method
 */
class PayEasyOption
{

    private $enable_mail;
    private $enable_cvs_mails;
    private $use_securitycd;
    private $use_securitycd_option;
    private $TdFlag;
    private $TdTenantName;
    private $ClientField1;
    private $ClientField2;
    private $PaymentTermDay;
    private $PaymentTermSec;
    private $ReceiptsDisp1;
    private $ReceiptsDisp2;
    private $ReceiptsDisp3;
    private $ReceiptsDisp4;
    private $ReceiptsDisp5;
    private $ReceiptsDisp6;
    private $ReceiptsDisp7;
    private $ReceiptsDisp8;
    private $ReceiptsDisp9;
    private $ReceiptsDisp10;
    private $ReceiptsDisp11;
    private $ReceiptsDisp12_1;
    private $ReceiptsDisp12_2;
    private $ReceiptsDisp12_3;
    private $ReceiptsDisp13_1;
    private $ReceiptsDisp13_2;
    private $ReceiptsDisp13_3;
    private $ReceiptsDisp13_4;
    private $EdyAddInfo1;
    private $EdyAddInfo2;
    private $SuicaAddInfo1;
    private $SuicaAddInfo2;
    private $SuicaAddInfo3;
    private $SuicaAddInfo4;
    private $Currency;
    private $order_mail_title1;
    private $order_mail_body1;
    private $SelectPageCall_PC;
    private $SelectPageCall_Mobile;

    /**
     * Get and set method for enable_mail
     * @return type
     */
    public function getEnableMail()
    {
        return $this->enable_mail;
    }

    public function setEnableMail($enable_mail)
    {
        $this->enable_mail = $enable_mail;
    }

    /**
     * Get and set method for enable_cvs_mails
     * @return type
     */
    public function getEnableCvsMails()
    {
        return $this->enable_cvs_mails;
    }

    public function setEnableCvsMails($enable_cvs_mails)
    {
        $this->enable_cvs_mails = $enable_cvs_mails;
    }

    /**
     * Get and set method for use_securitycd
     * @return type
     */
    public function getUseSecuritycd()
    {
        return $this->use_securitycd;
    }

    public function setUseSecuritycd($use_securitycd)
    {
        $this->use_securitycd = $use_securitycd;
    }

    /**
     * Get and set method for use_securitycd_option
     * @return type
     */
    public function getUseSecuritycdOption()
    {
        return $this->use_securitycd_option;
    }

    public function setUseSecuritycdOption($use_securitycd_option)
    {
        $this->use_securitycd_option = $use_securitycd_option;
    }

    /**
     * Get and set method for TdFlag
     * @return type
     */
    public function getTdFlag()
    {
        return $this->TdFlag;
    }

    public function setTdFlag($TdFlag)
    {
        $this->TdFlag = $TdFlag;
    }

    /**
     * Get and set method for TdTenantName
     * @return type
     */
    public function getTdTenantName()
    {
        return $this->TdTenantName;
    }

    public function setTdTenantName($TdTenantName)
    {
        $this->TdTenantName = $TdTenantName;
    }

    /**
     * Get and set method for ClientField1
     * @return type
     */
    public function getClientField1()
    {
        return $this->ClientField1;
    }

    public function setClientField1($ClientField1)
    {
        $this->ClientField1 = $ClientField1;
    }

    /**
     * Get and set method for ClientField2
     * @return type
     */
    public function getClientField2()
    {
        return $this->ClientField2;
    }

    public function setClientField2($ClientField2)
    {
        $this->ClientField2 = $ClientField2;
    }

    /**
     * Get and set method for PaymentTermDay
     * @return type
     */
    public function getPaymentTermDay()
    {
        return $this->PaymentTermDay;
    }

    public function setPaymentTermDay($PaymentTermDay)
    {
        $this->PaymentTermDay = $PaymentTermDay;
    }

    /**
     * Get and set method for PaymentTermSec
     * @return type
     */
    public function getPaymentTermSec()
    {
        return $this->PaymentTermSec;
    }

    public function setPaymentTermSec($PaymentTermSec)
    {
        $this->PaymentTermSec = $PaymentTermSec;
    }

    /**
     * Get and set method for RegisterDisp1
     * @return type
     */
    public function getReceiptsDisp1()
    {
        return $this->ReceiptsDisp1;
    }

    public function setReceiptsDisp1($ReceiptsDisp1)
    {
        $this->ReceiptsDisp1 = $ReceiptsDisp1;
    }

    /**
     * Get and set method for RegisterDisp2
     * @return type
     */
    public function getReceiptsDisp2()
    {
        return $this->ReceiptsDisp2;
    }

    public function setReceiptsDisp2($ReceiptsDisp2)
    {
        $this->ReceiptsDisp2 = $ReceiptsDisp2;
    }

    /**
     * Get and set method for RegisterDisp3
     * @return type
     */
    public function getReceiptsDisp3()
    {
        return $this->ReceiptsDisp3;
    }

    public function setReceiptsDisp3($ReceiptsDisp3)
    {
        $this->ReceiptsDisp3 = $ReceiptsDisp3;
    }

    /**
     * Get and set method for RegisterDisp4
     * @return type
     */
    public function getReceiptsDisp4()
    {
        return $this->ReceiptsDisp4;
    }

    public function setReceiptsDisp4($ReceiptsDisp4)
    {
        $this->ReceiptsDisp4 = $ReceiptsDisp4;
    }

    /**
     * Get and set method for RegisterDisp5
     * @return type
     */
    public function getReceiptsDisp5()
    {
        return $this->ReceiptsDisp5;
    }

    public function setReceiptsDisp5($ReceiptsDisp5)
    {
        $this->ReceiptsDisp5 = $ReceiptsDisp5;
    }

    /**
     * Get and set method for RegisterDisp6
     * @return type
     */
    public function getReceiptsDisp6()
    {
        return $this->ReceiptsDisp6;
    }

    public function setReceiptsDisp6($ReceiptsDisp6)
    {
        $this->ReceiptsDisp6 = $ReceiptsDisp6;
    }

    /**
     * Get and set method for RegisterDisp7
     * @return type
     */
    public function getReceiptsDisp7()
    {
        return $this->ReceiptsDisp7;
    }

    public function setReceiptsDisp7($ReceiptsDisp7)
    {
        $this->ReceiptsDisp7 = $ReceiptsDisp7;
    }

    /**
     * Get and set method for RegisterDisp8
     * @return type
     */
    public function getReceiptsDisp8()
    {
        return $this->ReceiptsDisp8;
    }

    public function setReceiptsDisp8($ReceiptsDisp8)
    {
        $this->ReceiptsDisp8 = $ReceiptsDisp8;
    }

    /**
     * Get and set method for RegisterDisp9
     * @return type
     */
    public function getReceiptsDisp9()
    {
        return $this->ReceiptsDisp9;
    }

    public function setReceiptsDisp9($ReceiptsDisp9)
    {
        $this->ReceiptsDisp9 = $ReceiptsDisp9;
    }

    /**
     * Get and set method for RegisterDisp10
     * @return type
     */
    public function getReceiptsDisp10()
    {
        return $this->ReceiptsDisp10;
    }

    public function setReceiptsDisp10($ReceiptsDisp10)
    {
        $this->ReceiptsDisp10 = $ReceiptsDisp10;
    }

    /**
     * Get and set method for RegisterDisp11
     * @return type
     */
    public function getReceiptsDisp11()
    {
        return $this->ReceiptsDisp11;
    }

    public function setReceiptsDisp11($ReceiptsDisp11)
    {
        $this->ReceiptsDisp11 = $ReceiptsDisp11;
    }

    /**
     * Get and set method for RegisterDisp12_1
     * @return type
     */
    public function getReceiptsDisp121()
    {
        return $this->ReceiptsDisp12_1;
    }

    public function setReceiptsDisp121($ReceiptsDisp12_1)
    {
        $this->ReceiptsDisp12_1 = $ReceiptsDisp12_1;
    }

    /**
     * Get and set method for RegisterDisp12_2
     * @return type
     */
    public function getReceiptsDisp122()
    {
        return $this->ReceiptsDisp12_2;
    }

    public function setReceiptsDisp122($ReceiptsDisp12_2)
    {
        $this->ReceiptsDisp12_2 = $ReceiptsDisp12_2;
    }

    /**
     * Get and set method for RegisterDisp12_3
     * @return type
     */
    public function getReceiptsDisp123()
    {
        return $this->ReceiptsDisp12_3;
    }

    public function setReceiptsDisp123($ReceiptsDisp12_3)
    {
        $this->ReceiptsDisp12_3 = $ReceiptsDisp12_3;
    }

    /**
     * Get and set method for RegisterDisp13_1
     * @return type
     */
    public function getReceiptsDisp131()
    {
        return $this->ReceiptsDisp13_1;
    }

    public function setReceiptsDisp131($ReceiptsDisp13_1)
    {
        $this->ReceiptsDisp13_1 = $ReceiptsDisp13_1;
    }

    /**
     * Get and set method for RegisterDisp13_2
     * @return type
     */
    public function getReceiptsDisp132()
    {
        return $this->ReceiptsDisp13_2;
    }

    public function setReceiptsDisp132($ReceiptsDisp13_2)
    {
        $this->ReceiptsDisp13_2 = $ReceiptsDisp13_2;
    }

    /**
     * Get and set method for RegisterDisp13_3
     * @return type
     */
    public function getReceiptsDisp133()
    {
        return $this->ReceiptsDisp13_3;
    }

    public function setReceiptsDisp133($ReceiptsDisp13_3)
    {
        $this->ReceiptsDisp13_3 = $ReceiptsDisp13_3;
    }

    /**
     * Get and set method for RegisterDisp13_4
     * @return type
     */
    public function getReceiptsDisp134()
    {
        return $this->ReceiptsDisp13_4;
    }

    public function setReceiptsDisp134($ReceiptsDisp13_4)
    {
        $this->ReceiptsDisp13_4 = $ReceiptsDisp13_4;
    }

    /**
     * Get and set method for EdyAddInfo1
     * @return type
     */
    public function getEdyAddInfo1()
    {
        return $this->EdyAddInfo1;
    }

    public function setEdyAddInfo1($EdyAddInfo1)
    {
        $this->EdyAddInfo1 = $EdyAddInfo1;
    }

    /**
     * Get and set method for EdyAddInfo2
     * @return type
     */
    public function getEdyAddInfo2()
    {
        return $this->EdyAddInfo2;
    }

    public function setEdyAddInfo2($EdyAddInfo2)
    {
        $this->EdyAddInfo2 = $EdyAddInfo2;
    }

    /**
     * Get and set method for SuicaAddInfo1
     * @return type
     */
    public function getSuicaAddInfo1()
    {
        return $this->SuicaAddInfo1;
    }

    public function setSuicaAddInfo1($SuicaAddInfo1)
    {
        $this->SuicaAddInfo1 = $SuicaAddInfo1;
    }

    /**
     * Get and set method for SuicaAddInfo2
     * @return type
     */
    public function getSuicaAddInfo2()
    {
        return $this->SuicaAddInfo2;
    }

    public function setSuicaAddInfo2($SuicaAddInfo2)
    {
        $this->SuicaAddInfo2 = $SuicaAddInfo2;
    }

    /**
     * Get and set method for SuicaAddInfo3
     * @return type
     */
    public function getSuicaAddInfo3()
    {
        return $this->SuicaAddInfo3;
    }

    public function setSuicaAddInfo3($SuicaAddInfo3)
    {
        $this->SuicaAddInfo3 = $SuicaAddInfo3;
    }

    /**
     * Get and set method for SuicaAddInfo4
     * @return type
     */
    public function getSuicaAddInfo4()
    {
        return $this->SuicaAddInfo4;
    }

    public function setSuicaAddInfo4($SuicaAddInfo4)
    {
        $this->SuicaAddInfo4 = $SuicaAddInfo4;
    }

    /**
     * Get and set method for Currency
     * @return type
     */
    public function getCurrency()
    {
        return $this->Currency;
    }

    public function setCurrency($Currency)
    {
        $this->Currency = $Currency;
    }

    /**
     * Get and set method for order_mail_title1
     * @return type
     */
    public function getOrderMailTitle1()
    {
        return $this->order_mail_title1;
    }

    public function setOrderMailTitle1($order_mail_title1)
    {
        $this->order_mail_title1 = $order_mail_title1;
    }

    /**
     * Get and set method for order_mail_body1
     * @return type
     */
    public function getOrderMailBody1()
    {
        return $this->order_mail_body1;
    }

    public function setOrderMailBody1($order_mail_body1)
    {
        $this->order_mail_body1 = $order_mail_body1;
    }

    /**
     * Get and set method for SelectPageCall_PC
     * @return type
     */
    public function getSelectPageCallPC()
    {
        return $this->SelectPageCall_PC;
    }

    public function setSelectPageCallPC($SelectPageCall_PC)
    {
        $this->SelectPageCall_PC = $SelectPageCall_PC;
    }

    /**
     * Get and set method for SelectPageCall_Mobile
     * @return type
     */
    public function getSelectPageCallMobile()
    {
        return $this->SelectPageCall_Mobile;
    }

    public function setSelectPageCallMobile($SelectPageCall_Mobile)
    {
        $this->SelectPageCall_Mobile = $SelectPageCall_Mobile;
    }

}
