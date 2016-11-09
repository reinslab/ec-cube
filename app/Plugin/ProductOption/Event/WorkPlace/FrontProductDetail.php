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


class FrontProductDetail extends AbstractWorkPlace
{
    public function createTwig(TemplateEvent $event)
    {
        $app = $this->app;
        $request = $app['request'];
        $parameters = $event->getParameters();
        $source = $event->getSource();
        
        $Product = $parameters['Product'];
        $ProductOptions = $app['eccube.productoption.repository.product_option']->getListByProductId($Product->getId());
        
        $isActive = false;
        $optionPrice = array();
        $optionPoint = array();
        foreach($ProductOptions as $ProductOption){
            $OptionCategories = $ProductOption->getOption()->getOptionCategories();
            if($OptionCategories){
                foreach($OptionCategories as $OptionCategory){
                    if(strlen($OptionCategory->getValue()) > 0)$isActive = true;
                    $price = $OptionCategory->getValue();
                    if($price === null)$price = 0;
                    $optionPrice[$OptionCategory->getId()] = $price;
                    if($this->app['eccube.productoption.service.util']->checkInstallPlugin('Point')){
                        $optionPoint[$OptionCategory->getId()] = $app['eccube.productoption.calculate.helper.factory']->getPointByOption($Product, $price);
                    }
                    $ProductClasses = $Product->getProductClasses();
                }
            }
        }
        
        $taxRules = array();
        foreach($Product->getProductClasses() as $ProductClass){
            if(!isset($default_class_id))$default_class_id = $ProductClass->getId();
            $TaxRule = $app['eccube.repository.tax_rule']->getByRule($Product, $ProductClass);
            $taxRules[$ProductClass->getId()]['tax_rate'] = $TaxRule->getTaxRate();
            $taxRules[$ProductClass->getId()]['tax_rule'] = $TaxRule->getCalcRule()->getId();
        }
        
        $builder = $app['form.factory']->createNamedBuilder('', 'add_cart', null, array(
            'product' => $Product,
            'id_add_product_id' => false,
            'product_option' => $ProductOptions,
        ));
        $form = $builder->getForm();
        
        if(preg_match('/<!\-\-\s*option_price\s*\-\->/',$source, $result)){
            $search = $result[0];
            $snipet = '';
            if(file_exists($app['config']['template_realdir']. '/Product/option_price.twig')){
                $snipet = file_get_contents($app['config']['template_realdir']. '/Product/option_price.twig');
            }
            if(strlen($snipet) == 0){
                $snipet = file_get_contents($app['config']['plugin_realdir']. '/ProductOption/Resource/template/default/Product/option_price.twig');
            }
            $replace = $snipet;
            $source = str_replace($search, $replace, $source);
        }else{
            $isActive = false;
        }
        
        if($this->app['eccube.productoption.service.util']->checkInstallPlugin('Point')){
            if(preg_match('/<!\-\-\s*option_point\s*\-\->/',$source, $result)){
                $search = $result[0];
                $snipet = '';
                if(file_exists($app['config']['template_realdir']. '/Product/option_point.twig')){
                    $snipet = file_get_contents($app['config']['template_realdir']. '/Product/option_point.twig');
                }
                if(strlen($snipet) == 0){
                    $snipet = file_get_contents($app['config']['plugin_realdir']. '/ProductOption/Resource/template/default/Product/option_point.twig');
                }
                $replace = $snipet;
                $source = str_replace($search, $replace, $source);
            }
        }
        
        if(preg_match('/<!--\s*option\s*-->/',$source, $result)){
            $search = $result[0];
            $snipet = '';
            if(file_exists($app['config']['template_realdir']. '/Product/option.twig')){
                $snipet = file_get_contents($app['config']['template_realdir']. '/Product/option.twig');
            }
            if(strlen($snipet) == 0){
                $snipet = file_get_contents($app['config']['plugin_realdir']. '/ProductOption/Resource/template/default/Product/option.twig');
            }
            $replace = $snipet;
            $source = str_replace($search, $replace, $source);
        }elseif(preg_match('/<.*id="detail_cart_box__cart_quantity/',$source, $result)){
            $search = $result[0];
            $snipet = '';
            if(file_exists($app['config']['template_realdir']. '/Product/option.twig')){
                $snipet = file_get_contents($app['config']['template_realdir']. '/Product/option.twig');
            }
            if(strlen($snipet) == 0){
                $snipet = file_get_contents($app['config']['plugin_realdir']. '/ProductOption/Resource/template/default/Product/option.twig');
            }
            $replace = $snipet.$search;
            $source = str_replace($search, $replace, $source);
        }
        
        if(preg_match('/\{%\s*block\s*javascript\s*%\}/',$source, $result)){
            $start_tag = $result[0];
            $index = strpos($source, $start_tag);
            $search_source = substr($source, $index);
            $end_index = strpos($search_source, '{% endblock %}');
            $search = substr($source, $index, ($end_index));

            $snipet = file_get_contents($app['config']['plugin_realdir']. '/ProductOption/Resource/template/default/Product/option_js.twig');
            $replace = $search.$snipet;
            $source = str_replace($search, $replace, $source);
        }        

        $pos = strrpos($source, '{% endblock %}');
        if($pos != false){
            $search = substr($source,0,$pos);
            $snipet = file_get_contents($app['config']['template_realdir']. '/Product/option_description.twig');
            if(strlen($snipet) == 0){
                $snipet = file_get_contents($app['config']['plugin_realdir']. '/ProductOption/Resource/template/default/Product/option_description.twig');
            }            
            $replace = $search . $snipet;
            $source = str_replace($search, $replace, $source);
        }
        
        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if($form->isValid()) {
                //追加のバリーデーション
            }
        }

