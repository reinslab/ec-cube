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


namespace Plugin\WellDirect\Controller;

use Eccube\Application;
use Eccube\Common\Constant;
use Eccube\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception as HttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class WellDirectController extends AbstractController
{

    /**
     * カートをロック状態に設定し、見積状態として保存する.(見積)
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function eststep(Application $app, Request $request)
    {


        // 未ログインの場合, ログイン画面へリダイレクト.
        if (!$app->isGranted('IS_AUTHENTICATED_FULLY')) {
        	$app->setLoginTargetPath('/cart/eststep');
            return $app->redirect($app->url('mypage_login'));
        }
    
		// カート情報保存
		$Customer = $app->user();

        // 受注情報を作成
        $Order = $app['eccube.service.shopping']->createEstimateOrder($Customer);

		//カスタム注文IDをセットする
		$Order = $app['eccube.service.shopping']->setCustomOrderId($app, $Order);
        
        // 見積情報作成
        $app['eccube.service.shopping']->estimatePurchase($Order);

		//商品オプション保存(商品オプションプラグインが有効な場合のみ)
		if ( is_object($app['eccube.productoption.service.shopping']) ) {
	        $Order = $app['eccube.productoption.service.shopping']->customOrder($Order);
		}

        // DB更新
        $app['orm.em']->persist($Order);
        $app['orm.em']->flush($Order);
        // 受注関連情報を最新状態に更新
        //$app['orm.em']->refresh($Order);

        // 受注IDセッションを削除
        $app['session']->remove('eccube.front.shopping.order.id');
        
        // カートセッションクリア
        $app['eccube.service.cart']->clear();
		// 商品オプションクリア(商品オプションプラグインが有効な場合のみ)
		if ( is_object($app['eccube.productoption.service.cart']) ) {
	        $app['eccube.productoption.service.cart']->clear();
		}

		//マイページにリダイレクト 
        return $app->redirect($app->url('mypage'));
    }

    /**
     * 見積データを削除
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function historydelete(Application $app, Request $request, $id)
    {
    
		// 受注検索
        $Order = $app['eccube.repository.order']->findOneBy(array(
            'id' => $id,
            'Customer' => $app->user(),
        ));
		

        $Order->setDelFlg(Constant::ENABLED);

        $app['orm.em']->persist($Order);
        $app['orm.em']->flush();
        //$app['orm.em']->refresh($Order);

		//マイページにリダイレクト 
        return $app->redirect($app->url('mypage'));
    }

    /**
     * 見積→注文処理
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function est2order(Application $app, Request $request, $id)
    {

    	// カートセッションを作成する
    	// 既存のカート情報はクリア(商品種別が異なっていると面倒なので)
        $app['eccube.service.cart']->clear();
        
		// 受注検索
        $Order = $app['eccube.repository.order']->findOneBy(array(
            'id' => $id,
            'Customer' => $app->user(),
        ));
		
        $this->isTokenValid($app);
        
        // プロダクトクラスIDを取得
        $arrDetail = $Order->getOrderDetails()->toArray();
        
        foreach($arrDetail as $idx => $objDetail) {
        	$objProductClass = $objDetail->getProductClass();
            $productClassId = $objProductClass->getId();
            $quantity = $objDetail->getQuantity();
            $app['eccube.service.cart']->addProduct($productClassId, $quantity)->save();
        }
        
        $app['eccube.service.cart']->lock();
        $app['eccube.service.cart']->save();
        
        // 削除用受注ID
        $app['session']->set('estimate_order_id', $id);
        
        // プレIDをセットしておく
        $app['eccube.service.cart']->setPreOrderId($Order->getPreOrderId());

		//マイページにリダイレクト 
        return $app->redirect($app->url('shopping'));
    }

    /**
     * 見積書ダウンロード
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function estdownload(Application $app, Request $request, $id)
    {
    
        // サービスの取得
        $service = $app['eccube.plugin.welldirect.service.order_pdf'];

        // 購入情報からPDFを作成する
        $status = $service->makePdf($id);

        // 異常終了した場合の処理
/*
        if (!$status) {
            $service->close();
            $app->addError('admin.order_pdf.download.failure', 'admin');
            return $app->render('OrderPdf/View/admin/order_pdf.twig', array(
                'form' => $form->createView()
            ));
        }
*/
        // ダウンロードする
        $response = new Response(
            $service->outputPdf(),
            200,
            array('content-type' => 'application/pdf')
        );

        // レスポンスヘッダーにContent-Dispositionをセットし、ファイル名をreceipt.pdfに指定
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $service->getPdfFileName() .'"');

        return $response;
    }


    /**
     * 入稿データ再アップロード
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function upload(Application $app, Request $request, $id)
    {
    	//受注検索
    	$Order = $app['eccube.repository.order']->findOneBy(array('id' => $id));
    	
    	//カスタム注文ID
    	$custom_order_id = $Order->getCustomOrderId();
    	
    	//アップロードファイル情報取得
		$pdf_files = $request->files->get('mypage_history');
		$objPdffile = $pdf_files['pdffile'];

		//フォームオブジェクト取得
        $builder = $app['form.factory']->createBuilder('mypage_history', null, array('order' => $Order));
        $form = $builder->getForm();
        $form->handleRequest($request);
		
		if ( $form->isValid() ) {
			//アップロードされていたら処理する
			if ( !is_null($objPdffile) ) {
			
				//更新前のファイル名
				$old_file_name = $Order->getPdfFileName();
			
				//拡張子チェック
				$orgFileName = $objPdffile->getClientOriginalName();
				$orgFileExt  = $objPdffile->getClientOriginalExtension();

	/*
				if ( strpos($orgFileName, '.pdf') === false ) {
					throw new UnsupportedMediaTypeHttpException();
				}
	*/
				
				//ファイル名
		        $pdf_file_name = $custom_order_id . uniqid('_') . '.' . $orgFileExt;

				//ファイル名セット
				$Order->setPdfFileName($pdf_file_name);
				//更新日時
				$Order->setUpdateDate(new \DateTime());
			
				//オリジナルファイル名設定
				$Order->setDataFileOriginalName($orgFileName);

		        // DB更新
		        $app['orm.em']->persist($Order);
		        $app['orm.em']->flush($Order);

				if ( !is_dir($app['config']['data_save_realdir']) ) {
					@mkdir($app['config']['data_save_realdir']);
				}

				//一時領域に移動
				$objPdffile->move($app['config']['data_save_realdir'], $pdf_file_name);
				
				//古いファイル削除
				@unlink($app['config']['data_save_realdir'] . '/' . $old_file_name);
			}
			// 元の画面にリダイレクト
			$request_uri = $_SERVER['REQUEST_URI'];
			$next_url = str_replace('/upload', '', $request_uri);

	        return $app->redirect($next_url);
		}
        $errors = $form->getErrors(true);
        $message = '';
        foreach ($errors as $error) {
            $message = $error->getMessage();
        }

