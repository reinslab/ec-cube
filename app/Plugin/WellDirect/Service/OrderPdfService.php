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

namespace Plugin\WellDirect\Service;

use Eccube\Application;
use Plugin\WellDirect\Service;

class OrderPdfService extends AbstractFPDIService
{
    // ====================================
    // 定数宣言
    // ====================================
    /** ORderPdf用リポジトリ名 */
    const REPOSITORY_ORDER_PDF = 'eccube.plugin.welldirect.repository.order_pdf_order';

    /** 通貨単位 */
    const MONETARY_UNIT = '円';

    /** ダウンロードするPDFファイルのデフォルト名 */
    const DEFAULT_PDF_FILE_NAME = 'mitsumorisyo1.pdf';

    /** FONT ゴシック */
    const FONT_GOTHIC = 'kozgopromedium';
    /** FONT 明朝 */
    const FONT_SJIS = 'kozminproregular';

    /** PDFテンプレートファイル名 */
    const PDF_TEMPLATE_FILE_PATH = '/../Resource/template/mitsumorisyo1.pdf';

    // ====================================
    // 変数宣言
    // ====================================
    /** @var \Eccube\Application */
    public $app;

    /** @var \Eccube\Entity\BaseInfo */
    public $BaseInfo;

    /*** 購入詳細情報 ラベル配列 @var array */
    private $labelCell = array();

    /*** 購入詳細情報 幅サイズ配列 @var array */
    private $widthCell = array();

    /** 最後に処理した注文番号 @var unknown */
    private $lastOrderId = null;

    /** 処理する注文番号件数 @var unknown */
    private $orderIdCnt = 0;
    
    /** 受注情報 **/
    private $order_data = null;

    // --------------------------------------
    // Font情報のバックアップデータ
    /** @var unknown フォント名 */
    private $bakFontFamily;
    /** @var unknown フォントスタイル */
    private $bakFontStyle;
    /** @var unknown フォントサイズ */
    private $bakFontSize;
    // --------------------------------------

    // lfTextのoffset
    private $baseOffsetX = 0;
    private $baseOffsetY = -4;

    /** ダウンロードファイル名 @var unknown */
    private $downloadFileName = null;

    /** 発行日 @var unknown */
    private $issueDate = "";
    
    /** 行数 */
    private $line_number = 0;
    
    /** Y座標(明細部) */
    private $coordinates_y = 96;

    /**
     * Font情報のバックアップ
     */
    protected function backupFont() {
        // フォント情報のバックアップ
        $this->bakFontFamily = $this->FontFamily;
        $this->bakFontStyle = $this->FontStyle;
        $this->bakFontSize = $this->FontSizePt;
    }
    
    /**
     * Font情報の復元
     */
    protected function restoreFont() {
        $this->SetFont($this->bakFontFamily, $this->bakFontStyle, $this->bakFontSize);
    }

