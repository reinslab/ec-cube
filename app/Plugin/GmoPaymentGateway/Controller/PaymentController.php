<?php
/*
 * Copyright(c) 2015 GMO Payment Gateway, Inc. All rights reserved.
 * http://www.gmo-pg.com/
 */

namespace Plugin\GmoPaymentGateway\Controller;

use Eccube\Application;
use Plugin\GmoPaymentGateway\Controller\Util\PaymentUtil;
use Plugin\GmoPaymentGateway\Service\client\PG_MULPAY_Client_Member;
use Plugin\GmoPaymentGateway\Controller\Util\PluginUtil;
use Symfony\Component\HttpFoundation\Request;
use Plugin\GmoPaymentGateway\Form\Type\RegistCreditSelectType;
use Plugin\GmoPaymentGateway\Form\Type\PaymentType;
use Eccube\Common\Constant;
class PaymentController
{
    public $app;
    public $dataReturn = array();
    public function index(\Eccube\Application $app)
    {
        $this->app = $app;

        $Order = $app['eccube.repository.order']->findOneBy(array('pre_order_id' => $app['eccube.service.cart']->getPreOrderId()));     
        $objMdl =& PluginUtil::getInstance($this->app);
        $gmoSetting = $objMdl->getUserSettings();
       
        if (is_null($Order)) {
            $error_title = 'エラー';
            $error_message = "注文情報の取得が出来ませんでした。この手続きは無効となりました。";
            return $app['view']->render('error.twig', array('error_message' => $error_message, 'error_title'=> $error_title));
        }
        
        // 商品公開ステータスチェック、商品制限数チェック、在庫チェック
        if (!$this->checkStockProduct($app, $Order)) {
            $app->addError('front.shopping.stock.error');
            return $app->redirect($app->url('shopping_error'));
        }
        
        if (empty($gmoSetting['server_url'])) {
            $error_title = 'エラー';
            $error_message = " 接続先サーバーURLが設定されていません。";
            return $app['view']->render('error.twig', array('error_message' => $error_message, 'error_title'=> $error_title));
        }
        $objUtil = new PaymentUtil($app);
        $PaymentExtension = $objUtil->getPaymentTypeConfig($Order->getPayment()->getId());
        $paymentCode = $PaymentExtension->getPaymentCode();
        $paymentInfo = $PaymentExtension->getArrPaymentConfig();
        
        if (empty($paymentInfo)) {
            $paymentInfo = array();
            $paymentInfo['use_securitycd'] = null;
            $paymentInfo['enable_customer_regist'] = false;
            $paymentInfo['credit_pay_methods'] = array();
            $paymentInfo['conveni'] = array();
        }

        $OrderExtension = new \Plugin\GmoPaymentGateway\Controller\DataObj\OrderExtension();
        $OrderExtension->setOrder($Order);
        $OrderExtension->setPaymentData($paymentInfo);
        switch ($paymentCode) {
            case $app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYCODE_CVS']:
                return $this->cvsProcess($Order, $paymentInfo);
                break;

            case $app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYCODE_ATM']:
                return $this->atmProcess($Order);
                break;

            case $app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYCODE_PAYEASY']:
                return $this->payeasyProcess($Order);
                break;

            case $app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYCODE_CREDIT']:
                return $this->creditProcess($Order, $paymentInfo, $OrderExtension);
                break;
            case $app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYCODE_RAKUTEN_ID']:
                return $this->rakutenProcess($Order, $paymentInfo);
                break;
            case $app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYCODE_TOKEN']:
                return $this->tokenProcess($Order, $paymentInfo);
                break;
            case $app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYCODE_REGIST_CREDIT']:
                return $this->registCreditProcess($Order, $paymentCode, $paymentInfo, $OrderExtension);
                break;
            default:
                break;
        }
    }
    
    public function rakutenResult(\Eccube\Application $app, $result) {
        $this->app = $app;
        
        // If result = success
        if ($result == 1){
            $Order = null;
            
            if (isset($_REQUEST['OrderID'])) {
                list($orderId, $dummy) = explode('-', $_REQUEST['OrderID']);
                $Order = $app['eccube.repository.order']->findOneBy(array('id' => $orderId));
            }
            
            if (is_null($Order)) {
                $error_title = 'エラー';
                $error_message = "注文情報の取得が出来ませんでした。この手続きは無効となりました。";
                return $app['view']->render('error.twig', array('error_message' => $error_message, 'error_title'=> $error_title));
            }
            
            $app['eccube.service.cart']->clear()->save();

            // Complete order
            $this->changeOrderData($Order);

            $this->sendOrderMail($Order);
            $this->app['session']->set('eccube.plugin.gmo_pg.orderId', $orderId);
            $this->app['session']->set('eccube.front.shopping.order.id', $orderId);// 本体の完了画面に受注IDを引き継ぐ

            return $app->redirect($app['url_generator']->generate('shopping_complete'));        
            
        } else { // result = failure
            
            $tpl_title = '楽天ID決済';
            $error = array();
            $redirectUrl = '';
            $re_submit = false;
            $tpl_is_loding = false;
            $rakutenRequest = false;
            $rakutenData['AccessID'] = '';
            $rakutenData['Token'] = '';
            $form = $app['form.factory']
            ->createBuilder('gmo_payment')
            ->add('mode', 'hidden')
            ->getForm();
            
            $errCode = $_REQUEST['ErrCode'];
            if (!empty($errCode) && isset($_REQUEST['ErrInfo'])){
                $errCode .=  '-';
            }
            
            $error[] = $errCode . $_REQUEST['ErrInfo'];
            
            return $app->render('GmoPaymentGateway/View/pg_mulpay_rakuten_id.twig', array(
            'form' => $form->createView(),
            'tpl_title' => $tpl_title,
            'error' => $error,
            're_submit' => $re_submit,
            'redirect_url' =>$redirectUrl,
            'tpl_is_loding' =>$tpl_is_loding,
            'rakutenRequest'=> $rakutenRequest,
            'rakutenData' => $rakutenData,
        ));
        }
        
    }
    
    public function tokenProcess($Order, $paymentInfo) 
    {
        $objUtil = new PaymentUtil($this->app);
        $form = $this->createCreditForm();
        // Default data
        $tpl_is_loding = false;
        $do_request = false;
        $tpl_pg_regist_card_form = false;
        $dataReturn = $this->initData();
        $dataReturn['next_action'] = "";

        $this->isComplete = false;
        $error = array();
        $error['payment'] = "";

        // $paymentInfo['enable_customer_regist'] = $objUtil->isRegistCardPaymentEnable();

        $objPluginutil =& PluginUtil::getInstance($this->app);
        $setting = $objPluginutil->getSubData();
        $userSetting = $setting['user_settings'];

        $server_url = $userSetting['server_url'];
        preg_match('@^(.*://[^/]+)(.*)$@i', $server_url, $matches);
        $js_urlpath = $matches[1];

        $tshop = $userSetting['ShopID'];

        if ('POST' === $this->app['request']->getMethod()) {
            $mode = $this->app['request']->request->get("mode");
            $MD = $this->app['request']->request->get("MD");
            if(!empty($_POST['mode']))
            {
                $mode = $_POST['mode'];
            }
            if ("next" == $mode) {
                return $this->tokenCommit($Order, $tshop, $js_urlpath);
            } elseif (!empty($MD)) {
                return $this->tokenSecureTran($Order, $tshop, $js_urlpath);
            }
        }


        return $this->app['view']->render('GmoPaymentGateway/View/pg_mulpay_token.twig', array(
            'form' => $form->createView(),
            'dataReturn' => $dataReturn,
            'error' => $error,
            'paymentInfo' => $paymentInfo,
            'title' => $Order->getPaymentMethod(),
            'tpl_is_loding' => $tpl_is_loding,
            'do_request' => $do_request,
            'tpl_pg_regist_card_form' => $tpl_pg_regist_card_form,
            'tshop' => $tshop,
            'js_urlpath' => $js_urlpath,
        ));    
    }
    
