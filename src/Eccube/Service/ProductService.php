<?php
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2015 LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace Eccube\Service;

use Eccube\Application;
use Eccube\Entity\Order;
use Eccube\Entity\Product;
use Eccube\Entity\ProductClass;
use Eccube\Entity\ClassCategory;
use Eccube\Entity\ClassName;

/**
 * @deprecated since 3.0.0, to be removed in 3.1
 */
class ProductService
{
    /** @var \Eccube\Application */
    public $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * 商品種別取得
     *
     * @param Order $Order
     * @return int
     * @deprecated since 3.0.0, to be removed in 3.1
     */
    public function getProductType(Product $Product)
    {
    	//product_class_idを取得
    	$objProductClass = $Product->getProductClasses();
    	$arrProductClass = $objProductClass->toArray();
    	$product_class_id = $arrProductClass[0]->getId();
    	$product_type = $arrProductClass[0]->getProductType();
    	$product_type_id = $product_type->getId();
        return $product_type_id;
    }

    /**
     * 商品種別判定(印刷物商品か否か)
     *
     * @param Order $Order
     * @return int
     * @deprecated since 3.0.0, to be removed in 3.1
     */
    public function isPrintProduct(Product $Product)
    {
    	//商品種別取得
    	$product_type = $this->getProductType($Product);
    	
    	//印刷物商品の場合はTrueを戻す
    	if ( $product_type == $this->app['config']['product_type_print'] ) {
    		return true;
    	}
        return false;
    }

    /**
     * 商品種別判定(印刷物商品か否か)
     *
     * @param Order $Order
     * @return int
     * @deprecated since 3.0.0, to be removed in 3.1
     */
    public function isPrintProductByOrder(Order $Order)
    {
        $objOrderDetail = $Order->getOrderDetails();
        $arrOrderDetail = $objOrderDetail->toArray();
        $flgPrintItem = false;
    	$objProduct = $arrOrderDetail[0]->getProduct();
        //印刷商品判定
        $flgPrintItem = $this->isPrintProduct($objProduct);

        return $flgPrintItem;
    }

    /**
     * 商品種別判定(印刷物商品か否か)
     *
     * @param Order $Order
     * @return int
     * @deprecated since 3.0.0, to be removed in 3.1
     */
    public function getProductClassId(Product $Product)
    {
    	//product_class_idを取得
    	$objProductClass = $Product->getProductClasses();
    	$arrProductClass = $objProductClass->toArray();
    	$product_class_id = $arrProductClass[0]->getId();
    	
        return $product_class_id;
    }
}