    /**
     * コンストラクタ
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->BaseInfo = $app['eccube.repository.base_info']->get();

        parent::__construct();

        // 購入詳細情報の設定を行う
        // 動的に入れ替えることはない
        $this->labelCell[] = '項　目';
        $this->labelCell[] = '数　量';
        $this->labelCell[] = '単　価';
        $this->labelCell[] = '金　額';
        $this->widthCell = array(110.3,12,21.7,24.5);

        // Fontの設定しておかないと文字化けを起こす
         $this->SetFont(self::FONT_SJIS);

        // PDFの余白(上左右)を設定
        $this->SetMargins(15, 20);

        // ヘッダーの出力を無効化
        $this->setPrintHeader(false);

        // フッターの出力を無効化
        $this->setPrintFooter(true);
        $this->setFooterMargin();
        $this->setFooterFont(array(self::FONT_SJIS, '', 8));
    }

    /**
     * 注文情報からPDFファイルを作成する.
     *
     * @param $id 受注ID
     * @return boolean
     */
    public function makePdf($id) {
    
    	//受注データ読み込み
        $this->order_data = $this->app['orm.em']->getRepository('Eccube\Entity\Order')->find($id);
    	
        // データが空であれば終了
        if($this->order_data == null || empty($this->order_data)) {
            return false;
        }
    
        // 有効期間の設定(1ヶ月は30日計算)
        $this->issueDate = '有効期限: ' . $this->app['config']['re_order_limit_day'] . '日';
        
        // ダウンロードファイル名の初期化
        $this->downloadFileName = null;
        
        // テンプレートファイルを読み込む
        $templateFilePath =  __DIR__ . self::PDF_TEMPLATE_FILE_PATH;
        $this->setSourceFile($templateFilePath);

		$this->lastOrderId = $this->order_data->getCustomOrderId();

		$objOrderDetail = $this->order_data->getOrderDetails();
		$order_count  = $objOrderDetail->count();
		$arrOrderDetail = $objOrderDetail->toArray();
		
		$next_page = true;
		$page_count = 1;
		$total_price = 0;
		$flg_total_price = false; //合計金額印字フラグ
		foreach ($arrOrderDetail as $index => $order_detail) {
		
			// 次ページフラグがONの場合のみ
			if ( $next_page ) {
		        // PDFにページを追加する
		        $this->addPdfPage();
		        
		        // ページ番号設定
		        //$this->lfText(180, 10, $this->getPage(), 7, '');
			
		        // タイトルを描画する
		        $this->renderTitle('御 見 積 書');

		        // =========================================
		        // 購入者情報部
		        // =========================================
		        // 郵便番号
		        //$text = '〒 '.$order->getZip01() . ' - ' . $order->getZip02();
		        //$this->lfText(23, 43, $text, 10);

		        // 購入者都道府県+住所1
		        //$text = $order->getPref() . $order->getAddr01();
		        //$this->lfText(27, 47, $text, 10);
		        //$this->lfText(27, 51, $order->getAddr02(), 10); //購入者住所2

				// 購入企業名
				$text = $this->order_data->getCompanyName();
		        $this->lfText(18, 40, $text, 11);
				
		        // 購入者氏名
		        $text = $this->order_data->getName01() . '　' . $this->order_data->getName02() . '　様';
		        $this->lfText(18, 47, $text, 11);

		        // =========================================
		        // 固定文章
		        // =========================================
		        $this->SetFont(self::FONT_SJIS, '', 8);
		        $this->lfText(18, 54, ' 毎度格別のお引き立てを賜り、厚く御礼申し上げます。', 8);
		        $this->lfText(18, 58, '下記のとおり御見積もりいたしました。', 8);
		        $this->lfText(18, 62, ' ご検討のうえ、ご用命賜りますようお願い致します。', 8);

		        // =========================================
		        // 右上表示
		        // =========================================
		        //見積番号
		        $this->lfText(165, 25, 'No. ' . $this->order_data->getCustomOrderId(), 6);
		        //見積作成日
		        $this->lfText(162, 28, $this->order_data->getCreateDate()->format('Y年m月d日'), 8);
		        //キャッチフレーズ
		        $this->lfText(151, 32, '"私たちはお客様に安心品質を', 7, 'I');
		        $this->lfText(158, 35, 'お届けします"', 7, 'I');

		        // =========================================
		        // お買上げ明細ヘッダ
		        // =========================================
		        //品名(固定)
		        $this->lfText(18, 77, '御見積明細', 10, '');
		        
		        //項目名
		        $this->lfText(55, 86.5, '項　　目', 9, '');
		        $this->lfText(120, 86.5, '数　量', 9, '');
		        $this->lfText(146, 86.5, '単　価', 9, '');
		        $this->lfText(172, 86.5, '金　額', 9, '');
				
		        // 店舗情報を描画する
		        $this->renderShopData();

				// 改ページフラグをOFFにする
		        $next_page = false;

				// 合計金額初期化
				$total_price = 0;
				$flg_total_price = false;
			}

	        // 注文情報を描画する
	        $this->renderOrderData($this->order_data, $order_detail);
	        
	        // 金額加算
	        $total_price += $order_detail->getPrice() * $order_detail->getQuantity();
		        
	        // 行数が一定を超えたら改ページ
	        if ( $this->coordinates_y > 200 ) {
	        	$next_page = true;
	        	$page_count++;
	        	$this->coordinates_y = 96;
	        	
	        	$flg_total_price = true;
	        }
	        
	        // 最終ページの場合
			if ( $index == (count($arrOrderDetail) - 1) ) {
	        	$flg_total_price = true;
			}

			// 合計金額印字フラグがONの場合に印字する
			if ( $flg_total_price ) {
	        	//合計金額セット
		    	$this->lfText(173, 243.5, '￥ ' . number_format($total_price), 9, '');
			}
		}

        return true;
    }

