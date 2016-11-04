<?php

/*
 * Copyright(c) 2015 GMO Payment Gateway, Inc. All rights reserved.
 * http://www.gmo-pg.com/
 */

namespace Plugin\WellDirect;

use Eccube\Event\TemplateEvent;
use Eccube\Event\EventArgs;

use Eccube\Util\EntityUtil;
use Eccube\Common\Constant;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\Validator\Constraints as Assert;

class WellDirect {

    private $app;

    public function __construct($app) {
        $this->app = $app;
    }

    /**
     * カート情報見積保存ボタン表示
     * @param Event $event
     * @return type
     */
    public function onRenderCartSaveButton(TemplateEvent $event)
    {
        $app = $this->app;

        $source = $event->getSource();

		//受注ステータスが入力中の場合のみ差し込む
        if(preg_match('/<(.*)\s*id="total_box__top_button.*>/',$source, $result)){
            $start_tag = $result[0];
            $tag_name = trim($result[1]);
            $end_tag = '</' . $tag_name . '>';
            $start_index = strpos($source, $start_tag);
            $end_index = strpos($source, $end_tag, $start_index);

            $search = substr($source, $start_index, ($end_index - $start_index));
            $search .= $end_tag;
                
	        // 差込テンプレート
	        $snipet = file_get_contents($app['config']['plugin_realdir']. '/WellDirect/Resource/template/default/Cart/index_button_cart_save.twig');
            $replace = $search.$snipet;

            $source = str_replace($search, $replace, $source);
		}
        
        $event->setSource($source);
    }
    

    private function getHtml(Crawler $crawler)
    {
        $html = '';
        foreach ($crawler as $domElement) {
            $domElement->ownerDocument->formatOutput = true;
            $html .= $domElement->ownerDocument->saveHTML();
        }

        return html_entity_decode($html, ENT_NOQUOTES, 'UTF-8');
    }
    

    /**
     * 注文履歴詳細に削除ボタン表示
     * @param Event $event
     * @return type
     */
    public function onRenderInsertDeleteButton(TemplateEvent $event)
    {
        $app = $this->app;

        $parameters = $event->getParameters();
        
        $source = $event->getSource();

        $Order = $parameters['Order'];


		//受注ステータスが入力中の場合のみ差し込む
		if ( $Order->getOrderStatus()->getId() == $app['config']['order_estimate'] ) {
	        if(preg_match('/<(.*)\s*class="col-sm-4 col-sm-offset-4.*>/',$source, $result)){
	            $start_tag = $result[0];
	            $tag_name = trim($result[1]);
	            $end_tag = '</' . $tag_name . '>';
	            $start_index = strpos($source, $start_tag);
	            $end_index = strpos($source, $end_tag, $start_index);

	            $search = substr($source, $start_index, ($end_index - $start_index));
	                
		        // 差込テンプレート
		        $snipet1 = file_get_contents($app['config']['plugin_realdir']. '/WellDirect/Resource/template/default/Mypage/index_button_order_delete.twig');
		        $snipet2 = file_get_contents($app['config']['plugin_realdir']. '/WellDirect/Resource/template/default/Mypage/index_button_pdf_download.twig');
		        $snipet = $snipet1 . $snipet2;
	            $replace = $search.$snipet;
	            $replace .= $end_tag;

	            $source = str_replace($search, $replace, $source);
	        }
		}
        
        $event->setSource($source);
    }

    /**
     * 利用規約同意チェックボックス表示
     * @param TemplateEvent $event
     * @return type
     */
//    public function onFormInitializeEntry(TemplateEvent $event)
    public function onRenderEntryIndexAddFieldInit(EventArgs $event)
    {

        $app = $this->app;
        
        $builder = $event->getArgument('builder');
        $request = $event->getRequest();
        
        $builder->add('section_name', 'text', array(
                'label' => '部署名',
                'required' => false,
                'constraints' => array(
                    new Assert\Length(array(
                        'max' => $app['config']['stext_len'],
                    )),
                ),
    		));

		// モードに応じて入力チェックの内容を変更する
    	switch ($request->get('mode')) {
    	case 'confirm':
	        $builder->add('entry_checkbox', 'checkbox', array(
	                'label' => '規約に同意する',
	                'required' => true,
	                'constraints' => array(
	                    new Assert\NotBlank(),
	                ),
	    		));
	    		break;
    	default:
	        $builder->add('entry_checkbox', 'checkbox', array(
	                'label' => '規約に同意する',
	                'required' => false,
	                'constraints' => array(
	                ),
	    		));
    		break;
    	}

    }

