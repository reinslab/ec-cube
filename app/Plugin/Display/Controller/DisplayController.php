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

namespace Plugin\Display\Controller;

use Eccube\Application;
use Eccube\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception as HttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Constraints as Assert;

class DisplayController extends AbstractController
{

    private $main_title;

    private $sub_title;

    public function __construct()
    {
    }

    /**
     * 商品展開一覧
     * @param Application $app
     * @param Request     $request
     * @param unknown     $id
     * @throws NotFoundHttpException
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function index(Application $app, Request $request)
    {
        $pagination = null;

        $pagination = $app['eccube.plugin.display.repository.display_product']->findList();

        return $app->render('Display/Resource/template/admin/index.twig', array(
            'pagination' => $pagination,
            'totalItemCount' => count($pagination)
        ));
    }

    /**
     * 商品展開の新規作成
     * @param Application $app
     * @param Request     $request
     * @param unknown     $id
     * @throws NotFoundHttpException
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function create(Application $app, Request $request, $id)
    {

        $builder = $app['form.factory']->createBuilder('admin_display');
        $form = $builder->getForm();

        $service = $app['eccube.plugin.display.service.display'];

        $Product = null;

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            $data = $form->getData();
            if ($form->isValid()) {
                $status = $service->createDisplay($data);

                if (!$status) {
                    $app->addError('admin.display.notfound', 'admin');
                } else {
                    $app->addSuccess('admin.plugin.display.regist.success', 'admin');
                }

                return $app->redirect($app->url('admin_display_list'));
            }

            if (!is_null($data['Product'])) {
                $Product = $data['Product'];
            }
        }

        return $this->renderRegistView(
            $app,
            array(
                'form' => $form->createView(),
                'Product' => $Product
            )
        );
    }

    /**
     * 編集
     * @param Application $app
     * @param Request     $request
     * @param unknown     $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(Application $app, Request $request, $id)
    {

        if (is_null($id) || strlen($id) == 0) {
            $app->addError("admin.display.display_id.notexists", "admin");
            return $app->redirect($app->url('admin_display_list'));
        }

        $service = $app['eccube.plugin.display.service.display'];

        // IDから商品展開情報を取得する
        $Display = $app['eccube.plugin.display.repository.display_product']->findById($id);

        if (is_null($Display)) {
            $app->addError('admin.display.notfound', 'admin');
            return $app->redirect($app->url('admin_display_list'));
        }

        $Display = $Display[0];

        // formの作成
        $form = $app['form.factory']
            ->createBuilder('admin_display', $Display)
            ->getForm();

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $status = $service->updateDisplay($form->getData());

                if (!$status) {
                    $app->addError('admin.display.notfound', 'admin');
                } else {
                    $app->addSuccess('admin.plugin.display.update.success', 'admin');
                }

                return $app->redirect($app->url('admin_display_list'));
            }
        }

        return $this->renderRegistView(
            $app,
            array(
                'form' => $form->createView(),
                'Product' => $Display->getProduct()
            )
        );
    }

    /**
     * 商品展開の削除
     * @param Application $app
     * @param Request     $request
     * @param unknown     $id
     * @throws NotFoundHttpException
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function delete(Application $app, Request $request, $id)
    {

        $this->isTokenValid($app);

        if (!'POST' === $request->getMethod()) {
            throw new HttpException();
        }
        if (is_null($id) || strlen($id) == 0) {
            $app->addError("admin.display.display_id.notexists", "admin");
            return $app->redirect($app->url('admin_display_list'));
        }


        $service = $app['eccube.plugin.display.service.display'];

        // 商品展開情報を削除する
        if ($service->deleteDisplay($id)) {
            $app->addSuccess('admin.plugin.display.delete.success', 'admin');
        } else {
            $app->addError('admin.display.notfound', 'admin');
        }

        return $app->redirect($app->url('admin_display_list'));

    }

    /**
     * 上へ
     * @param Application $app
     * @param Request     $request
     * @param unknown     $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function rankUp(Application $app, Request $request, $id)
    {

        $this->isTokenValid($app);

        if (is_null($id) || strlen($id) == 0) {
            $app->addError("admin.display.display_id.notexists", "admin");
            return $app->redirect($app->url('admin_display_list'));
        }

        $service = $app['eccube.plugin.display.service.display'];

        // IDから商品展開情報を取得する
        $Display = $app['eccube.plugin.display.repository.display_product']->find($id);
        if (is_null($Display)) {
            $app->addError('admin.display.notfound', 'admin');
            return $app->redirect($app->url('admin_display_list'));
        }

        // ランクアップ
        $service->rankUp($id);

        $app->addSuccess('admin.plugin.display.complete.up', 'admin');

        return $app->redirect($app->url('admin_display_list'));
    }

    /**
     * 下へ
     * @param Application $app
     * @param Request     $request
     * @param unknown     $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function rankDown(Application $app, Request $request, $id)
    {

        $this->isTokenValid($app);

        if (is_null($id) || strlen($id) == 0) {
            $app->addError("admin.display.display_id.notexists", "admin");
            return $app->redirect($app->url('admin_display_list'));
        }

        $service = $app['eccube.plugin.display.service.display'];

        // IDから商品展開情報を取得する
        $Display = $app['eccube.plugin.display.repository.display_product']->find($id);
        if (is_null($Display)) {
            $app->addError('admin.display.notfound', 'admin');
            return $app->redirect($app->url('admin_display_list'));
        }

        // ランクアップ
        $service->rankDown($id);

        $app->addSuccess('admin.plugin.display.complete.down', 'admin');

        return $app->redirect($app->url('admin_display_list'));
    }

    /**
     * 編集画面用のrender
     * @param unknown $app
     * @param unknown $parameters
     */
    protected function renderRegistView($app, $parameters = array())
    {
        // 商品検索フォーム
        $searchProductModalForm = $app['form.factory']->createBuilder('admin_search_product')->getForm();
        $viewParameters = array(
            'searchProductModalForm' => $searchProductModalForm->createView(),
        );
        $viewParameters += $parameters;
        return $app->render('Display/Resource/template/admin/regist.twig', $viewParameters);
    }

}