// A => 再注文ボタンを制御する
        //再注文ボタン表示フラグ
        $flg_re_order = false;
        //受注データ保存後30日以内なら表示する
        $now = date('Y-m-d');
        $order_date = $Order->getCreateDate();
        $order_date = $order_date->format('Y-m-d H:i:s');
        $diff = abs(strtotime($now) - strtotime($order_date)) / (60 * 60 * 24);
        if ( $diff <= $app['config']['re_order_limit_day'] ) {
        	$flg_re_order = true;
        }
// A => 再注文ボタンを制御する
		
		//印刷商品判定
		$flgPrintItem = $app['eccube.service.product']->isPrintProductByOrder($Order);

        return $app['view']->render('Mypage/history.twig', array(
            'Order' => $Order,
// A => 再注文ボタンを制御する
            'flg_re_order' => $flg_re_order,
// A => 再注文ボタンを制御する
// A => form
			'form' => $form->createView(),
// A => form
// A => 印刷商品判定フラグ
            'flgPrintItem' => $flgPrintItem,
// A => 印刷商品判定フラグ
        ));
    	
    }



    /**
     * 入稿データ読み込み
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function dataFileLoad(Application $app, Request $request, $id)
    {
        // 未ログインの場合, ログイン画面へリダイレクト.
        if (!$app->isGranted('IS_AUTHENTICATED_FULLY')) {
        	$app->setLoginTargetPath('/data/load/' . $id);
            return $app->redirect($app->url('mypage_login'));
        }
        // IDが未設定の場合はトップページにリダイレクト
        if ( is_null($id) ) {
        	return $app->redirect($app->url('top'));
        }
        
		// 受注検索
        $Order = $app['eccube.repository.order']->findOneBy(array(
            'id' => $id,
            'Customer' => $app->user(),
        ));
        
        // 受注データが取得できなければトップページにリダイレクト
        if ( is_null($Order) ) {
        	return $app->redirect($app->url('top'));
        }
        
        // 入稿データが無ければリダイレクト
        if ( is_null($Order->getPdfFileName()) ) {
        	return $app->redirect($app->url('top'));
        }
        
        // 入稿データが実在しなければリダイレクト
        $data_file = $app['config']['data_save_realdir'] . '/' . $Order->getPdfFileName();
        if ( !file_exists($data_file) ) {
        	// TODO:エラーを戻したほうが良いのか？
        	return $app->redirect($app->url('top'));
        }
        
        return $app
            ->sendFile($data_file)
            ->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $Order->getPdfFileName());

        
    }

}
