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


class FrontMypage extends AbstractWorkPlace
{
    public function createTwig(TemplateEvent $event)
    {
        $app = $this->app;
        $parameters = $event->getParameters();

        $pagination = $parameters['pagination'];
        $plgOrderDetails = array();
        
        foreach($pagination as $Order){
            $orderDetails = $Order->getOrderDetails();
            $plgOrderDetails[$Order->getId()] = $app['eccube.productoption.service.util']->getPlgOrderDetails($orderDetails);
        }

        $source = $event->getSource();
        if(preg_match('/<(.*)\s*id="history_detail_list__category_name.*>\n/',$source, $result)){
            $start_tag = $result[0];
            $tag_name = trim($result[1]);
            $end_tag = '</' . $tag_name . '>';
            $start_index = strpos($source, $start_tag);
            $end_index = strpos($source, $end_tag, $start_index);
            $search = substr($source, $start_index, ($end_index - $start_index));
                
            $snipet = file_get_contents($app['config']['plugin_realdir']. '/ProductOption/Resource/template/default/Mypage/detail_option.twig');
            $replace = $search.$snipet;
            $source = str_replace($search, $replace, $source);
        }       

        $event->setSource($source);
        $parameters['plgOrderDetails'] = $plgOrderDetails;
        $event->setParameters($parameters);
    }
}
