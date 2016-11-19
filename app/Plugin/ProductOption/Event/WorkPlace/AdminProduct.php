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


class AdminProduct extends AbstractWorkPlace
{
    public function createTwig(TemplateEvent $event)
    {
        $app = $this->app;

        $source = $event->getSource();
        
        if(preg_match('/admin_product_product_delete.*>/',$source, $result)){
            $search = $result[0];
            $snipet = file_get_contents($app['config']['plugin_realdir']. '/ProductOption/Resource/template/admin/Product/index.twig');
            $replace = $search. $snipet;
            $source = str_replace($search, $replace, $source);
        }
        
        if(preg_match('/CSVダウンロード<\/a><\/li>/',$source, $result)){
            $search = $result[0];
            $snipet = file_get_contents($app['config']['plugin_realdir']. '/ProductOption/Resource/template/admin/Product/csv_download.twig');
            $replace = $search. $snipet;
            $source = str_replace($search, $replace, $source);
        }

        $event->setSource($source);
    }
    
    public function save(EventArgs $event)
    {
        $app = $this->app;
        
        $Product = $event->getArgument('Product');
        $CopyProduct = $event->getArgument('CopyProduct');
        
        $oldProductOptions = $app['eccube.productoption.repository.product_option']->getListByProductId($Product->getId());

        foreach ($oldProductOptions as $oldProductOption) {
            $newProductOption = new \Plugin\ProductOption\Entity\ProductOption();            
            $newProductOption->setProduct($CopyProduct);
            $newProductOption->setProductId($CopyProduct->getId());
            $Option = $oldProductOption->getOption();
            $newProductOption->setOption($Option);
            $newProductOption->setOptionId($Option->getId());
            $newProductOption->setRank($oldProductOption->getRank());
            $app['eccube.productoption.repository.product_option']->save($newProductOption);
        }
    }
}