    /**
     * PDFファイルを出力する.
     * @return
     */
    public function outputPdf() {
         return $this->Output($this->getPdfFileName(), "S");
    }

    /**
     * PDFファイル名を取得する
     * PDFが1枚の時は注文番号をファイル名につける
     * @return string ファイル名
     */
    public function getPdfFileName() {
        if(!is_null($this->downloadFileName)) {
            return $this->downloadFileName;
        }
        $this->downloadFileName = 'mitsumori-No' . $this->lastOrderId . '.pdf';
        return $this->downloadFileName;
    }

    /**
     * フッターに発行日を出力する
     */
    public function Footer() {
        $this->Cell(0, 0, $this->getPage(), 0, 0, 'C');
        $this->Cell(0, 0, $this->issueDate, 0, 0, 'R');
    }
    /**
     * 作成するPDFのテンプレートファイルを指定する
     */
    protected function addPdfPage() {
        // ページを追加
        $this->AddPage();

        // テンプレートに使うテンプレートファイルのページ番号を取得
        $tplIdx = $this->importPage(1);

        // テンプレートに使うテンプレートファイルのページ番号を指定
        $this->useTemplate($tplIdx, null, null, null, null, true);
    }

    /**
     * PDFに店舗情報を設定する
     * ショップ名、ロゴ画像以外はdtb_helpに登録されたデータを使用する.
     *
     */
    protected function renderShopData() {
        // 基準座標を設定する
        $this->setBasePosition();

        // 特定商取引法を取得する
//        $Help = $this->app['eccube.plugin.order_pdf.repository.order_pdf_help']->get();

        // ショップ名
        //$this->lfText(150, 58, $this->BaseInfo['shop_name'], 8, 'B');

        // URL
        $url  = empty($_SERVER["HTTPS"]) ? "http://" : "https://";
        $url .= $_SERVER["HTTP_HOST"];
        $url .= '/';
        //$this->lfText(150, 61, $url, 8, '');
        
        // 会社名
        $this->lfText(150, 40, $this->BaseInfo['company_name'], 8);
        
        // 郵便番号
        //$text = '〒 ' . $this->BaseInfo['zip01'] . ' - ' . $this->BaseInfo['zip02'];
        //$this->lfText(125, 71, $text, 8);
        // 都道府県+所在地
        $lawPref = is_null($this->BaseInfo['Pref']) ? null : $this->BaseInfo['Pref'];
        $text = $lawPref . $this->BaseInfo['addr01'];
        $this->lfText(151, 44, $text . $this->BaseInfo['addr02'], 7, '');
        //$this->lfText(125, 77, $this->BaseInfo['addr02'], 8);

        // 電話番号
        $text = 'TEL: ' . $this->BaseInfo['tel01'] . '-' . $this->BaseInfo['tel02'] . '-' . $this->BaseInfo['tel03'];
        $this->lfText(151, 50, $text, 8, '');  //TEL

        //FAX番号が存在する場合、表示する
        if (strlen($this->BaseInfo['fax01']) > 0) {
            $text = 'FAX: ' . $this->BaseInfo['fax01'] . '-' . $this->BaseInfo['fax02'] . '-' . $this->BaseInfo['fax03'];
	        $this->lfText(151, 54, $text, 8, '');  //FAX
        }

        // メールアドレス
        if (strlen($this->BaseInfo['email01']) > 0) {
            $text = 'Email: '.$this->BaseInfo['email01'];
            //$this->lfText(125, 83, $text, 8);      //Email
        }
        // ロゴ画像
//        $logoFilePath =  __DIR__ . '/../Resource/template/logo.png';
//        $this->Image($logoFilePath, 124, 46, 40);

    }

    /**
     * メッセージを設定する
     * @param array $formData
     */
    protected function renderMessageData(array $formData) {
        $this->lfText(27, 70, $formData['message1'], 8);  //メッセージ1
        $this->lfText(27, 74, $formData['message2'], 8);  //メッセージ2
        $this->lfText(27, 78, $formData['message3'], 8);  //メッセージ3
    }