    /**
     * 新規会員カスタマイズ項目追加
     * @param TemplateEvent $event
     * @return type
     */
    public function onRenderEntryIndexAddField(TemplateEvent $event)
    {
        $app = $this->app;
        
        $source = $event->getSource();
        
        //入力画面
        if(preg_match('/<(.*)\s*id="top_box__company_name.*>/',$source, $result)){
            $start_tag = $result[0];
            $tag_name = trim($result[1]);
            $end_tag = '</' . $tag_name . '>';
            $start_index = strpos($source, $start_tag);
            $end_index = strpos($source, $end_tag, $start_index);

            $search = substr($source, $start_index, ($end_index - $start_index));
            $search .= $end_tag;
                
	        // 差込テンプレート(部署名)
	        $snipet = file_get_contents($app['config']['plugin_realdir']. '/WellDirect/Resource/template/default/Entry/entry_text_section_name.twig');
            $replace = $search.$snipet;

            $source = str_replace($search, $replace, $source);
        }

        if(preg_match('/<(.*)\s*id="top_box__agreement.*>/',$source, $result)){
            $start_tag = $result[0];
            $tag_name = trim($result[1]);
            $end_tag = '</' . $tag_name . '>';
            $start_index = strpos($source, $start_tag);
            $end_index = strpos($source, $end_tag, $start_index);

            $search = substr($source, $start_index, ($end_index - $start_index));
            $search .= $end_tag;
                
	        // 差込テンプレート(同意チェックボックス)
	        $snipet = file_get_contents($app['config']['plugin_realdir']. '/WellDirect/Resource/template/default/Entry/entry_checkbox_yes.twig');
            $replace = $search.$snipet;

            $source = str_replace($search, $replace, $source);
        }
        
        $event->setSource($source);

    }

    /**
     * 新規会員カスタマイズ項目追加
     * @param TemplateEvent $event
     * @return type
     */
//    public function onFormInitializeEntry(TemplateEvent $event)
    public function onRenderEntryConfirmAddField(TemplateEvent $event)
    {
        $app = $this->app;
        
        $source = $event->getSource();
        
        //確認画面
        if(preg_match('/<(.*)\s*id="confirm_box__company_name.*>/',$source, $result)){
            $start_tag = $result[0];
            $tag_name = trim($result[1]);
            $end_tag = '</' . $tag_name . '>';
            $start_index = strpos($source, $start_tag);
            $end_index = strpos($source, $end_tag, $start_index);

            $search = substr($source, $start_index, ($end_index - $start_index));
            $search .= $end_tag;
                
	        // 差込テンプレート(同意チェックボックス)
	        $snipet = file_get_contents($app['config']['plugin_realdir']. '/WellDirect/Resource/template/default/Entry/entry_text_section_name_confirm.twig');
            $replace = $search.$snipet;

            $source = str_replace($search, $replace, $source);
        }
        
        $event->setSource($source);

    }

    /**
     * 利用規約同意チェックボックス表示
     * @param TemplateEvent $event
     * @return type
     */
    public function onRenderShoppingIndexAddFieldInit(EventArgs $event)
    {
        $app = $this->app;
/*
        $builder = $event->getArgument('builder');
        $objOrder = $event->getArgument('Order');
        $objOrderDetail = $objOrder->getOrderDetails();
        $arrOrderDetail = $objOrderDetail->toArray();
        $flgPrintItem = false;
        foreach($arrOrderDetail as $idx => $order_detail) {
        	$objProduct = $order_detail->getProduct();
        	//印刷販売か否か
        	if ( $objProduct->hasProductClass() ) {
        		$flgPrintItem = true;
        		break;
        	}
        }

        if ( $flgPrintItem ) {
	        $builder
	            ->add('pdffile', 'file', array(
	                'label' => '入稿データ選択',
	                'mapped' => false,
	                'required' => true,
	                'constraints' => array(
	                    new Assert\NotBlank(array('message' => 'ファイルを選択してください。')),
	                    new Assert\File(array(
	                        'maxSize' => $app['config']['pdf_size'] . 'M',
	                        'maxSizeMessage' => 'PDFファイルは' . $app['config']['pdf_size'] . 'M以下でアップロードしてください。',
	                    )),
	                ),
	            ));
        }
*/
    }

