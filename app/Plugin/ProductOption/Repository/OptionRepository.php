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
use Plugin\ProductOption\Entity\Option;

class OptionRepository extends EntityRepository
{

    public function getList()
    {
        $qb = $this->createQueryBuilder('o')
                ->orderBy('o.rank', 'DESC')
                ->andWhere('o.del_flg = 0');
        $Options = $qb->getQuery()
                ->getResult();

        return $Options;
    }
    
    public function getIds()
    {
        $qb = $this->createQueryBuilder('o')
                ->select('o.id')
                ->orderBy('o.rank', 'DESC')
                ->andWhere('o.del_flg = 0');
        $results = $qb->getQuery()
                ->getResult();

        $Ids = array();
        foreach($results as $result){
            $Ids[] = $result['id'];
        }
        return $Ids;
    }

    public function up(\Plugin\ProductOption\Entity\Option $Option)
    {
        $em = $this->getEntityManager();
        $em->getConnection()->beginTransaction();
        try {
            $rank = $Option->getRank();

            //
            $Option2 = $this->findOneBy(array('rank' => $rank + 1));
            if (!$Option2) {
                throw new \Exception();
            }
            $Option2->setRank($rank);
            $em->persist($Option2);

            $Option->setRank($rank + 1);

            $em->persist($Option);
            $em->flush();

            $em->getConnection()->commit();
        } catch (\Exception $e) {
            $em->getConnection()->rollback();

            return false;
        }

        return true;
    }

    public function down(\Plugin\ProductOption\Entity\Option $Option)
    {
        $em = $this->getEntityManager();
        $em->getConnection()->beginTransaction();
        try {
            $rank = $Option->getRank();

            //
            $Option2 = $this->findOneBy(array('rank' => $rank - 1));
            if (!$Option2) {
                throw new \Exception();
            }
            $Option2->setRank($rank);
            $em->persist($Option2);

            $Option->setRank($rank - 1);

            $em->persist($Option);
            $em->flush();

            $em->getConnection()->commit();
        } catch (\Exception $e) {
            $em->getConnection()->rollback();

            return false;
        }

        return true;
    }

    public function save(\Plugin\ProductOption\Entity\Option $Option)
    {
        $em = $this->getEntityManager();
        $em->getConnection()->beginTransaction();
        try {
            if (!$Option->getId()) {
                $rank = $this->createQueryBuilder('o')
                        ->select('MAX(o.rank)')
                        ->getQuery()
                        ->getSingleScalarResult();
                if (!$rank) {
                    $rank = 0;
                }
                $Option->setRank($rank + 1);
                $Option->setDelFlg(0);
            }

            $em->persist($Option);
            $em->flush();

            $em->getConnection()->commit();
        } catch (\Exception $e) {
            $em->getConnection()->rollback();

            return false;
        }

        return true;
    }

    public function delete(\Plugin\ProductOption\Entity\Option $Option)
    {
        $em = $this->getEntityManager();
        $em->getConnection()->beginTransaction();
        try {
            if ($Option->getOptionCategories()->count() > 0) {
                foreach($Option->getOptionCategories() as $OptionCategory){
                    $em->getRepository('Plugin\ProductOption\Entity\OptionCategory')
                                ->delete($OptionCategory);
                }
            }

            $ProductOptions = $em->createQueryBuilder()
                                 ->from('Plugin\ProductOption\Entity\ProductOption','po')                    
                                 ->select('po')
                                 ->where('po.Option = :Option')
                                 ->setParameter('Option',$Option)
                                 ->getQuery()
                                 ->getResult();

            if (count($ProductOptions) > 0) {
                foreach($ProductOptions as $ProductOption){
                    $em->getRepository('Plugin\ProductOption\Entity\ProductOption')
                                ->delete($ProductOption);
                }
            }

            $rank = $Option->getRank();
            $em->createQueryBuilder()
                    ->update('Plugin\ProductOption\Entity\Option', 'o')
                    ->set('o.rank', 'o.rank - 1')
                    ->where('o.rank > :rank')->setParameter('rank', $rank)
                    ->getQuery()
                    ->execute();

            $Option->setDelFlg(1);
            $em->persist($Option);
            $em->flush();

            $em->getConnection()->commit();
        } catch (\Exception $e) {
            $em->getConnection()->rollback();

            return false;
        }

        return true;
    }

}
