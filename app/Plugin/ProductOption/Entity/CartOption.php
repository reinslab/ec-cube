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
use Doctrine\Common\Collections\ArrayCollection;

class CartOption extends \Eccube\Entity\AbstractEntity
{
    private $CartOptions;

    public function __construct()
    {
        $this->CartOptions = new ArrayCollection();
    }
    
    public function addCartOption($CartOption)
    {
        $this->CartOptions[] = $CartOption;

        return $this;
    }
    
    public function clearCartOptions()
    {
        $this->CartOptions->clear();

        return $this;
    }


    public function getCartOptions()
    {
        return $this->CartOptions;
    }

    public function setCartOptions($CartOptions)
    {
        $this->CartOptions = $CartOptions;

        return $this;
    }
}