    /**
     * 購入確認ページカスタマイズ項目追加
     * @param TemplateEvent $event
     * @return type
     */
//    public function onFormInitializeEntry(TemplateEvent $event)
    public function onRenderShoppingIndexAddField(TemplateEvent $event)
    {
        $app = $this->app;

        $parameters = $event->getParameters();
        $Order = $parameters['Order'];
        //印刷商品判定
        $flgPrintItem = $app['eccube.service.product']->isPrintProductByOrder($Order);
        
        $source = $event->getSource();

		//印刷商品のみ
        if ( $flgPrintItem ) {
	        //PDFアップロード差込
	        if(preg_match('/<(.*)\s*class="heading02.*>/',$source, $result)){
	            $start_tag = $result[0];
	                
		        // 差込テンプレート(部署名)
		        $snipet = file_get_contents($app['config']['plugin_realdir']. '/WellDirect/Resource/template/default/Shopping/index_file_upload_pdf.twig');
	            $replace = $snipet . $start_tag;

	            $source = str_replace($start_tag, $replace, $source);
	        }
        }

        $event->setSource($source);

    }


    /**
     * PDFUpload処理
     * @param TemplateEvent $event
     * @return type
     */
    public function uploadPdfFile(EventArgs $event)
    {
        $app = $this->app;

    	$request = $event->getRequest();
        $Order   = $event->getArgument('Order');
    	
		$flg_pdf_file_save = false;

		$pdf_files = $request->files->get('shopping');
		$objPdffile = $pdf_files['pdffile'];

		//未選択時は処理しない
		if ( !is_null($objPdffile) ) {

			$orgFileName = $objPdffile->getClientOriginalName();
			$orgFileExt  = $objPdffile->getClientOriginalExtension();

			//アップロードファイル名
	        $pdf_file_name = date('mdHis') . uniqid('_') . '.' . $orgFileExt;

/*
			if ( strpos($orgFileName, '.pdf') === false ) {
				throw new UnsupportedMediaTypeHttpException();
			}
*/
			//カスタム注文IDもセットする
			$Order = $app['eccube.service.shopping']->setCustomOrderId($app, $Order);

			//受注ステータス
			$app['eccube.service.shopping']->setOrderStatus($Order, $app['config']['order_new']);
			//ファイル名セット
			$Order->setPdfFileName($pdf_file_name);
			//入稿データ登録済みフラグ
			$Order->setPdfUploadFlg(1);

	        // DB更新
	        $app['orm.em']->persist($Order);
	        $app['orm.em']->flush($Order);

			//一時領域に移動
			$objPdffile->move($app['config']['image_save_realdir'], $pdf_file_name);
		}
    }

    /**
     * PDFUpload処理(GMO決済使用時)
     *
     * GMO決済前にファイル移動をすると入力バリデーターでエラーになる
     * 決済前にDBに一時ファイル情報を保存し、購入完了のタイミングでファイル移動を行う。
     
     * @param TemplateEvent $event
     * @return type
     */
    public function uploadPdfTmpFile(EventArgs $event)
    {
        $app = $this->app;

		//受注ID
        $order_id   = $event->getArgument('orderId');
        
        //受注検索
        $Order = $app['eccube.repository.order']->findOneBy(array('id' => $order_id));

        if ( !is_null($Order) && !$Order->isPdfUploadFlg() ) {
        	$pdf_file = $Order->getPdfFileName();

        	//ファイル移動
        	@copy($app['config']['image_temp_realdir'] . '/' . $pdf_file, $app['config']['image_save_realdir'] . '/' . $pdf_file);
        	@unlink($app['config']['image_temp_realdir'] . '/' . $pdf_file);

			//入稿データ登録済みフラグ
			$Order->setPdfUploadFlg(1);
			
			//カスタム注文IDをセットしておく（念のため処理）
			$Order = $app['eccube.service.shopping']->setCustomOrderId($app, $Order);

	        // DB更新
	        $app['orm.em']->persist($Order);
	        $app['orm.em']->flush($Order);

        }
    	
    }

