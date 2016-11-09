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

class OptionCategory extends \Eccube\Entity\AbstractEntity
{

    private $id;
    private $name;
    private $option_id;
    private $description;
    private $disable_flg;
    private $init_flg;
    private $value;
    private $delivery_free_flg;
    private $option_image;
    private $rank;
    private $del_flg;
    private $create_date;
    private $update_date;
    private $Option;
    private $Creator;

    public function __toString()
    {
        return $this->getName();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setOptionId($option_id)
    {
        $this->option_id = $option_id;

        return $this;
    }

    public function getOptionId()
    {
        return $this->option_id;
    }

    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setCreateDate($date)
    {
        $this->create_date = $date;

        return $this;
    }

    public function getCreateDate()
    {
        return $this->create_date;
    }

    public function setUpdateDate($date)
    {
        $this->update_date = $date;

        return $this;
    }

    public function getUpdateDate()
    {
        return $this->update_date;
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

    public function setDelFlg($delFlg)
    {
        $this->del_flg = $delFlg;

        return $this;
    }

    public function getDelFlg()
    {
        return $this->del_flg;
    }

    public function setDisableFlg($flg)
    {
        $this->disable_flg = $flg;

        return $this;
    }

    public function getDisableFlg()
    {
        return $this->disable_flg;
    }

    public function setInitFlg($flg)
    {
        $this->init_flg = $flg;

        return $this;
    }

    public function getInitFlg()
    {
        return $this->init_flg;
    }

    public function setOptionImage($optionimage)
    {
        $this->option_image = $optionimage;

        return $this;
    }

    public function getOptionImage()
    {
        return $this->option_image;
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
    
    public function setDeliveryFreeFlg($flg)
    {
        $this->delivery_free_flg = $flg;

        return $this;
    }

    public function getDeliveryFreeFlg()
    {
        return $this->delivery_free_flg;
    }    

    public function setOption(\Plugin\ProductOption\Entity\Option $Option)
    {
        $this->Option = $Option;

        return $this;
    }

    public function getOption()
    {
        return $this->Option;
    }

    public function setCreator(\Eccube\Entity\Member $creator)
    {
        $this->Creator = $creator;

        return $this;
    }

    public function getCreator()
    {
        return $this->Creator;
    }

}
