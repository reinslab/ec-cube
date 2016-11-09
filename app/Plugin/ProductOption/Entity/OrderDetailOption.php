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

class OrderDetailOption extends \Eccube\Entity\AbstractEntity
{
    private $index;
    private $serial;
    
    public function __toString()
    {
        return $this->getIndex();
    }

    public function setIndex($idx)
    {
        $this->index = $idx;

        return $this;
    }

    public function getIndex()
    {
        return $this->index;
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