    /**
     * クレジット決済およびコンビニ決済押下時の処理
     * GMOペイメント処理の前にCallされます。
     * @param TemplateEvent $event
     * @return type
     */
    public function onControllerShoppingConfirmBefore($event = null) {

    	$request = $event->getRequest();

		// 入力中受注ID
		$pre_order_id = $this->app['session']->get('estimate_order_id');
		
		// MyPageから来た場合
		$Order = null;
		if ( $pre_order_id != '' ) {
        	$Order = $this->app['eccube.repository.order']->findOneBy(array('id' => $pre_order_id));
		} else {
			$Order = $this->app['eccube.repository.order']->findOneBy(array('pre_order_id' => $this->app['eccube.service.cart']->getPreOrderId()));
		}
		//フォームオブジェクト取得
        $form = $this->app['eccube.service.shopping']->getShippingForm($Order);

		if ($form->isValid()) {
			$flg_pdf_file_save = false;

			//ファイル名
			$upload_files = $request->files->get('shopping');
			$objUploadfile = $upload_files['pdffile'];

			//未選択時は処理しない
			if ( !is_null($objUploadfile) ) {
				$orgFileName = $objUploadfile->getClientOriginalName();
				$orgFileExt  = $objUploadfile->getClientOriginalExtension();

				//アップロードファイル名
		        $upload_file_name = date('mdHis') . uniqid('_') . '.' . $orgFileExt;

/*
				if ( strpos($orgFileName, '.pdf') === false ) {
					throw new UnsupportedMediaTypeHttpException();
				}
*/
				//カスタム注文IDもセットする
				$Order = $this->app['eccube.service.shopping']->setCustomOrderId($this->app, $Order);

				//受注ステータス
				//$this->app['eccube.service.shopping']->setOrderStatus($Order, $this->app['config']['order_new']);
				//ファイル名セット
				$Order->setPdfFileName($upload_file_name);

				//入稿データ登録済みフラグ
				$Order->setPdfUploadFlg(0);
				//IDセット

		        // DB更新
		        $this->app['orm.em']->persist($Order);
		        $this->app['orm.em']->flush($Order);

				//一時ファイル名取得
				$tmp_file_name = $_FILES['shopping']['tmp_name']['pdffile'];

				//一時領域にコピー
				@copy($tmp_file_name, $this->app['config']['image_temp_realdir'] . '/' . $upload_file_name);
				//$target = $objPdffile->move($this->app['config']['image_temp_realdir'], $pdf_file_name);

			}
		}
    }

    /**
     * マイページ会員情報変更入力項目差込
     * @param TemplateEvent $event
     * @return type
     */
    public function onRenderMyPageChangeAddFieldInit(EventArgs $event)
    {
        $app = $this->app;
        
        $builder = $event->getArgument('builder');
        $builder->add('section_name', 'text', array(
                'label' => '部署名',
                'required' => false,
                'constraints' => array(
                    new Assert\Length(array(
                        'max' => $app['config']['stext_len'],
                    )),
                ),
    		));
    }

    /**
     * マイページ会員情報変更項目追加
     * @param TemplateEvent $event
     * @return type
     */
    public function onRenderMyPageChangeAddField(TemplateEvent $event)
    {
        $app = $this->app;
        
        $source = $event->getSource();

        //入力画面
        if(preg_match('/<(.*)\s*id="detail_box__company_name.*>/',$source, $result)){
            $start_tag = $result[0];
            $tag_name = trim($result[1]);
            $end_tag = '</' . $tag_name . '>';
            $start_index = strpos($source, $start_tag);
            $end_index = strpos($source, $end_tag, $start_index);

            $search = substr($source, $start_index, ($end_index - $start_index));
            $search .= $end_tag;
                
	        // 差込テンプレート(部署名)
	        $snipet = file_get_contents($app['config']['plugin_realdir']. '/WellDirect/Resource/template/default/MyPage/change_textbox_entry_section_name.twig');
            $replace = $search.$snipet;

            $source = str_replace($search, $replace, $source);
        }
        
        $event->setSource($source);

    }

    /**
     * 見積情報削除（見積→注文時）
     * @param TemplateEvent $event
     * @return type
     */
    public function deleteEstimateOrder(EventArgs $event)
    {
        $app = $this->app;
        $pre_order_id = $app['session']->get('estimate_order_id');

		//見積IDがある場合のみ
		if ( $pre_order_id != '' ) {
	        //$Order = $app['eccube.repository.order']->findOneBy(array('id' => $pre_order_id));
	        
	        //削除フラグセット
	        //$Order->setDelFlg(Constant::ENABLED);

	        //$app['orm.em']->persist($Order);
	        //$app['orm.em']->flush();
	        //$app['orm.em']->refresh($Order);
	        
	        //見積ID削除
	        $app['session']->set('estimate_order_id', '');
		}
        
        return;
    }

/////////////////////////////////////////////////////////////////////////
// ↓管理画面

