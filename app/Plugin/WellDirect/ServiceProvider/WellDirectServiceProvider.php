<?php
/*
 * Copyright(c) 2015 GMO Payment Gateway, Inc. All rights reserved.
 * http://www.gmo-pg.com/
 */

namespace Plugin\WellDirect\ServiceProvider;

use Silex\Application as BaseApplication;
use Silex\ServiceProviderInterface;

class WellDirectServiceProvider implements ServiceProviderInterface
{
    public function register(BaseApplication $app)
    {
		///////////////////////////////////////////////
		//ルーティング設定
		///////////////////////////////////////////////
        // 見積保存
        $app->match('/cart/eststep', '\Plugin\WellDirect\Controller\WellDirectController::eststep')->bind('cart_eststep');

        // 見積削除
        $app->match('/history/delete/{id}', '\Plugin\WellDirect\Controller\WellDirectController::historydelete')->value('id', null)->assert('id', '\d+|')->bind('history_delete');

		// データ再入稿
        $app->match('/mypage/history/upload/{id}', '\Plugin\WellDirect\Controller\WellDirectController::upload')->value('id', null)->assert('id', '\d+')->bind('history_upload');

        // 見積→注文
        $app->match('/mypage/est2order/{id}', '\Plugin\WellDirect\Controller\WellDirectController::est2order')->value('id', null)->assert('id', '\d+|')->bind('mypage_est2order');
        
        // 見積書ダウンロード
        $app->match('/mypage/estdownload/{id}', '\Plugin\WellDirect\Controller\WellDirectController::estdownload')->value('id', null)->assert('id', '\d+|')->bind('mypage_estdownload');

        // 入稿データダウンロード
        $app->match('/' . $app["config"]["admin_route"] . '/order/download/{id}', '\Plugin\WellDirect\Controller\Admin\WellDirectAdminController::pdfDownload')->value('id', null)->assert('id', '\d+|')->bind('admin_order_pdf_download');

        // 印刷通知
        $app->match('/' . $app["config"]["admin_route"] . '/order/printmail/{id}', '\Plugin\WellDirect\Controller\Admin\WellDirectAdminController::printMail')->value('id', null)->assert('id', '\d+|')->bind('admin_order_print_mail');



		///////////////////////////////////////////////
        // 型登録
		///////////////////////////////////////////////
        // Form/Type
        $app['form.types'] = $app->share($app->extend('form.types', function ($types) use($app) {
            $types[] = new \Plugin\WellDirect\Form\Type\OrderPdfType($app);
            return $types;
        }));

        // Form/Type/Extension
        $app['form.type.extensions'] = $app->share($app->extend('form.type.extensions', function ($extensions) use ($app) {
            $extensions[] = new \Plugin\WellDirect\Form\Extension\WellDirectCustomerExtension($app);
            $extensions[] = new \Plugin\WellDirect\Form\Extension\Admin\WellDirectOrderExtension($app);
            $extensions[] = new \Plugin\WellDirect\Form\Extension\Admin\WellDirectCustomerExtension($app);

            return $extensions;
        }));


        //Repository
        $app['eccube.welldirect.repository.nyukinstatus'] = $app->share(function () use ($app) {
            return $app['orm.em']->getRepository('Plugin\WellDirect\Entity\Master\NyukinStatus');
        });

        // -----------------------------
        // サービスの登録
        // -----------------------------
        // 帳票作成
        $app['eccube.plugin.welldirect.service.order_pdf'] = $app->share(function () use ($app) {
            return new \Plugin\WellDirect\Service\OrderPdfService($app);
        });

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
