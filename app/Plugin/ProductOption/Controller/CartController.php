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

namespace Plugin\ProductOption\Controller;

use Eccube\Application;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Exception\CartException;
use Symfony\Component\HttpFoundation\Request;

class CartController extends \Eccube\Controller\AbstractController
{

    public function up(Application $app, Request $request, $productClassId, $cartNo)
    {

        $this->isTokenValid($app);

        // FRONT_CART_UP_INITIALIZE
        $event = new EventArgs(
            array(
                'productClassId' => $productClassId,
            ),
            $request
        );
        $app['eccube.event.dispatcher']->dispatch(EccubeEvents::FRONT_CART_UP_INITIALIZE, $event);

        try {

            $productClassId = $event->getArgument('productClassId');

            $app['eccube.productoption.service.cart']->upProductQuantity($productClassId, $cartNo)->save();

            // FRONT_CART_UP_COMPLETE
            $event = new EventArgs(
                array(
                    'productClassId' => $productClassId,
                ),
                $request
            );
            $app['eccube.event.dispatcher']->dispatch(EccubeEvents::FRONT_CART_UP_COMPLETE, $event);

            if ($event->hasResponse()) {
                return $event->getResponse();
            }

        } catch (CartException $e) {

            // FRONT_CART_UP_EXCEPTION
            $event = new EventArgs(
                array(
                    'exception' => $e,
                ),
                $request
            );
            $app['eccube.event.dispatcher']->dispatch(EccubeEvents::FRONT_CART_UP_EXCEPTION, $event);

            if ($event->hasResponse()) {
                return $event->getResponse();
            }

            $app->addRequestError($e->getMessage());
        }

        return $app->redirect($app->url('cart'));
    }
    
    public function down(Application $app, Request $request, $productClassId, $cartNo)
    {
        $this->isTokenValid($app);

        // FRONT_CART_DOWN_INITIALIZE
        $event = new EventArgs(
            array(
                'productClassId' => $productClassId,
            ),
            $request
        );
        $app['eccube.event.dispatcher']->dispatch(EccubeEvents::FRONT_CART_DOWN_INITIALIZE, $event);

        try {
            $productClassId = $event->getArgument('productClassId');
            $app['eccube.productoption.service.cart']->downProductQuantity($productClassId,$cartNo)->save();

            // FRONT_CART_UP_COMPLETE
            $event = new EventArgs(
                array(
                    'productClassId' => $productClassId,
                ),
                $request
            );
            $app['eccube.event.dispatcher']->dispatch(EccubeEvents::FRONT_CART_DOWN_COMPLETE, $event);

            if ($event->hasResponse()) {
                return $event->getResponse();
            }

        } catch (CartException $e) {

            // FRONT_CART_DOWN_EXCEPTION
            $event = new EventArgs(
                array(
                    'exception' => $e,
                ),
                $request
            );
            $app['eccube.event.dispatcher']->dispatch(EccubeEvents::FRONT_CART_DOWN_EXCEPTION, $event);

            if ($event->hasResponse()) {
                return $event->getResponse();
            }

            $app->addRequestError($e->getMessage());
        }

        return $app->redirect($app->url('cart'));
    }
    
    public function remove(Application $app, Request $request, $productClassId, $cartNo)
    {
        $this->isTokenValid($app);

        // FRONT_CART_REMOVE_INITIALIZE
        $event = new EventArgs(
            array(
                'productClassId' => $productClassId,
            ),
            $request
        );
        $app['eccube.event.dispatcher']->dispatch(EccubeEvents::FRONT_CART_REMOVE_INITIALIZE, $event);

        $productClassId = $event->getArgument('productClassId');
        $app['eccube.productoption.service.cart']->removeProduct($productClassId,$cartNo)->save();

        // FRONT_CART_REMOVE_COMPLETE
        $event = new EventArgs(
            array(
                'productClassId' => $productClassId,
            ),
            $request
        );
        $app['eccube.event.dispatcher']->dispatch(EccubeEvents::FRONT_CART_REMOVE_COMPLETE, $event);

        if ($event->hasResponse()) {
            return $event->getResponse();
        }

        return $app->redirect($app->url('cart'));
    }
}