    public function tokenCommit($Order, $tshop, $js_urlpath)
    {
        $error = array();
        $error['payment'] = '';
        $tpl_pg_regist_card_form = false;
        $tpl_is_loding = true;
        $do_request = true;
        $dataReturn = $this->initData();
        $dataReturn['tpl_url'] = $this->curPageURL();
        $dataReturn['next_action'] = "";

        $objUtil = new PaymentUtil($this->app);
        $PaymentExtension = $objUtil->getPaymentTypeConfig($Order->getPayment()->getId());
        $paymentCode = $PaymentExtension->getPaymentCode();
        $objClientMember = new PG_MULPAY_Client_Member($this->app);
        $paymentInfo = $this->getPaymentInfo($PaymentExtension, $this->app);

        $form = $this->createCreditForm();

        $form->handleRequest($this->app['request']);
        
        $objPageHelper = $this->prepareOrderData($Order, $this->app, $paymentCode, $PaymentExtension);
        
        $formData = $_POST;
        $mode = $formData['mode'];

        $objPageHelper->modeAction($mode, $formData, $Order, $PaymentExtension, $this->app);

        if ($objPageHelper->isComplete) {
            $orderId = $objPageHelper->orderId;
            $order = $this->app['eccube.repository.order']->findOneBy(array('id' => $orderId));

            $this->changeOrderData($Order);

            // メール送信
            $this->sendOrderMail($order);
            $this->app['session']->set('eccube.plugin.gmo_pg.orderId', $orderId);
            $this->app['session']->set('eccube.front.shopping.order.id', $orderId);// 本体の完了画面に受注IDを引き継ぐ

            return $this->app->redirect($this->app['url_generator']->generate('shopping_complete'));
        }
        $dataReturn = $objPageHelper->dataReturn;

        if (isset($dataReturn['arrTdData']['ACS']) && $dataReturn['arrTdData']['ACS'] == 1) {
            $tempUrl = $this->curPageURL();
            $dataReturn['arrTdData']['TermUrl'] = $tempUrl;
            $dataReturn['next_action'] = $dataReturn['arrTdData']['ACSUrl'];
            $this->app['session']->set('MD', $dataReturn['arrTdData']['MD']);
            $this->app['session']->set('PaReq', $objPageHelper->dataReturn['arrTdData']['PaReq']);
        }
        $error = $objPageHelper->error;

        if (empty($error)) {
            $tpl_is_loding = true;
            $do_request = true;
        } else {
            $tpl_is_loding = false;
            $do_request = false;
        }
        if (!isset($error["payment"])) $error["payment"] = "";

        if (!isset($dataReturn['next_action'])) {
            $dataReturn['next_action'] = "";
        }

        return $this->app['view']->render('GmoPaymentGateway/View/pg_mulpay_token.twig', array(
            'form' => $form->createView(),
            'dataReturn' => $dataReturn,
            'error' => $error,
            'paymentInfo' => $paymentInfo,
            'title' => $Order->getPaymentMethod(),
            'tpl_is_loding' => $tpl_is_loding,
            'do_request' => $do_request,
            'tpl_pg_regist_card_form' => $tpl_pg_regist_card_form,
            'tshop' => $tshop,
            'js_urlpath' => $js_urlpath,
        ));
    }

    public function tokenSecureTran($Order, $tshop, $js_urlpath)
    {
        $this->app['session']->set('PaRes', $this->app['request']->request->get('PaRes'));
        $error = array();
        $error['payment'] = '';
        $tpl_is_loding = false;
        $do_request = true;

        $objUtil = new PaymentUtil($this->app);
        $PaymentExtension = $objUtil->getPaymentTypeConfig($Order->getPayment()->getId());
        $paymentCode = $PaymentExtension->getPaymentCode();
        $paymentInfo = $PaymentExtension->getArrPaymentConfig();
        $form = $this->createCreditForm();
        if (empty($paymentInfo)) {
            $paymentInfo = array();
            $paymentInfo['use_securitycd'] = null;
            $paymentInfo['enable_customer_regist'] = false;
            $paymentInfo['credit_pay_methods'] = array();
        }
        $paymentInfo['enable_customer_regist'] = false;

        $form->handleRequest($this->app['request']);
        $formData = $form->getData();
        $objPageHelper = $this->prepareOrderData($Order, $this->app, $paymentCode, $PaymentExtension);
        $formData = array_merge((array)$formData, $this->app['request']->request->all());
        $objPageHelper->modeAction('tokenSecureTran', $formData, $Order, $PaymentExtension, $this->app);
        
        if ($objPageHelper->isComplete) {
            // Remove session before complete
            $this->app['session']->set('PaReq', null);
            $this->app['session']->set('PaRes', null);
            $this->app['session']->set('MD', null);
            $orderId = $objPageHelper->orderId;
            $order = $this->app['eccube.repository.order']->findOneBy(array('id' => $orderId));

            $this->changeOrderData($Order);

            // メール送信
            $this->sendOrderMail($order);
            $this->app['session']->set('eccube.plugin.gmo_pg.orderId', $orderId);
            $this->app['session']->set('eccube.front.shopping.order.id', $orderId);// 本体の完了画面に受注IDを引き継ぐ

            return $this->app->redirect($this->app['url_generator']->generate('shopping_complete'));
        }
        $dataReturn = $objPageHelper->dataReturn;
        $error = $objPageHelper->error;
        if (!empty($error)) {
            $do_request = false;
        }

        if (!isset($dataReturn['next_action'])) {
            $dataReturn['next_action'] = "";
        }

        return $this->app['view']->render('GmoPaymentGateway/View/pg_mulpay_token.twig', array(
            'form' => $form->createView(),
            'dataReturn' => $dataReturn,
            'error' => $error,
            'paymentInfo' => $paymentInfo,
            'title' => $Order->getPaymentMethod(),
            'tpl_is_loding' => $tpl_is_loding,
            'do_request' => $do_request,
            'tshop' => $tshop,
            'js_urlpath' => $js_urlpath,
        ));
    }


