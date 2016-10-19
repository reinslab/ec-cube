<?php

/*
 * Copyright(c) 2015 GMO Payment Gateway, Inc. All rights reserved.
 * http://www.gmo-pg.com/
 */

namespace Plugin\GmoPaymentGateway\Controller;

use Eccube\Application;
use Symfony\Component\HttpFoundation\Request;
use Plugin\GmoPaymentGateway\Controller\Util\CommonUtil;
use Plugin\GmoPaymentGateway\Service\client\PG_MULPAY_Client_Member;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Plugin\GmoPaymentGateway\Controller\Util\PluginUtil;
use Plugin\GmoPaymentGateway\Controller\Helper\Helper_OrganizeGmoMember;

class GmoMemberController {

    private $app;

    public function index(Application $app, Request $request, $page_no = null, $page_count = null) {
        $this->app = $app;

        $pagination = array();
        $disps = $app['eccube.repository.master.disp']->findAll();
        $pageMaxis = $app['eccube.repository.master.page_max']->findAll();
        $page_count = $request->get('page_count');
        $page_count = is_null($page_count) ? $app['config']['default_page_count'] : $page_count;
        $page_no = is_null($page_no) ? $page_no = 1 : $page_no;

        $step_no = $request->get('step_no');
        $total_step = $request->get('total_step');
        
        $qb = $this->app['eccube.plugin.gmo_pg.repository.gmo_member']->getAllGmoMember();

        $pagination = $app['paginator']()->paginate(
                $qb, $page_no, $page_count
        );

        return $this->app['view']->render('GmoPaymentGateway/View/admin/gmo_member.twig', array(
                    'page_no' => $page_no,
                    'page_count' => $page_count,
                    'pageMaxis' => $pageMaxis,
                    'pagination' => $pagination,
                    'step_no' => $step_no,
                    'total_step' => $total_step,
        ));
    }
    
    /**
     * Migrate dtb_customer into dtb_gmo_member
     * @param Application $app
     * @return type
     */
    public function organizeGmoMember(Application $app, Request $request) {

        $this->app = $app;
        // Check in case user not setting
        $objMdl = &PluginUtil::getInstance($app);
        $gmoSetting = $objMdl->getUserSettings();
        if(empty($gmoSetting)){
            $app->addError('PGマルチペイメントサービス決済モジュールで設定されていません。', 'admin');
            return $app->redirect($app['url_generator']->generate('plugin_GmoPaymentGateway_member'));
        }
        
        $app['orm.em']->getConnection()->beginTransaction();
        
        @ini_set('memory_limit', -1);
        set_time_limit(0);
        while (@ob_end_flush());
        try {
            // Create new gmo member
            $Helper = new Helper_OrganizeGmoMember($app);
            $Helper->migrateMember();
            
            // Get duplicate GmoMemberId
            $total = $app['eccube.plugin.gmo_pg.repository.gmo_member']->getTotalDuplicateMemberId($app);
            $total_step = ceil($total/$gmoSetting['member_max_process']);

            $app->clearMessage();
            if($total == 0){
                $app->addSuccess('処理が完了しました。', 'admin');
            } else {
                $app->addSuccess('前の仕様でGMOメンバーIDが重複された' . $total . '件が見つかりました。次に重複されたGMOメンバーIDを再生成します。処理時間が長いので、数ステップに分割されます。ステップ ○○のボタンを押してください。', 'admin');
                return $app->redirect($app['url_generator']->generate('plugin_GmoPaymentGateway_member', 
                        array('step_no' => 1, 'total_step' => $total_step)));
            }
        } catch (Exception $ex) {
            $objMdl->printLog("\n Error:  " . $ex->getMessage());
            $app['orm.em']->getConnection()->rollback();            
            $app->addError($ex->getMessage(), 'admin');
        }

        return $app->redirect($app['url_generator']->generate('plugin_GmoPaymentGateway_member'));
    }
    
