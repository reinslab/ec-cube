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

namespace Plugin\TransportCSVimportB2\ServiceProvider;

use Silex\Application as BaseApplication;
use Silex\ServiceProviderInterface;

class TransportCSVimportB2ServiceProvider implements ServiceProviderInterface
{
    public function register(BaseApplication $app)
    {
        // Setting
        $app->match('/' . $app["config"]["admin_route"] . '/plugin/transport_csv_import_b2/config', '\\Plugin\\TransportCSVimportB2\\Controller\\ConfigController::edit')->bind('plugin_TransportCSVimportB2_config');
        // ImportB2
        $app->match('/' . $app["config"]["admin_route"] . '/order/import/b2', '\\Plugin\\TransportCSVimportB2\\Controller\\TransportCSVimportB2Controller::index')->bind('admin_import_b2');
        // ImportB2mail
        $app->match('/' . $app["config"]["admin_route"] . '/order/mail/b2_mail_all', '\\Plugin\\TransportCSVimportB2\\Controller\\TransportCSVmailB2Controller::mailAll')->bind('admin_b2_mail_all');

        // メニュー登録
        $app['config'] = $app->share($app->extend('config', function ($config) {
            $nav = $config['nav'];
            foreach ($nav as $key => $val) {
                if ("order" == $val["id"]) {
                    $nav[$key]['child'][] = array(
                         "id"           => "import_b2"
                        ,"name"         => "ヤマトB2送り状番号発行済みCSV"
                        ,"url"          => "admin_import_b2"
                    );
                }
            }

            $config['nav'] = $nav;
            return $config;
        }));

        // 不要？
        $app['eccube.plugin.transport_csv.repository.transport_csv_plugin'] = $app->share(function () use ($app) {
            return $app['orm.em']->getRepository('Plugin\TransportCSVimportB2\Entity\TransportCSVimportB2Plugin');
        });

        $app['eccube.plugin.transport_csv.repository.transport_csv_customer'] = $app->share(function () use ($app) {
            return $app['orm.em']->getRepository('Plugin\TransportCSVimportB2\Entity\TransportCSVimportB2Customer');
        });
        
        // フォーム登録
        $app['form.types'] = $app->share($app->extend('form.types', function ($types) use ($app) {
            $types[] = new \Plugin\TransportCSVimportB2\Form\Type\TransportCSVimportB2Type($app);
            $types[] = new \Plugin\TransportCSVimportB2\Form\Type\TransportCSVimportB2InvoiceNumberType($app);
            return $types;
        }));
    }

    public function boot(BaseApplication $app)
    {
    }
}
