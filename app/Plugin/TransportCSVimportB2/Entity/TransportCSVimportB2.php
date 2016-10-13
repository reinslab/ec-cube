<?php
/*
* This file is part of EC-CUBE
*
* Copyright(c) 2015 Takashi Otaki All Rights Reserved.
* 
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/


namespace Plugin\TransportCSVimportB2\Entity;

class TransportCSVimportB2 extends \Eccube\Entity\AbstractEntity
{
    private $id;

    private $invoice_number;

    private $Order;

    private $order_id;

    private $Shipping;

    private $shipping_id;

    public function getInvoiceNumber()
    {
        return $this->invoice_number;
    }

    public function setInvoiceNumber($invoice_number)
    {
        $this->invoice_number = $invoice_number;

        return $this;
    }

    public function getOrder()
    {
        return $this->Order;
    }

    public function setOrder(\Eccube\Entity\Order $Order)
    {
        $this->Order = $Order;

        return $this;
    }

    public function getOrderId()
    {
        return $this->order_id;
    }

    public function setOrderId($order_id)
    {
        $this->order_id = $order_id;

        return $this;
    }

    public function getShipping()
    {
        return $this->Shipping;
    }

    public function setShipping(\Eccube\Entity\Shipping $Shipping)
    {
        $this->Shipping = $Shipping;

        return $this;
    }

    public function getShippingId()
    {
        return $this->shipping_id;
    }

    public function setShippingId($shipping_id)
    {
        $this->shipping_id = $shipping_id;

        return $this;
    }

}
