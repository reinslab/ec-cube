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
use Plugin\ProductOption\Entity\ProductOption;

class OptionCategoryRepository extends EntityRepository
{

    public function getList($Option = null)
    {
        $qb = $this->createQueryBuilder('oc')
                ->orderBy('oc.rank', 'DESC')
                ->andWhere('o.del_flg = 0');
        if ($Option) {
            $qb->where('oc.Option = :Option')->setParameter('Option', $Option);
        }
        $OptionCategories = $qb->getQuery()
                ->getResult();

        return $OptionCategories;
    }

    public function up(\Plugin\ProductOption\Entity\OptionCategory $OptionCategory)
    {
        $em = $this->getEntityManager();
        $em->getConnection()->beginTransaction();
        try {
            $rank = $OptionCategory->getRank();
            $Option = $OptionCategory->getOption();

            //
            $OptionCategory2 = $this->findOneBy(array('rank' => $rank + 1, 'Option' => $Option));
            if (!$OptionCategory2) {
                throw new \Exception();
            }
            $OptionCategory2->setRank($rank);
            $em->persist($OptionCategory2);

            $OptionCategory->setRank($rank + 1);

            $em->persist($OptionCategory);
            $em->flush();

            $em->getConnection()->commit();
        } catch (\Exception $e) {
            $em->getConnection()->rollback();

            return false;
        }

        return true;
    }

    public function down(\Plugin\ProductOption\Entity\OptionCategory $OptionCategory)
    {
        $em = $this->getEntityManager();
        $em->getConnection()->beginTransaction();
        try {
            $rank = $OptionCategory->getRank();
            $Option = $OptionCategory->getOption();

            //
            $OptionCategory2 = $this->findOneBy(array('rank' => $rank - 1, 'Option' => $Option));
            if (!$OptionCategory2) {
                throw new \Exception();
            }
            $OptionCategory2->setRank($rank);
            $em->persist($OptionCategory2);

            $OptionCategory->setRank($rank - 1);

            $em->persist($OptionCategory);
            $em->flush();

            $em->getConnection()->commit();
        } catch (\Exception $e) {
            $em->getConnection()->rollback();

            return false;
        }

        return true;
    }

    public function save(\Plugin\ProductOption\Entity\OptionCategory $OptionCategory)
    {
        $em = $this->getEntityManager();
        $em->getConnection()->beginTransaction();
        $Option = $OptionCategory->getOption();
        try {
            if (!$OptionCategory->getId()) {
                $rank = $this->createQueryBuilder('oc')
                        ->select('MAX(oc.rank)')
                        ->where('oc.Option = :Option')->setParameter('Option', $Option)
                        ->getQuery()
                        ->getSingleScalarResult();
                if (!$rank) {
                    $rank = 0;
                }
                $OptionCategory->setRank($rank + 1);
                $OptionCategory->setDelFlg(0);
            }
            
            if($OptionCategory->getInitFlg()){
                $qb = $em->createQueryBuilder()
                        ->update('Plugin\ProductOption\Entity\OptionCategory', 'oc')
                        ->set('oc.init_flg', 'false')
                        ->where('oc.Option = :Option')
                        ->setParameter('Option', $Option);
                if ($OptionCategory->getId()) {
                    $qb->andWhere('oc.id <> :id')
                       ->setParameter('id', $OptionCategory->getId());
                }
                $qb->getQuery()
                   ->execute();
            }

            $em->persist($OptionCategory);
            $em->flush();

            $em->getConnection()->commit();
        } catch (\Exception $e) {
            $em->getConnection()->rollback();

            return false;
        }

        return true;
    }

    public function delete(\Plugin\ProductOption\Entity\OptionCategory $OptionCategory)
    {
        $em = $this->getEntityManager();
        $em->getConnection()->beginTransaction();
        try {
            $rank = $OptionCategory->getRank();
            $Option = $OptionCategory->getOption();

            $em->createQueryBuilder()
                    ->update('Plugin\ProductOption\Entity\OptionCategory', 'oc')
                    ->set('oc.rank', 'oc.rank - 1')
                    ->where('oc.rank > :rank AND oc.Option = :Option')
                    ->setParameter('rank', $rank)
                    ->setParameter('Option', $Option)
                    ->getQuery()
                    ->execute();

            $OptionCategory->setDelFlg(1);
            $em->persist($OptionCategory);
            $em->flush();

            $em->getConnection()->commit();
        } catch (\Exception $e) {
            $em->getConnection()->rollback();

            return false;
        }

        return true;
    }

}
