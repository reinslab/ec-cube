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

class OrderOptionItem extends \Eccube\Entity\AbstractEntity
{

    private $order_option_id;
    private $option_id;
    private $optioncategory_id;
    private $value;
    private $option_name;
    private $option_category_name;
    private $price;
    private $OrderOption;
    private $Option;
    private $OptionCategory;

    public function getLabel()
    {
        return $this->option_name . ':' . $this->option_category_name;
    }
    
    public function setOrderOptionId($orderOptionId)
    {
        $this->order_option_id = $orderOptionId;

        return $this;
    }

    public function getOrderOptionId()
    {
        return $this->order_option_id;
    }
    
    public function setOptionId($optionId)
    {
        $this->option_id = $optionId;

        return $this;
    }

    public function getOptionId()
    {
        return $this->option_id;
    }
    
    public function setOptionCategoryId($optionCategoryId)
    {
        $this->optioncategory_id = $optionCategoryId;

        return $this;
    }

    public function getOptionCategoryId()
    {
        return $this->optioncategory_id;
    }
    
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }
    
    public function setOptionName($optionName)
    {
        $this->option_name = $optionName;

        return $this;
    }

    public function getOptionName()
    {
        return $this->option_name;
    }
    
    public function setOptionCategoryName($optionCategoryName)
    {
        $this->option_category_name = $optionCategoryName;

        return $this;
    }

    public function getOptionCategoryName()
    {
        return $this->option_category_name;
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
    
    public function setOrderOption(\Plugin\ProductOption\Entity\OrderOption $orderOption = null)
    {
        $this->OrderOption = $orderOption;

        return $this;
    }

    public function getOrderOption()
    {
        return $this->OrderOption;
    }

    public function setOption(\Plugin\ProductOption\Entity\Option $option = null)
    {
        $this->Option = $option;

        return $this;
    }

    public function getOption()
    {
        return $this->Option;
    }
    
    public function setOptionCategory(\Plugin\ProductOption\Entity\OptionCategory $optionCategory = null)
    {
        $this->OptionCategory = $optionCategory;

        return $this;
    }

    public function getOptionCategory()
    {
        return $this->OptionCategory;
    }
}
