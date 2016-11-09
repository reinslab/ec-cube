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


class FrontShoppingPoint extends AbstractWorkPlace
{
    public function createTwig(TemplateEvent $event)
    {
        if(!$this->app['eccube.productoption.service.util']->checkInstallPlugin('Point'))return;
        if(!$this->app->isGranted('ROLE_USER'))return;
        
        $args = $event->getParameters();

        $Order = $args['Order'];
        $Customer = $Order->getCustomer();

        // ポイント利用画面で入力された利用ポイントを取得
        $usePoint = $this->app['eccube.plugin.point.repository.point']->getLatestPreUsePoint($Order);
        $usePoint = abs($usePoint);

        // 加算ポイントの取得
        $calculator = $this->app['eccube.plugin.point.calculate.helper.factory'];
        $calculator->setUsePoint($usePoint); // calculatorに渡す際は絶対値
        $calculator->addEntity('Order', $Order);
        $calculator->addEntity('Customer', $Customer);
        $addPoint = $calculator->getAddPointByOrder();

        // 受注明細がない場合にnullが返る. 通常では発生し得ないため. その場合は表示を行わない
        if (is_null($addPoint)) {
            return true;
        }

        // 現在の保有ポイント取得
        $currentPoint = $calculator->getPoint();

        // 会員のポイントテーブルにレコードがない場合はnullを返す. その場合は0で表示する
        if (is_null($currentPoint)) {
            $currentPoint = 0;
        }

        // ポイント基本情報を取得
        $PointInfo = $this->app['eccube.plugin.point.repository.pointinfo']->getLastInsertData();

        // ポイント表示用変数作成
        $point = array();
        $point['current'] = $currentPoint;
        $point['use'] = $usePoint;
        $point['add'] = $addPoint;
        $point['rate'] = $PointInfo->getPlgPointConversionRate();

        // 加算ポイント/利用ポイントを表示する
        $search = $this->app->renderView(
            'Point/Resource/template/default/Event/ShoppingConfirm/point_summary.twig',
            array(
                'point' => $point,
            )
        );
        
        $calculator = $this->app['eccube.productoption.calculate.helper.factory'];
        $calculator->setUsePoint($usePoint); // calculatorに渡す際は絶対値
        $calculator->addEntity('Order', $Order);
        $calculator->addEntity('Customer', $Customer);
        $newAddPoint = $calculator->getAddPointByOrder();
        $point['add'] = $newAddPoint;
        
        $replace = $this->app->renderView(
            'Point/Resource/template/default/Event/ShoppingConfirm/point_summary.twig',
            array(
                'point' => $point,
            )
        );
        
        $source = $event->getSource();
        $source = str_replace($search, $replace, $source);
        $event->setSource($source);
    }
}
