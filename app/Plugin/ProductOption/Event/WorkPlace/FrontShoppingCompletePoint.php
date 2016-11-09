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

namespace Plugin\ProductOption\Event\WorkPlace;

use Eccube\Event\EventArgs;
use Eccube\Event\TemplateEvent;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;


class FrontShoppingCompletePoint extends AbstractWorkPlace
{
    public function save(EventArgs $event)
    {
        if(!$this->app['eccube.productoption.service.util']->checkInstallPlugin('Point'))return;
        if(!$this->app->isGranted('ROLE_USER'))return;
        
        $Order = $event->getArgument('Order');
        
        $usePoint = $this->app['eccube.plugin.point.repository.point']->getLatestPreUsePoint($Order);
        
        $calculator = $this->app['eccube.productoption.calculate.helper.factory'];
        $calculator->addEntity('Order', $Order);
        $calculator->addEntity('Customer', $Order->getCustomer());
        $calculator->setUsePoint($usePoint * -1);

        $addPoint = $calculator->getAddPointByOrder();
        if (is_null($addPoint)) {
            $addPoint = 0;
        }
        

        $qb = $this->app['orm.em']->createQueryBuilder()
            ->select('p')
            ->from('Plugin\Point\Entity\Point','p')
            ->andWhere('p.customer_id = :customer_id')
            ->andWhere('p.order_id = :order_id')
            ->andWhere('p.plg_point_type = :point_type')
            ->setParameter('customer_id', $Order->getCustomer()->getId())
            ->setParameter('order_id', $Order->getId())
            ->setParameter('point_type', \Plugin\Point\Helper\PointHistoryHelper\PointHistoryHelper::STATE_ADD)
            ->orderBy('p.plg_point_id', 'desc')
            ->setMaxResults(1);

        $Point = $qb->getQuery()->getSingleResult();
        
        if($Point){
            $Point->setPlgDynamicPoint($addPoint);
            $this->app['orm.em']->persist($Point);
            $this->app['orm.em']->flush();
            
            $this->app['monolog.point']->addInfo('productoption update start');
            $this->app['monolog.point']->addInfo('update add point', array(
                    'customer_id' => $Order->getCustomer()->getId(),
                    'order_id' => $Order->getId(),
                    'add point' => $addPoint,
                    'use point' => $usePoint,
                )
            );
            $this->app['monolog.point']->addInfo('update end');
        }
        
        //snapshot
        $SnapShot = $this->app['eccube.plugin.point.repository.pointsnapshot']->findOneBy(array('Order' => $Order, 'Customer' => $Order->getCustomer()));
        if($SnapShot){
            $SnapShot->setPlgPointAdd($addPoint);
            $this->app['orm.em']->persist($SnapShot);
            $this->app['orm.em']->flush();
        }
    }
}