    /**
     * PDFに備考を設定数
     * @param array $formData
     */
    protected function renderEtcData(array $formData) {
        // フォント情報のバックアップ
        $this->backupFont();

        $this->Cell(0, 10, '', 0, 1, 'C', 0, '');

        $this->SetFont(self::FONT_GOTHIC, 'B', 9);
        $this->MultiCell(0, 6, '＜ 備考 ＞', 'T', 2, 'L', 0, '');

        $this->SetFont(self::FONT_SJIS, '', 8);

        $this->ln();
        // rtrimを行う
        $text = preg_replace('/\s+$/us', '', $formData['note1'] . "\n" .$formData['note2'] . "\n" . $formData['note3']);
        $this->MultiCell(0, 4, $text, '', 2, 'L', 0, '');

        // フォント情報の復元
        $this->restoreFont();
    }

    /**
     * タイトルをPDFに描画する.
     *
     * @param string $title
     */
    protected function renderTitle($title) {
        // 基準座標を設定する
        $this->setBasePosition();

        // フォント情報のバックアップ
        $this->backupFont();

       //文書タイトル（納品書・請求書）
        $this->SetFont(self::FONT_GOTHIC, '', 18);
        $this->Cell(0, 10, $title, 0, 2, 'C', 0, '');
        $this->Cell(0, 66, '', 0, 2, 'R', 0, '');
        $this->Cell(5, 0, '', 0, 0, 'R', 0, '');

        // フォント情報の復元
        $this->restoreFont();
    }

    /**
     * 注文情報を設定する
     *
     * @param \Eccube\Entity\Order $order
     * @param \Eccube\Entity\OrderDetail $order_detail
     */
    protected function renderOrderData(\Eccube\Entity\Order $order, \Eccube\Entity\OrderDetail $order_detail) {
        // 基準座標を設定する
        $this->setBasePosition();

        // フォント情報のバックアップ
        $this->backupFont();
        
        // =========================================
        // 商品オプション取得(商品オプションプラグインが有効な場合のみ)
        // =========================================
		$arrLabels = array();
        if ( is_object($this->app['eccube.productoption.repository.order_detail']) ) {
			$plgOrderDetail = $this->app['eccube.productoption.repository.order_detail']->findOneBy(array('order_detail_id' => $order_detail->getId()));
			$plgOrderOption = null;
			$serial = null;
			if ( !is_null($plgOrderDetail) ) {
				$plgOrderOption = $plgOrderDetail->getOrderOption();
				$arrLabels = $plgOrderOption->getLabel();
			}
        }
        // =========================================
        // お買い上げ明細部
        // =========================================
        
        //商品名
        $product_name = $order_detail->getProductName();
       	$this->lfText(25, $this->coordinates_y, $product_name, 9, '');

		//明細(全パターン共通)
    	//数量
    	$this->lfText(130, $this->coordinates_y, number_format($order_detail->getQuantity()), 9, '');
    	//単価
    	$this->lfText(152, $this->coordinates_y, number_format($order_detail->getPrice()), 9, '');
    	//金額
    	$this->lfText(176, $this->coordinates_y, number_format($order_detail->getPrice() * $order_detail->getQuantity()), 9, '');

       	$this->coordinates_y += 4;
        
        //明細(商品情報)
        $class_name1 = $order_detail->getClassName1();
        $class_name2 = $order_detail->getClassName2();
        
        //規格有無で表示を分岐
        if ( $class_name1 != '' ) {
        	if ( $class_name2 == '' ) {
	        	$product_class_name1 = $order_detail->getClassCategoryName1();
	        	//印刷物(規格2なし)の場合は規格を出力
	        	$this->lfText(30, $this->coordinates_y, $class_name1 . '：' . $product_class_name1 , 9, '');
		       	$this->coordinates_y += 4;
        	} else {
	        	//印刷物(規格1、規格2ともにあり)の場合は規格を出力
	        	$product_class_name1 = $order_detail->getClassCategoryName1();
	        	$product_class_name2 = $order_detail->getClassCategoryName2();
	        	$this->lfText(30, $this->coordinates_y,  $class_name1 . '：' . $product_class_name1 , 9, '');
		       	$this->coordinates_y += 4;
	        	$this->lfText(30, $this->coordinates_y, $class_name2 . '：' . $product_class_name2 , 9, '');
		       	$this->coordinates_y += 4;
        	}
        }
        
    	//商品オプション
    	foreach($arrLabels as $idx => $label) {
    		//区切り文字を全角にする
    		$label = str_replace(':', '：', $label);
    		if ( mb_strlen($label, 'utf-8') > 40 ) {
    			$length = ceil(mb_strlen($label, 'utf-8') / 40);
    			$arrTmp = array();
    			$start = 0;
    			for($i=0; $i<=$length; $i++) {
    				$arrTmp[] = mb_substr($label, $start, 40, 'utf-8');
    				$start += 40;
    			}
    			foreach($arrTmp as $tmp) {
    				if ( $tmp == '' ) {
    					continue;
    				}
		        	$this->lfText(30, $this->coordinates_y,  $tmp . '[' . $this->coordinates_y . ']', 9, '');
			       	$this->coordinates_y += 4;
    			}
    			//$label = mb_strcut($label, 0, 50, 'utf-8') . '...';
    		} else {
	        	$this->lfText(30, $this->coordinates_y,  $label . '[' . $this->coordinates_y . ']' , 9, '');
		       	$this->coordinates_y += 4;
    		}
    	}

        // フォント情報の復元
        $this->restoreFont();

		// 間隔調整
	    $this->coordinates_y += 10;
    }

