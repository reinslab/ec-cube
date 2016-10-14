<?php
/*
* This file is part of EC-CUBE
*
* Copyright(c) 2000-2015 LOCKON CO.,LTD. All Rights Reserved.
* http://www.lockon.co.jp/
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Plugin\TransportCSVexportB2\ServiceProvider;

use Silex\Application as BaseApplication;
use Silex\ServiceProviderInterface;

class TransportCSVexportB2ServiceProvider implements ServiceProviderInterface
{
    public function register(BaseApplication $app)
    {
        // Setting
        $app->match('/' . $app["config"]["admin_route"] . '/plugin/transport_csv_export_b2/config', '\\Plugin\\TransportCSVexportB2\\Controller\\ConfigController::edit')->bind('plugin_TransportCSVexportB2_config');
        // ExportB2
        $app->match('/' . $app["config"]["admin_route"] . '/order/export/b2', '\\Plugin\\TransportCSVexportB2\\Controller\\TransportCSVexportB2Controller::exportB2')->bind('admin_order_export_b2');
        $app->match('/' . $app["config"]["admin_route"] . '/order/export/b2t', '\\Plugin\\TransportCSVexportB2\\Controller\\TransportCSVexportB2Controller::exportB2t')->bind('admin_order_export_b2t');
        $app->match('/' . $app["config"]["admin_route"] . '/order/ajax/b2', '\\Plugin\\TransportCSVexportB2\\Controller\\TransportCSVexportB2Controller::ajaxB2')->bind('admin_order_ajax_b2');

        // 不要？
        $app['eccube.plugin.transport_csv_export_b2.repository.transport_csv_export_b2_plugin'] = $app->share(function () use ($app) {
            return $app['orm.em']->getRepository('Plugin\TransportCSVexportB2\Entity\TransportCSVexportB2Plugin');
        });

        $app['eccube.plugin.transport_csv_export_b2.repository.transport_csv_export_b2_customer'] = $app->share(function () use ($app) {
            return $app['orm.em']->getRepository('Plugin\TransportCSVexportB2\Entity\TransportCSVexportB2Customer');
        });
        
        // 型登録
        $app['form.types'] = $app->share($app->extend('form.types', function ($types) use ($app) {
            $types[] = new \Plugin\TransportCSVexportB2\Form\Type\TransportCSVexportB2Type($app);
            return $types;
        }));

    }

    public function boot(BaseApplication $app)
    {
    }
}
