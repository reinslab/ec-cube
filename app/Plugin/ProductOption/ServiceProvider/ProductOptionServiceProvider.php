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

namespace Plugin\ProductOption\ServiceProvider;

use Eccube\Application;
use Silex\Application as BaseApplication;
use Silex\ServiceProviderInterface;

class ProductOptionServiceProvider implements ServiceProviderInterface
{

    public function register(BaseApplication $app)
    {
        // admin/navi
        $app['config'] = $app->share($app->extend('config', function ($config) {
            $nav = $config['nav'];
            foreach ($nav as $key => $val) {
                if ("product" == $val['id']) {
                    $childNavi['id'] = "product_option";
                    $childNavi['name'] = "オプション管理";
                    $childNavi['url'] = "admin_product_option";
                    $nav[$key]['child'][] = $childNavi;
                    
                    $childNavi['id'] = "admin_product_option_import";
                    $childNavi['name'] = "オプションCSV割当";
                    $childNavi['url'] = "admin_product_option_import";
                    $nav[$key]['child'][] = $childNavi;
                    
                    continue;
                }
                if ("setting" == $val['id']) {
                    $childNavi['id'] = "admin_setting_productoption";
                    $childNavi['name'] = "商品オプションプラグイン設定";
                    $childNavi['url'] = "admin_setting_productoption";
                    $nav[$key]['child'][] = $childNavi;
                    continue;
                }
            }            
            $config['nav'] = $nav;                  

            return $config;
        }));

        // Form/Type
        $app['form.types'] = $app->share($app->extend('form.types', function ($types) use($app) {
            $types[] = new \Plugin\ProductOption\Form\Type\Master\TypeType($app);
            $types[] = new \Plugin\ProductOption\Form\Type\Admin\OptionType($app);
            $types[] = new \Plugin\ProductOption\Form\Type\Admin\OptionCategoryType($app);
            $types[] = new \Plugin\ProductOption\Form\Type\Admin\ProductOptionType($app);
            $types[] = new \Plugin\ProductOption\Form\Type\Admin\OrderType($app);
            $types[] = new \Plugin\ProductOption\Form\Type\Admin\OrderDetailOptionType($app);
            $types[] = new \Plugin\ProductOption\Form\Type\Admin\ShipmentItemOptionType($app);
            $types[] = new \Plugin\ProductOption\Form\Type\Admin\OptionSelectType($app);
            $types[] = new \Plugin\ProductOption\Form\Type\Admin\ConfigType($app);
            $types[] = new \Plugin\ProductOption\Form\Type\ShippingMultipleType($app);
            $types[] = new \Plugin\ProductOption\Form\Type\ShippingMultipleItemType($app);
            return $types;
        }));

        // Form/Extention
        $app['form.type.extensions'] = $app->share($app->extend('form.type.extensions', function ($extensions) use ($app) {
            $extensions[] = new \Plugin\ProductOption\Form\Extension\AddOptionCartExtension($app);
                    
            return $extensions;
        }));

        //Repository
        $app['eccube.productoption.repository.option'] = $app->share(function () use ($app) {
            return $app['orm.em']->getRepository('Plugin\ProductOption\Entity\Option');
        });
        $app['eccube.productoption.repository.option_category'] = $app->share(function () use ($app) {
            return $app['orm.em']->getRepository('Plugin\ProductOption\Entity\OptionCategory');
        });
        $app['eccube.productoption.repository.product_option'] = $app->share(function () use ($app) {
            return $app['orm.em']->getRepository('Plugin\ProductOption\Entity\ProductOption');
        });
        $app['eccube.productoption.repository.order_option'] = $app->share(function () use ($app) {
            return $app['orm.em']->getRepository('Plugin\ProductOption\Entity\OrderOption');
        });
        $app['eccube.productoption.repository.order_detail'] = $app->share(function () use ($app) {
            return $app['orm.em']->getRepository('Plugin\ProductOption\Entity\OrderDetail');
        });
        $app['eccube.productoption.repository.shipment_item'] = $app->share(function () use ($app) {
            return $app['orm.em']->getRepository('Plugin\ProductOption\Entity\ShipmentItem');
        });
        $app['eccube.productoption.repository.master.type'] = $app->share(function () use ($app) {
            return $app['orm.em']->getRepository('Plugin\ProductOption\Entity\Master\Type');
        });

        // Service
        $app['eccube.productoption.service.util'] = $app->share(function () use ($app) {
            return new \Plugin\ProductOption\Service\UtilService($app);
        });
        $app['eccube.productoption.service.cart'] = $app->share(function () use ($app) {
            return new \Plugin\ProductOption\Service\ProductOptionCartService($app);
        });
        $app['eccube.productoption.service.shopping'] = $app->share(function () use ($app) {
            return new \Plugin\ProductOption\Service\ShoppingService($app , $app['eccube.service.cart'], $app['eccube.service.order']);
        });
        if($app['eccube.productoption.service.util']->checkInstallPlugin('Point')){
            $app['eccube.productoption.calculate.helper.factory'] = $app->share(function () use ($app) {
                return new \Plugin\ProductOption\Helper\PointCalculateHelper($app);
            });
        }

        //Routing
        $app->match('/' . $app["config"]["admin_route"] . '/product/option_export', '\Plugin\ProductOption\Controller\Admin\ProductController::export')->bind('admin_product_option_export');
        $app->match('/' . $app["config"]["admin_route"] . '/product/option_import', '\Plugin\ProductOption\Controller\Admin\CsvImportController::import')->bind('admin_product_option_import');

        $app->match('/' . $app["config"]["admin_route"] . '/product/option', '\Plugin\ProductOption\Controller\Admin\OptionController::index')->bind('admin_product_option');
        $app->match('/' . $app["config"]["admin_route"] . '/product/option/new', '\Plugin\ProductOption\Controller\Admin\OptionController::index')->bind('admin_product_option_new');
        $app->match('/' . $app["config"]["admin_route"] . '/product/option/{id}/edit', '\Plugin\ProductOption\Controller\Admin\OptionController::index')->assert('id', '\d+')->bind('admin_product_option_edit');
        $app->match('/' . $app["config"]["admin_route"] . '/product/option/{id}/delete', '\Plugin\ProductOption\Controller\Admin\OptionController::delete')->assert('id', '\d+')->bind('admin_product_option_delete');
        $app->match('/' . $app["config"]["admin_route"] . '/product/option/{id}/up', '\Plugin\ProductOption\Controller\Admin\OptionController::up')->assert('id', '\d+')->bind('admin_product_option_up');
        $app->match('/' . $app["config"]["admin_route"] . '/product/option/{id}/down', '\Plugin\ProductOption\Controller\Admin\OptionController::down')->assert('id', '\d+')->bind('admin_product_option_down');
        $app->match('/' . $app["config"]["admin_route"] . '/product/option/rank/move', '\Plugin\ProductOption\Controller\Admin\OptionController::moveRank')->bind('admin_product_option_rank_move');

        $app->match('/' . $app["config"]["admin_route"] . '/product/option_category/{option_id}', '\Plugin\\ProductOption\Controller\Admin\OptionCategoryController::index')->assert('option_id', '\d+')->bind('admin_product_option_category');
        $app->match('/' . $app["config"]["admin_route"] . '/product/option_category/{option_id}/new', '\Plugin\ProductOption\Controller\Admin\OptionCategoryController::index')->assert('option_id', '\d+')->bind('admin_product_option_category_new');
        $app->match('/' . $app["config"]["admin_route"] . '/product/option_category/{option_id}/{id}/edit', '\Plugin\ProductOption\Controller\Admin\OptionCategoryController::index')->assert('option_id', '\d+')->assert('id', '\d+')->bind('admin_product_option_category_edit');
        $app->match('/' . $app["config"]["admin_route"] . '/product/option_category/{option_id}/{id}/delete', '\Plugin\ProductOption\Controller\Admin\OptionCategoryController::delete')->assert('option_id', '\d+')->assert('id', '\d+')->bind('admin_product_option_category_delete');
        $app->match('/' . $app["config"]["admin_route"] . '/product/option_category/{option_id}/{id}/up', '\Plugin\ProductOption\Controller\Admin\OptionCategoryController::up')->assert('option_id', '\d+')->assert('id', '\d+')->bind('admin_product_option_category_up');
        $app->match('/' . $app["config"]["admin_route"] . '/product/option_category/{option_id}/{id}/down', '\Plugin\ProductOption\Controller\Admin\OptionCategoryController::down')->assert('option_id', '\d+')->assert('id', '\d+')->bind('admin_product_option_category_down');
        $app->match('/' . $app["config"]["admin_route"] . '/product/option_category/rank/move', '\Plugin\ProductOption\Controller\Admin\OptionCategoryController::moveRank')->bind('admin_product_option_category_rank_move');
        $app->post('/' . $app["config"]["admin_route"] . '/product/option_category/image/add', '\Plugin\\ProductOption\Controller\Admin\OptionCategoryController::addImage')->bind('admin_product_option_category_image_add');
        
        $app->match('/' . $app["config"]["admin_route"] . '/product/product/option/{id}', '\Plugin\ProductOption\Controller\Admin\ProductOptionController::index')->assert('id', '\d+')->bind('admin_product_product_option');
        $app->match('/' . $app["config"]["admin_route"] . '/product/product/option/{id}/edit', '\Plugin\ProductOption\Controller\Admin\ProductOptionController::edit')->assert('id', '\d+')->bind('admin_product_product_option_edit');
        $app->match('/' . $app["config"]["admin_route"] . '/product/product/option/rank/{id}', '\Plugin\ProductOption\Controller\Admin\ProductOptionRankController::index')->assert('id', '\d+')->bind('admin_product_product_option_rank');
        $app->match('/' . $app["config"]["admin_route"] . '/product/product/option/rank/move', '\Plugin\ProductOption\Controller\Admin\ProductOptionRankController::moveRank')->bind('admin_product_product_option_rank_move');
        $app->match('/' . $app["config"]["admin_route"] . '/setting/productoption', '\Plugin\ProductOption\Controller\Admin\ConfigController::index')->bind('admin_setting_productoption');
        
        $app->put('/cart/up/{productClassId}/{cartNo}', '\Plugin\ProductOption\Controller\CartController::up')->bind('plg_productoption_cart_up')->assert('productClassId', '\d+')->assert('cartNo','\d+');
        $app->put('/cart/down/{productClassId}/{cartNo}', '\Plugin\ProductOption\Controller\CartController::down')->bind('plg_productoption_cart_down')->assert('productClassId', '\d+')->assert('cartNo','\d+');
        $app->put('/cart/remove/{productClassId}/{cartNo}', '\Plugin\ProductOption\Controller\CartController::remove')->bind('plg_productoption_cart_remove')->assert('productClassId', '\d+')->assert('cartNo','\d+');
        
        // locale message
        $app['translator'] = $app->share($app->extend('translator', function ($translator, \Silex\Application $app) {
            $translator->addLoader('yaml', new \Symfony\Component\Translation\Loader\YamlFileLoader());
            
            $file = __DIR__ . '/../Resource/locale/message.' . $app['locale'] . '.yml';
            if (file_exists($file)) {
                $translator->addResource('yaml', $file, $app['locale']);
            }

            return $translator;
        }));
    }

    public function boot(BaseApplication $app)
    {
        
    }

}