    public function rakutenProcess($Order, $paymentInfo) {
        $tpl_title = '楽天ID決済';
        $error = array();
        $mode = '';
        $redirectUrl = '';
        $re_submit = true;
        $tpl_is_loding = true;
        $rakutenRequest = false;
        $rakutenData['AccessID'] = '';
        $rakutenData['Token'] = '';
        $objUtil = new PaymentUtil($this->app);
        $PaymentExtension = $objUtil->getPaymentTypeConfig($Order->getPayment()->getId());
        $paymentCode = $PaymentExtension->getPaymentCode();
        $objPageHelper = $this->prepareOrderData($Order, $this->app, $paymentCode, $PaymentExtension);
        $form = $this->app['form.factory']
            ->createBuilder('gmo_payment')
            ->add('mode', 'hidden')
            ->getForm();

        if ('POST' === $this->app['request']->getMethod()) {
            $form->handleRequest($this->app['request']);
            $data = $form->getData();
            $mode = $data['mode'];
            $formData = array();
            $ret = $objPageHelper->modeAction($mode, $formData, $Order, $PaymentExtension, $this->app);
            if ($ret) {
                $results = $objPageHelper->getResults();
                $redirectUrl = $results['StartURL'];
                $rakutenData['AccessID'] = $results['AccessID'];
                $rakutenData['Token'] = $results['Token'];
                $re_submit = true;
                $rakutenRequest = true;
            } else {
                $error = $objPageHelper->getErrors();
                if ($error) {
                    $tpl_is_loding = false;
                    $re_submit = false;
                }
            }
        }

        return $this->app['view']->render('GmoPaymentGateway/View/pg_mulpay_rakuten_id.twig', array(
            'form' => $form->createView(),
            'tpl_title' => $tpl_title,
            'error' => $error,
            're_submit' => $re_submit,
            'redirect_url' =>$redirectUrl,
            'tpl_is_loding' =>$tpl_is_loding,
            'rakutenRequest'=> $rakutenRequest,
            'rakutenData' => $rakutenData,
        ));
    }

    
    public function cvsProcess($Order, $paymentInfo)
    {
        $objUtil = new PaymentUtil($this->app);
        $conveniStores = $objUtil->getConveni();
        $do_request = false;
        $tpl_is_loding = false;
        $error = array();
        $error['payment'] = '';
        $error['conveni'] = '';
        $dataReturn = array();

        if ('POST' === $this->app['request']->getMethod()) {
            return $this->cvsCommit($Order);
        }

        return $this->app['view']->render('GmoPaymentGateway/View/pg_mulpay_cvs.twig',
            array(
                'conveniStores' => $conveniStores,
                'dataReturn' => $dataReturn,
                'error' => $error,
                'paymentInfo' => $paymentInfo,
                'title' => $Order->getPaymentMethod(),
                'tpl_is_loding' => $tpl_is_loding,
                'do_request' => $do_request,
            ));
    }

    /**
     * Process payment for RegistCredit
     *
     * @param Object $Order
     * @param array $paymentCode
     * @param object $PaymentExtension
     * @return view
     */
    public function registCreditCommit($Order, $paymentCode, $PaymentExtension)
    {
        $error['Payment'] = '';
        $error['CardSeq'] = '';
        $response = '';
        $formData = $this->app['request']->request->all();
        if (!isset($formData['CardSeq']) || !isset($formData['gmo_regist_credit']['method'])) {
            $error['payment'] = '※ お支払いカード登録番号が入力されていません。';
        } else {
            $formData['Method'] = $formData['gmo_regist_credit']['method'];
            $objPageHelper = $this->prepareOrderData($Order, $this->app, $paymentCode, $PaymentExtension);
            $mode = $this->app['request']->attributes->get('mode');
            if ($mode == '') {
                $mode = 'next';
            }
            $objPageHelper->modeAction($mode, $formData, $Order, $PaymentExtension, $this->app);
            if ($objPageHelper->isComplete) {
                $orderId = $objPageHelper->orderId;
                // メール送信
                $order = $this->app['eccube.repository.order']->findOneBy(array('id' => $orderId));

                $this->changeOrderData($Order);

                $this->sendOrderMail($order);
                $this->app['session']->set('eccube.plugin.gmo_pg.orderId', $orderId);
                $this->app['session']->set('eccube.front.shopping.order.id', $orderId);// 本体の完了画面に受注IDを引き継ぐ

                $response = $this->app->url('shopping_complete');
                return array($error, $response);
            }
            $this->dataReturn = $objPageHelper->dataReturn;
            // Return error if have. else return result to execute 3d request
            $error = $objPageHelper->error;
            if (isset($this->dataReturn['arrTdData']['ACS']) && $this->dataReturn['arrTdData']['ACS'] == 1) {
                $tempUrl = $this->curPageURL();
                $this->dataReturn['arrTdData']['TermUrl'] = $tempUrl;
                $this->dataReturn['next_action'] = $objPageHelper->dataReturn['arrTdData']['ACSUrl'];
                $this->app['session']->set('MD', $objPageHelper->dataReturn['arrTdData']['MD']);
                $this->app['session']->set('PaReq', $objPageHelper->dataReturn['arrTdData']['PaReq']);
            } else {
                $dataReturn = $this->initParamCreditRegist();
                $dataReturn['next_action'] = "";
                $this->dataReturn = $dataReturn;
            }
        }
        return array($error, $response);
    }
    
    /**
     * CSV commit
     *
     * @param Application $this ->app
     * @param Request $request
     * @return type
     */
    public function cvsCommit($Order)
    {
        $tpl_is_loding = true;
        $do_request = false;
        $dataReturn['tpl_is_td_tran'] = false;
        $dataReturn['tpl_btn_next'] = false;
        $dataReturn['tpl_payment_onload'] = '';
        $error = array();
        $error['payment'] = '';

        $objUtil = new PaymentUtil($this->app);
        $PaymentExtension = $objUtil->getPaymentTypeConfig($Order->getPayment()->getId());
        $paymentCode = $PaymentExtension->getPaymentCode();
        $paymentInfo = $PaymentExtension->getArrPaymentConfig();
        if (empty($paymentInfo)) {
            $paymentInfo = array();
            $paymentInfo['use_securitycd'] = null;
            $paymentInfo['enable_customer_regist'] = false;
            $paymentInfo['credit_pay_methods'] = array();
            $paymentInfo['conveni'] = array();
        }
        $conveniStores = $objUtil->getConveni();

        $conv = $this->app['request']->request->get('Convenience');
        if (isset($conv)) {
            $formData = $this->app['request']->request->all();
            $tpl_is_loding = false;
            $do_request = true;

            $objPageHelper = $this->prepareOrderData($Order, $this->app, $paymentCode, $PaymentExtension);
            $mode = $this->app['request']->attributes->get('mode');
            if ($mode == '') {
                $mode = 'next';
            }
            $objPageHelper->modeAction($mode, $formData, $Order, $PaymentExtension, $this->app);
            if ($objPageHelper->isComplete) {
                $orderId = $objPageHelper->orderId;
                // メール送信
                $order = $this->app['eccube.repository.order']->findOneBy(array('id' => $orderId));

                $this->changeOrderData($Order);

                $this->sendOrderMail($order);
                $this->app['session']->set('eccube.plugin.gmo_pg.orderId', $orderId);
                $this->app['session']->set('eccube.front.shopping.order.id', $orderId);// 本体の完了画面に受注IDを引き継ぐ

                return $this->app->redirect($this->app['url_generator']->generate('shopping_complete'));
            }
            $error = $objPageHelper->error;
            $tpl_is_loding = false;
        } else {
            $error['payment'] = '※ コンビニ選択が入力されていません。';
            $error['conveni'] = '';
            $tpl_is_loding = false;
        }

        return $this->app['view']->render('GmoPaymentGateway/View/pg_mulpay_cvs.twig',
            array(
                'conveniStores' => $conveniStores,
                'error' => $error,
                'paymentInfo' => $paymentInfo,
                'title' => $Order->getPaymentMethod(),
                'tpl_is_loding' => $tpl_is_loding,
                'do_request' => $do_request,
            ));
    }

