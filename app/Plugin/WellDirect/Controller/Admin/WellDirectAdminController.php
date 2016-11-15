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


namespace Plugin\WellDirect\Controller\Admin;

use Eccube\Application;
use Eccube\Entity\MailHistory;
use Eccube\Common\Constant;
use Eccube\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception as HttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class WellDirectAdminController extends AbstractController
{

    /**
     * PDFダウンロード
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function pdfDownload(Application $app, Request $request, $id)
    {
    
    	$arrOids = array();
    	
    	//ダウンロード対象の受注番号
    	if ( is_numeric($id) ) {
    		$arrOids[] = $id;
    	} else {
    		$namekey = 'ids';
    		$arr_req_keys = array_keys($_REQUEST);
    		foreach($arr_req_keys as $key) {
    			if ( strpos($key, $namekey) > -1 ) {
    				$arrOids[] = str_replace($namekey, '', $key);
    			}
    		}
    	}
    	
        $now = new \DateTime();
        $nowTime = $now->format('YmdHis');
    	
    	// PDFダウンロード一時ディレクトリ作成
    	$pdf_download_dir = $app['config']['image_temp_realdir'] . '/' . $nowTime;

		// ダウンロードファイル名
        $zip_filename = 'order_uploadfile_' . $nowTime . '.zip';
        $zip_filepath = $app['config']['image_temp_realdir'] . '/' . $zip_filename;
    	
    	// ディレクトリが無ければ作成
    	if (!is_dir($pdf_download_dir) ) {
    		@mkdir($pdf_download_dir, 0777, true);
    	}
    	
    	// ファイルをコピーする
    	foreach($arrOids as $oid) {
    		//受注データ取得
            $TargetOrder = $app['eccube.repository.order']->find($oid);
            if (is_null($TargetOrder)) {
                throw new NotFoundHttpException();
            }

            //PDFファイルが無い場合はスキップする
            if ( $TargetOrder->getPdfFileName() == '' ) {
            	continue;
            }
            

			//カスタム注文ID
            $custom_order_id = $TargetOrder->getCustomOrderId();
            
            //一時ディレクトリにファイルコピ
            @copy($app['config']['image_save_realdir'] . '/' . $TargetOrder->getPdfFileName(), $pdf_download_dir . '/' . $TargetOrder->getPdfFileName());
    	}
    	
    	//フォルダごと圧縮(Linux環境限定)
    	$command = "cd " . $pdf_download_dir . ";zip " . $zip_filename . " ./*";
    	//$command = "zip " . $zip_filepath . " " . $pdf_download_dir . "/*";
    	
    	// 圧縮
    	exec($command, $out, $ret);
    	
    	//ZIPファイル移動
    	@copy($pdf_download_dir . '/' . $zip_filename, $zip_filepath);
    	
    	// 一時ディレクトリ削除
    	$this->remove_directory($pdf_download_dir);
$app->log("pdf_download_dir = " . $pdf_download_dir);
$app->log("zip_filename = " . $zip_filepath);
$app->log("command = " . $command);
if ( is_array($out) ) {
	foreach($out as $abc) {
$app->log($abc);
	}
}
$app->log("ret = " . $ret);
   	
/*    	【ZipArchiveはメモリ消費が激しいので使用中止】
        $zip = new \ZipArchive();
        $now = new \DateTime();

		//メモリの上限を一時的に増やす
		ini_set('memory_limit', '3096M');
		
        $zip_filename = 'order_uploadfile_' . $now->format('YmdHis') . '.zip';
        $zip_filepath = $app['config']['image_temp_realdir'] . '/' . $zip_filename;
        $result = $zip->open($zip_filepath, \ZIPARCHIVE::CREATE | \ZIPARCHIVE::OVERWRITE);
    
    	//PDFダウンロード
    	$index = 0;
    	foreach($arrOids as $oid) {
    		//配列サイズを超えたら終了
    		if ( count($arrOids) <= $index ) {
    			break;
    		}
    		$oid = $arrOids[$index];
    		$index++;
    		
            $TargetOrder = $app['eccube.repository.order']->find($oid);
            if (is_null($TargetOrder)) {
                throw new NotFoundHttpException();
            }
            
            //PDFファイルが無い場合はスキップする
            if ( $TargetOrder->getPdfFileName() == '' ) {
            	continue;
            }
            
            $custom_order_id = $TargetOrder->getCustomOrderId();
            
            ob_start();
            $zip->addFromString($custom_order_id . '_' . $TargetOrder->getPdfFileName(), file_get_contents($app['config']['image_save_realdir'] . '/' . $TargetOrder->getPdfFileName()));
            while(ob_get_level() > 0) {
            	ob_end_clean();
            }

			//１ファイル毎に５分ずつ拡張する
			set_time_limit(300);
            
            //受注ステータス更新
            $OrderStatus = $app['eccube.repository.order_status']->find($app['config']['order_data_check_now']);
            $orderRepository = $app['orm.em']->getRepository('Eccube\Entity\Order');
    	}
    	
    	//クローズ
    	$zip->close();
*/
        return $app
            ->sendFile($zip_filepath)
            ->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $zip_filename);
    }

	public function remove_directory($dir) {

		// 一時ディレクトリ削除
		if ($handle = opendir("$dir")) {
			while (false !== ($item = readdir($handle))) {
				if ($item != "." && $item != "..") {
					if (is_dir("$dir/$item")) {
						$this->remove_directory("$dir/$item");
					} else {
						unlink("$dir/$item");
						//echo " removing $dir/$item<br>\n";
					}
				}
			}
			closedir($handle);
			rmdir($dir);
			//echo "removing $dir<br>\n";
		}
	}
	
    /**
     * 印刷開始通知
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function printMail(Application $app, Request $request, $id)
    {
    	$arrOids = array();
    	
    	//ダウンロード対象の受注番号
    	if ( is_numeric($id) ) {
    		$arrOids[] = $id;
    	} else {
    		$namekey = 'ids';
    		$arr_req_keys = array_keys($_REQUEST);
    		foreach($arr_req_keys as $key) {
    			if ( strpos($key, $namekey) > -1 ) {
    				$arrOids[] = str_replace($namekey, '', $key);
    			}
    		}
    	}

    
    	$template_id = $app['config']['mailtemplate_printstart'];
        $MailTemplate = $app['eccube.repository.mail_template']->find($template_id);
    	$BaseInfo = $app['eccube.repository.base_info']->get();
    	//印刷開始通知
    	foreach($arrOids as $oid) {

            $TargetOrder = $app['eccube.repository.order']->find($oid);
            if (is_null($TargetOrder)) {
                throw new NotFoundHttpException();
            }
            
            //印刷開始案内メール送信状況が送信済みの場合はスキップする
            if ( $TargetOrder->getPrintStartMailStatus() == 1 ) {
            	continue;
            }

	        //印刷商品判定
	        $flgPrintItem = $app['eccube.service.product']->isPrintProductByOrder($TargetOrder);

	        $body = $app->renderView($MailTemplate->getFileName(), array(
	            'header' => $MailTemplate->getHeader(),
	            'footer' => $MailTemplate->getFooter(),
	            'Order' => $TargetOrder,
	            'flgPrintItem' => $flgPrintItem,
	        ));

			$subject = '[' . $BaseInfo->getShopName() . '] ' . $MailTemplate->getSubject();
	        $message = \Swift_Message::newInstance()
	            ->setSubject($subject)
	            ->setFrom(array($BaseInfo->getEmail01() => $BaseInfo->getShopName()))
	            ->setTo(array($TargetOrder->getEmail()))
	            ->setBcc($BaseInfo->getEmail01())
	            ->setReplyTo($BaseInfo->getEmail03())
	            ->setReturnPath($BaseInfo->getEmail04())
	            ->setBody($body);

	        $app->mail($message);
	        
            // 送信履歴を保存.
            $MailHistory = new MailHistory();
            $MailHistory
                ->setSubject($subject)
                ->setMailBody($body)
                ->setMailTemplate($MailTemplate)
                ->setSendDate(new \DateTime())
                ->setOrder($TargetOrder);

            $app['orm.em']->persist($MailHistory);
            $app['orm.em']->flush($MailHistory);
            
            //印刷開始案内メール送信状況更新
            $TargetOrder->setPrintStartMailStatus(1);
            $TargetOrder->setUpdateDate(new \DateTime());
            $app['orm.em']->persist($TargetOrder);
            $app['orm.em']->flush($TargetOrder);

    	}
    	
        $app->addSuccess('admin.welldirect.order.printmail.complete', 'admin');

        return $app->redirect('/' . $app["config"]["admin_route"] . '/order/page/1?resume=1');
    }

}
