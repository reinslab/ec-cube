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

class ProductOption extends \Eccube\Entity\AbstractEntity
{

    private $add = false;
    private $id;
    private $product_id;
    private $option_id;
    private $rank;
    private $Product;
    private $Option;

    public function getId()
    {
        return $this->id;    
    }
    
    public function setAdd($add)
    {
        $this->add = $add;

        return $this;
    }

    public function getAdd()
    {
        return $this->add;
    }

    public function setProductId($productId)
    {
        $this->product_id = $productId;

        return $this;
    }

    public function getProductId()
    {
        return $this->product_id;
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
    
    public function setRank($rank)
    {
        $this->rank = $rank;

        return $this;
    }

    public function getRank()
    {
        return $this->rank;
    }

    public function setProduct(\Eccube\Entity\Product $product = null)
    {
        $this->Product = $product;

        return $this;
    }

    public function getProduct()
    {
        return $this->Product;
    }

    public function setOption(\Plugin\ProductOption\Entity\Option $Option = null)
    {
        $this->Option = $Option;

        return $this;
    }

    public function getOption()
    {
        return $this->Option;
    }

}
