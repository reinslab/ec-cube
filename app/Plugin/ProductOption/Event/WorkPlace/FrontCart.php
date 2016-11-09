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


class FrontCart extends AbstractWorkPlace
{
    public function createTwig(TemplateEvent $event)
    {
        $app = $this->app;
        $parameters = $event->getParameters();

        $CartOption = $app['eccube.productoption.service.cart']->getCartOption();
        
        $source = $event->getSource();
        
        if(preg_match('/<.*id="cart_item_list__item_price"/',$source, $result)){
            $search = $result[0];
            $snipet = file_get_contents($app['config']['plugin_realdir']. '/ProductOption/Resource/template/default/Cart/cart_option.twig');
            $replace = $snipet.$search;
            $source = str_replace($search, $replace, $source);
        }
        
        if(preg_match('/\{%\s*for\s*CartItem\s*in\s*Cart.CartItems\s*%\}/',$source, $result)){
            $search = $result[0];
            $snipet = '{% for key, CartItem in Cart.CartItems %}';
            $replace = $snipet;
            $source = str_replace($search, $replace, $source);
        }
        
        if(preg_match("/\{\s*\{\s*url\('cart_up'\s*,\s*\{\s*'productClassId'\s*:\s*ProductClass.id\s*\}\s*\)\s*\}\s*\}/",$source, $result)){
            $search = $result[0];
            $snipet = "{{ url('plg_productoption_cart_up', {'productClassId': ProductClass.id, 'cartNo': key }) }}";
            $replace = $snipet;
            $source = str_replace($search, $replace, $source);
        }
        
        if(preg_match("/\{\s*\{\s*url\('cart_down'\s*,\s*\{\s*'productClassId'\s*:\s*ProductClass.id\s*\}\s*\)\s*\}\s*\}/",$source, $result)){
            $search = $result[0];
            $snipet = "{{ url('plg_productoption_cart_down', {'productClassId': ProductClass.id, 'cartNo': key }) }}";
            $replace = $snipet;
            $source = str_replace($search, $replace, $source);
        }
        
        if(preg_match("/\{\s*\{\s*url\('cart_remove'\s*,\s*\{\s*'productClassId':\s*ProductClass.id\s*\}\s*\)\s*\}\s*\}/",$source, $result)){
            $search = $result[0];
            $snipet = "{{ url('plg_productoption_cart_remove', {'productClassId': ProductClass.id, 'cartNo': key }) }}";
            $replace = $snipet;
            $source = str_replace($search, $replace, $source);
        }

        $event->setSource($source);
        $parameters['CartOptions'] = $CartOption->getCartOptions();
        $event->setParameters($parameters);
    }
}
