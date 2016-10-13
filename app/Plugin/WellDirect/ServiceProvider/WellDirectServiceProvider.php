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


		///////////////////////////////////////////////
        // �^�o�^
		///////////////////////////////////////////////
        // ���喼
        $app['form.types'] = $app->share($app->extend('form.types', function ($types) use ($app) {
            $types[] = new \Plugin\WellDirect\Form\Type\SectionType($app);
            return $types;
        }));
    }

    public function boot(BaseApplication $app)
    {
    }
}