    /**
     * 購入商品詳細情報を設定する
     *
     * @param \Plugin\OrderPdf\Entity\OrderPdfOrder $order
     */
    protected function renderOrderDetailData($order) {
        $arrOrder = array();
        // テーブルの微調整を行うための購入商品詳細情報をarrayに変換する

        // =========================================
        // 受注詳細情報
        // =========================================
        $i = 0;
        foreach ($order->getOrderDetails() as $orderDetail) {
            // class categoryの生成
             $classcategory = "";
             if ($orderDetail->getClassCategoryName1()) {
                 $classcategory .= ' [ ' . $orderDetail->getClassCategoryName1();
                 if ($orderDetail->getClassCategoryName2() == '') {
                     $classcategory .= ' ]';
                 } else {
                     $classcategory .= ' * ' . $orderDetail->getClassCategoryName2() . ' ]';
                 }
             }

             // 税
             $tax = $this->app['eccube.service.tax_rule']
                 ->calcTax($orderDetail->getPrice(), $orderDetail->getTaxRate(), $orderDetail->getTaxRule());
             $orderDetail->setPriceIncTax($orderDetail->getPrice() + $tax);

             // product
             $arrOrder[$i][0] = sprintf('%s / %s / %s', $orderDetail->getProductName(), $orderDetail->getProductCode(), $classcategory);;
             // 購入数量
             $arrOrder[$i][1] = number_format($orderDetail->getQuantity());
             // 税込金額（単価）
             $arrOrder[$i][2] = number_format($orderDetail->getPriceIncTax()) . self::MONETARY_UNIT;
             // 小計（商品毎）
             $arrOrder[$i][3] = number_format($orderDetail->getTotalPrice()) . self::MONETARY_UNIT;

             $i++;
        }

        // =========================================
        // 小計
        // =========================================
        $arrOrder[$i][0] = '';
        $arrOrder[$i][1] = '';
        $arrOrder[$i][2] = '';
        $arrOrder[$i][3] = '';

        $i++;
        $arrOrder[$i][0] = '';
        $arrOrder[$i][1] = '';
        $arrOrder[$i][2] = '商品合計';
        $arrOrder[$i][3] = number_format($order->getSubtotal()) . self::MONETARY_UNIT;

        $i++;
        $arrOrder[$i][0] = '';
        $arrOrder[$i][1] = '';
        $arrOrder[$i][2] = '送料';
        $arrOrder[$i][3] = number_format($order->getDeliveryFeeTotal()) . self::MONETARY_UNIT;

        $i++;
        $arrOrder[$i][0] = '';
        $arrOrder[$i][1] = '';
        $arrOrder[$i][2] = '手数料';
        $arrOrder[$i][3] = number_format($order->getCharge()) . self::MONETARY_UNIT;

        $i++;
        $arrOrder[$i][0] = '';
        $arrOrder[$i][1] = '';
        $arrOrder[$i][2] = '値引き';
        $arrOrder[$i][3] = '- '.number_format($order->getDiscount()) . self::MONETARY_UNIT;

        $i++;
        $arrOrder[$i][0] = '';
        $arrOrder[$i][1] = '';
        $arrOrder[$i][2] = '請求金額';
        $arrOrder[$i][3] = number_format($order->getPaymentTotal()).self::MONETARY_UNIT;

        // PDFに設定する
        $this->FancyTable($this->labelCell, $arrOrder, $this->widthCell);


    }

