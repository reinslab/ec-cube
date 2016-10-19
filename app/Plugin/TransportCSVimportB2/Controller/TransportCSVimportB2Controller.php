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

namespace Plugin\TransportCSVimportB2\Controller;

use Eccube\Application;
use Eccube\Entity\Master\CsvType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Plugin\TransportCSVimportB2\Controller\Util\PluginUtil;
use Doctrine\ORM\Query;

use Eccube\Util\Str;
use Eccube\Service\CsvImportService;
use Eccube\Exception\CsvImportException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Eccube\Entity\MailHistory;

/*
use Eccube\Common\Constant;
use Eccube\Entity\Category;
use Eccube\Entity\Product;
use Eccube\Entity\ProductCategory;
use Eccube\Entity\ProductClass;
use Eccube\Entity\ProductImage;
use Eccube\Entity\ProductStock;
*/

class TransportCSVimportB2Controller
{
    private $importB2Twig = 'TransportCSVimportB2/View/admin/transport_csv_import_b2.twig';
    
    private $errors = array();

    private $fileName;

    private $em;
    
    public function __construct()
    {
    }

    /**
     * カテゴリ登録CSVアップロード
     */
    public function index(Application $app, Request $request)
    {

        $form = $app['form.factory']->createBuilder('admin_import_b2')->getForm();

        $headers = $this->getB2CsvHeader2(); // customized by wellco

        if ('POST' === $request->getMethod()) {

            $form->handleRequest($request);

            if ($form->isValid()) {

                $formFile   = $form['import_file']->getData();
                $send_flg   = $form['send_flg']->getData();
                $status_flg = $form['status_flg']->getData();

                if (!empty($formFile)) {

                    $data = $this->getImportData($app, $formFile);
                    if ($data === false) {
                        $this->addErrors('CSVのフォーマットが一致しません。');
                        return $this->render($app, $form, $headers, $this->importB2Twig);
                    }

                    $keys = array_keys($headers);
                    $columnHeaders = $data->getColumnHeaders();
                    if ($keys !== $columnHeaders) {
                        $this->addErrors('CSVのフォーマットが一致しません。');
                        return $this->render($app, $form, $headers, $this->importB2Twig);
                    }

                    $size = count($data);
                    if ($size < 1) {
                        $this->addErrors('CSVデータが存在しません。');
                        return $this->render($app, $form, $headers, $this->importB2Twig);
                    }

                    $headerSize = count($keys);

                    $this->em = $app['orm.em'];
                    $this->em->getConfiguration()->setSQLLogger(null);

                    $this->em->getConnection()->beginTransaction();

                    // 店舗情報
                    $baseInfo = $app['eccube.repository.base_info']->get();

                    // Plugin
                    $Plugin = $this->em->getRepository('Plugin\TransportCSVimportB2\Entity\TransportCSVimportB2Plugin')->find(1);
                    // メールテンプレート
                    $MailTemplate = $app['eccube.repository.mail_template']->find($Plugin->getMailTemplateId());

                    $objTCSV =& PluginUtil::getInstance($app);
                    $subData = $objTCSV->getUserSettings();

                    // CSVファイルの登録処理
                    foreach ($data as $row) {

                        if ($headerSize != count($row)) {
                            $this->addErrors(($data->key() + 1) . '行目のCSVフォーマットが一致しません。');
                            return $this->render($app, $form, $headers, $this->importB2Twig);
                        }

                        if (Str::isBlank($row['お客様管理番号'])) {
                            $this->addErrors(($data->key() + 1) . '行目のお客様管理番号が設定されていません。');
                            return $this->render($app, $form, $headers, $this->importB2Twig);
                        } else {
                            list($order_id, $shipping_id) = explode('_', $row['お客様管理番号']);
                            $order_id = (int)$order_id;
                            $shipping_id = (int)$shipping_id;
                            $Order = $app['eccube.repository.order']->find($order_id);
                            $Shipping = $app['eccube.repository.shipping']->find($shipping_id);
                            if (!$Order || !$Shipping) {
                                $this->addErrors(($data->key() + 1) . '行目のお客様管理番号が存在しません。');
                                return $this->render($app, $form, $headers, $this->importB2Twig);
                            } else {
                                $TransportCSVimportB2 = $this->em->getRepository('Plugin\TransportCSVimportB2\Entity\TransportCSVimportB2')->findOneBy(array('order_id' => $order_id, 'shipping_id' => $shipping_id));

                                if (is_null($TransportCSVimportB2)) {
                                    $TransportCSVimportB2 = new \Plugin\TransportCSVimportB2\Entity\TransportCSVimportB2();
                                }

                                $TransportCSVimportB2->setShippingId(Str::trimAll($shipping_id));
                                $TransportCSVimportB2->setOrderId(Str::trimAll($order_id));
                            }
                            
                            if (Str::isBlank($row['伝票番号'])) {
                                $this->addErrors(($data->key() + 1) . '行目の伝票番号が設定されていません。');
                                return $this->render($app, $form, $headers, $this->importB2Twig);
                            } else {
                                $TransportCSVimportB2->setInvoiceNumber(Str::trimAll($row['伝票番号']));
                            }
                            
                            // メール送信
                            if ($send_flg == 1) {
                                $Shippings = $app['eccube.repository.shipping']->find($shipping_id);

                                $mailData['subject'] = $MailTemplate->getSubject();
                                $mailData['header'] = $MailTemplate->getHeader();
                                $mailData['footer'] = $MailTemplate->getFooter();
                                
                                $ShippingName = $Shippings->getName01(). $Shippings->getName02(). '　様';
                                $mailData['header'] = str_replace('お名前　　：', 'お名前　　：'. $ShippingName, $mailData['header']);
                                $mailData['header'] = str_replace('送り状番号：', '送り状番号：'. $row['伝票番号'], $mailData['header']);
                                
                                $body = $this->createBody($app, $mailData['header'], $mailData['footer'], $Order);
                                // メール送信
                                $app['eccube.service.mail']->sendAdminOrderMail($Order, $mailData);

                                // 送信履歴を保存.
                                $MailHistory = new MailHistory();
                                $MailHistory
                                    ->setSubject($mailData['subject'])
                                    ->setMailBody($body)
                                    ->setMailTemplate($MailTemplate)
                                    ->setSendDate(new \DateTime())
                                    ->setOrder($Order);

                                $app['orm.em']->persist($MailHistory);
                                $app['orm.em']->flush($MailHistory);
                            }

                            // 対応状況変更
                            if ($status_flg == 1) {                     // customized by wellco
                                $status = $this->getStatus($order_id, $app);
                                
                                if (in_array($status, $subData['order_status'])) {
                                    $orderRepository = $app['orm.em']->getRepository('Eccube\Entity\Order');
                                    $Status = $app['eccube.repository.order_status']->find(5);
                                    $orderRepository->changeStatus($order_id, $Status);
                                }
                            }

                        }
                        $this->em->persist($TransportCSVimportB2);
                    }

                    $this->em->flush();
                    $this->em->getConnection()->commit();

                    $app->addSuccess('B2送り状番号を登録しました。', 'admin');
                }

            }
        }

        return $this->render($app, $form, $headers, $this->importB2Twig);
    }

