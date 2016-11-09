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


class FrontCartPoint extends AbstractWorkPlace
{
    public function createTwig(TemplateEvent $event)
    {
        if(!$this->app['eccube.productoption.service.util']->checkInstallPlugin('Point'))return;
        
        $PointInfo = $this->app['eccube.plugin.point.repository.pointinfo']->getLastInsertData();
        $pointRate = $PointInfo->getPlgPointConversionRate();

        $point = array();
        $point['rate'] = $pointRate;

        if ($this->app->isGranted('ROLE_USER')) {
            $calculator = $this->app['eccube.plugin.point.calculate.helper.factory'];
            $Customer = $this->app->user();
            $parameters = $event->getParameters();
            $calculator->addEntity('Customer', $Customer);
            $calculator->addEntity('Cart', $parameters['Cart']);

            // 現在の保有ポイント
            $currentPoint = $calculator->getPoint();
            // カートの加算ポイント
            $addPoint = $calculator->getAddPointByCart();
            // getPointはnullを返す場合がある.
            $point['current'] = is_null($currentPoint) ? 0 : $currentPoint;
            $point['add'] = $addPoint;
            
            $calculator = $this->app['eccube.productoption.calculate.helper.factory'];
            $parameters = $event->getParameters();
            $calculator->addEntity('Customer', $Customer);
            $calculator->addEntity('Cart', $parameters['Cart']);
            $newAddPoint = $calculator->getAddPointByCart();

            $template = 'Point/Resource/template/default/Event/Cart/point_box.twig';
        } else {
            $template = 'Point/Resource/template/default/Event/Cart/point_box_no_customer.twig';
        }

        $search = $this->app->renderView($template, array('point' => $point));
        if(isset($newAddPoint))$point['add'] = $newAddPoint;
        $replace = $this->app->renderView($template, array('point' => $point));
        
        $source = $event->getSource();
        $source = str_replace($search, $replace, $source);
        $event->setSource($source);
    }
}
