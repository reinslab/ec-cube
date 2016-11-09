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

class OrderDetail extends \Eccube\Entity\AbstractEntity
{
    private $order_detail_id;
    private $order_option_id;
    private $OrderDetail;
    private $OrderOption;

    public function setOrderDetailId($orderDetailId)
    {
        $this->order_detail_id = $orderDetailId;

        return $this;
    }

    public function getOrderDetailId()
    {
        return $this->order_detail_id;
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

    public function setOrderDetail(\Eccube\Entity\OrderDetail $orderDetail = null)
    {
        $this->OrderDetail = $orderDetail;

        return $this;
    }

    public function getOrderDetail()
    {
        return $this->OrderDetail;
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

}