    /**
     * ATM Index
     *
     * @param $Order
     * @return type
     */
    public function atmProcess($Order)
    {
        $tpl_is_loding = true;
        $tpl_title = 'Pay-easy決済(銀行ATM)';
        $tpl_is_select_page_call = false;
        $error = array();
        $mode = '';
        $re_submit = true;
        $objUtil = new PaymentUtil($this->app);
        $PaymentExtension = $objUtil->getPaymentTypeConfig($Order->getPayment()->getId());
        $paymentCode = $PaymentExtension->getPaymentCode();
        $paymentInfo = $PaymentExtension->getArrPaymentConfig();
        if (empty($paymentInfo)) {
            $paymentInfo = array();
            $paymentInfo['use_securitycd'] = null;
            $paymentInfo['enable_customer_regist'] = false;
            $paymentInfo['credit_pay_methods'] = array();
        }
        $objPageHelper = $this->prepareOrderData($Order, $this->app, $paymentCode, $PaymentExtension);
        $form = $this->app['form.factory']
            ->createBuilder('gmo_payment')
            ->add('mode', 'hidden')
            ->getForm();

        if ('POST' === $this->app['request']->getMethod()) {
            $form->handleRequest($this->app['request']);
            $data = $form->getData();
            $mode = $data['mode'];

            $formData = array();

            $ret = $objPageHelper->modeAction($mode, $formData, $Order, $PaymentExtension, $this->app);

            if ($ret) {
                $orderId = $objPageHelper->orderId;
                $order = $this->app['eccube.repository.order']->findOneBy(array('id' => $orderId));

                $this->changeOrderData($Order);

                // メール送信
                $this->sendOrderMail($order);
                $this->app['session']->set('eccube.plugin.gmo_pg.orderId', $orderId);
                $this->app['session']->set('eccube.front.shopping.order.id', $orderId);// 本体の完了画面に受注IDを引き継ぐ

                return $this->app->redirect($this->app['url_generator']->generate('shopping_complete'));
            } else {
                if ($mode === 'return') {
                    return $this->app->redirect($this->app['url_generator']->generate('shopping'));
                }
                $error = $objPageHelper->getErrors();
                if ($error) {
                    $tpl_is_loding = false;
                    $re_submit = false;
                }
            }
        }

        return $this->app['view']->render('GmoPaymentGateway/View/pg_mulpay_atm.twig', array(
            'form' => $form->createView(),
            'tpl_title' => $tpl_title,
            'tpl_is_loding' => $tpl_is_loding,
            'tpl_is_select_page_call' => $tpl_is_select_page_call,
            'error' => $error,
            're_submit' => $re_submit
        ));
    }

    /**
     * Pay easy commit
     *
     * @param Application $app
     * @return type
     */
    public function payeasyProcess($Order)
    {
        $tpl_is_loding = true;
        $tpl_title = 'Pay-easy決済(ネットバンク)';
        $tpl_is_select_page_call = false;
        $error = array();
        $mode = '';
        $re_submit = true;
        $waitingTime = 0;
        $encryptedReceiptNumber = '';
        $redirectUrl = '';
        $objUtil = new PaymentUtil($this->app);
        $PaymentExtension = $objUtil->getPaymentTypeConfig($Order->getPayment()->getId());
        $paymentCode = $PaymentExtension->getPaymentCode();
        $paymentInfo = $PaymentExtension->getArrPaymentConfig();
        if (empty($paymentInfo)) {
            $paymentInfo = array();
            $paymentInfo['use_securitycd'] = null;
            $paymentInfo['enable_customer_regist'] = false;
            $paymentInfo['credit_pay_methods'] = array();
        }

        $objPageHelper = $this->prepareOrderData($Order, $this->app, $paymentCode, $PaymentExtension);
        $form = $this->app['form.factory']
            ->createBuilder('gmo_payment')
            ->add('mode', 'hidden')
            ->getForm();

        if ('POST' === $this->app['request']->getMethod()) {
            $form->handleRequest($this->app['request']);
            $data = $form->getData();
            $mode = $data['mode'];

            $formData = array();

            $ret = $objPageHelper->modeAction($mode, $formData, $Order, $PaymentExtension, $this->app);

            if ($ret) {
                if (!empty($paymentInfo['SelectPageCall_PC'])) {
                    $tpl_is_select_page_call = true;
                    $encryptedReceiptNumber = $objPageHelper->getReceiptNo();
                    $redirectUrl = $paymentInfo['SelectPageCall_PC'];
                    $waitingTime = 20000;
                } else {
                    $orderId = $objPageHelper->orderId;
                    $order = $this->app['eccube.repository.order']->findOneBy(array('id' => $orderId));

                    $this->changeOrderData($Order);

                    // メール送信
                    $this->sendOrderMail($order);
                    $this->app['session']->set('eccube.plugin.gmo_pg.orderId', $orderId);
                    $this->app['session']->set('eccube.front.shopping.order.id', $orderId);// 本体の完了画面に受注IDを引き継ぐ

                    return $this->app->redirect($this->app['url_generator']->generate('shopping_complete'));
                }
            } else {
                if ($mode === 'return') {
                    return $this->app->redirect($this->app['url_generator']->generate('cart'));
                }
                $error = $objPageHelper->getErrors();
                if ($error) {
                    $tpl_is_loding = false;
                    $re_submit = false;
                }
            }
        }

        return $this->app['view']->render('GmoPaymentGateway/View/pg_mulpay_payeasy.twig', array(
            'form' => $form->createView(),
            'tpl_title' => $tpl_title,
            'tpl_is_loding' => $tpl_is_loding,
            'tpl_is_select_page_call' => $tpl_is_select_page_call,
            'error' => $error,
            're_submit' => $re_submit,
            'waiting_time' => $waitingTime,
            'encrypted_receipt_no' => $encryptedReceiptNumber,
            'redirect_url' => $redirectUrl
        ));

    }

    public function registCreditProcess($Order, $paymentCode, $paymentInfo, $OrderExtension)
    {
        $objUtil = new PaymentUtil($this->app);
        $PaymentExtension = $objUtil->getPaymentTypeConfig($Order->getPayment()->getId());
        $form = $this->createCreditRegistForm($this->app, $paymentInfo);
        // Default data
        $tpl_is_loding = false;
        $do_request = false;
        $dataReturn = $this->initParamCreditRegist();
        $dataReturn['next_action'] = "";

        $this->isComplete = false;
        $error = array();
        $error['payment'] = "";
        $error['CardSeq'] = '';
    
        $tpl_plg_target_seq = null;
        $listData = null;
        $this->dataReturn = $dataReturn;
        $objClientMember = new PG_MULPAY_Client_Member($this->app);
        $ret = $objClientMember->searchCard($OrderExtension);
        if (!$ret) {
            $error['payment'] = '※ 登録カードが見つかりませんでした。';
        } else {
            $listCard = $objClientMember->results;
            // Only get card has del_flg = 0
            foreach ($listCard as $item) {
                $item['expire_month'] = substr($item['Expire'], 2);
                $item['expire_year'] = substr($item['Expire'], 0, 2);
                $listData[] = $item;
            }
            foreach ($listData as $data) {
                if (isset($data['DefaultFlag']) && $data['DefaultFlag'] == '1') {
                    $tpl_plg_target_seq = $data['CardSeq'];
                }
            }
            if ($tpl_plg_target_seq === null) {
                $tpl_plg_target_seq = $listData[0]['CardSeq'];
            }
        }
        
        if ('POST' === $this->app['request']->getMethod()) {
            $mode = $this->app['request']->request->get("mode");
            $MD = $this->app['request']->request->get("MD");
            
            if ("next" == $mode) {
                list($error, $response) = $this->registCreditCommit($Order, $paymentCode, $PaymentExtension);
                if (!empty($response)) {
                    return $response;
                }
            } elseif (!empty($MD)) {
                return $this->registSecureTran($Order, $PaymentExtension, $listData, $tpl_plg_target_seq);
            }
        }
        return $this->app['view']->render('GmoPaymentGateway/View/pg_mulpay_regist_credit.twig', array(
            'form' => $form->createView(),
            'dataReturn' => $this->dataReturn,
            'listData' => $listData,
            'error' => $error,
            'paymentInfo' => $paymentInfo,
            'title' => $Order->getPaymentMethod(),
            'tpl_is_loding' => $tpl_is_loding,
            'do_request' => $do_request,
            'tpl_plg_target_seq' => $tpl_plg_target_seq,
        ));
    }
    
