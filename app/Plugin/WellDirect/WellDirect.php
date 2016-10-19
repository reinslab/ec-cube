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
    public function insertCartSaveButton(FilterResponseEvent $event)
    {
        $app = $this->app;

        $request = $event->getRequest();
        $response = $event->getResponse();
        $html = $response->getContent();
        
        $crawler = new Crawler($html);

        
        $oldCrawler = $crawler
            ->filter('div#total_box__user_action_menu')
            ->eq(0);
        $html = $this->getHtml($crawler);
        $oldHtml = '';
        $newHtml = '';

        if (count($oldCrawler) > 0) {
            $oldHtml = $oldCrawler->html();
            $oldHtml = html_entity_decode($oldHtml, ENT_NOQUOTES, 'UTF-8');

            $twig = $app->renderView(
                'WellDirect/Resource/template/default/Cart/cart_save_button.twig'
            );

			if ( strpos($oldHtml, $twig) === false ) {
	            $newHtml = $twig . $oldHtml;
			} else {
				$newHtml = $oldHtml;
			}
        }

        $html = str_replace($oldHtml, $newHtml, $html);
        
        $response->setContent($html);
        $event->setResponse($response);
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

        $Order = $parameters['Order'];

        $source = $event->getSource();
        if(preg_match('/<(.*)\s*class="col-sm-4 col-sm-offset-4.*>\n/',$source, $result)){
            $start_tag = $result[0];
            $tag_name = trim($result[1]);
            $end_tag = '</' . $tag_name . '>';
            $start_index = strpos($source, $start_tag);
            $end_index = strpos($source, $end_tag, $start_index);

            $search = substr($source, $start_index, ($end_index - $start_index));
                
	        // 差込テンプレート
	        $snipet = file_get_contents($app['config']['plugin_realdir']. '/WellDirect/Resource/template/default/Mypage/order_delete_button.twig');
            $replace = $search.$snipet;
            $replace .= $end_tag;

            $source = str_replace($search, $replace, $source);
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
        $builder->add('section_name', 'text', array(
                'label' => '部署名',
                'required' => false,
                'constraints' => array(
                    new Assert\Length(array(
                        'max' => $app['config']['stext_len'],
                    )),
                ),
    		));
        $builder->add('entry_checkbox', 'checkbox', array(
                'label' => '規約に同意する',
                'required' => true,
    		));

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
        if(preg_match('/<(.*)\s*id="top_box__company_name.*>\n/',$source, $result)){
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

        if(preg_match('/<(.*)\s*id="top_box__agreement.*>\n/',$source, $result)){
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
        if(preg_match('/<(.*)\s*id="confirm_box__company_name.*>\n/',$source, $result)){
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

        $builder = $event->getArgument('builder');
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

    /**
     * 購入確認ページカスタマイズ項目追加
     * @param TemplateEvent $event
     * @return type
     */
//    public function onFormInitializeEntry(TemplateEvent $event)
    public function onRenderShoppingIndexAddField(TemplateEvent $event)
    {
        $app = $this->app;
        
        $source = $event->getSource();

        //入力画面
        if(preg_match('/<(.*)\s*id="payment_list.*>/',$source, $result)){
            $start_tag = $result[0];
            $tag_name = trim($result[1]);
            $end_tag = '</' . $tag_name . '>';
            $start_index = strpos($source, $start_tag);
            $end_index = strpos($source, $end_tag, $start_index);

            $search = substr($source, $start_index, ($end_index - $start_index));
            $search .= $end_tag;
                
	        // 差込テンプレート(部署名)
	        $snipet = file_get_contents($app['config']['plugin_realdir']. '/WellDirect/Resource/template/default/Shopping/upload_pdf_file.twig');
            $replace = $search.$snipet;

            $source = str_replace($search, $replace, $source);
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
		//ファイル名
        $pdf_file_name = date('mdHis') . uniqid('_') . '.pdf';

		$pdf_files = $request->files->get('shopping');
		$objPdffile = $pdf_files['pdffile'];
		$orgFileName = $objPdffile->getClientOriginalName();
		//未選択時は処理しない
		if ( $orgFileName != '' ) {
			if ( strpos($orgFileName, '.pdf') === false ) {
				throw new UnsupportedMediaTypeHttpException();
			}
			//カスタム注文IDもセットする
			$Order = $app['eccube.service.shopping']->setCustomOrderId($app, $Order);

			//受注ステータス
			$app['eccube.service.shopping']->setOrderStatus($Order, $app['config']['order_new']);
			//ファイル名セット
			$Order->setPdfFileName($pdf_file_name);
			//IDセット

	        // DB更新
	        $app['orm.em']->persist($Order);
	        $app['orm.em']->flush($Order);

			//一時領域に移動
			$objPdffile->move($app['config']['image_save_realdir'], $pdf_file_name);
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
        if(preg_match('/<(.*)\s*id="detail_box__company_name.*>\n/',$source, $result)){
            $start_tag = $result[0];
            $tag_name = trim($result[1]);
            $end_tag = '</' . $tag_name . '>';
            $start_index = strpos($source, $start_tag);
            $end_index = strpos($source, $end_tag, $start_index);

            $search = substr($source, $start_index, ($end_index - $start_index));
            $search .= $end_tag;
                
	        // 差込テンプレート(部署名)
	        $snipet = file_get_contents($app['config']['plugin_realdir']. '/WellDirect/Resource/template/default/MyPage/entry_text_section_name.twig');
            $replace = $search.$snipet;

            $source = str_replace($search, $replace, $source);
        }
        
        $event->setSource($source);

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
        if(preg_match('/<(.*)\s*id="number_info_box__order_status_info.*>\n/',$source, $result)){
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
/*
        if(preg_match('/<(.*)\s*id="detail__insert_button.*>\n/',$source, $result)){

            $start_tag = $result[0];
                

	        // 差込テンプレート(受注番号)
	        $snipet = file_get_contents($app['config']['plugin_realdir']. '/WellDirect/Resource/template/admin/Order/add_shipping.twig');
            $replace = $snipet . $start_tag;

            $source = str_replace($start_tag, $replace, $source);
        }
*/        
        //入稿データダウンロード
        if(preg_match('/<(.*)\s*id="number_info_box__update_date.*>\n/',$source, $result)){

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