    /**
     * 会員情報変更入力項目差込
     * @param TemplateEvent $event
     * @return type
     */
    public function onRenderAdminCustomerAddFieldInit(EventArgs $event)
    {

        $app = $this->app;
        
        $builder = $event->getArgument('builder');
        $builder->add('reins_customer_code', 'text', array(
                'label' => '取引先コード',
                'required' => false,
                'constraints' => array(
                    new Assert\Length(array(
                        'max' => $app['config']['stext_len'],
                    )),
                ),
    		));
        $builder->add('section_name', 'text', array(
                'label' => '部署名',
                'required' => false,
                'constraints' => array(
                    new Assert\Length(array(
                        'max' => $this->app['config']['stext_len'],
                    )),
                ),
    		));

    }

    /**
     * 会員情報変更入力項目差込
     * @param TemplateEvent $event
     * @return type
     */
    public function onRenderAdminCustomerAddField(TemplateEvent $event)
    {
        $app = $this->app;
        
        $source = $event->getSource();

        //部署名
        if(preg_match('/<(.*)\s*id="detail_box__address.*>/',$source, $result)){
            $start_tag = $result[0];
                

	        // 差込テンプレート(受注番号)
	        $snipet = file_get_contents($app['config']['plugin_realdir']. '/WellDirect/Resource/template/admin/Customer/add_section_name.twig');
            $replace = $snipet . $start_tag;

            $source = str_replace($start_tag, $replace, $source);
        }

        //入力画面
        if(preg_match('/<(.*)\s*class="extra-form.*>/',$source, $result)){
            $start_tag = $result[0];

	        // 差込テンプレート(取引先コード)
	        $snipet = file_get_contents($app['config']['plugin_realdir']. '/WellDirect/Resource/template/admin/Customer/add_reins_customer_code.twig');
            $replace = $snipet . $start_tag;

            $source = str_replace($start_tag, $replace, $source);

        }
        
        $event->setSource($source);

    }

    /**
     * 受注編集入力項目差込
     * @param TemplateEvent $event
     * @return type
     */
    public function onRenderAdminOrderAddField(TemplateEvent $event)
    {
        $app = $this->app;
        
        $source = $event->getSource();

        //基幹システム受注番号
        if(preg_match('/<(.*)\s*id="number_info_box__order_status_info.*>/',$source, $result)){
            $start_tag = $result[0];
            $tag_name = trim($result[1]);
            $end_tag = '</' . $tag_name . '>';
            $start_index = strpos($source, $start_tag);
            $end_index = strpos($source, $end_tag, $start_index);

            $search = substr($source, $start_index, ($end_index - $start_index));
            $search .= $end_tag;
                

	        // 差込テンプレート(受注番号)
	        $snipet = file_get_contents($app['config']['plugin_realdir']. '/WellDirect/Resource/template/admin/Order/add_reins_order_id.twig');
            $replace = $search . $snipet;

            $source = str_replace($search, $replace, $source);
        }
        
        //発送個数、伝票番号
        if(preg_match('/<(.*)\s*id="detail__insert_button.*>/',$source, $result)){
/*
            $start_tag = $result[0];
                

	        // 差込テンプレート(受注番号)
	        $snipet = file_get_contents($app['config']['plugin_realdir']. '/WellDirect/Resource/template/admin/Order/add_shipping.twig');
            $replace = $snipet . $start_tag;

            $source = str_replace($start_tag, $replace, $source);
*/
        }
        
        //入稿データダウンロード
        if(preg_match('/<(.*)\s*id="number_info_box__update_date.*>/',$source, $result)){

            $start_tag = $result[0];
            $tag_name = trim($result[1]);
            $end_tag = '</' . $tag_name . '>';
            $start_index = strpos($source, $start_tag);
            $end_index = strpos($source, $end_tag, $start_index);

            $search = substr($source, $start_index, ($end_index - $start_index));
            $search .= $end_tag;
                

	        // 差込テンプレート(受注番号)
	        $snipet = file_get_contents($app['config']['plugin_realdir']. '/WellDirect/Resource/template/admin/Order/add_pdf_file.twig');
            $replace = $search . $snipet;

            $source = str_replace($search, $replace, $source);
        }
        
        $event->setSource($source);

    }
    
    

}
