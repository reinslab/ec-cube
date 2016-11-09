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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class ConfigController extends \Eccube\Controller\AbstractController
{
    public function index(Application $app, Request $request)
    {
        $enablePoint = $app['eccube.productoption.service.util']->checkInstallPlugin('Point');
        
        $form = $app['form.factory']
            ->createBuilder('admin_setting_productoption')
            ->getForm();
        
        $file = $app['eccube.repository.page_layout']
            ->getReadTemplateFile('Product/option', false);

        $form->get('tpl_option')->setData($file['tpl_data']);
        
        $file = $app['eccube.repository.page_layout']
            ->getReadTemplateFile('Product/option_description', false);

        $form->get('tpl_option_description')->setData($file['tpl_data']);
        
        $file = $app['eccube.repository.page_layout']
            ->getReadTemplateFile('Product/option_price', false);

        $form->get('tpl_option_price')->setData($file['tpl_data']);
        
        if($enablePoint){
            $file = $app['eccube.repository.page_layout']
                ->getReadTemplateFile('Product/option_point', false);

            $form->get('tpl_option_point')->setData($file['tpl_data']);
        }

        if ('POST' === $request->getMethod()) {
            switch($request->get('mode')){
                case 'regist':
                    $form->handleRequest($request);
                    if ($form->isValid()) {
                        // ファイル生成・更新
                        $templatePath = $app['eccube.repository.page_layout']->getWriteTemplatePath(false);
                        
                        $filePath = $templatePath . '/Product/option.twig';
                        $fs = new Filesystem();
                        $fs->dumpFile($filePath, $form->get('tpl_option')->getData());
                        
                        $filePath = $templatePath . '/Product/option_description.twig';
                        $fs = new Filesystem();
                        $fs->dumpFile($filePath, $form->get('tpl_option_description')->getData());
                        
                        $filePath = $templatePath . '/Product/option_price.twig';
                        $fs = new Filesystem();
                        $fs->dumpFile($filePath, $form->get('tpl_option_price')->getData());

                        if($enablePoint){
                            $filePath = $templatePath . '/Product/option_point.twig';
                            $fs = new Filesystem();
                            $fs->dumpFile($filePath, $form->get('tpl_option_point')->getData());
                        }
                        // twig キャッシュの削除.
                        $finder = Finder::create()->in($app['config']['root_dir'] . '/app/cache/twig');
                        $fs->remove($finder);
                
                        $app->addSuccess('admin.setting.productoption.save.complete', 'admin');
                    }
                    break;
                case 'init1':
                case 'init2':
                case 'init3':
                case 'init4':
                    $requestData = $request->get('admin_setting_productoption');
                    switch($request->get('mode')){
                        case 'init1':
                            $content = file_get_contents($app['config']['plugin_realdir'] . '/ProductOption/Resource/template/default/Product/option.twig');
                            $requestData['tpl_option'] = $content;
                            break;
                        case 'init2':
                            $content = file_get_contents($app['config']['plugin_realdir'] . '/ProductOption/Resource/template/default/Product/option_description.twig');
                            $requestData['tpl_option_description'] = $content;
                            break;
                        case 'init3':
                            $content = file_get_contents($app['config']['plugin_realdir'] . '/ProductOption/Resource/template/default/Product/option_price.twig');
                            $requestData['tpl_option_price'] = $content;
                            break;
                        case 'init4':
                            $content = file_get_contents($app['config']['plugin_realdir'] . '/ProductOption/Resource/template/default/Product/option_point.twig');
                            $requestData['tpl_option_point'] = $content;
                            break;
                        default:
                            break;
                    }
                    $request->request->set('admin_setting_productoption',$requestData);
                    $form->handleRequest($request);                    
                    break;
                default:
                    break;
            }
        }
        
        return $app->render('ProductOption/Resource/template/admin/Setting/config.twig', array(
            'form' => $form->createView(),
            'enablePoint' => $enablePoint,
        ));
    }
}