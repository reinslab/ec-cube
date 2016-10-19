<?php
/*
 * Copyright(c) 2015 GMO Payment Gateway, Inc. All rights reserved.
 * http://www.gmo-pg.com/
 */

namespace Plugin\GmoPaymentGateway\Controller\DataObj;

use Eccube\Entity\Payment;

/**
 * Extra object contains payment info (dtb_payment) and related informations
 */
class PaymentExtension extends \Eccube\Entity\Payment
{

    /**
     *
     * @var type Plugin\GmoPaymentGateway\Entity\GmoPaymentMethod
     */
    private $GmoPaymentMethod;

    /**
     *
     * @var type string
     */
    private $paymentCode;

    /**
     * Data that was unserialized from dtb_gmo_payment_method#memo05
     * @var type array
     */
    private $arrPaymentConfig;

    /**
     * Set payment
     *
     * @param  Eccube\Entity\Payment
     * @return PaymentExtension
     */
    public function setGmoPaymentMethod(\Plugin\GmoPaymentGateway\Entity\GmoPaymentMethod $Payment)
    {
        $this->GmoPaymentMethod = $Payment;

        return $this;
    }

    /**
     * Get payment code
     *
     * @return string
     */
    public function getGmoPaymentMethod()
    {
        return $this->GmoPaymentMethod;
    }

    /**
     * Set payment code
     *
     * @param  string $memo10
     * @return Payment
     */
    public function setPaymentCode($paymentCode)
    {
        $this->paymentCode = $paymentCode;

        return $this;
    }

    /**
     * Get payment code
     *
     * @return string
     */
    public function getPaymentCode()
    {
        return $this->paymentCode;
    }

    /**
     * Set payment code
     *
     * @param  string $arrPaymentConfig
     * @return array of data
     */
    public function setArrPaymentConfig($arrPaymentConfig)
    {
        $this->arrPaymentConfig = $arrPaymentConfig;

        return $this;
    }

    /**
     * Get payment code
     *
     * @return array
     */
    public function getArrPaymentConfig()
    {
        return $this->arrPaymentConfig;
    }

}
