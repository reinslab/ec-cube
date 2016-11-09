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
use Eccube\Common\Constant;
use Eccube\Entity\Master\CsvType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ServiceExportShipping extends AbstractWorkPlace
{
    public function execute()
    {
        $app = $this->app;
        if (!$app->isGranted('ROLE_ADMIN')) {
            return $app->redirect($app->url('admin_login'));
        }
        
        $request = $app['request'];
        
        // タイムアウトを無効にする.
        set_time_limit(0);

        // sql loggerを無効にする.
        $em = $app['orm.em'];
        $em->getConfiguration()->setSQLLogger(null);

        $response = new StreamedResponse();
        $response->setCallback(function () use ($app, $request) {

            // CSV種別を元に初期化.
            $app['eccube.service.csv.export']->initCsvType(CsvType::CSV_TYPE_SHIPPING);

            // ヘッダ行の出力.
            $app['eccube.service.csv.export']->exportHeader();

            // 受注データ検索用のクエリビルダを取得.
            $qb = $app['eccube.service.csv.export']
                ->getOrderQueryBuilder($request);

            // データ行の出力.
            $app['eccube.service.csv.export']->setExportQueryBuilder($qb);
            $app['eccube.service.csv.export']->exportData(function ($entity, $csvService) use ($app) {

                $Csvs = $csvService->getCsvs();

                /** @var $Order \Eccube\Entity\Order */
                $Order = $entity;
                /** @var $Shippings \Eccube\Entity\Shipping[] */
                $Shippings = $Order->getShippings();

                foreach ($Shippings as $Shipping) {
                    /** @var $ShipmentItems \Eccube\Entity\ShipmentItem */
                    $ShipmentItems = $Shipping->getShipmentItems();
                    foreach ($ShipmentItems as $ShipmentItem) {
                        $row = array();

                        // CSV出力項目と合致するデータを取得.
                        foreach ($Csvs as $Csv) {
                            // 受注データを検索.
                            $data = $csvService->getData($Csv, $Order);
                            if (is_null($data)) {
                                // 配送情報を検索.
                                $data = $csvService->getData($Csv, $Shipping);
                            }
                            if (is_null($data)) {
                                // 配送商品を検索.
                                $data = $csvService->getData($Csv, $ShipmentItem);
                            }
                            
                            if(is_null($data)){
                                $csvEntityName = str_replace('\\\\', '\\', $Csv->getEntityName());
                                if($csvEntityName == 'Plugin\ProductOption\Entity\ShipmentItem'){
                                    $plgShipmentItem = $app['eccube.productoption.repository.shipment_item']->findOneBy(array('item_id' => $ShipmentItem->getId()));
                                    if($plgShipmentItem){
                                        $data = implode($app['config']['csv_export_multidata_separator'],$plgShipmentItem->getOrderOption()->getLabel());
                                    }else{
                                        $data = '';
                                    }
                                }
                            }
                            $row[] = $data;
                        }
                        //$row[] = number_format(memory_get_usage(true));
                        // 出力.
                        $csvService->fputcsv($row);
                    }
                }
            });
        });

        $now = new \DateTime();
        $filename = 'shipping_' . $now->format('YmdHis') . '.csv';
        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->headers->set('Content-Disposition', 'attachment; filename=' . $filename);
        $response->send();

        exit;
    }
}
