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


class AdminOrderEditSearchProduct extends AbstractWorkPlace
{
    public function createTwig(TemplateEvent $event)
    {
        $app = $this->app;
        $parameters = $event->getParameters();
        
        $ProductOptions = array();
        
        $Products = $parameters['Products'];
        foreach($Products as $product){
            $ProductOptions[$product->getId()] = $app['eccube.productoption.repository.product_option']->getListByProductId($product->getId());
        }
        
        $source = $event->getSource();
        
        if(preg_match('/<(.*)\s*class="extra-form.*>\n/',$source, $result)){
            $start_tag = $result[0];
            $tag_name = trim($result[1]);
            $index = strpos($source, $start_tag);
            $search_source = substr($source, 0 , $index);
            $start_index = strrpos($search_source, '<td>');
            $end_index = strrpos($search_source, '</td>');
            $search = substr($source, $start_index, ($end_index - $start_index));

            $snipet = file_get_contents($app['config']['plugin_realdir']. '/ProductOption/Resource/template/admin/Order/search_product_option.twig');
            $replace = $search.$snipet;
            $source = str_replace($search, $replace, $source);
        }
        
        if(preg_match('/function\s*fnAddOrderDetail.*{\n/',$source, $result)){
            $search = $result[0];
            $snipet = file_get_contents($app['config']['plugin_realdir']. '/ProductOption/Resource/template/admin/Order/search_product_js.twig');
            $replace = $search.$snipet;
            $source = str_replace($search, $replace, $source);
        }

        $event->setSource($source);
        $parameters['ProductOptions'] = $ProductOptions;
        $parameters['optionIds'] = $app['serializer']->serialize($app['eccube.productoption.repository.option']->getIds(), 'json');
        $event->setParameters($parameters);
    }
}
