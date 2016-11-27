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

use Eccube\Application;
use Symfony\Component\HttpFoundation\Request;

class OptionController extends \Eccube\Controller\AbstractController
{

    public function index(Application $app, Request $request, $id = null)
    {
        if ($id) {
            $TargetOption = $app['orm.em']
                    ->getRepository('Plugin\ProductOption\Entity\Option')
                    ->find($id);
            if (is_null($TargetOption)) {
                throw new NotFoundHttpException();
            }
        } else {
            $TargetOption = new \Plugin\ProductOption\Entity\Option();
        }

        $form = $app['form.factory']
                ->createBuilder('admin_product_option', $TargetOption)
                ->getForm();

        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $status = $app['eccube.productoption.repository.option']->save($TargetOption);

                if ($status) {
                    $app->addSuccess('admin.product.option.save.complete', 'admin');

                    return $app->redirect($app->url('admin_product_option'));
                } else {
                    $app->addError('admin.product.option.save.error', 'admin');
                }
            }
        }

        $Options = $app['eccube.productoption.repository.option']->getList();

        return $app->renderView('ProductOption/Resource/template/admin/Product/option.twig', array(
                    'form' => $form->createView(),
                    'Options' => $Options,
                    'TargetOption' => $TargetOption,
        ));
    }

    public function up(Application $app, Request $request, $id)
    {
        $TargetOption = $app['eccube.productoption.repository.option']->find($id);
        if (!$TargetOption) {
            throw new NotFoundHttpException;
        }

        $form = $app['form.factory']
                ->createNamedBuilder('admin_product_option', 'form', null, array(
                    'allow_extra_fields' => true,
                ))
                ->getForm();

        $status = false;
        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $status = $app['eccube.productoption.repository.option']->up($TargetOption);
            }
        }

        if ($status === true) {
            $app->addSuccess('admin.product.option.up.complete', 'admin');
        } else {
            $app->addError('admin.product.option.up.error', 'admin');
        }

        return $app->redirect($app->url('admin_product_option'));
    }

    public function down(Application $app, Request $request, $id)
    {
        $TargetOption = $app['eccube.productoption.repository.option']->find($id);
        if (!$TargetOption) {
            throw new NotFoundHttpException();
        }

        $form = $app['form.factory']
                ->createNamedBuilder('admin_product_option', 'form', null, array(
                    'allow_extra_fields' => true,
                ))
                ->getForm();

        $status = false;
        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $status = $app['eccube.productoption.repository.option']->down($TargetOption);
            }
        }

        if ($status === true) {
            $app->addSuccess('admin.product.option.down.complete', 'admin');
        } else {
            $app->addError('admin.product.option.down.error', 'admin');
        }

        return $app->redirect($app->url('admin_product_option'));
    }

    public function delete(Application $app, Request $request, $id)
    {
        $this->isTokenValid($app);
        
        $TargetOption = $app['eccube.productoption.repository.option']->find($id);
        if (!$TargetOption) {
            throw new NotFoundHttpException();
        }

        $status = false;
        $status = $app['eccube.productoption.repository.option']->delete($TargetOption);

        if ($status === true) {
            $app->addSuccess('admin.product.option.delete.complete', 'admin');
        } else {
            $app->addError('admin.product.option.delete.error', 'admin');
        }

        return $app->redirect($app->url('admin_product_option'));
    }

    public function moveRank(Application $app, Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $ranks = $request->request->all();
            foreach ($ranks as $optionId => $rank) {
                $Option = $app['eccube.productoption.repository.option']
                        ->find($optionId);
                $Option->setRank($rank);
                $app['orm.em']->persist($Option);
            }
            $app['orm.em']->flush();
        }
        return true;
    }
}
