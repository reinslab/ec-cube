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
use Eccube\Common\Constant;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;

class OptionCategoryController extends \Eccube\Controller\AbstractController
{

    public function index(Application $app, Request $request, $option_id, $id = null)
    {
        //
        $Option = $app['eccube.productoption.repository.option']->find($option_id);
        if (!$Option) {
            throw new NotFoundHttpException();
        }
        if ($id) {
            $TargetOptionCategory = $app['eccube.productoption.repository.option_category']->find($id);
            if (!$TargetOptionCategory || $TargetOptionCategory->getOption() != $Option) {
                throw new NotFoundHttpException();
            }
        } else {
            $TargetOptionCategory = new \Plugin\ProductOption\Entity\OptionCategory();
            $TargetOptionCategory->setOption($Option);
        }

        //
        $form = $app['form.factory']
                ->createBuilder('admin_product_option_category', $TargetOptionCategory)
                ->getForm();

        // ファイルの登録
        $images = array();
        $filename = $TargetOptionCategory->getOptionImage();
        if (strlen($filename) > 0)
            $images[] = $filename;
        if (count($images) > 0)
            $form['images']->setData($images);

        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                // 画像の削除
                $delete_images = $form->get('delete_images')->getData();
                foreach ($delete_images as $delete_image) {
                    // 削除
                    $TargetOptionCategory->setOptionImage('');
                    $fs = new Filesystem();
                    $fs->remove($app['config']['image_save_realdir'] . '/' . $delete_image);
                }
                // 画像の登録
                $add_images = $form->get('add_images')->getData();
                foreach ($add_images as $add_image) {
                    $TargetOptionCategory->setOptionImage($add_image);

                    // 移動
                    $file = new File($app['config']['image_temp_realdir'] . '/' . $add_image);
                    $file->move($app['config']['image_save_realdir']);
                }

                $status = $app['eccube.productoption.repository.option_category']->save($TargetOptionCategory);

                if ($status) {
                    $app->addSuccess('admin.product.option_category.save.complete', 'admin');

                    return $app->redirect($app->url('admin_product_option_category', array('option_id' => $Option->getId())));
                } else {
                    $app->addError('admin.product.option_category.save.error', 'admin');
                }
            }
        }

        $OptionCategories = $app['eccube.productoption.repository.option_category']->getList($Option);

        return $app->render('ProductOption/Resource/template/admin/Product/option_category.twig', array(
                    'form' => $form->createView(),
                    'Option' => $Option,
                    'OptionCategories' => $OptionCategories,
                    'TargetOptionCategory' => $TargetOptionCategory,
        ));
    }

    public function up(Application $app, Request $request, $option_id, $id)
    {
        //
        $Option = $app['eccube.productoption.repository.option']->find($option_id);
        if (!$Option) {
            throw new NotFoundHttpException();
        }
        $TargetOptionCategory = $app['eccube.productoption.repository.option_category']->find($id);
        if (!$TargetOptionCategory || $TargetOptionCategory->getOption() != $Option) {
            throw new NotFoundHttpException();
        }

        //
        $form = $app['form.factory']
                ->createNamedBuilder('admin_product_option_category', 'form', null, array(
                    'allow_extra_fields' => true,
                ))
                ->getForm();

        //
        $status = false;
        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $status = $app['eccube.productoption.repository.option_category']->up($TargetOptionCategory);
            }
        }

        if ($status === true) {
            $app->addSuccess('admin.product.option_category.up.complete', 'admin');
        } else {
            $app->addError('admin.product.option_category.up.error', 'admin');
        }

        return $app->redirect($app->url('admin_product_option_category', array('option_id' => $Option->getId())));
    }

    public function down(Application $app, Request $request, $option_id, $id)
    {
        //
        $Option = $app['eccube.productoption.repository.option']->find($option_id);
        if (!$Option) {
            throw new NotFoundHttpException();
        }
        $TargetOptionCategory = $app['eccube.productoption.repository.option_category']->find($id);
        if (!$TargetOptionCategory || $TargetOptionCategory->getOption() != $Option) {
            throw new NotFoundHttpException();
        }

        //
        $form = $app['form.factory']
                ->createNamedBuilder('admin_product_option_category', 'form', null, array(
                    'allow_extra_fields' => true,
                ))
                ->getForm();

        //
        $status = false;
        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $status = $app['eccube.productoption.repository.option_category']->down($TargetOptionCategory);
            }
        }

        if ($status === true) {
            $app->addSuccess('admin.product.option_category.down.complete', 'admin');
        } else {
            $app->addError('admin.product.option_category.down.error', 'admin');
        }

        return $app->redirect($app->url('admin_product_option_category', array('option_id' => $Option->getId())));
    }

    public function delete(Application $app, Request $request, $option_id, $id)
    {
        $this->isTokenValid($app);
        
        //
        $Option = $app['eccube.productoption.repository.option']->find($option_id);
        if (!$Option) {
            throw new NotFoundHttpException();
        }
        $TargetOptionCategory = $app['eccube.productoption.repository.option_category']->find($id);
        if (!$TargetOptionCategory || $TargetOptionCategory->getOption() != $Option) {
            throw new NotFoundHttpException();
        }


        //
        $status = false;
        $status = $app['eccube.productoption.repository.option_category']->delete($TargetOptionCategory);

        if ($status === true) {
            $app->addSuccess('admin.product.option_category.delete.complete', 'admin');
        } else {
            $app->addError('admin.product.option_category.delete.error', 'admin');
        }

        return $app->redirect($app->url('admin_product_option_category', array('option_id' => $Option->getId())));
    }

    public function moveRank(Application $app, Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $ranks = $request->request->all();
            foreach ($ranks as $optionCategoryId => $rank) {
                $OptionCategory = $app['eccube.productoption.repository.option_category']
                        ->find($optionCategoryId);
                $OptionCategory->setRank($rank);
                $app['orm.em']->persist($OptionCategory);
            }
            $app['orm.em']->flush();
        }
        return true;
    }

    public function addImage(Application $app, Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException();
        }
        $images = $request->files->get('admin_product_option_category');

        $files = array();
        if (count($images) > 0) {
            foreach ($images as $image) {
	        //ファイルフォーマット検証
                $mimeType = $image->getMimeType();
                if (0 !== strpos($mimeType, 'image')) {
                    throw new UnsupportedMediaTypeHttpException();
                }

                $extension = $image->guessExtension();
                $filename = date('mdHis') . uniqid('_') . '.' . $extension;
                $image->move($app['config']['image_temp_realdir'], $filename);
                $files[] = $filename;
            }
        }

        return $app->json(array('files' => $files), 200);
    }

}