    public function creditProcess($Order, $paymentInfo, $OrderExtension)
    {
        $objUtil = new PaymentUtil($this->app);
        $formCredit = $this->createCreditForm();
        $formRegist = $this->createCreditRegistForm($this->app, $paymentInfo);
        // Default data
        $tpl_is_loding = false;
        $do_request = false;
        $tpl_pg_regist_card_form = false;
        $isDisplayRegistCredit = false;
        $listData = array();
        $tpl_plg_target_seq = null;
        $dataReturn = $this->initData();
        $creditReloadFlg = false;
        $this->isComplete = false;
        $error = array();
        $error['payment'] = "";
        $customer = $Order->getCustomer();
        $customerId = null;
        if(!is_null($customer)){
            $customerId = $customer->getId();
        }
        $paymentInfo['enable_customer_regist'] = $objUtil->isRegistCardPaymentEnable();
        if ($paymentInfo['enable_customer_regist'] && !is_null($customerId) && $customerId != 0){
            $tpl_pg_regist_card_form = true;
            $objClientMember = new PG_MULPAY_Client_Member($this->app);
            $OrderExtension = $objUtil->getOrderPayData($Order->getId());
            $ret = $objClientMember->searchCard($OrderExtension, null, null, true);
            if ($ret) {
                $isDisplayRegistCredit = true;
                // Get all card that user saved
                $listCard = $objClientMember->results;
                $is_subs = false;
                $card_seq = null;
                if ($objUtil->isSubscription()) {
                    $is_subs = true;
                    $this->app['eccube.plugin.subs.repository.gmo_subs_order']->setApp($this->app);
                    $result = $this->app['eccube.plugin.subs.repository.gmo_subs_order']->getLatestCardSeq($customerId);
                    if (count($result) > 0) { // Get card_seq of customer that purchased regular order
                        $card_seq = $result[0]['card_seq'];
                    }
                }
                // Only display cards del_flg = 0;
                foreach ((array)$listCard as $item) {
                    if ($item['DeleteFlag'] == 0) {
                        $item['expire_month'] = substr($item['Expire'], 2);
                        $item['expire_year'] = substr($item['Expire'], 0, 2);
                        $listData[] = $item;  
                    }
                }
                if ($is_subs && !is_null($card_seq) && count($listData) > 0) {
                    $copyListData = array();
                    $find_out = false;
                    foreach ((array)$listData as $card) {
                        if ($card['CardSeq'] == $card_seq) { // Filter cards of customer only use card that purchased
                            $copyListData[] = $card;
                            $find_out = true;
                            break;
                        }
                    }
                    if ($find_out) {
                        $listData = $copyListData;
                    }
                }
               
                // Get card lastest selected
                foreach ((array)$listData as $data) {
                    if (isset($data['DefaultFlag']) && $data['DefaultFlag'] == '1') {
                        $tpl_plg_target_seq = $data['CardSeq'];
                    }
                }
                if (count($listData) > 0 && $tpl_plg_target_seq === null) {
                    $tpl_plg_target_seq = $listData[0]['CardSeq'];
                }
                if (count($listData) <= 0) {
                    $isDisplayRegistCredit = false;
                } else {
                    if(count($listData) >= $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_REGIST_CARD_NUM']) {
                        $tpl_pg_regist_card_form = false;
                        $dataReturn['tpl_pg_regist_card_max'] = true;
                    }
                }
            }
        }
        if ('POST' === $this->app['request']->getMethod()) {
            // Prepare data to re-use code:
            $data['tpl_pg_regist_card_max'] = $dataReturn['tpl_pg_regist_card_max'];
            $data['tpl_pg_regist_card_form'] = $tpl_pg_regist_card_form;
            $data['listData'] = $listData;
            $data['tpl_plg_target_seq'] = $tpl_plg_target_seq;
            $data['creditReloadFlg'] = $creditReloadFlg;
            $data['registReloadFlg'] = $creditReloadFlg;
            $data['isDisplayRegistCredit'] = $isDisplayRegistCredit;
            $mode = $this->app['request']->request->get("mode");
            $MD = $this->app['request']->request->get("MD");
          
            if ("next" == $mode) {
                return $this->creditCommit($Order, $data);
            } elseif (!empty($MD)) {
                return $this->secureTran($Order, $data);
            }
        }

        return $this->app['view']->render('GmoPaymentGateway/View/pg_mulpay_credit.twig', array(
            'formCredit' => $formCredit->createView(),
            'formRegist' => $formRegist->createView(),
            'dataReturn' => $dataReturn,
            'error' => $error,
            'paymentInfo' => $paymentInfo,
            'title' => $Order->getPaymentMethod(),
            'tpl_is_loding' => $tpl_is_loding,
            'do_request' => $do_request,
            'tpl_pg_regist_card_form' => $tpl_pg_regist_card_form,
            'listData'=>$listData,
            'tpl_plg_target_seq'=>$tpl_plg_target_seq,
            'creditReloadFlg' => $creditReloadFlg,
            'isDisplayRegistCredit'=> $isDisplayRegistCredit,
        ));
    }
    
