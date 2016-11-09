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

namespace Plugin\ProductOption\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Constraints as Assert;
use Eccube\Application;
use Eccube\Common\Constant;
use Eccube\Entity\ClassName;
use Eccube\Entity\Product;
use Eccube\Entity\ProductClass;

class ProductOptionRankController extends \Eccube\Controller\AbstractController
{

    /**
     * 商品規格が登録されていなければ新規登録、登録されていれば更新画面を表示する
     */
    public function index(Application $app, Request $request, $id)
    {

        $Product = $app['eccube.repository.product']->find($id);

        if (!$Product) {
            throw new NotFoundHttpException();
        }

        $ProductOptions = $app['eccube.productoption.repository.product_option']->getListByProductId($id);

        return $app->renderView('ProductOption/Resource/template/admin/Product/product_option_rank.twig', array(
                    'Product' => $Product,
                    'ProductOptions' => $ProductOptions,
        ));
    }
    
    public function moveRank(Application $app, Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $ranks = $request->request->all();
            foreach ($ranks as $optionId => $rank) {
                $ProductOption = $app['eccube.productoption.repository.product_option']
                        ->find($optionId);
                $ProductOption->setRank($rank);
                $app['orm.em']->persist($ProductOption);
            }
            $app['orm.em']->flush();
        }
        return true;
    }    

}
