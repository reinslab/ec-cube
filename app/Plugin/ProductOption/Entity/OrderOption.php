<?php
/*
 * Plugin Name : ProductOption
 *
 * Copyright (C) 2015 BraTech Co., Ltd. All Rights Reserved.
 * http://www.bratech.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\ProductOption\Entity;

class OrderOption extends \Eccube\Entity\AbstractEntity
{

    private $id;
    private $serial;
    private $order_id;
    private $Order;
    private $OrderOptionItems;
    
    public function __construct()
    {
        $this->OrderOptionItems = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setSerial($serial)
    {
        $this->serial = is_array($serial) ? serialize($serial) : $serial;

        return $this;
    }

    public function getSerial()
    {
        return unserialize($this->serial);
    }
    
    public function setOrderId($optionId)
    {
        $this->order_id = $optionId;

        return $this;
    }

    public function getOrderId()
    {
        return $this->order_id;
    }

    public function setOrder(\Eccube\Entity\Order $Order = null)
    {
        $this->Order = $Order;

        return $this;
    }

    public function getOrder()
    {
        return $this->Order;
    }
    
    public function addOrderOptionItem(\Plugin\ProductOption\Entity\OrderOptionItem $orderOptionItem)
    {
        $this->OrderOptionItems[] = $orderOptionItem;

        return $this;
    }

    public function removeOrderOptionItem(\Plugin\ProductOption\Entity\OrderOptionItem $orderOptionItem)
    {
        $this->OrderOptionItems->removeElement($orderOptionItem);
    }

    public function getOrderOptionItems()
    {
        return $this->OrderOptionItems;
    }
    
    public function getLabel()
    {
        $arrLabel = array();
        foreach($this->OrderOptionItems as $OrderOptionItem){
            $arrLabel[] = $OrderOptionItem->getLabel();
        }
        return $arrLabel;
    }
    
    public function getPrice()
    {
        $price = 0;
        foreach($this->OrderOptionItems as $OrderOptionItem){
            $price += $OrderOptionItem->getPrice();
        }
        return $price;
    }
    
    public function getCurrentPrice()
    {
        $price = 0;
        foreach ($this->OrderOptionItems as $OrderOptionItem) {
            $OptionCategory = $OrderOptionItem->getOptionCategory();
            if ($OptionCategory) {
                if(strlen($OptionCategory->getValue()) > 0)
                    $price += $OptionCategory->getValue();
            }
        }
        return $price;
    }

}
