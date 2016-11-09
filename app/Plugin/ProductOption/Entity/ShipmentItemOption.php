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

class ShipmentItemOption extends \Eccube\Entity\AbstractEntity
{
    private $item_index;
    private $shipping_index;
    private $serial;
    
    public function __toString()
    {
        return $this->getIndex();
    }

    public function setItemIndex($idx)
    {
        $this->item_index = $idx;

        return $this;
    }

    public function getItemIndex()
    {
        return $this->item_index;
    }
    
    public function setShippingIndex($idx)
    {
        $this->shipping_index = $idx;

        return $this;
    }

    public function getShippingIndex()
    {
        return $this->shipping_index;
    }

    public function setSerial($serial)
    {
        $this->serial = $serial;

        return $this;
    }

    public function getSerial()
    {
        return $this->serial;
    }
}
