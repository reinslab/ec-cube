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
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class Option extends \Eccube\Entity\AbstractEntity
{

    private $optionCategories = array();
    private $id;
    private $name;
    private $manage_name;
    private $description;
    private $Type;
    private $pricedisp_flg;
    private $description_flg;
    private $is_required;
    private $rank;
    private $del_flg;
    private $create_date;
    private $update_date;
    private $OptionCategories;
    private $ProductOptions;
    private $Creator;
    private $defaultCategory;
    private $disableCategory;

    public function __construct()
    {
        $this->OptionCategories = new ArrayCollection();
        $this->ProductOptions = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getName();
    }
    
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addConstraint(new UniqueEntity(array(
            'fields'  => 'manage_name',
            'message' => '既に利用されている管理名です'
        )));
    }

    public function getOptionCategoriesSelect()
    {
        if (!$this->optionCategories){
            foreach ($this->getOptionCategories() as $OptionCategory) {
                $select = $OptionCategory->getName();
                if($this->pricedisp_flg){
                    $description = "";
                    $OptionCategoryVal = $OptionCategory->getValue();
                    if(strlen($OptionCategory->getValue()) > 0 && !empty($OptionCategoryVal)){
                        $description .= '（¥ ' . number_format($OptionCategoryVal) . ' + 税';
                    }
                    if($OptionCategory->getDeliveryFreeFlg() == 1){
                        if(strlen($description) == 0){
                            $description .= '（';
                        }else{
                            $description .= ' , ';
                        }
                        $description .= '送料無料';
                    }
                    if(strlen($description) > 0){
                        $description .= '）';
                        $select .= $description;
                    }
                }
                $this->optionCategories[$OptionCategory->getId()] = $select;
            }
        }
        return $this->optionCategories;
    }

    public function getDefaultCategory()
    {
        if($this->defaultCategory)return $this->defaultCategory;
        foreach ($this->getOptionCategories() as $OptionCategory) {
            if ($OptionCategory->getInitFlg() == 1) {
                $this->defaultCategory = $OptionCategory;
                break;
            }
        }
        return $this->defaultCategory;
    }
    
    public function getDisableCategory()
    {
        if($this->disableCategory)return $this->disableCategory;
        foreach ($this->getOptionCategories() as $OptionCategory) {
            if ($OptionCategory->getDisableFlg() == 1) {
                $this->disableCategory = $OptionCategory;
                break;
            }
        }
        return $this->disableCategory;
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

    public function setManageName($name)
    {
        $this->manage_name = $name;

        return $this;
    }

    public function getManageName()
    {
        return $this->manage_name;
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

    public function setType(\Plugin\ProductOption\Entity\Master\Type $type = null)
    {
        $this->Type = $type;

        return $this;
    }

    public function getType()
    {
        return $this->Type;
    }

    public function setDescriptionFlg($flg)
    {
        $this->description_flg = $flg;

        return $this;
    }

    public function getDescriptionFlg()
    {
        return $this->description_flg;
    }

    public function setPricedispFlg($flg)
    {
        $this->pricedisp_flg = $flg;

        return $this;
    }

    public function getPricedispFlg()
    {
        return $this->pricedisp_flg;
    }

    public function setIsRequired($flg)
    {
        $this->is_required = $flg;

        return $this;
    }

    public function getIsRequired()
    {
        return $this->is_required;
    }

    public function addOptionCategory(\Plugin\ProductOption\Entity\OptionCategory $optionCategories)
    {
        $this->OptionCategories[] = $optionCategories;

        return $this;
    }

    public function removeOptionCategory(\Plugin\ProductOption\Entity\OptionCategory $optionCategories)
    {
        $this->OptionCategories->removeElement($optionCategories);
    }

    public function getOptionCategories()
    {
        return $this->OptionCategories;
    }

    public function addProductOption(\Plugin\ProductOption\Entity\ProductOption $productOptions)
    {
        $this->ProductOptions[] = $productOptions;

        return $this;
    }

    public function removeProductOption(\Plugin\ProductOption\Entity\ProductOption $productOptions)
    {
        $this->ProductOptions->removeElement($productOptions);
    }

    public function getProductOptions()
    {
        return $this->ProductOptions;
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