    /**
     * Credit commit
     *
     * @param Application $app
     * @param Request $request
     * @return type
     */
    public function creditCommit($Order, $data)
    {
        $error = array();
        $error['payment'] = '';
        $tpl_is_loding = true;
        $do_request = true;
        $dataReturn = $this->initData();
        $dataReturn['tpl_url'] = $this->curPageURL();
        $objUtil = new PaymentUtil($this->app);
        
        $PaymentExtension = $objUtil->getPaymentTypeConfig($Order->getPayment()->getId());
        $paymentCode = $PaymentExtension->getPaymentCode();
        $paymentInfo = $this->getPaymentInfo($PaymentExtension, $this->app);
        $formRegist = $this->createCreditRegistForm($this->app, $paymentInfo);
        $formCredit = $this->createCreditForm();
        // Handle request when submit form
        if (isset($_POST['type_submit']) && $_POST['type_submit'] =='regist') {
            $form = $formRegist->handleRequest($this->app['request']);
            $paymentCode = $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYCODE_REGIST_CREDIT'];
        } else {
            $form = $formCredit->handleRequest($this->app['request']);
        }
        if ($form->isValid()) {
            $formData = $form->getData();
            $objPageHelper = $this->prepareOrderData($Order, $this->app, $paymentCode, $PaymentExtension);
            $mode = $this->app['request']->get('mode');
            if ($mode == '') {
                $mode = 'next';
            }
            $formData = array_merge($formData, $_POST);
            // In case regist credit create key Method base on method key.
            if (isset($_POST['type_submit']) && $_POST['type_submit'] =='regist') {
                $formData['Method'] = $formData['method'];
            }
          
            $objPageHelper->modeAction($mode, $formData, $Order, $PaymentExtension, $this->app);
            if ($objPageHelper->isComplete) {
                $orderId = $objPageHelper->orderId;
                $order = $this->app['eccube.repository.order']->findOneBy(array('id' => $orderId));

                $this->changeOrderData($Order);

                // メール送信
                $this->sendOrderMail($order);
                $this->app['session']->set('eccube.plugin.gmo_pg.orderId', $orderId);
                $this->app['session']->set('eccube.front.shopping.order.id', $orderId);// 本体の完了画面に受注IDを引き継ぐ

                return $this->app->redirect($this->app['url_generator']->generate('shopping_complete'));
            }
            $dataReturn = $objPageHelper->dataReturn;
           
            if (isset($dataReturn['arrTdData']['ACS']) && $dataReturn['arrTdData']['ACS'] == 1) {
                // In case regist credit create key Method base on method key.
                if (isset($_POST['type_submit']) && $_POST['type_submit'] =='regist') {
                    $data['registReloadFlg'] = true;
                } else {
                    $data['creditReloadFlg'] = true;
                }
                $tempUrl = $this->curPageURL();
                $dataReturn['arrTdData']['TermUrl'] = $tempUrl;
                $dataReturn['next_action'] = $dataReturn['arrTdData']['ACSUrl'];
                $this->app['session']->set('MD', $dataReturn['arrTdData']['MD']);
                $this->app['session']->set('PaReq', $objPageHelper->dataReturn['arrTdData']['PaReq']);
            }
            $error = $objPageHelper->error;

            if (empty($error)) {
                $tpl_is_loding = true;
                $do_request = true;
            } else {
                $tpl_is_loding = false;
                $do_request = false;
            }
            if (!isset($error["payment"])) $error["payment"] = "";
        } else {
            $tpl_is_loding = false;
            $do_request = false;
        }
     
        if (!isset($dataReturn['next_action'])) {
            $dataReturn['next_action'] = "";
        }
        
        $dataReturn['tpl_pg_regist_card_max'] = $data['tpl_pg_regist_card_max'];
        return $this->app['view']->render('GmoPaymentGateway/View/pg_mulpay_credit.twig', array(
            'formCredit' => $formCredit->createView(),
            'formRegist' => $formRegist->createView(),
            'dataReturn' => $dataReturn,
            'error' => $error,
            'paymentInfo' => $paymentInfo,
            'title' => $Order->getPaymentMethod(),
            'tpl_is_loding' => $tpl_is_loding,
            'do_request' => $do_request,
            'tpl_pg_regist_card_form' => $data['tpl_pg_regist_card_form'],
            'listData'=>$data['listData'],
            'tpl_plg_target_seq'=>$data['tpl_plg_target_seq'],
            'creditReloadFlg' => $data['creditReloadFlg'],
            'registReloadFlg' => $data['registReloadFlg'],
            'isDisplayRegistCredit'=> $data['isDisplayRegistCredit'],
        ));

    }
    
    /**
     * Secure transaction
     *
     * @param Application $app
     * @param Request $request
     * @return type
     */
    public function registSecureTran($Order, $PaymentExtension, $listData, $tpl_plg_target_seq)
    {
        $this->app['session']->set('PaRes', $this->app['request']->request->get('PaRes'));
        $error = array();
        $error['payment'] = '';
        $tpl_is_loding = false;
        $do_request = true;

        $paymentCode = $PaymentExtension->getPaymentCode();
        $paymentInfo = $PaymentExtension->getArrPaymentConfig();
        if (empty($paymentInfo)) {
            $paymentInfo = array();
            $paymentInfo['use_securitycd'] = null;
            $paymentInfo['enable_customer_regist'] = false;
            $paymentInfo['credit_pay_methods'] = array();
        }
        $form = $this->createCreditRegistForm($this->app, $paymentInfo);
        $paymentInfo['enable_customer_regist'] = false;
        $form->handleRequest($this->app['request']);
        $formData = $form->getData();
        $objPageHelper = $this->prepareOrderData($Order, $this->app, $paymentCode, $PaymentExtension);
        $formData = array_merge((array)$formData, $this->app['request']->request->all());
        $objPageHelper->modeAction('SecureTran', $formData, $Order, $PaymentExtension, $this->app);
        if ($objPageHelper->isComplete) {
            // Remove session before complete
            $this->app['session']->set('PaReq', null);
            $this->app['session']->set('PaRes', null);
            $this->app['session']->set('MD', null);
            $orderId = $objPageHelper->orderId;
            $order = $this->app['eccube.repository.order']->findOneBy(array('id' => $orderId));

            $this->changeOrderData($Order);

            // メール送信
            $this->sendOrderMail($order);
            $this->app['session']->set('eccube.plugin.gmo_pg.orderId', $orderId);
            $this->app['session']->set('eccube.front.shopping.order.id', $orderId);// 本体の完了画面に受注IDを引き継ぐ

            return $this->app->redirect($this->app['url_generator']->generate('shopping_complete'));
        }
        $dataReturn = $objPageHelper->dataReturn;
        $error = $objPageHelper->error;
        if (!empty($error)) {
            $do_request = false;
        }
        // Remove session PaReq,PaRes,MD
        $this->app['session']->set('PaReq', null);
        $this->app['session']->set('PaRes', null);
        $this->app['session']->set('MD', null);
        if (!isset($dataReturn['next_action'])) {
            $dataReturn['next_action'] = "";
        }

        return $this->app['view']->render('GmoPaymentGateway/View/pg_mulpay_regist_credit.twig', array(
            'form' => $form->createView(),
            'dataReturn' => $dataReturn,
            'listData' => $listData,
            'error' => $error,
            'paymentInfo' => $paymentInfo,
            'title' => $Order->getPaymentMethod(),
            'tpl_is_loding' => $tpl_is_loding,
            'do_request' => $do_request,
            'tpl_plg_target_seq' => $tpl_plg_target_seq,
        ));
    }
    
