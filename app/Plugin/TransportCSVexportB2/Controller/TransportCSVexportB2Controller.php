<?php
/*
* This file is part of EC-CUBE
*
* Copyright(c) 2000-2015 LOCKON CO.,LTD. All Rights Reserved.
* http://www.lockon.co.jp/
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Plugin\TransportCSVexportB2\Controller;

use Eccube\Application;
use Eccube\Entity\Master\CsvType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Plugin\TransportCSVexportB2\Controller\Util\PluginUtil;
use Doctrine\ORM\Query;

class TransportCSVexportB2Controller
{
    var $app;
    var $subData = array();
    var $OrderStatus = array();
    var $BaseInfo;
    var $yamatoCvt = array(1 => 0, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 7 => 7);
    var $ids = array();
    const CSVB2_NAME = 'B2CSV_TransportCSVexportB2';
    
    public function __construct()
    {
    }

    /**
     * B2CSVの出力.
     *
     * @param Application $app
     * @param Request $request
     * @return StreamedResponse
     */
    public function exportB2t(Application $app, Request $request)
    {
        // ステータス変更
        $this->changeStatus($app, $request);
        // CSV出力
        return $this->exportB2($app, $request);
    }

    /**
     * B2CSVの出力.
     *
     * @param Application $app
     * @param Request $request
     * @return StreamedResponse
     */
    public function exportB2(Application $app, Request $request)
    {
        $objTCSV =& PluginUtil::getInstance($app);
        $this->subData = $objTCSV->getUserSettings();
        $this->ids = $this->getIds($app, $request);
        $this->app = $app;

        // タイムアウトを無効にする.
        set_time_limit(0);

        // sql loggerを無効にする.
        $em = $app['orm.em'];
        $em->getConfiguration()->setSQLLogger(null);


        $response = new StreamedResponse();
        $response->setCallback(function () use ($app, $request) {

            // CSV種別を元に初期化.
            $id = $this->getCsvId(self::CSVB2_NAME, $app);
            $app['eccube.service.csv.export']->initCsvType($id);

            // ヘッダ行の出力.
            $app['eccube.service.csv.export']->exportHeader();

            // 受注データ検索用のクエリビルダを取得.
            $qb = $app['eccube.service.csv.export']
                ->getOrderQueryBuilder($request);
            
            // 店舗情報
            $this->BaseInfo = $app['eccube.repository.base_info']->get();
            
            // データ行の出力.
            $app['eccube.service.csv.export']->setExportQueryBuilder($qb);
            $app['eccube.service.csv.export']->exportData(function ($entity, $csvService) {

                $Csvs = $csvService->getCsvs();

                $Order = $entity;
                $OrderDetails = $Order->getOrderDetails();
                $Shippings = $Order->getShippings();

                foreach ($Shippings as $Shipping) {

                    $row = array();
                    $ShipmentItems = $Shipping->getShipmentItems();
                    
                    // 商品
                    $items = array();
                    $codes = array();
                    foreach ($ShipmentItems as $ShipmentItem) {
                        $items[] = $ShipmentItem->offsetGet('product_name');
                        $codes[] = $ShipmentItem->offsetGet('product_code');
                    }

                    // CSV出力項目と合致するデータを取得.
                    foreach ($Csvs as $Csv) {
                        // 受注データを検索.
                        $data = $csvService->getData($Csv, $Order);
                        $payment_id = $this->app['orm.em']->getConnection()->fetchColumn("select payment_id from dtb_order where order_id = ?", array($Order->offsetGet('id')));
                        if (is_null($data)) {
                            // 配送情報を検索.
                            if ($data = $csvService->getData($Csv, $Shipping)) {
                                switch ($Csv->getFieldName()) {
                                    // 名前
                                    case 'name01':
                                        $data = $data . $Shipping->offsetGet('name02');
                                        break;
                                    // カナ
                                    case 'kana01':
                                        //$data = mb_convert_kana($data . $Shipping->offsetGet('kana02'), "kvrn");        // customized by wellco
                                        $data = mb_convert_kana($data . $Shipping->offsetGet('kana02'), "kvrn","UTF-8");  // customized by wellco
                                        break;
                                    // 郵便番号
                                    case 'zip01':
                                        $data = $data . '-' . $Shipping->offsetGet('zip02');
                                        break;
                                    // 住所
                                    case 'addr01':
                                        $data = $Shipping->offsetGet('Pref') . $data;
                                        break;
                                    // 電話番号
                                    case 'tel01':
                                        $data = $data . '-' . $Shipping->offsetGet('tel02') . '-' . $Shipping->offsetGet('tel03');
                                        break;
                                    // お届け予定日
                                    case 'shipping_delivery_date':
                                        $data = date('Y/m/d', strtotime($data));
                                        break;
                                }
                            } else {
                                switch ($Csv->getFieldName()) {
                                    case 'deliv_class':
                                        if ($yamato = substr($this->subData['yamato_deliv_type'], 2, 1)) {
                                            if (substr($this->subData['yamato_deliv_type'], 0, 1) == 1) {
                                                $data = $this->yamatoCvt[$yamato];
                                            }
                                        }
                                        if ($this->subData['payment_type'.$payment_id]) {
                                            $data = 2;
                                        }
                                        break;
                                    case 'cool_class':
                                        if ($yamato = substr($this->subData['yamato_deliv_type'], 3, 1)) {
                                            if (substr($this->subData['yamato_deliv_type'], 0, 1) == 1) {
                                                $data = $yamato;
                                            }
                                        }
                                        break;
                                    case 'customer_code':
                                        $data = sprintf("%08d", $Order->offsetGet('id')) . '_' . sprintf("%08d", $Shipping->offsetGet('id'));
                                        break;
                                    case 'campany_name':
                                        $data = $this->BaseInfo->getCompanyName();
                                        break;
                                    case 'campany_kana':
                                        $data = mb_convert_kana($this->BaseInfo->getCompanyKana(), "kvrn");
                                        break;
                                    case 'zip01':
                                        if ($this->BaseInfo->getZip01() && $this->BaseInfo->getZip02()) {
                                            $data = $this->BaseInfo->getZip01() . '-' . $this->BaseInfo->getZip02();
                                        } else {
                                            $data = '';
                                        }
                                        break;
                                    case 'tel01':
                                        if ($this->BaseInfo->getTel01() && $this->BaseInfo->getTel02() && $this->BaseInfo->getTel03()) {
                                            $data = $this->BaseInfo->getTel01() . '-' . $this->BaseInfo->getTel02() . '-' . $this->BaseInfo->getTel03();
                                        } else {
                                            $data = '';
                                        }
                                        break;
                                    case 'addr01':
                                        $data = $this->BaseInfo->getPref() . $this->BaseInfo->getAddr01();
                                        break;
                                    case 'addr02':
                                        $data = $this->BaseInfo->getAddr02();
                                        break; 
                                    case 'product_code01':
                                        $data = mb_convert_kana($codes[0], "kvrn");
                                        break; 
                                    case 'product_name01':
                                        $data = $items[0];
                                        break;
                                    case 'product_code02':
                                        if (array_key_exists(1, $codes)) {
                                        $data = mb_convert_kana($codes[1], "kvrn");
                                        }
                                        break; 
                                    case 'product_name02':                              // 品名２ = 注文ID
                                        //if (array_key_exists(1, $items)) {            // customized by wellco
                                        //    $data = $items[1];                        // customized by wellco
                                        //}                                             // customized by wellco
                                        $data = $Order->offsetGet('custom_order_id');   // customized by wellco
                                        break; 
                                }
                            }
                        }
                        switch ($Csv->getFieldName()) {
                            // 出荷予定日
                            case 'send_date':
                                $now = new \DateTime();
                                $data = $now->format('Y/m/d');
                                break;
                            // 配達時間帯
                            case 'shipping_delivery_time':
/*
                                if ($data == '午前') {
                                    $data = '0812';
                                } elseif ($data == '午後') {
                                    $data = '1221';
                                }
                                $data = '';
*/
                                break;
                            // コレクト代金引換額（税込）
                            case 'daibiki_price':
                                if (count($Shippings) < 2) {
                                    if ($this->subData['payment_type'.$payment_id]) {
                                        $data = $Order->offsetGet('payment_total');
                                    }
                                }
                                break;
                            // コレクト内消費税額等
                            case 'daibiki_tax':
                                if (count($Shippings) < 2) {
                                    if ($this->subData['payment_type'.$payment_id]) {
                                        $data = $Order->offsetGet('tax');
                                    }
                                }
                                break;
                        }
                        // ご請求先顧客コード
                        if (is_null($data) && $Csv->getFieldName() == 'cstmr_code' && isset($this->subData['billing_code'])) {
                            $data = $this->subData['billing_code'];
                        }
                        // 運賃管理番号
                        if (is_null($data) && $Csv->getFieldName() == 'kanri' && isset($this->subData['fare_number'])) {
                            $data = $this->subData['fare_number'];
                        }
                        $row[] = $data;
                    }
                    //$row[] = number_format(memory_get_usage(true));
                    // 出力.
                    if ($this->ids) {
                        if (in_array($Order->offsetGet('id'), $this->ids)) {
                            $csvService->fputcsv($row);
                        }
                    } else {
                        $csvService->fputcsv($row);
                    }
                }
            });
        });

        $now = new \DateTime();
        $filename = 'b2_' . $now->format('YmdHis') . '.csv';
        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->headers->set('Content-Disposition', 'attachment; filename=' . $filename);
        $response->send();

        return $response;
    }

    /**
     * B2CSVの出力.
     *
     * @param Application $app
     * @param Request $request
     * @return StreamedResponse
     */
    public function ajaxB2(Application $app, Request $request)
    {
        $objTCSV =& PluginUtil::getInstance($app);
        $this->subData = $objTCSV->getUserSettings();
        $this->ids = $this->getIds($app, $request);

        if ($OrderStatus = $app['eccube.repository.order_status']->findAllArray()) {
            foreach ($OrderStatus as $val) {
                $this->OrderStatus[$val['id']] = $val['name'];
            }
        }
        
        // タイムアウトを無効にする.
        set_time_limit(0);

        // sql loggerを無効にする.
        $em = $app['orm.em'];
        $em->getConfiguration()->setSQLLogger(null);
        
        $response = new StreamedResponse();
        $response->setCallback(function () use ($app, $request) {
        
            // 受注データ検索用のクエリビルダを取得.
            $qb = $app['eccube.service.csv.export']
                    ->getOrderQueryBuilder($request);
            
            $qb->select('o.id');
            
            $result = $qb->getQuery()->getResult(Query::HYDRATE_ARRAY);

            if ($this->ids) {
                foreach ($result as $keys => $vals) {
                    if (!in_array($vals['id'], $this->ids)) {
                        unset($result[$keys]);
                    }
                }
            }

            $data = array();

            $data['status'] = false;
            $data['deliv']  = false;
            $data['os_flg'] = false;
            $data['os_chk'] = false;
            $data['os_str'] = '';

            if ($this->subData['order_status']) {
                $data['os_flg'] = true;
                $tmp = array();
                foreach ($this->subData['order_status'] as $id) {
                    $tmp[] = $this->OrderStatus[$id];
                }
                $data['os_str'] = implode(',', $tmp);
            }
            
//            if (count($result) < 1000) {
            if (false) {
                $id = array();
                foreach ($result as $val) {
                    $id[] = (int)$val['id'];
                }
                $delevs = $this->getDelevIds($id, $app);
                // ヤマト出力時e飛伝が混ざってればtrue
                if (!$data['deliv']) {
                    if (count($delevs) > 1) {
                        $data['deliv'] = true;
                    }
                }
                $status = $this->getStatuses($id, $app);
            } else {
                $base_deliv = '';
                foreach ($result as $val) {
                    // ヤマト出力時e飛伝が混ざってればtrue
                    if (!$data['deliv']) {
                        $delevs = $this->getDelevId($val['id'], $app);
                        foreach ($delevs as $v) {
                            if ($base_deliv && $v['delivery_id'] != $base_deliv) {
                                $data['deliv'] = true;
                            }
                            $base_deliv = $v['delivery_id'];
                        }
                    }
                    if (!$data['status']/* || ($data['os_flg'] && !$data['os_chk'])*/) {
                        $status = $this->getStatus($val['id'], $app);
                        // 3:キャンセル,5:発送済みの場合はtrue
                        if (!$data['status']) {
                            if ($status == 3 || $status == 5) {
                                $data['status'] = true;
                            }
                        }
/*
                        // 設定された対応状況以外ならtrue
                        if (!$data['os_chk']) {
                            if (!in_array($status, $this->subData['order_status'])) {
                                $data['os_chk'] = true;
                            }
                        }
*/
                    }
                    
                    if( $data['status'] && $data['deliv']/* && $data['os_chk'] */) break;
                }
            }
            echo json_encode($data);
        });
        
        $response->headers->set('Content-Type', 'application/json');
        $response->send();

        return $response;

    }

    /**
     * e飛伝CSVの出力.
     *
     * @param Application $app
     * @param Request $request
     * @return StreamedResponse
     */
    public function getCsvId($key, $app)
    {
        $sql = "select id from mtb_csv_type where name = ?";
        $param = array($key);
        return $app['orm.em']->getConnection()->fetchColumn($sql, $param);
    }

    /**
     * e飛伝CSVの出力.
     *
     * @param Application $app
     * @param Request $request
     * @return StreamedResponse
     */
    public function getStatus($order_id, $app)
    {
        $sql = "select status from dtb_order where order_id = ?";
        $param = array($order_id);
        return $app['orm.em']->getConnection()->fetchColumn($sql, $param);
    }

    /**
     * e飛伝CSVの出力.
     *
     * @param Application $app
     * @param Request $request
     * @return StreamedResponse
     */
    public function getDelevId($order_id, $app)
    {
        $sql = "select delivery_id from dtb_shipping where order_id = ?";
        $param = array($order_id);
        return $app['orm.em']->getConnection()->fetchAll($sql, $param);
    }

    /**
     * e飛伝CSVの出力.
     *
     * @param Application $app
     * @param Request $request
     * @return StreamedResponse
     */
    public function getStatuses($order_id, $app)
    {
        $sql = "select status from dtb_order where order_id in (?)";
        $param = array(implode("','", $order_id));
        return $app['orm.em']->getConnection()->fetchAll($sql, $param);
    }

    /**
     * e飛伝CSVの出力.
     *
     * @param Application $app
     * @param Request $request
     * @return StreamedResponse
     */
    public function getDelevIds($order_id, $app)
    {
        $sql = "select delivery_id from dtb_shipping where order_id in (?)";
        $param = array(implode("','", $order_id));
        return $app['orm.em']->getConnection()->fetchAll($sql, $param);
    }

    /**
     * e飛伝CSVの出力.
     *
     * @param Application $app
     * @param Request $request
     * @return StreamedResponse
     */
    public function changeStatus(Application $app, Request $request)
    {
        $objTCSV =& PluginUtil::getInstance($app);
        $subData = $objTCSV->getUserSettings();

        if (!$subData['order_status']) {
            return;
        }
        
        // 受注データ検索用のクエリビルダを取得.
        $qb = $app['eccube.service.csv.export']
                ->getOrderQueryBuilder($request);
            
        $qb->select('o.id');
        
        if ($result = $qb->getQuery()->getResult(Query::HYDRATE_ARRAY)) {
            $orderRepository = $app['orm.em']->getRepository('Eccube\Entity\Order');
            $Status = $app['eccube.repository.order_status']->find(5);
            foreach ($result as $val) {
                $status = $this->getStatus($val['id'], $app);
                if (in_array($status, $subData['order_status'])) {
                    $orderRepository->changeStatus($val['id'], $Status);
                }
            }
        }
    }

    /**
     * e飛伝CSVの出力.
     *
     * @param Application $app
     * @param Request $request
     * @return StreamedResponse
     */
    public function getIds(Application $app, Request $request)
    {
        $ids = array();
        
        // 受注データ検索用のクエリビルダを取得.
        $qb = $app['eccube.service.csv.export']
                ->getOrderQueryBuilder($request);
            
        $qb->select('o.id');
        
        if ($result = $qb->getQuery()->getResult(Query::HYDRATE_ARRAY)) {
            foreach ($result as $val) {
                if ($request->get("ids{$val['id']}")) {
                    $ids[] = $val['id'];
                }
            }
        }
        
        return $ids;
    }

}
