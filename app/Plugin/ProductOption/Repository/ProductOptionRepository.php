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

class ProductOptionRepository extends EntityRepository
{

    private $app;

    public function setApp($app)
    {
        $this->app = $app;
    }

    public function getListByProductId($productId)
    {
        $qb = $this->createQueryBuilder('po')
                ->where('po.product_id = :productId')
                ->setParameter('productId', $productId)
                ->orderBy('po.rank','ASC');

        return $qb->getQuery()
                        ->getResult();
    }
    
    public function save(\Plugin\ProductOption\Entity\ProductOption $ProductOption)
    {
        $em = $this->getEntityManager();
        $em->getConnection()->beginTransaction();
        try {
            if (!$ProductOption->getId()) {
                $rank = $this->createQueryBuilder('po')
                        ->select('MAX(po.rank)')
                        ->where('po.product_id = :productId')
                        ->setParameter('productId', $ProductOption->getProduct()->getId())
                        ->getQuery()
                        ->getSingleScalarResult();
                if (!$rank) {
                    $rank = 0;
                }
                $ProductOption->setRank($rank + 1);
            }

            $em->persist($ProductOption);
            $em->flush();

            $em->getConnection()->commit();
        } catch (\Exception $e) {
            $em->getConnection()->rollback();

            return false;
        }

        return true;
    }
    
    public function delete(\Plugin\ProductOption\Entity\ProductOption $ProductOption)
    {
        $em = $this->getEntityManager();
        $em->getConnection()->beginTransaction();
        try {
            $rank = $ProductOption->getRank();
            $Product = $ProductOption->getProduct();

            $em->createQueryBuilder()
                    ->update('Plugin\ProductOption\Entity\ProductOption', 'po')
                    ->set('po.rank', 'po.rank - 1')
                    ->where('po.rank > :rank AND po.Product = :Product')
                    ->setParameter('rank', $rank)
                    ->setParameter('Product', $Product)
                    ->getQuery()
                    ->execute();
            $em->remove($ProductOption);
            $em->flush();

            $em->getConnection()->commit();
        } catch (\Exception $e) {
            $em->getConnection()->rollback();

            return false;
        }

        return true;
    }    

    public function isExist($productId, $optionId)
    {
        $em = $this->getEntityManager();
        $em->getConnection()->beginTransaction();
        try {
            //
            $ProductOption = $this->findOneBy(array('product_id' => $productId, 'option_id' => $optionId));
            if (!$ProductOption) {
                throw new \Exception();
            }
        } catch (\Exception $e) {

            return false;
        }

        return true;
    }

}