    /**
     * Create new Gmo_member_id for duplicated member
     * @param Application $app
     * @return type
     */
    public function processDuplicate(Application $app, Request $request, $step_no) {
        $this->app = $app;
        $total_step = $request->get('total_step');
        
        // Check in case user not setting
        $objMdl = &PluginUtil::getInstance($app);
        $gmoSetting = $objMdl->getUserSettings();
        if(empty($gmoSetting)){
            $app->addError('PGマルチペイメントサービス決済モジュールで設定されていません。', 'admin');
            return $app->redirect($app['url_generator']->generate('plugin_GmoPaymentGateway_member'));
        }
        
        $app['orm.em']->getConnection()->beginTransaction();
        
        @ini_set('memory_limit', -1);
        set_time_limit(0);
        while (@ob_end_flush());
        try {            
            // Get duplicate GmoMemberId
            $data = $app['eccube.plugin.gmo_pg.repository.gmo_member']->getDuplicateMemberId($app, $gmoSetting['member_max_process']);

            foreach ($data as $item) {
                $objClientDelete = new PG_MULPAY_Client_Member($app);
                $retDel = $objClientDelete->deleteGmoMember($item['old_member_id']);
                
                // Create new GmoMemberId with newer method
                $GmoMemberId = CommonUtil::createGmoMemberId($item['customer_id'], $app, true);

                // Get Customer
                $Customer = $app['orm.em']->getRepository('Eccube\Entity\Customer')->find($item['customer_id']);

                // Insert to dtb_gmo_member
                $objClientSave = new PG_MULPAY_Client_Member($app);
                $retSave = $objClientSave->saveGmoMember($GmoMemberId);
                
                if ($retSave) {
                    $app['eccube.plugin.gmo_pg.repository.gmo_member']->updateOrCreate($app, $Customer, $GmoMemberId);
                } elseif ($retDel && !$retSave) {
                    $app['eccube.plugin.gmo_pg.repository.gmo_member']->updateOrCreate($app, $Customer, null);
                }
            }

            $app['orm.em']->flush();
            $app['orm.em']->getConnection()->commit();
            
            if (($step_no) == $total_step) {
                $app->addSuccess('処理が完了しました。', 'admin');
                return $app->redirect($app['url_generator']->generate('plugin_GmoPaymentGateway_member'));
            } else {
                $app->addSuccess('ステップ'. $step_no . 'の処理が完了しました。', 'admin');
                return $app->redirect($app['url_generator']->generate('plugin_GmoPaymentGateway_member', array('step_no' => $step_no + 1, 'total_step' => $total_step)));
            }
        } catch (Exception $ex) {
            $objMdl->printLog("\n Error:  " . $ex->getMessage());
            $app['orm.em']->getConnection()->rollback();            
            $app->addError($ex->getMessage(), 'admin');
            return $app->redirect($app['url_generator']->generate('plugin_GmoPaymentGateway_member', 
                                         array('step_no'=>$step_no, 'total_step' =>$total_step)));
        }
    }
    

    /**
     * Export gmo member to csv
     * @return \Plugin\GmoPaymentGateway\Controller\StreamedResponse
     */
    public function exportGmoMember(Application $app, Request $request) {
        $this->app = $app;
        // タイムアウトを無効にする.
        set_time_limit(0);
        
        // sql loggerを無効にする.
        $em = $app['orm.em'];
        $em->getConfiguration()->setSQLLogger(null);

        $response = new StreamedResponse();
        $response->setCallback(function () use ($app, $request) {
            $CsvType = $app['eccube.repository.master.csv_type']
                    ->findOneBy(array('name' => $app['config']['GmoPaymentGateway']['const']['CSV_GMO_MEMBER_TYPE']));
            // CSV種別を元に初期化.
            $app['eccube.service.csv.export']->initCsvType($CsvType);

            // ヘッダ行の出力.
            $app['eccube.service.csv.export']->exportHeader();

            // 受注データ検索用のクエリビルダを取得.
            $qb = $app['eccube.plugin.gmo_pg.repository.gmo_member']->getAllGmoMember(false);

            // データ行の出力.
            $app['eccube.service.csv.export']->setExportQueryBuilder($qb);
            $app['eccube.service.csv.export']->exportData(function ($entity, $csvService) {
                $Csvs = $csvService->getCsvs();

                /** @var $Customer \Plugin\GmoPaymentGateway\Entity\GmoMember */
                $GmoMember = $entity;
                $Customer = $GmoMember->getCustomer();

                $row = array();

                foreach ($Csvs as $Csv) {
                    $fieldName = $Csv->getFieldName();
                    if ($fieldName == 'name') {
                        $data = $Customer->getName01() . $Customer->getName02();
                    } else {
                        $data = $csvService->getData($Csv, $GmoMember);
                    }
                    $row[] = $data;
                }

                //$row[] = number_format(memory_get_usage(true));
                // 出力.
                $csvService->fputcsv($row);
            });
        });

        $now = new \DateTime();
        $filename = 'gmo_member_' . $now->format('YmdHis') . '.csv';
        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->headers->set('Content-Disposition', 'attachment; filename=' . $filename);
        $response->send();

        return $response;
    }

}
