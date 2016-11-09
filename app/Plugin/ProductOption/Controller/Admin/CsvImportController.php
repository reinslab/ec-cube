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
use Eccube\Exception\CsvImportException;
use Eccube\Service\CsvImportService;
use Eccube\Util\Str;
use Eccube\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class CsvImportController extends AbstractController
{

    private $errors = array();

    private $fileName;

    private $em;

    private $Twig = 'ProductOption/Resource/template/admin/Product/import_csv.twig';
    
    private $app;


    /**
     * CSVアップロード
     */
    public function import(Application $app, Request $request)
    {
        $this->app = $app;

        $form = $app['form.factory']->createBuilder('admin_csv_import')->getForm();

        $headers = $this->getCsvHeader();
        $ids = array();
        if ('POST' === $request->getMethod()) {

            $form->handleRequest($request);

            if ($form->isValid()) {

                $formFile = $form['import_file']->getData();

                if (!empty($formFile)) {

                    $data = $this->getImportData($app, $formFile);

                    $keys = array_keys($headers);
                    $columnHeaders = $data->getColumnHeaders();

                    if (count($keys) !== count($columnHeaders)) {
                        $this->addErrors('CSVのフォーマットが一致しません。');
                        return $this->render($app, $form, $headers, $this->Twig);
                    }

                    $size = count($data);
                    if ($size < 1) {
                        $this->addErrors('CSVデータが存在しません。');
                        return $this->render($app, $form, $headers, $this->Twig);
                    }

                    $headerSize = count($keys);

                    $this->em = $app['orm.em'];
                    $this->em->getConfiguration()->setSQLLogger(null);

                    $this->em->getConnection()->beginTransaction();
                    
                    $product_id_key = array_search('product_id',$headers);
                    $product_option_key = array_search('product_option',$headers);

                    // CSVファイルの登録処理
                    foreach ($data as $row) {
                        if ($headerSize != count($row)) {
                            $this->addErrors(($data->key() + 1) . '行目のCSVフォーマットが一致しません。');
                            return $this->render($app, $form, $headers, $this->Twig);
                        }
                        
                        if($row[$product_id_key] == '') {
                            $this->addErrors(($data->key() + 1) . '行目の'.$product_id_key.'が存在しません。');
                            
                            return $this->render($app, $form, $headers, $this->Twig);
                        }else{
                            $product_id = intval($row[$product_id_key]);
                            if(!is_numeric($product_id)){
                                $this->addErrors(($data->key() + 1) . '行目の'.$product_id_key.'のフォーマットに問題があります。');
                                
                                return $this->render($app, $form, $headers, $this->Twig);
                            }

                            $Product = $app['eccube.repository.product']->find($product_id);
                            if(!$Product){
                                $this->addErrors(($data->key() + 1) . '行目の'.$product_id_key.'は存在しないIDです。');
                                
                                return $this->render($app, $form, $headers, $this->Twig);
                            }
                            
                            // 一旦クリア
                            $currentProductOptions = $app['eccube.productoption.repository.product_option']->getListByProductId($product_id);
                            foreach ($currentProductOptions as $currentProductOption) {
                                $app['orm.em']->remove($currentProductOption);
                            }
                            $this->em->flush();

                            if(strlen($row[$product_option_key]) > 0){
                                $productOptions = explode($app['config']['csv_import_delimiter'], $row[$product_option_key]);
                                foreach($productOptions as $option_id){
                                    $Option = $app['eccube.productoption.repository.option']->find($option_id);
                                    if($Option){
                                        $ProductOption = new \Plugin\ProductOption\Entity\ProductOption();
                                        $ProductOption->setProduct($Product);
                                        $ProductOption->setProductId($product_id);
                                        $ProductOption->setOption($Option);
                                        $ProductOption->setOptionId($option_id);
                                        $app['eccube.productoption.repository.product_option']->save($ProductOption);
                                    }
                                }
                            }
                        }
                    }

                    $this->em->flush();
                    $this->em->getConnection()->commit();
                    $this->em->close();

                    $app->addSuccess('admin.product.option.csv_import.save.complete', 'admin');
                    //アップロードファイル削除
                    unlink($app['config']['csv_temp_realdir'] . '/' . $this->fileName);
                }

            }
        }

        return $this->render($app, $form, $headers, $this->Twig, $ids);
    }


    /**
     * 登録、更新時のエラー画面表示
     *
     */
    protected function render($app, $form, $headers, $twig, $ids = array())
    {

        if ($this->hasErrors()) {
            if ($this->em) {
                $this->em->getConnection()->rollback();
                $this->em->close();
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
            'form' => $form->createView(),
            'headers' => $headers,
            'errors' => $this->errors,
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
        $check_str = str_getcsv($file,"\n");
        $encode = Str::characterEncoding($check_str[0]);
        if ($encode != 'UTF-8') {
            $file = mb_convert_encoding($file, 'UTF-8', $encode);
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

        $data->setHeaderRowNumber(0);

        return $data;
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
     * ヘッダー定義
     */
    protected function getCsvHeader()
    {
        return array('商品ID' => 'product_id','商品名' => 'product_name','オプション割当情報' => 'product_option');
    }
    

}