    /**
     * Secure transaction
     *
     * @param Application $app
     * @param Request $request
     * @return type
     */
    public function secureTran($Order, $data)
    {
        $this->app['session']->set('PaRes', $this->app['request']->request->get('PaRes'));
        $error = array();
        $error['payment'] = '';
        $tpl_is_loding = false;
        $do_request = true;
        
        $objUtil = new PaymentUtil($this->app);
        $PaymentExtension = $objUtil->getPaymentTypeConfig($Order->getPayment()->getId());
        $paymentCode = $PaymentExtension->getPaymentCode();
        $paymentInfo = $PaymentExtension->getArrPaymentConfig();
        $form = $this->createCreditForm();
        if (empty($paymentInfo)) {
            $paymentInfo = array();
            $paymentInfo['use_securitycd'] = null;
            $paymentInfo['enable_customer_regist'] = false;
            $paymentInfo['credit_pay_methods'] = array();
        }
        $formCredit = $this->createCreditForm();
        $formRegist = $this->createCreditRegistForm($this->app, $paymentInfo);
        // Hanlde request when submit form
        if (isset($_POST['type_submit']) && $_POST['type_submit'] =='regist') {
            $form = $formRegist->handleRequest($this->app['request']);
            $paymentCode = $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYCODE_REGIST_CREDIT'];
        } else {
            $form = $formCredit->handleRequest($this->app['request']);
        }
        $formData = $form->getData();
        $objPageHelper = $this->prepareOrderData($Order, $this->app, $paymentCode, $PaymentExtension);
        $formData = array_merge((array)$formData, $this->app['request']->request->all());
        $objPageHelper->modeAction('SecureTran', $formData, $Order, $PaymentExtension, $this->app);
        if ($objPageHelper->isComplete) {
            // Remove session before complete
            $this->app['session']->set('PaReq', null);
            $this->app['session']->set('PaRes', null);
            $this->app['session']->set('MD', null);
            $orderId = $objPageHelper->orderId;
            $order = $this->app['eccube.repository.order']->findOneBy(array('id' => $orderId));

            $this->changeOrderData($Order);

            // メール送信
            $this->sendOrderMail($order);
            $this->app['session']->set('eccube.plugin.gmo_pg.orderId', $orderId);
            $this->app['session']->set('eccube.front.shopping.order.id', $orderId);// 本体の完了画面に受注IDを引き継ぐ

            return $this->app->redirect($this->app['url_generator']->generate('shopping_complete'));
        }
        $dataReturn = $objPageHelper->dataReturn;
        $error = $objPageHelper->error;
        if (!empty($error)) {
            $do_request = false;
        }

        if (!isset($dataReturn['next_action'])) {
            $dataReturn['next_action'] = "";
        }

        $dataReturn['tpl_pg_regist_card_max'] = $data['tpl_pg_regist_card_max'];
        return $this->app['view']->render('GmoPaymentGateway/View/pg_mulpay_credit.twig', array(
            'formCredit' => $formCredit->createView(),
            'formRegist' => $formRegist->createView(),
            'dataReturn' => $dataReturn,
            'error' => $error,
            'paymentInfo' => $paymentInfo,
            'title' => $Order->getPaymentMethod(),
            'tpl_is_loding' => $tpl_is_loding,
            'do_request' => $do_request,
            'tpl_pg_regist_card_form' => $data['tpl_pg_regist_card_form'],
            'listData'=>$data['listData'],
            'tpl_plg_target_seq'=>$data['tpl_plg_target_seq'],
            'creditReloadFlg' => $data['creditReloadFlg'],
            'isDisplayRegistCredit'=> $data['isDisplayRegistCredit'],
        ));
    }

    /**
     * Return cart page when click back button
     * @param Application $app
     * @param Request $request
     */

    public function goBack(Application $app, Request $request)
    {
        $pre_order_id = $app['eccube.service.cart']->getPreOrderId();
        if (empty($pre_order_id)) {
            return $app->redirect($app->url('shopping'));
        }

        $Order = $app['eccube.repository.order']->findOneBy(array('pre_order_id' => $pre_order_id));
        if (!empty($Order)) {
            // 受注情報を更新（購入処理中として更新する）
            $Order->setOrderStatus($app['eccube.repository.order_status']->find($app['config']['order_processing']));
            $app['orm.em']->persist($Order);
            $app['orm.em']->flush();
        }

        return $app->redirect($app->url('shopping'));
    }

    /**
     * Util methods
     *
     * @param type $orderId
     * @param type $paymentId
     * @param Application $app
     */
    function setToken($orderId, $paymentId, Application $app)
    {

        // Check if token exists in transaction
        $transactionIdName = $app['config']['transaction_id_name'];
        $token = '';
        foreach ($app['session']->getFlashBag()->get($transactionIdName, array()) as $item) {
            $token = $item;
            break;
        }

        // If token does not exist in
        if (empty($token)) {
            $token = sha1(uniqid(rand(), true));
        }
        $gmoOrderPaymentRepo = $app['orm.em']->getRepository('\Plugin\GmoPaymentGateway\Entity\GmoOrderPayment');
        $gmoOrderPayment = $gmoOrderPaymentRepo->findOneBy(array('id' => $orderId));
        if (is_null($gmoOrderPayment)) {
            $gmoOrderPayment = new \Plugin\GmoPaymentGateway\Entity\GmoOrderPayment();
            $gmoOrderPayment->setId($orderId);
        }
        $gmoOrderPayment->setMemo08($token);
        $gmoOrderPayment->setMemo03($paymentId);

        $app['orm.em']->persist($gmoOrderPayment);
        $app['orm.em']->flush();

    }

    /**
     * 外部ページからの遷移の際に受注情報内のTRANSACTION IDとのCSFRチェックを行う。
     *
     * @param integer $order_id 受注ID
     * @param text $transactionid TRANSACTION ID
     * @return void
     */
    function IsValidToken($order_id, $transactionId)
    {
        $objQuery =& Query_Ex::getSingletonInstance();
        if ($objQuery->get(PG_MULPAY_ORDER_COL_TRANSID, 'dtb_order', 'order_id = ?', array($order_id)) == $transactionId) {
            return true;
        }
        return false;
    }

    /**
     * Get error message
     *
     * @param \Symfony\Component\Form\Form $form
     * @return type
     */
    private function getErrorMessages(\Symfony\Component\Form\Form $form)
    {
        $errors = array();
        foreach ($form->getErrors() as $key => $error) {
            $template = $error->getMessageTemplate();
            $parameters = $error->getMessageParameters();

            foreach ($parameters as $var => $value) {
                $template = str_replace($var, $value, $template);
            }

            $errors[$key] = $template;
        }
        if ($form->count()) {
            foreach ($form as $child) {
                if (!$child->isValid()) {
                    $errors[$child->getName()] = $this->getErrorMessages($child);
                }
            }
        }
        return $errors;
    }

    /**
     * Function create credit card form
     * @param Application $app
     * @param array $paymentInfo
     * @return $form
     */
    private function createCreditForm()
    {
        $creditFrom = new PaymentType($this->app);
        $form = $this->app['form.factory']->createBuilder($creditFrom)->getForm();
        return $form;
    }

    /**
     * Function create regist credit card form
     * @param Application $app
     * @param array $paymentInfo
     * @return $form
     */
    private function createCreditRegistForm($app, $paymentInfo)
    {
        $objUtil = new PaymentUtil($app);
        $arrPayMethod = $objUtil->getCreditPayMethod();
        $paymentInfo['enable_customer_regist'] = $objUtil->isRegistCardPaymentEnable();
        $listPayMethod = array();
        foreach ($paymentInfo['credit_pay_methods'] as $pay_method) {
            if (!is_null($arrPayMethod[$pay_method])) {
                $listPayMethod[$pay_method] = $arrPayMethod[$pay_method];
            }
        }
       
        $formRegist = new RegistCreditSelectType($listPayMethod);
        $form = $app['form.factory']->createBuilder($formRegist)->getForm();
        return $form;
    }

    /**
     * Init data for credit card screen
     * @return string
     */
    private function initData()
    {
        $dataReturn = array();
        $dataReturn['result'] = array();
        $dataReturn['arrTdData'] = array();
        $dataReturn['tpl_url'] = '';
        $dataReturn['tpl_is_td_tran'] = false;
        $dataReturn['tpl_btn_next'] = false;
        $dataReturn['tpl_payment_onload'] = false;
        $dataReturn['arrTdData']['TermUrl'] = '';
        $dataReturn['tpl_pg_regist_card_max'] = false;
        $dataReturn['registerCardFlg'] = false;
        $dataReturn['tpl_pg_regist_card_form'] = false;
        $dataReturn['arrTdData']['PaReq'] = '';
        $dataReturn['arrTdData']['PaRes'] = '';
        $dataReturn['arrTdData']['MD'] = '';
        $dataReturn['next_action'] = '';
        return $dataReturn;

    }

