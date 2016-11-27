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

namespace Plugin\ProductOption\Repository;

use Doctrine\ORM\EntityRepository;

class OrderOptionRepository extends EntityRepository
{
    public function save(\Plugin\ProductOption\Entity\OrderOption $OrderOption)
    {
        $em = $this->getEntityManager();
        $em->getConnection()->beginTransaction();
        try {
            // 一旦登録されている情報を削除
            $OrderOptionItems = $OrderOption->getOrderOptionItems();
            foreach($OrderOptionItems as $orderOptionItem){
                $OrderOption->removeOrderOptionItem($orderOptionItem);
                $em->remove($orderOptionItem);
            }
            
            $em->persist($OrderOption);
            $em->flush();
            
            $Options = $OrderOption->getSerial();
            foreach($Options as $option_key => $option_value){
                $option_id = str_replace('productoption', '', $option_key);
                $Option = $em->getRepository('Plugin\ProductOption\Entity\Option')->find($option_id);

                $OrderOptionItem = new \Plugin\ProductOption\Entity\OrderOptionItem();
                $OrderOptionItem->setOrderOptionId($OrderOption->getId())
                                ->setOrderOption($OrderOption)
                                ->setOptionId($option_id)
                                ->setOption($Option)
                                ->setValue($option_value)
                                ->setOptionName($Option->getName());
                if($Option->getType()->getId() == 1 || $Option->getType()->getId() == 2){
                    $OptionCategory = $em->getRepository('Plugin\ProductOption\Entity\OptionCategory')->find(intval($option_value));
                    $OrderOptionItem->setOptionCategoryId($OptionCategory->getId())
                                    ->setOptionCategory($OptionCategory)
                                    ->setOptionCategoryName($OptionCategory->getName());
                    
                    if(strlen($OptionCategory->getValue()) > 0){
                        $OrderOptionItem->setPrice($OptionCategory->getValue());
                    }
                }else{
                    $OrderOptionItem->setOptionCategoryName($option_value);
                }
                
                $OrderOption->addOrderOptionItem($OrderOptionItem);
                
                $em->persist($OrderOptionItem);
            }
            
            $em->persist($OrderOption);
            $em->flush();

            $em->getConnection()->commit();
        } catch (\Exception $e) {
            $em->getConnection()->rollback();

            return false;
        }

        return $OrderOption;
    }
}
