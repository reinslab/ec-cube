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
		//���[�e�B���O�ݒ�
		///////////////////////////////////////////////
        // ���ϕۑ�
        $app->match('/cart/eststep', '\Plugin\WellDirect\Controller\WellDirectController::eststep')->bind('cart_eststep');

        // ���ύ폜
        $app->match('/history/delete/{id}', '\Plugin\WellDirect\Controller\WellDirectController::historydelete')->value('id', null)->assert('id', '\d+|')->bind('history_delete');

		// �f�[�^�ē��e
        $app->match('/shopping/{id}', '\Plugin\WellDirect\Controller\::index_reupload')->bind('shopping_confirm_reupload')->assert('id', '\d+');

        // ���e�f�[�^�_�E�����[�h
        $app->match('/' . $app["config"]["admin_route"] . '/order/download/{id}', '\Plugin\WellDirect\Controller\Admin\WellDirectAdminController::pdfDownload')->value('id', null)->assert('id', '\d+|')->bind('admin_order_pdf_download');



		///////////////////////////////////////////////
        // �^�o�^
		///////////////////////////////////////////////
        // Form/Type
        $app['form.types'] = $app->share($app->extend('form.types', function ($types) use($app) {
//            $types[] = new \Plugin\WellDirect\Form\Type\Master\NyukinStatusType($app);
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
