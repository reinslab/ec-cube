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

class ProductOptionController extends \Eccube\Controller\AbstractController
{


    public function index(Application $app, Request $request, $id)
    {

        $Product = $app['eccube.repository.product']->find($id);

        if (!$Product) {
            throw new NotFoundHttpException();
        }

        $Options = $app['eccube.productoption.repository.option']->getList();

        foreach ($Options as $Option) {
            $ProductOption = new \Plugin\ProductOption\Entity\ProductOption();
            $ProductOption->setProduct($Product);
            $ProductOption->setOption($Option);
            $ProductOption->setProductId($Product->getId());
            $ProductOption->setOptionId($Option->getId());
            if ($app['eccube.productoption.repository.product_option']->isExist($Product->getId(), $Option->getId()))
                $ProductOption->setAdd(true);
            $data[] = $ProductOption;
        }

        $arrLine = $app->form()->add('product_options', 'collection', array(
                    'type' => 'admin_product_product_option',
                    'allow_add' => true,
                    'allow_delete' => true,
                    'data' => $data,
                ))
                ->getForm()
                ->createView();

        return $app->renderView('ProductOption/Resource/template/admin/Product/product_option.twig', array(
                    'optionForm' => $arrLine,
                    'Product' => $Product,
                    'Options' => $Options,
        ));
    }


    public function edit(Application $app, Request $request, $id)
    {
        $Product = $app['eccube.repository.product']->find($id);

        if (!$Product) {
            throw new NotFoundHttpException();
        }

        $form = $app->form()
                ->add('product_options', 'collection', array(
                    'type' => 'admin_product_product_option',
                    'allow_add' => true,
                    'allow_delete' => true,
                ))
                ->getForm();

        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);

            $checkProductClasses = array();
            $removeProductClasses = array();

            $tmpProductOption = null;

            // 一旦クリア
            $currentProductOptions = $app['eccube.productoption.repository.product_option']->getListByProductId($Product->getId());
            foreach ($currentProductOptions as $currentProductOption) {
                $app['orm.em']->remove($currentProductOption);
            }
            $app['orm.em']->flush();

            $ProductOptions = $form->get('product_options');
            foreach ($ProductOptions as $formData) {
                $ProductOption = $formData->getData();

                if ($ProductOption->getAdd()) {
                    if ($formData->isValid()) {
                        $ProductOption->setProduct($Product);
                        $ProductOption->setProductId($Product->getId());
                        $option_id = $ProductOption->getOptionId();
                        $TargetOption = $app['orm.em']
                                ->getRepository('Plugin\ProductOption\Entity\Option')
                                ->find($option_id);
                        $ProductOption->setOption($TargetOption);
                        $ProductOption->setOptionId($option_id);
                        $app['eccube.productoption.repository.product_option']->save($ProductOption);
                    }
                }
            }

            $app['orm.em']->flush();

            $app->addSuccess('admin.product.product_option.complete', 'admin');
        }

        return $app->redirect($app->url('admin_product_product_option_rank', array('id' => $id)));
    }

}
