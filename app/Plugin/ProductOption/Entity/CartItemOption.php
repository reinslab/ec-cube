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

use Doctrine\ORM\Mapping as ORM;

class CartItemOption extends \Eccube\Entity\AbstractEntity
{
    private $class_id;
    private $option_price;
    private $option_price_inc_tax;
    private $price;    
    private $quantity;
    private $option;
    private $label;
    private $delivery_free_flg;

    public function __construct()
    {
    }
    
    public function setClassId($class_id)
    {
        $this->class_id = $class_id;

        return $this;
    }

    public function getClassId()
    {
        return $this->class_id;
    }
    
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    public function getPrice()
    {
        return $this->price;
    }
    
    public function setOptionPrice($option_price)
    {
        $this->option_price = $option_price;

        return $this;
    }

    public function getOptionPrice()
    {
        return $this->option_price;
    }    

    public function setOptionPriceIncTax($option_price_inc_tax)
    {
        $this->option_price_inc_tax = $option_price_inc_tax;

        return $this;
    }

    public function getOptionPriceIncTax()
    {
        return $this->option_price_inc_tax;
    }
    
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }


    public function getQuantity()
    {
        return $this->quantity;
    }
    
    public function setOption($option)
    {
        $this->option = $option;

        return $this;
    }
    
    public function getOption()
    {
        return $this->option;
    }
    
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }
    
    public function getLabel()
    {
        return $this->label;
    }
    
    public function setDeliveryFreeFlg($flg)
    {
        $this->delivery_free_flg = $flg;

        return $this;
    }
    
    public function getDeliveryFreeFlg()
    {
        return $this->delivery_free_flg;
    }
}