        $parameters['form'] = $form->createView();
        $parameters['ProductOptions'] = $ProductOptions;
        $parameters['optionPrice'] = $optionPrice;
        $parameters['optionPoint'] = $optionPoint;
        $parameters['taxRules'] = $taxRules;
        $parameters['default_class_id'] = $default_class_id;
        $parameters['isActive'] = $isActive;
        $event->setSource($source);
        $event->setParameters($parameters);
    }
    
    public function execute(EventArgs $event)
    {   
        $app = $this->app;
        $request = $app['request'];
        $Product = $event->getArgument('Product');
        $ProductOption = $app['eccube.productoption.repository.product_option']->getListByProductId($Product->getId());        
        
        $builder = $app['form.factory']->createNamedBuilder('', 'add_cart', null, array(
            'product' => $Product,
            'id_add_product_id' => false,
            'product_option' => $ProductOption,
        ));
        $form = $builder->getForm();
        
        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            $data = $form->getData();
            if ($data['mode'] !== 'add_favorite') {
                if ($form->isValid()) {
                    try {
                        $Options = array();
                        foreach($data as $option_key => $value){
                            if(strpos($option_key,'productoption') !== false){
                                $option_id = str_replace('productoption', '', $option_key);
                                $Option = $app['eccube.productoption.repository.option']->find($option_id);
                                if($Option){
                                    $add = true;
                                    if($Option->getType()->getId() == 1 || $Option->getType()->getId() == 2 ){
                                        if($Option->getDisableCategory()){
                                            if($Option->getDisableCategory()->getId() == $value){
                                                $add = false;
                                            }
                                        }
                                    }elseif($Option->getType()->getId() == 3 || $Option->getType()->getId() == 4){
                                        if(strlen($value) == 0)$add = false;
                                    }
                                    if($add){
                                        $Options[$option_key] = (string)$value;
                                    }
                                }
                            }
                        }
                        $app['eccube.service.cart']->addProduct($data['product_class_id'], $data['quantity'])->save();
                        $app['eccube.productoption.service.cart']->addProductOption($data['product_class_id'], $Options, $data['quantity']);
                    } catch (CartException $e) {
                        $app->addRequestError($e->getMessage());
                    }

                    $app->redirect($app->url('cart'))->send();
                    exit;
                }
            }
        }   
    }
}
