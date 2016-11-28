<?php
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2015 LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */


namespace Plugin\Display\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query;
use Doctrine\ORM\Id\SequenceGenerator;
use Eccube\Common\Constant;
use Eccube\Entity\Master\Disp;

/**
 * DisplayProductRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class DisplayProductRepository extends EntityRepository
{

    /**
     * find list
     * @return mixed
     */
    public function findList()
    {

        $qb = $this->createQueryBuilder('rp')
            ->select('rp, p')
            ->innerJoin('rp.Product', 'p');

        $qb->addOrderBy('rp.rank', 'DESC');

        return $qb->getQuery()->getResult(Query::HYDRATE_ARRAY);

    }

    /**
     * find by rank up
     * @param $rank
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function findByRankUp($rank)
    {
        try {
            $qb = $this->createQueryBuilder('rp')
                ->andWhere('rp.rank > :rank')
                ->addOrderBy('rp.rank', 'ASC')
                ->setMaxResults(1);

            $product = $qb
                ->getQuery()
                ->setParameters(array(
                    'rank' => $rank,
                ))
                ->getSingleResult();

            return $product;
        } catch (NoResultException $e) {
            throw new NotFoundHttpException();
        }
    }

    /**
     * find by rank down
     * @param $rank
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function findByRankDown($rank)
    {
        try {
            $qb = $this->createQueryBuilder('rp')
                ->andWhere('rp.rank < :rank')
                ->addOrderBy('rp.rank', 'DESC')
                ->setMaxResults(1);

            $product = $qb
                ->getQuery()
                ->setParameters(array(
                    'rank' => $rank,
                ))
                ->getSingleResult();

            return $product;
        } catch (NoResultException $e) {
            throw new NotFoundHttpException();
        }
    }

    /**
     * get max rank
     * @return mixed
     */
    public function getMaxRank()
    {
        // 最大のランクを取得する.
        //change from MAX(m.id) to MAX(m.rank)
        $sql = "SELECT MAX(m.rank) AS max_rank FROM Plugin\Display\Entity\DisplayProduct m";
        $q = $this->getEntityManager()->createQuery($sql);

        return $q->getSingleScalarResult();
    }


    public function getDisplayProduct(Disp $Disp)
    {

        $query = $this->createQueryBuilder('rp')
            ->innerJoin('Eccube\Entity\Product', 'p', 'WITH', 'p.id = rp.Product')
            ->where('p.Status = :Disp')
            ->orderBy('rp.rank', 'DESC')
            ->setParameter('Disp', $Disp)
            ->getQuery();

        return $query->getResult();

    }
}
