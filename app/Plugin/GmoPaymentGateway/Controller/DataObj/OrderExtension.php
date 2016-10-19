<?php
/*
 * Copyright(c) 2015 GMO Payment Gateway, Inc. All rights reserved.
 * http://www.gmo-pg.com/
 */

namespace Plugin\GmoPaymentGateway\Controller\DataObj;

/**
 * Extra object contains order (dtb_order) and related informations
 *
 *
 */
class OrderExtension
{

    /**
     *
     * @var type Eccube\Entity\Order
     */
    private $Order;

     /**
     *
     * @var type Eccube\Entity\Customer
     */
    private $Customer;
    
    /**
     *
     * @var type array of payment data, extracted from dtb_gmo_order_payment#memo09
     */
    private $arrPaymentData;

    /**
     * Temporary ID
     * @var type string
     */
    private $OrderID;
    private $arrResult;

    /**
     * contain all the memos, 01 -> 10
     * @var Plugin\GmoPaymentGateway\Entity\GmoOrderPayment
     */
    private $gmoOrderPayment;

    public function getOrder()
    {
        return $this->Order;
    }

    public function setOrder(\Eccube\Entity\Order $order)
    {
        $this->Order = $order;
    }

    public function getCustomer()
    {
        return $this->Customer;
    }

    public function setCustomer(\Eccube\Entity\Customer $customer)
    {
        $this->Customer = $customer;
    }

    public function getPaymentData()
    {
        return $this->arrPaymentData;
    }

    public function setPaymentData($arrPaymentData)
    {
        $this->arrPaymentData = $arrPaymentData;
    }

    public function getOrderID()
    {
        return $this->OrderID;
    }

    public function setOrderID($OrderID)
    {
        $this->OrderID = $OrderID;
    }

    public function getResult()
    {
        return $this->arrResult;
    }

    public function setResult($arrResult)
    {
        $this->arrResult = $arrResult;
    }

    public function getGmoOrderPayment()
    {
        return $this->gmoOrderPayment;
    }

    public function setGmoOrderPayment($orderPayment)
    {
        $this->gmoOrderPayment = $orderPayment;
    }

}
