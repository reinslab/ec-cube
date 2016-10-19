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
		//ƒ‹[ƒeƒBƒ“ƒOÝ’è
		///////////////////////////////////////////////
        // Œ©Ï•Û‘¶
        $app->match('/cart/eststep', '\Plugin\WellDirect\Controller\WellDirectController::eststep')->bind('cart_eststep');

        // Œ©Ïíœ
        $app->match('/history/delete/{id}', '\Plugin\WellDirect\Controller\WellDirectController::historydelete')->value('id', null)->assert('id', '\d+|')->bind('history_delete');


		///////////////////////////////////////////////
        // Œ^“o˜^
		///////////////////////////////////////////////
        // •”–å–¼
        $app['form.types'] = $app->share($app->extend('form.types', function ($types) use ($app) {
            $types[] = new \Plugin\WellDirect\Form\Type\SectionType($app);
            return $types;
        }));
    }

    public function boot(BaseApplication $app)
    {
    }
}