    /**
     * 登録、更新時のエラー画面表示
     *
     */
    protected function render($app, $form, $headers, $twig)
    {
        $objTCSV =& PluginUtil::getInstance($app);
        $subData = $objTCSV->getUserSettings();
        
        $arrOS = array();
        
        if ($OrderStatus = $app['eccube.repository.order_status']->findAllArray()) {
            foreach ($OrderStatus as $val) {
                $arrOS[$val['id']] = $val['name'];
            }
        }
        
        $order_status = '';
        
        if ($subData['order_status']) {
            $tmp = array();
            foreach ($subData['order_status'] as $id) {
                $tmp[] = $arrOS[$id];
            }
            $order_status = implode(',', $tmp);
        }
        
        if ($this->hasErrors()) {
            if ($this->em) {
                $this->em->getConnection()->rollback();
            }
        }

        if (!empty($this->fileName)) {
            try {
                $fs = new Filesystem();
                $fs->remove($app['config']['csv_temp_realdir'] . '/' . $this->fileName);
            } catch (\Exception $e) {
                // エラーが発生しても無視する
            }
        }

        return $app->render($twig, array(
            'form'         => $form->createView(),
            'headers'      => $headers,
            'errors'       => $this->errors,
            'order_status' => $order_status,
        ));
    }