    /**
     * Init data for regist credit card screen
     * @return string
     */
    private function initParamCreditRegist()
    {

        $dataReturn = array();
        $dataReturn['result'] = array();
        $dataReturn['arrTdData'] = array();
        $dataReturn['tpl_url'] = '';
        $dataReturn['tpl_is_td_tran'] = false;
        $dataReturn['tpl_btn_next'] = false;
        $dataReturn['tpl_payment_onload'] = false;
        $dataReturn['arrTdData']['TermUrl'] = '';
        $dataReturn['tpl_pg_regist_card_max'] = false;
        $dataReturn['registerCardFlg'] = false;
        $dataReturn['tpl_pg_regist_card_form'] = false;
        $dataReturn['arrTdData']['PaReq'] = '';
        $dataReturn['arrTdData']['PaRes'] = '';
        $dataReturn['arrTdData']['MD'] = '';
        $dataReturn['tpl_plg_pg_mulpay_is_subscription'] = false;
        $dataReturn['tpl_plg_pg_mulpay_subscription_name'] = 'test';
        return $dataReturn;

    }

    /**
     * Get current url
     * @return string current url
     */
    function curPageURL()
    {
        $pageURL = 'http';
        if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
            $pageURL .= "s";
        }
        $pageURL .= "://";
        if ($_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
        } else {
            $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
        }
        return $pageURL;
    }

    /**
     * Get payment info
     * @param type $Order
     * @param type $PaymentExtension
     * @param type $app
     * @return type
     */
    public function getPaymentInfo($PaymentExtension, $app)
    {
        $objUtil = new PaymentUtil($app);
        $paymentInfo = $PaymentExtension->getArrPaymentConfig();
        if (empty($paymentInfo)) {
            $paymentInfo = array();
            $paymentInfo['use_securitycd'] = null;
            $paymentInfo['enable_customer_regist'] = false;
            $paymentInfo['credit_pay_methods'] = array();
        }
        $paymentInfo['enable_customer_regist'] = $objUtil->isRegistCardPaymentEnable();
        return $paymentInfo;
    }

    /**
     * Check allow display check box save credit card
     * @param object $Order
     * @param object $paymentInfo
     * @param application $app
     * @return boolean
     */
    public function checkRegistCredit($Order, $paymentInfo, $objClientMember)
    {
        $OrderExtension = new \Plugin\GmoPaymentGateway\Controller\DataObj\OrderExtension();
        $OrderExtension->setOrder($Order);
        $OrderExtension->setPaymentData($paymentInfo);

        $ret = $objClientMember->searchCard($OrderExtension);
        return $ret;
    }

    /**
     * Prepare data to send third party to check payment
     * @param object $Order
     * @param application $app
     * @param integer $paymentCode
     * @param object $PaymentExtension
     * @return \Plugin\GmoPaymentGateway\Controller\className
     */
    public function prepareOrderData($Order, $app, $paymentCode, $PaymentExtension)
    {

        // 受注情報が決済処理中となっているか確認
        $orderStatus = $Order->getOrderStatus()->getId();

        if ($orderStatus != $app['config']['order_processing']) {
            switch ($orderStatus) {
                case  $app['config']['order_new']:
                case  $app['config']['order_pre_end']:
                    return $app->redirect($app['url_generator']->generate('shopping_complete'));
                    break;
                case  $app['config']['order_pay_wait']:
                    // リンク型遷移での戻りは各ヘルパーに処理させる場合があるため、リダイレクトしない。
                    if ($app['request']->get('mode') != 'pgreturn') {
                        return $app->redirect($app['url_generator']->generate('shopping_complete'));
                    }
                    break;
                default:
                    if ($app['request']->get('mode') != 'pgreturn' && !is_null($orderStatus)) {
                        $error_title = 'エラー';
                        $error_message = "注文情報が無効です。この手続きは無効となりました。";
                        return $app['view']->render('error.twig', array('error_message' => $error_message, 'error_title'=> $error_title));
                    }
                    break;
            }
        }
        if (is_null($paymentCode) or empty($paymentCode)) {
            $error_title = 'エラー';
            $error_message = "注文情報の決済方法と決済モジュールの設定が一致していません。この手続きは無効となりました。管理者に連絡をして下さい。";
            return $app['view']->render('error.twig', array('error_message' => $error_message, 'error_title'=> $error_title));
        }

        $helper_name = 'PageHelper_' . $paymentCode;

        if (!file_exists(__DIR__ . '/Helper/' . $helper_name . '.php')) {
            $error_title = 'エラー';
            $error_message = "決済モジュールのページヘルパーが読み込めません。この手続きは無効となりました。管理者に連絡をして下さい。";
            return $app['view']->render('error.twig', array('error_message' => $error_message, 'error_title'=> $error_title));
        }

        $code = $PaymentExtension->getGmoPaymentMethod()->getMemo03();
        $orderId = $Order->getId();
        $this->setToken($orderId, $code, $app);

        $className = "Plugin\\GmoPaymentGateway\\Controller\\Helper\\" . $helper_name;
        
        $objPageHelper = new $className();
        return $objPageHelper;
    }
    
    protected function isGranted($app) {
        if ($this->app['security']->isGranted('ROLE_USER')) {
            return true;
        }
        return false;
    }

    public function changeOrderData($Order)
    {
        $this->app['session']->remove('formData');
        
        $em = $this->app['orm.em'];
        $em->getConnection()->beginTransaction();
        $Order->setOrderDate(new \DateTime());
        $listOldVersion = array('3.0.1','3.0.2','3.0.3','3.0.4');
        if (in_array(Constant::VERSION, $listOldVersion)) {
            $formData = $this->app['session']->get('gmo_payment_formData');
            // お届け先情報を更新
            $shippings = $Order->getShippings();
            foreach ($shippings as $shipping) {
                $shipping->setShippingDeliveryName($formData['delivery']->getName());
                if (!empty($formData['deliveryTime'])) {
                    $shipping->setShippingDeliveryTime($formData['deliveryTime']->getDeliveryTime());
                }
                if (!empty($formData['deliveryDate'])) {
                    $shipping->setShippingDeliveryDate(new \DateTime($formData['deliveryDate']));
                }
                $shipping->setShippingDeliveryFee($shipping->getDeliveryFee()->getFee());
            }
        }

        $orderService = $this->app['eccube.service.order'];
        $orderService->setStockUpdate($em, $Order);
        if ($this->isGranted($this->app)) {
            // 会員の場合、購入金額を更新
            $orderService->setCustomerUpdate($em, $Order, $this->app->user());
        }

        if (version_compare(Constant::VERSION, '3.0.10', '>=')) {
            // 受注完了を他プラグインへ通知する.
            $this->app['eccube.service.shopping']->notifyComplete($Order);
        }

        $em->flush();
        $em->getConnection()->commit();
    }
    
    /**
     * Check product stock across eccube version.
     * @param type $app
     * @param type $Order
     */
    private function checkStockProduct($app, $Order){
        $listOldVersion = array('3.0.1', '3.0.2', '3.0.3', '3.0.4');
        $orderService = in_array(Constant::VERSION, $listOldVersion) ? $app['eccube.service.order'] : $app['eccube.service.shopping'];
        return $orderService->isOrderProduct($app['orm.em'], $Order);
    }

    private function sendOrderMail($Order)
    {
        if (version_compare(Constant::VERSION, '3.0.10', '>=')) {
            $this->app['eccube.service.shopping']->sendOrderMail($Order);
        } else {
            $this->app['eccube.service.mail']->sendOrderMail($Order);
        }
    }
}
