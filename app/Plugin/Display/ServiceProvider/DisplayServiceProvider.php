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

namespace Plugin\Display\ServiceProvider;

use Silex\Application as BaseApplication;
use Silex\ServiceProviderInterface;
use Symfony\Component\Yaml\Yaml;

class DisplayServiceProvider implements ServiceProviderInterface
{

    public function register(BaseApplication $app)
    {
        // おすすめ情報テーブルリポジトリ
        $app['eccube.plugin.display.repository.display_product'] = $app->share(function () use ($app) {
            return $app['orm.em']->getRepository('Plugin\Display\Entity\DisplayProduct');
        });

        // 商品展開の一覧
        $app->match('/' . $app["config"]["admin_route"] . '/display', '\Plugin\Display\Controller\DisplayController::index')
            ->value('id', null)->assert('id', '\d+|')
            ->bind('admin_display_list');

        // 商品展開の新規先
        $app->match('/' . $app["config"]["admin_route"] . '/display/new', '\Plugin\Display\Controller\DisplayController::create')
            ->value('id', null)->assert('id', '\d+|')
            ->bind('admin_display_new');

        // 商品展開の新規作成・編集確定
        $app->match('/' . $app["config"]["admin_route"] . '/display/commit', '\Plugin\Display\Controller\DisplayController::commit')
        ->value('id', null)->assert('id', '\d+|')
        ->bind('admin_display_commit');

        // 商品展開の編集
        $app->match('/' . $app["config"]["admin_route"] . '/display/edit/{id}', '\Plugin\Display\Controller\DisplayController::edit')
            ->value('id', null)->assert('id', '\d+|')
            ->bind('admin_display_edit');

        // 商品展開の削除
        $app->match('/' . $app["config"]["admin_route"] . '/display/delete/{id}', '\Plugin\Display\Controller\DisplayController::delete')
        ->value('id', null)->assert('id', '\d+|')
        ->bind('admin_display_delete');

        // 商品展開のランク移動（上）
        $app->match('/' . $app["config"]["admin_route"] . '/display/rank_up/{id}', '\Plugin\Display\Controller\DisplayController::rankUp')
            ->value('id', null)->assert('id', '\d+|')
            ->bind('admin_display_rank_up');

        // 商品展開のランク移動（下）
        $app->match('/' . $app["config"]["admin_route"] . '/display/rank_down/{id}', '\Plugin\Display\Controller\DisplayController::rankDown')
            ->value('id', null)->assert('id', '\d+|')
            ->bind('admin_display_rank_down');

        // 商品検索画面表示
        $app->post('/' . $app["config"]["admin_route"] . '/display/search/product', '\Plugin\Display\Controller\DisplaySearchModelController::searchProduct')
            ->bind('admin_display_search_product');

        // ブロック
        $app->match('/block/display_product_block', '\Plugin\Display\Controller\Block\DisplayController::index')
            ->bind('block_display_product_block');


        // 型登録
        $app['form.types'] = $app->share($app->extend('form.types', function ($types) use ($app) {
            $types[] = new \Plugin\Display\Form\Type\DisplayProductType($app);
            return $types;
        }));

        // サービスの登録
        $app['eccube.plugin.display.service.display'] = $app->share(function () use ($app) {
            return new \Plugin\Display\Service\DisplayService($app);
        });

        // メッセージ登録
        $app['translator'] = $app->share($app->extend('translator', function ($translator, \Silex\Application $app) {
            $translator->addLoader('yaml', new \Symfony\Component\Translation\Loader\YamlFileLoader());

            $file = __DIR__ . '/../Resource/locale/message.' . $app['locale'] . '.yml';
            if (file_exists($file)) {
                $translator->addResource('yaml', $file, $app['locale']);
            }

            return $translator;
        }));

        // メニュー登録
        $app['config'] = $app->share($app->extend('config', function ($config) {
            $addNavi['id'] = 'admin_display';
            $addNavi['name'] = '商品展開管理';
            $addNavi['url'] = 'admin_display_list';
            $nav = $config['nav'];
            foreach ($nav as $key => $val) {
                if ('content' == $val['id']) {
                    $nav[$key]['child'][] = $addNavi;
                }
            }
            $config['nav'] = $nav;
            return $config;
        }));
    }

    public function boot(BaseApplication $app)
    {
    }
}