    /**
     * アップロードされたCSVファイルの行ごとの処理
     *
     * @param $formFile
     * @return CsvImportService
     */
    protected function getImportData($app, $formFile)
    {
        // アップロードされたCSVファイルを一時ディレクトリに保存
        $this->fileName = 'upload_' . Str::random() . '.' . $formFile->getClientOriginalExtension();
        $formFile->move($app['config']['csv_temp_realdir'], $this->fileName);

        $file = file_get_contents($app['config']['csv_temp_realdir'] . '/' . $this->fileName);
        // アップロードされたファイルがUTF-8以外は文字コード変換を行う
        //$encode = Str::characterEncoding(substr($file, 0, 6));      // customized by wellco
        if ($encode != 'UTF-8') {
            //$file = mb_convert_encoding($file, 'UTF-8', $encode);   // customized by wellco
            $file = mb_convert_encoding($file, 'UTF-8', 'SJIS');      // customized by wellco
        }
        $file = Str::convertLineFeed($file);

        $tmp = tmpfile();
        fwrite($tmp, $file);
        rewind($tmp);
        $meta = stream_get_meta_data($tmp);
        $file = new \SplFileObject($meta['uri']);

        set_time_limit(0);

        // アップロードされたCSVファイルを行ごとに取得
        $data = new CsvImportService($file, $app['config']['csv_import_delimiter'], $app['config']['csv_import_enclosure']);

        $ret = $data->setHeaderRowNumber(0);

        return ($ret !== false) ? $data : false;
    }

    /**
     * 登録、更新時のエラー画面表示
     *
     */
    protected function addErrors($message)
    {
        $e = new CsvImportException($message);
        $this->errors[] = $e;
    }

    /**
     * @return array
     */
    protected function getErrors()
    {
        return $this->errors;
    }

    /**
     *
     * @return boolean
     */
    protected function hasErrors()
    {
        return count($this->getErrors()) > 0;
    }

    /**
     * カテゴリCSVヘッダー定義
     */
    private function getB2CsvHeader()
    {
        return array(
            "お客様管理番号" => "customer_code",
            "送り状種別" => "deliv_class",
            "クール区分" => "cool_class",
            "伝票番号" => "denpyou_number",
            "出荷予定日" => "send_date",
            "お届け予定（指定）日" => "shipping_delivery_date",
            "配達時間帯" => "shipping_delivery_time",
            "お届け先コード" => "shipping_code",
            "お届け先電話番号" => "tel01",
            "お届け先電話番号枝番" => "tel_eda",
            "お届け先郵便番号" => "zip01",
            "お届け先住所" => "addr01",
            "お届け先住所（アパートマンション名）" => "addr02",
            "お届け先会社・部門名１" => "company_name",
            "お届け先会社・部門名２" => "company_name_xxx",
            "お届け先名" => "name01",
            "お届け先名略称カナ" => "kana01",
            "敬称" => "keisyou",
            "ご依頼主コード" => "code",
            "ご依頼主電話番号" => "tel01",
            "ご依頼主電話番号枝番" => "shipping_tel_eda",
            "ご依頼主郵便番号" => "zip01",
            "ご依頼主住所" => "addr01",
            "ご依頼主住所（アパートマンション名）" => "addr02",
            "ご依頼主名" => "campany_name",
            "ご依頼主略称カナ" => "campany_kana",
            "品名コード１" => "product_code01",
            "品名１" => "product_name01",
            "品名コード２" => "product_code02",
            "品名２" => "product_name02",
            "荷扱い１" => "item01",
            "荷扱い２" => "item02",
            "記事" => "news",
            "コレクト代金引換額（税込）" => "daibiki_price",
            "コレクト内消費税額等" => "daibiki_tax",
            "営業所止置き" => "stop",
            "営業所コード" => "code",
            "発行枚数" => "cnt",
            "個数口枠の印字" => "inji",
            "ご請求先顧客コード" => "cstmr_code",
            "ご請求先分類コード" => "bunrui_code",
            "運賃管理番号" => "kanri",
        );
    }
    /** 
     * カテゴリCSVヘッダー定義
     * customized by wellco
     */
    private function getB2CsvHeader2()
    {
        return array(
            "伝票番号" => "denpyou_number",
            "お届け先コード" => "shipping_code",
            "お届け先名" => "name01",
            "荷物状況" => "dummy01",
            "日付" => "dummy02",
            "時刻" => "dummy03",
            "出荷日" => "dummy04",
            "ｻｲｽﾞ品目" => "dummy05",
            "運賃" => "dummy06",
            "お客様管理番号" => "customer_code",
        );
    }

    /**
     * ステータス取得
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

    private function createBody($app, $header, $footer, $Order)
    {
        return $app->renderView('Mail/order.twig', array(
            'header' => $header,
            'footer' => $footer,
            'Order' => $Order,
        ));
    }
}