    /**
     * PDFへのテキスト書き込み
     *
     * @param unknown $x X座標
     * @param unknown $y Y座標
     * @param unknown $text テキスト
     * @param number $size フォントサイズ
     * @param string $style フォントスタイル
     */
    protected function lfText($x, $y, $text, $size = 0, $style = '')
    {
        // 退避
        $bak_font_style = $this->FontStyle;
        $bak_font_size = $this->FontSizePt;

        $this->SetFont('', $style, $size);
        $this->Text($x + $this->baseOffsetX, $y + $this->baseOffsetY, $text);

        // 復元
        $this->SetFont('', $bak_font_style, $bak_font_size);
    }

    /**
     * Colored table
     *
     * FIXME: 後の列の高さが大きい場合、表示が乱れる。
     *
     * @param unknown $header 出力するラベル名一覧
     * @param unknown $data 出力するデータ
     * @param unknown $w 出力するセル幅一覧
     */
    protected function FancyTable($header, $data, $w) {
        // フォント情報のバックアップ
        $this->backupFont();

        // 開始座標の設定
         $this->setBasePosition(0,149);

        // Colors, line width and bold font
        $this->SetFillColor(216, 216, 216);
        $this->SetTextColor(0);
        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(.3);
        $this->SetFont(self::FONT_SJIS, 'B', 8);
        $this->SetFont('', 'B');

        // Header
        $this->Cell(5, 7, '', 0, 0, '', 0, '');
        for ($i = 0; $i < count($header); $i++) {
            $this->Cell($w[$i], 7, $header[$i], 1, 0, 'C', 1);
        }
        $this->Ln();

        // Color and font restoration
        $this->SetFillColor(235, 235, 235);
        $this->SetTextColor(0);
        $this->SetFont('');
        // Data
        $fill = 0;
        $h = 4;
        foreach ($data as $row) {
            // 行のの処理
            $i = 0;
            $h = 4;
            $this->Cell(5, $h, '', 0, 0, '', 0, '');

            // Cellの高さを保持
            $cellHeight = 0;
            foreach ($row as $col) {
                // 列の処理
                // FIXME 汎用的ではない処理。この指定は呼び出し元で行うようにしたい。
                // テキストの整列を指定する
                $align = ($i == 0) ? 'L' : 'R';

                // セル高さが最大値を保持する
                if ($h >= $cellHeight) {
                    $cellHeight = $h;
                }

                // 最終列の場合は次の行へ移動
                // (0: 右へ移動(既定)/1: 次の行へ移動/2: 下へ移動)
                $ln = ($i == (count($row) - 1)) ? 1 : 0;

                $h = $this->MultiCell(
                        $w[$i],             // セル幅
                        $cellHeight,        // セルの最小の高さ
                        $col,               // 文字列
                        1,                  // 境界線の描画方法を指定
                        $align,             // テキストの整列
                        $fill,              // 背景の塗つぶし指定
                        $ln                 // 出力後のカーソルの移動方法
                     );
                $h = $this->getLastH();

                $i++;
            }
            $fill = !$fill;
        }
        $this->Cell(5, $h, '', 0, 0, '', 0, '');
        $this->Cell(array_sum($w), 0, '', 'T');
        $this->SetFillColor(255);

        // フォント情報の復元
        $this->restoreFont();
    }

    /**
     * 基準座標を設定する
     * @param unknown $x
     * @param unknown $y
     */
    protected function setBasePosition($x = null, $y = null) {
        // 現在のマージンを取得する
        $result = $this->getMargins();

        // 基準座標を指定する
        $this->setX(is_null($x) ? $result['left'] : $x);
        $this->setY(is_null($y) ? $result['top']: $y);
    }

    /**
     * データが設定されていない場合にデフォルト値を設定する.
     *
     * @param unknown $formData
     */
    protected function setDefaultData(&$formData) {

        $defaultList = array(
            'title' => 'お買上げ明細書(納品書)',
            'message1' => 'このたびはお買上げいただきありがとうございます。',
            'message2' => '下記の内容にて納品させていただきます。',
            'message3' => 'ご確認くださいますよう、お願いいたします。',
        );

        foreach($defaultList as $key => $value) {
            if(is_null($formData[$key])) {
                $formData[$key] = $value;
            }
        }
    }

}
