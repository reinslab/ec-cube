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

class ShipmentItem extends \Eccube\Entity\AbstractEntity
{
    private $item_id;
    private $order_option_id;
    private $ShipmentItem;
    private $OrderOption;

    public function setItemId($itemId)
    {
        $this->item_id = $itemId;

        return $this;
    }

    public function getItemId()
    {
        return $this->item_id;
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

    public function setShipmentItem(\Eccube\Entity\ShipmentItem $shipmentItem = null)
    {
        $this->ShipmentItem = $shipmentItem;

        return $this;
    }

    public function getShipmentItem()
    {
        return $this->ShipmentItem;
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
