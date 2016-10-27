<?php

/*
 * Copyright(c) 2015 GMO Payment Gateway, Inc. All rights reserved.
 * http://www.gmo-pg.com/
 */

namespace Plugin\GmoPaymentGateway;

use Eccube\Util\EntityUtil;
use Eccube\Common\Constant;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Plugin\GmoPaymentGateway\Controller\Util\CommonUtil;
use Plugin\GmoPaymentGateway\Controller\Util\PluginUtil;
use Plugin\GmoPaymentGateway\Controller\Util\PaymentUtil;
use Plugin\GmoPaymentGateway\Service\client\PG_MULPAY_Client_Util;
use Plugin\GmoPaymentGateway\Service\client\PG_MULPAY_Client_Member;
use Plugin\GmoPaymentGateway\Controller\Helper\PageHelper_PaymentEdit;

class GmoPaymentGateway {

    private $app;

    public function __construct($app) {
        $this->app = $app;
    }

    /**
     * Render shopping complete page with Gmo authen result piece (plugin)
     * @param FilterResponseEvent $event
     * @return type
     */
    public function onRenderShoppingCompleteBefore(FilterResponseEvent $event) {
        $nonMember = $this->app['session']->get('eccube.front.shopping.nonmember');
        if ($this->app->isGranted('ROLE_USER') || !is_null($nonMember)) {
            // Return if no order Id in session (normal payment method was selected)
            $orderId = $this->app['session']->get('eccube.plugin.gmo_pg.orderId');
            if ($orderId == null) {
                return;
            }

            // Return if payment method is not Gmo payment
            $orderRep = $this->app['orm.em']->getRepository('\Eccube\Entity\Order');
            $Order = $orderRep->findOneBy(array('id' => $orderId));
            if ($Order != null) {
                $paymentId = $Order->getPayment()->getId();
                $GmoPayment = $this->app['orm.em']->getRepository('Plugin\GmoPaymentGateway\Entity\GmoPaymentMethod')->findOneBy(array('id' => $paymentId));
                if (is_null($GmoPayment) || is_null($GmoPayment->getMemo03())) {
                    return;
                }
            }

            // Get request
            $request = $event->getRequest();
            // Get response
            $response = $event->getResponse();
            // Find dom and add extension template
            $html = $this->getHTMLShoppingComplete($request, $response, $orderId);
            // Set content for response
            $response->setContent($html);
            $event->setResponse($response);

            // Remove orderId from session
            $this->app['session']->set('eccube.plugin.gmo_pg.orderId', null);
        }
    }
    
    /**
     * Find and add extension template to response.
     * @param FilterResponseEvent $event
     * @return type
     */
    public function getHTMLShoppingComplete(Request $request, Response $response, $orderId){
        $crawler = new Crawler($response->getContent());
        $html = $this->getHtml($crawler);
        // Get info which need for extension template
        $orderGmoInfo = $this->app['eccube.plugin.gmo_pg.repository.gmo_order_payment']->findOneBy(array('id' => $orderId));
        $arrOther = unserialize($orderGmoInfo->getMemo02());
        // Get and render extension template
        $insert = $this->app->renderView('GmoPaymentGateway/View/pg_mulpay_result.twig', array(
            'arrOther' => $arrOther,
        ));
        // add extension template to response's html
        try {
            $oldHtml = $crawler->filter('#deliveradd_input > div > div')->html();
            $newHtml = $oldHtml . $insert;
            $html = str_replace($oldHtml, $newHtml, $html);
        } catch (\InvalidArgumentException $e) {
            $objMdl = &PluginUtil::getInstance($this->app);
            $objMdl->printLog('******* Exception when add template before shopping complete start *******');
            $objMdl->printLog($e->getTraceAsString());
            $objMdl->printLog('******* Exception when add template before shopping complete end *******');
        }

        return html_entity_decode($html, ENT_NOQUOTES, 'UTF-8');
    }

    public function onRenderShoppingBefore(FilterResponseEvent $event) {
        $nonMember = $this->app['session']->get('eccube.front.shopping.nonmember');
        if ($this->app->isGranted('ROLE_USER') || !is_null($nonMember)) {
            $Order = $this->app['eccube.repository.order']->findOneBy(array('pre_order_id' => $this->app['eccube.service.cart']->getPreOrderId()));

            if (!is_null($Order)) {
                $Payment = $Order->getPayment();
                $PaymentConfig = null;
                if (!is_null($Payment)) {
                    $PaymentConfig = $this->app['eccube.plugin.gmo_pg.repository.gmo_payment_method']->find($Payment->getId());
                }
                if (!is_null($PaymentConfig)) {
                    // Get request
                    $request = $event->getRequest();
                    // Get response
                    $response = $event->getResponse();
                    // Proccess html
                    $html = $this->getHtmlShoppingConfirm($request, $response, $Payment);
                    // Set content for response
                    $response->setContent($html);
                    $event->setResponse($response);
                }

            }

            // Remove because regis credit method is not used anymore
            // $source = $event->getResponse()->getContent();
            // $preOrderId = $this->app['eccube.service.cart']->getPreOrderId();
            // $orderRepo = $this->app['eccube.repository.order'];
            // $Order = $orderRepo->findOneBy(array('pre_order_id' => $preOrderId));
            // if (!is_null($Order)) {
            //     $orderId = $Order->getId();

            //     // Check user has used credit card or not
            //     $allowFlag = $this->lfCheckPayment($orderId);
            //     // If user existed credit card allow display method credit regist
            //     // else remove this method in array payment after render shopping view
            //     if (!$allowFlag) {
            //         // Remove method credit regist
            //         $paymentCode = $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_REGIST_CREDIT'];
            //         $Payment = $this->app['eccube.plugin.gmo_pg.repository.gmo_payment_method']->findOneBy(array('memo03' => $paymentCode));

            //         if (!is_null($Payment)) {
            //             $paymentId = $Payment->getId();
            //             $source .= "
            //             <script>
            //                 $(function(){
            //                     $('ul.payment_list li').each(function(){
            //                          if( $(this).find('input[id=shopping_payment_$paymentId]').val() == $paymentId){
            //                             $(this).remove();
            //                         }
            //                     });
            //                  });
            //             </script>
            //             ";
            //         }
            //     }
            //     $event->getResponse()->setContent($source);
            // }
        }
    }


    /**
     * Filter and add rename button submit in shopping confirm page 
     * @param Request $request
     * @param Response $response
     * @param type $Payment
     * @return html
     */
    private function getHtmlShoppingConfirm(Request $request, Response $response, $Payment){
        $crawler = new Crawler($response->getContent());
        $html = $this->getHtml($crawler);
        $newMethod = $Payment->getMethod() . 'へ';
        try {
            $listOldVersion = array('3.0.1', '3.0.2', '3.0.3', '3.0.4');
            if (in_array(Constant::VERSION, $listOldVersion)) {
                $oldMethod = $crawler->filter('.btn.btn-primary.btn-block')->html();
            }else{
                $oldMethod = $crawler->filter('#order-button')->html();
            }

            // GMO支払い方法レコードを取得
            $PaymentConfig = $this->app['eccube.plugin.gmo_pg.repository.gmo_payment_method']->find($Payment->getId());
            // GMO支払い方法コードを取得
            $memo03 = $PaymentConfig->getMemo03();
            $const = $this->app['config']['GmoPaymentGateway']['const'];

            // 楽天ID決済に一致するか？
            if ($memo03 == $const['PG_MULPAY_PAYID_RAKUTEN_ID']) {
                // 楽天ID決済の場合は「注文する」ボタンに楽天ID決済ロゴ画像を
                // 利用する
                $imgpath  = $this->app['config']['root_urlpath'];
                $imgpath .= $this->app['config']['plugin_html_urlpath'];
                $imgpath .= "gmo_pg/btn_check_01.gif";
                $newMethod = '<img src="' . $imgpath . '" />';
            }

            $html = str_replace($oldMethod, $newMethod, $html);
        } catch (\InvalidArgumentException $e) {
            $objMdl = &PluginUtil::getInstance($this->app);
            $objMdl->printLog('******* Exception when change content button shopping start *******');
            $objMdl->printLog($e->getTraceAsString());
            $objMdl->printLog('******* Exception when change content button shopping end *******');
        }
        return html_entity_decode($html, ENT_NOQUOTES, 'UTF-8');
    }

    public function onRenderAdminSettingShopPaymentEditBefore(FilterResponseEvent $event) {
        if ($this->app['security']->isGranted('ROLE_ADMIN')) {
            $paymentId = $this->app['request']->attributes->get('id');
            $GmoPaymentMethod = $this->app['eccube.plugin.gmo_pg.repository.gmo_payment_method']->findOneBy(array('id' => $paymentId));
            if (!empty($GmoPaymentMethod)) {
                // Get request
                $request = $event->getRequest();
                // Get response
                $response = $event->getResponse();

                // Render fragment into HTML
                $insert = PageHelper_PaymentEdit::renderFragment($this->app, $GmoPaymentMethod);

                // Append fragment HTML into current page source
                $source = $event->getResponse()->getContent();
                $html = PageHelper_PaymentEdit::appendHTML($source, $insert);

                // Set content for response
                $response->setContent($html);
                $event->setResponse($response);
            }
        }
    }

    public function onControllerAdminSettingShopPaymentEditAfter() {
        if ($this->app['security']->isGranted('ROLE_ADMIN')) {
            $paymentId = $this->app['request']->get('id');
            $GmoPaymentMethod = $this->app['orm.em']->getRepository('Plugin\GmoPaymentGateway\Entity\GmoPaymentMethod')->findOneBy(array('id' => $paymentId));
            if ($this->app['request']->getMethod() === 'POST' && !empty($GmoPaymentMethod)) {
                $form = $this->app['form.factory']->createBuilder('payment_register')->getForm();
                $form->handleRequest($this->app['request']);
                if ($form->isValid()) {
                    $data = PageHelper_PaymentEdit::getDataFromForm($this->app, $form, $GmoPaymentMethod);
                    if (!empty($data)) {

                        $encryptData = serialize($data);
                        $GmoPaymentMethod->setMemo05($encryptData);

                        $this->app['orm.em']->persist($GmoPaymentMethod);
                        $this->app['orm.em']->flush();
                    }
                }
            }
        }
    }

    /**
     * event for rendering the PaymentUtil layout before Order edit page
     * @param FilterResponseEvent $event
     */
    public function onRenderAdminOrderEditBefore(FilterResponseEvent $event) {
        if ($this->app['security']->isGranted('ROLE_ADMIN')) {
            $orderId = $this->app['request']->get('id');
            $gmoOrder = $this->app['eccube.plugin.gmo_pg.repository.gmo_order_payment']->find($orderId);
            $paymentId = $this->app['eccube.plugin.gmo_pg.repository.gmo_payment_method']->findAll();
            $Payment = $this->app['eccube.repository.order']->findOneBy(array('id' => $orderId))->getPayment();
            $a = array();   

            // Create array all of payment
            foreach ($paymentId as $key => $value) {
                $a[$paymentId[$key]->getId()] = $paymentId[$key]->getMethod();
            }
            $source = $event->getResponse()->getContent();
            // If user create new order in admin, not allow user select payment method from GMO
            if (is_null($gmoOrder) || EntityUtil::isEmpty($Payment)) {
                $arrPaymentId = array();
                foreach ($paymentId as $key => $value) {
                    array_push($arrPaymentId, $paymentId[$key]->getId());
                }
                $arrPaymentRemove = '';
                foreach ($arrPaymentId as $key => $value) {
                    $arrPaymentRemove .= '$("#order_Payment option[value=' . $value . ']").remove();
                            ';
                }
                $jqueryCode = '
                    <script>
                        $(function(){
                            ' . $arrPaymentRemove . '
                        });
                    </script>
                ';
                $source .= $jqueryCode;
                $event->getResponse()->setContent($source);
                return;
            }
            // Get PaymentData and Order Object 
            $objUtil = new PaymentUtil($this->app);
            $orderExt = $objUtil->getOrderPayData($orderId);
            $paymentObj = $this->app['orm.em']->getRepository('Eccube\Entity\Payment')->findAll();

            // Create arr all of payment method and jquery code for remove payment method not use in $orderExt
            $arrPaymentId = array();
            foreach ($paymentObj as $key => $value) {
                array_push($arrPaymentId, $paymentObj[$key]->getId());
            }

            $arrPaymentRemove = '';
            $GmoPayment = $this->app['eccube.plugin.gmo_pg.repository.gmo_payment_method']->findOneBy(array('id' => $Payment->getId()));
            if (EntityUtil::isEmpty($GmoPayment)) {
                return;
            }
            $value = $Payment->getId();
            $jqueryCode = '
                <script type="text/javascript">
                    $(function(){
                        $("#order_Payment").after("<input type=' . "'hidden'" . ' name=' . "'order[Payment]'" . ' id=' . "'order_payment'" . ' value=' . "'$value'" . ' />");
                        $("#order_Payment").attr("disabled", "disabled");
                    });
                </script>
            ';

            $source .= $jqueryCode;
            // Get all elements by tag name

            $listOldVersion = array('3.0.1', '3.0.2', '3.0.3', '3.0.4');
            if (in_array(Constant::VERSION, $listOldVersion)) {
                $beforeClass = "box";
                $parentClass = "col-md-9";
            }else{
                $beforeClass = "box";
                $parentClass = "col-md-12";
            }
            
            $request = $event->getRequest();
            // Get response
            $response = $event->getResponse();

            // // get template rendered
            $insert = $this->renderGMOSectionOrderEdit($orderId);
            
            // $crawler = new Crawler($response->getContent());
            $crawler = new Crawler($source);
            $html = $this->getHtml($crawler);
            
            $oldHtml = $crawler->filter("div.$parentClass > div.$beforeClass")->last();
            $oldHtml2 = null;
            foreach ($oldHtml as $domElement) {
                $oldHtml2 = $domElement->ownerDocument->saveHTML($domElement);
            }
            if (!is_null($oldHtml2)) {
                if (in_array(Constant::VERSION, $listOldVersion)) {
                    $newHtml = $oldHtml2 . $insert;
                } else {
                    $newHtml = $insert . $oldHtml2;
                }
                $html = str_replace($oldHtml2, $newHtml, $html);

                // Set content for response
                $response->setContent($html);
                $event->setResponse($response);
            }
        }
    }

    /*
     * Preparing data and view for including layout into dom
     * Get PaymentData and Order Object 
     */
    private function renderGMOSectionOrderEdit($orderId){
        $objUtil = new PaymentUtil($this->app);
        $orderExt = $objUtil->getOrderPayData($orderId);
        $paymentStatus = $objUtil->getPaymentStatus();

        // Get info for Order
        $orderExtGetPaymentData = $orderExtGetPaymentInfo = $orderExtGetPaymentLog = $gmoPaymentMemo03 = $paymentData = $orderExtGetOrder = array();
        $paramTransactionID = '';
        if (!empty($orderExt)) {
            $orderExtGetOrder = $orderExt->getOrder();
            $orderExtGetGmoOrderPayment = $orderExt->getGmoOrderPayment();
            $orderExtGetPaymentData = CommonUtil::unSerializeData($orderExtGetGmoOrderPayment->getMemo05());
            $orderExtGetPaymentInfo = CommonUtil::unSerializeData($orderExtGetGmoOrderPayment->getMemo02());
            $orderExtGetPaymentLog = CommonUtil::unSerializeData($orderExtGetGmoOrderPayment->getMemo09());

            $paymentData = $orderExt->getPaymentData();
            if (!empty($paymentData)) {
                if (!empty($paymentData['orderTemp'])) {
                    $orderTemp = $this->app['orm.em']->getRepository('Eccube\Entity\OrderTemp')
                            ->findOneBy(array('id' => $paymentData['orderTemp']));

                    if (!empty($orderTemp)) {
                        $paramSession = unserialize($orderTemp->getSession());
                        $paramTransactionID = $paramSession['transactionid'];
                    }
                }
            }

            // Get info for GmoPayment
            $gmoPaymentMethod = $this->app['orm.em']->getRepository('Plugin\GmoPaymentGateway\Entity\GmoPaymentMethod')->findOneBy(array('id' => $orderExtGetOrder->getPayment()->getId()));
            $gmoPaymentMemo03 = $gmoPaymentMethod->getMemo03();
        }

        // Get conveni method
        $conveniStoresence = $objUtil->getConveni();

        $jobCode = '';
        if (isset($orderExtGetPaymentData['JobCd'])) {
            $jobCode = $orderExtGetPaymentData['JobCd'];
        }
        // Determine what operation buttons is available in the order edit page
        $buttons = $this->paymentOperationButtonsCondition($gmoPaymentMemo03, $orderExtGetPaymentData['pay_status'], $jobCode);
        $appendTwig = 'GmoPaymentGateway/View/admin/order_edit.twig'; // View to include into the dom of Order edit page
        $insert = $this->app['twig']->render($appendTwig, array(
            'Order' => $orderExtGetOrder,
            'orderId' => $orderId,
            'orderExtGetPaymentData' => $orderExtGetPaymentData,
            'orderExtGetPaymentInfo' => $orderExtGetPaymentInfo,
            'orderExtGetPaymentLog' => $orderExtGetPaymentLog,
            'appConst' => $this->app['config']['GmoPaymentGateway']['const'],
            'paymentStatus' => $paymentStatus,
            //  tpl_mode' => 'edit',
            'transactionId' => $this->app['config']['transaction_id_name'],
            'paramTransactionID' => $paramTransactionID,
            'plg_pg_mulpay_payid' => $gmoPaymentMemo03,
            'paymentData' => $paymentData,
            'conveniStoresence' => $conveniStoresence,
            'buttons' => $buttons,
        ));

        return $insert;
    }

    public function onRenderAdminOrderNewBefore(FilterResponseEvent $event) {
        if ($this->app['security']->isGranted('ROLE_ADMIN')) {
            $payments = $this->app['eccube.plugin.gmo_pg.repository.gmo_payment_method']->findAll();
            $arrPaymentId = array();

            foreach ($payments as $key => $value) {
                array_push($arrPaymentId, $payments[$key]->getId());
            }
            $source = $event->getResponse()->getContent();

            $arrPaymentRemove = '';
            foreach ($arrPaymentId as $key => $value) {
                $arrPaymentRemove .= '$("#order_Payment option[value=' . $value . ']").remove();
                        ';
            }

            $jqueryCode = '
                <script>
                    $(function(){
                        ' . $arrPaymentRemove . '
                    });
                </script>
            ';

            $source .= $jqueryCode;
            $event->getResponse()->setContent($source);
        }
    }

    /**
     * handle the request while clicking event from the button in PaymentUtil layout
     */
    public function onControllerAdminOrderEditControllerAfter($event = null) {
        $request = $this->app['request'];
        if ($this->app['security']->isGranted('ROLE_ADMIN')) {
            $order_id = $request->get('id');

            if ($request->getMethod() === 'POST' && $order_id) {
                $mode = $request->get('mode_type');
                if (!CommonUtil::isBlank($order_id) && CommonUtil::isInt($order_id)) {
                    $objUtil = new PaymentUtil($this->app);
                    $arrOrder = $objUtil->getOrderPayData($order_id);
                    $arrOrderMemo = unserialize($arrOrder->getGmoOrderPayment()->getMemo05());
                    $arrOrder->setOrderID($arrOrderMemo['OrderID']);

                    $objClient = new PG_MULPAY_Client_Util($this->app);

                    $ret = false;

                    switch ($mode) {
                        case 'plg_pg_mulpay_commit':
                            $ret = $objClient->commitOrder($arrOrder); // TuanDinh done
                            break;
                        case 'plg_pg_mulpay_change':
                            $ret = $objClient->changeOrder($arrOrder); // done
                            break;
                        case 'plg_pg_mulpay_cancel':
                            $ret = $objClient->cancelOrder($arrOrder); // TuanDinh done
                            break;
                        case 'plg_pg_mulpay_reauth':
                            $ret = $objClient->reauthOrder($arrOrder); // TuanDinh done
                            break;
                        case 'plg_pg_mulpay_get_status':
                            $ret = $objClient->getOrderInfo($arrOrder); // done
                            break;
                        default:
                            return;
                            break;
                    }

                    if (is_array($ret)) {
                        $error_message = 'エラーが発生しました。エラーコード：' . $ret['ErrCode'] . ":" . $ret['ErrInfo'];
                        $this->app->addDanger($error_message, 'admin');
                    } else if ($ret) {
                        $this->app->addSuccess('決済操作が完了しました。', 'admin');
                    } else {
                        $errors = $objClient->getError();
                        $error_message = '';
                        if (empty($errors)) {
                            $error_message .= '対象の変更は出来ない決済です。';
                        } else {
                            foreach ($errors as $errMess) {
                                $error_message .= $errMess;
                            }
                        }
                        $this->app->addDanger($error_message, 'admin');
                    }

                    $url = $this->app->url
                        ('admin_order_edit', array('id' => $order_id));

                    if ($event instanceof \Symfony\Component\HttpKernel\Event\KernelEvent) {
                        $response = $this->app->redirect($url);
                        $event->setResponse($response);
                        return;
                    } else {
                        header("Location: " . $url);
                        exit;
                    }
                }
            }
        }
    }

    public function onControllerShoppingConfirmBefore($event = null) {
        $nonMember = $this->app['session']->get('eccube.front.shopping.nonmember');
        if ($this->app->isGranted('ROLE_USER') || !is_null($nonMember)) {
            $Order = $this->app['eccube.repository.order']->findOneBy(array('pre_order_id' => $this->app['eccube.service.cart']->getPreOrderId()));
            $listOldVersion = array('3.0.1', '3.0.2', '3.0.3', '3.0.4');
            if (in_array(Constant::VERSION, $listOldVersion)) {
                $form = $this->app['form.factory']->createBuilder('shopping')->getForm();
                $deliveries = $this->findDeliveriesFromOrderDetails($this->app, $Order->getOrderDetails());
                // 配送業社の設定
                $shippings = $Order->getShippings();
                $delivery = $shippings[0]->getDelivery();

                // Formのカスタマイズ
                $this->setFormDelivery($form, $deliveries, $delivery);           // 配送業社の設定
                $this->setFormDeliveryDate($form, $Order, $this->app);           // お届け日の設定
                $this->setFormDeliveryTime($form, $delivery);                    // お届け時間の設定
                $this->setFormPayment($form, $delivery, $Order->getPayment());   // 支払い方法選択
            } else {
                $form = $this->app['eccube.service.shopping']->getShippingForm($Order);
            }

            if ('POST' === $this->app['request']->getMethod()) {
                $form->handleRequest($this->app['request']);

                if ($form->isValid()) {
                    $formData = $form->getData();

                    $gmoPaymentMethod = $this->app['eccube.plugin.gmo_pg.repository.gmo_payment_method']->find($formData['payment']->getId());
                    if (in_array(Constant::VERSION, $listOldVersion)) {
                        $this->app['session']->set('gmo_payment_formData', $formData);
                    }
                    if (!is_null($gmoPaymentMethod)) {
                        // 受注情報、配送情報を更新（決済処理中として更新する）
                        $this->app['eccube.service.order']->setOrderUpdate($this->app['orm.em'], $Order, $formData);
                        $Order->setOrderStatus($this->app['eccube.repository.order_status']->find($this->app['config']['order_processing']));
                        $this->app['orm.em']->persist($Order);
                        $this->app['orm.em']->flush();

                        $url = $this->app->url('gmo_shopping_payment');

                        if ($event instanceof \Symfony\Component\HttpKernel\Event\KernelEvent) {
                            $response = $this->app->redirect($url);
                            $event->setResponse($response);
                            return;
                        } else {
                            header("Location: " . $url);
                            exit;
                        }
                    }
                }
            }
        }
    }

    function lfCheckPayment($orderId) {
        $objMdl = & PluginUtil::getInstance($this->app);
        $gmoSetting = $objMdl->getUserSettings();
        if (empty($gmoSetting['server_url'])) {
            return false;
        }
        $objUtil = new PaymentUtil($this->app);
        $OrderExtension = $objUtil->getOrderPayData($orderId);
        if (!$this->app['security']->isGranted('ROLE_USER')) {
            return false;
        } else {
            $objClient = new PG_MULPAY_Client_Member($this->app);
            $ret = $objClient->searchCard($OrderExtension);
            if ($ret) {
                return true;
            } else {
                $results = $objClient->getResults();
                if (empty($results['CardSeq']) || !isset($results['CardSeq'])) {
                    return false;
                }
            }
        }
        return true;
    }

    function getElementsByClassName($elements, $className) {
        $arrElements = array();
        for ($i = 0; $i < $elements->length; $i++) {
            if (@$elements->item($i)->attributes->getNamedItem('class')->nodeValue == $className) {
                $arrElements[] = $elements->item($i);
            }
        }

        return $arrElements;
    }

    // --------------------------------------------------------------------------------------------
    // これより下はサービス化される予定

    /**
     * 配送業者を取得
     */
    private function findDeliveriesFromOrderDetails($app, $details) {

        $productTypes = array();
        foreach ($details as $detail) {
            $productTypes[] = $detail->getProductClass()->getProductType();
        }

        $qb = $app['orm.em']->createQueryBuilder();
        $deliveries = $qb->select("d")
                ->from("\Eccube\Entity\Delivery", "d")
                ->where($qb->expr()->in('d.ProductType', ':productTypes'))
                ->setParameter('productTypes', $productTypes)
                ->andWhere("d.del_flg = :delFlg")
                ->setParameter('delFlg', $app['config']['disabled'])
                ->orderBy("d.rank", "ASC")
                ->getQuery()
                ->getResult();

        return $deliveries;
    }

    /**
     * 配送業者のフォームを設定
     */
    private function setFormDelivery($form, $deliveries, $delivery = null) {

        // 配送業社の設定
        $form->add('delivery', 'entity', array(
            'class' => 'Eccube\Entity\Delivery',
            'property' => 'name',
            'choices' => $deliveries,
            'data' => $delivery,
        ));
    }

    /**
     * お届け日のフォームを設定
     */
    private function setFormDeliveryDate($form, $Order, $app) {

        // お届け日の設定
        $minDate = 0;
        $deliveryDateFlag = false;

        // 配送時に最大となる商品日数を取得
        foreach ($Order->getOrderDetails() as $detail) {
            $deliveryDate = $detail->getProductClass()->getDeliveryDate();
            if (!is_null($deliveryDate)) {
                if ($minDate < $deliveryDate->getValue()) {
                    $minDate = $deliveryDate->getValue();
                }
                // 配送日数が設定されている
                $deliveryDateFlag = true;
            }
        }

        // 配達最大日数期間を設定
        $deliveryDates = array();

        // 配送日数が設定されている
        if ($deliveryDateFlag) {
            $period = new \DatePeriod(
                    new \DateTime($minDate . ' day'), new \DateInterval('P1D'), new \DateTime($minDate + $app['config']['deliv_date_end_max'] . ' day')
            );

            foreach ($period as $day) {
                $deliveryDates[$day->format('Y/m/d')] = $day->format('Y/m/d');
            }
        }


        $form->add('deliveryDate', 'choice', array(
            'choices' => $deliveryDates,
            'required' => false,
            'empty_value' => '指定なし',
        ));
    }

    /**
     * お届け時間のフォームを設定
     */
    private function setFormDeliveryTime($form, $delivery) {
        // お届け時間の設定
        $form->add('deliveryTime', 'entity', array(
            'class' => 'Eccube\Entity\DeliveryTime',
            'property' => 'deliveryTime',
            'choices' => $delivery->getDeliveryTimes(),
        ));
    }

    /**
     * 支払い方法のフォームを設定
     */
    private function setFormPayment($form, $delivery, $payment = null) {

        // 支払い方法選択
        $paymentOptions = $delivery->getPaymentOptions();
        $payments = array();
        // 初期値で設定されている配送業社を設定
        foreach ($paymentOptions as $paymentOption) {
            $payments[] = $paymentOption->getPayment();
        }
        $form->add('payment', 'entity', array(
            'class' => 'Eccube\Entity\Payment',
            'property' => 'method',
            'choices' => $payments,
            'data' => $payment,
                // 'expanded' => true,
        ));
    }

    /**
     * Set the available settlement operation buttons in the order edit page
     *
     * @param $paymentMethod    the payment method of the order
     * @param $paymentStatus    the status of the order's payment
     * @param $jobCd            the jobCd that the order was registered with
     *
     * @return $availableOperations
     */
    private function paymentOperationButtonsCondition($paymentMethod, $paymentStatus, $jobCd) {

        /**
         * Array contains the available payment operations
         * These operations include:
         *      + 'COMMIT'
         *      + 'CHANGE'
         *      + 'CANCEL'
         *      + 'REAUTHORIZE'
         *      + 'GETORDERINFO'
         * @var    array
         */
        $availableOperations = array();

        $consts = $this->app['config']['GmoPaymentGateway']['const'];

        switch ($paymentMethod) {
            case $consts['PG_MULPAY_PAYID_CREDIT']:
            case $consts['PG_MULPAY_PAYID_REGIST_CREDIT']:
            case $consts['PG_MULPAY_PAYID_TOKEN']:
                // Set payment operations for credit and registered credit
                //This button is always available
                $availableOperations['GETORDERINFO'] = 1;
                if (!empty($paymentStatus)) {
                    if ($paymentStatus == $consts['PG_MULPAY_PAY_STATUS_AUTH']) {
                        $availableOperations['COMMIT'] = 1;
                    }
                    if ($paymentStatus == $consts['PG_MULPAY_PAY_STATUS_AUTH'] or
                            $paymentStatus == $consts['PG_MULPAY_PAY_STATUS_COMMIT'] or
                            $paymentStatus == $consts['PG_MULPAY_PAY_STATUS_SALES'] or
                            $paymentStatus == $consts['PG_MULPAY_PAY_STATUS_CAPTURE']) {
                        $availableOperations['CANCEL'] = 1;
                        $availableOperations['CHANGE'] = 1;
                    }
                    if ($paymentStatus == $consts['PG_MULPAY_PAY_STATUS_VOID'] or
                            $paymentStatus == $consts['PG_MULPAY_PAY_STATUS_RETURN'] or
                            $paymentStatus == $consts['PG_MULPAY_PAY_STATUS_RETURNX']) {
                        $availableOperations['REAUTHORIZE'] = 1;
                    }
                }
                break;
            case $consts['PG_MULPAY_PAYID_RAKUTEN_ID']:
                // Set payment operations for rakutenID

                $availableOperations['GETORDERINFO'] = 1;
                if ('AUTH' == $jobCd) {
                    if ($paymentStatus == $consts['PG_MULPAY_PAY_STATUS_AUTH']) {
                        $availableOperations['COMMIT'] = 1;
                    }
                    if ($paymentStatus == $consts['PG_MULPAY_PAY_STATUS_AUTH'] or
                            $paymentStatus == $consts['PG_MULPAY_PAY_STATUS_COMMIT'] or
                            $paymentStatus == $consts['PG_MULPAY_PAY_STATUS_SALES'] or
                            $paymentStatus == $consts['PG_MULPAY_PAY_STATUS_CAPTURE']) {
                        $availableOperations['CANCEL'] = 1;
                        $availableOperations['CHANGE'] = 1;
                    }
                }
                break;
            default:
                break;
        }
        return $availableOperations;
    }

    /**
     * Add sub menu link to change card page on menu in my page
     */
    public function onRenderMypageBefore(FilterResponseEvent $event) {
        // If user go to withdraw page and delete account, token will be null and isGranted function will be error.
        // Must check null token before call isGranted function.
        if(!is_null($this->app['security']->getToken())){
            // check user login before render sub menu link to change card page
            if ($this->app->isGranted('ROLE_USER')) {
                $objMdl = & PluginUtil::getInstance($this->app);
                $subData = $objMdl->getUserSettings();
                // check config for card_regist_flg and credit payment method 
                if ($subData['card_regist_flg'] && in_array($this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_CREDIT'], $subData['enable_payment_type'])) {
                    // Get request
                    $request = $event->getRequest();
                    // Get response
                    $response = $event->getResponse();
                    // Proccess html
                    $html = $this->getHtmlMypage($request, $response);
                    // Set content for response
                    $response->setContent($html);
                    $event->setResponse($response);
                }
            }
        }
    }

    /**
     * Add menu card management into menu Mypage
     * @param Request $request
     * @param Response $response
     * @return string html
     */
    private function getHtmlMypage(Request $request, Response $response){
        $crawler = new Crawler($response->getContent());
        $html = $this->getHtml($crawler);
        // Get mypageno
        $myPageNo = $request->get('mypageno');
        $insert = $this->app->renderView('GmoPaymentGateway/View/mypage_navi_add.twig', array(
            'mypageno' => $myPageNo,
        ));
        try {
            $oldHtml = $crawler->filter("#main_middle .local_nav")->children('ul')->html();
            $newHtml = $oldHtml . $insert;
            $html = str_replace($oldHtml, $newHtml, $html);
        } catch (\InvalidArgumentException $e) {
            $objMdl = &PluginUtil::getInstance($this->app);
            $objMdl->printLog('******* Exception when add template my page start *******');
            $objMdl->printLog($e->getTraceAsString());
            $objMdl->printLog('******* Exception when add template my page end *******');
        }
        return html_entity_decode($html, ENT_NOQUOTES, 'UTF-8');
    }

    /**
     * Handle event admin_customer_edit
     * Add gmo_member_id
     * return new view
     */
    public function onRenderAdminCustomerEditBefore(FilterResponseEvent $event) {
        // Check admin login
        if (!$this->app['security']->isGranted('ROLE_ADMIN')) {
            return;
        }
        // Get customer id from request
        $customerId = $this->app['request']->get('id');
        if (!isset($customerId)) {
            return;
        }
        $GmoMemberId = CommonUtil::getGmoMemberId($customerId, $this->app);
//        if(is_null($GmoMemberId)){
//            $GmoMemberId = CommonUtil::createGmoMemberId($customerId, $this->app, true);
//            // Save gmo member id
//            $this->app['eccube.plugin.gmo_pg.repository.gmo_member']->updateOrCreate($this->app);
//        }

        // Get request
        $request = $event->getRequest();
        // Get response
        $response = $event->getResponse();
        // Proccess html
        $html = $this->getHtmlAdminCustomerEdit($request, $response, $GmoMemberId);
        // Set content for response
        $response->setContent($html);
        $event->setResponse($response);
    }

    /**
     * Handle event render admin customer edit
     * Add template for display gmo member id
     * @param Request $request
     * @param Response $response
     * @param type $GmoMemberId
     * @return string html
     */
    private function getHtmlAdminCustomerEdit(Request $request, Response $response, $GmoMemberId){
        $crawler = new Crawler($response->getContent());
        $html = $this->getHtml($crawler);
        $insert = $this->app->renderView('GmoPaymentGateway/View/admin/cutomer_edit.twig', array(
            'GmoMemberId' => $GmoMemberId,
        ));
        try {
            $oldHtml = $crawler->filter('#aside_wrap .col-md-9')->first()->html();
            $newHtml = $oldHtml . $insert;
            $html = str_replace($oldHtml, $newHtml, $html);
        } catch (\InvalidArgumentException $e) {
            $objMdl = &PluginUtil::getInstance($this->app);
            $objMdl->printLog('******* Exception when add template customer edit start *******');
            $objMdl->printLog($e->getTraceAsString());
            $objMdl->printLog('******* Exception when add template customer edit end *******');
        }
        return html_entity_decode($html, ENT_NOQUOTES, 'UTF-8');
    }
    
    /**
     * Handle event admin payment metthod delete
     * Update status gmo payment method and enable_payment_type of config subdata
     * return void
     */
    public function onControllerAdminPaymentDeleteControllerBefore(){
        // Check admin login
        if (!$this->app['security']->isGranted('ROLE_ADMIN')) {
            return;
        }
        // Get request
        $request = $this->app['request'];
        // Get id
        $id = $request->get('id');
        if(!isset($id)){
            return;
        }
        // Get Gmo payment method
        $GmoPaymentMethod = $this->app['eccube.plugin.gmo_pg.repository.gmo_payment_method']
                            ->findOneBy(array('id' => $id));
        if(EntityUtil::isEmpty($GmoPaymentMethod)){
            return;
        }
        // Get gmo plugin
        $GmoPlugin = $this->app['eccube.plugin.gmo_pg.repository.gmo_plugin']
                ->findOneBy(array('code' => $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_CODE']));
        if(EntityUtil::isEmpty($GmoPlugin)){
            return; 
        }
        // Get sub data
        $subData = unserialize($GmoPlugin->getSubData());
        if(empty($subData)){
            return;
        }
        $paymetType[] = $GmoPaymentMethod->getMemo03();
        $subData['user_settings']['enable_payment_type'] = array_diff($subData['user_settings']['enable_payment_type'], $paymetType);
        // Set sub data
        $GmoPlugin->setSubData(serialize($subData));
        // Set del_plg: 1 for Gmo payment method
        $GmoPaymentMethod->setDelFlg(Constant::ENABLED);
        // Save data
        $this->app['orm.em']->persist($GmoPlugin);
        $this->app['orm.em']->persist($GmoPaymentMethod);
        $this->app['orm.em']->flush();
    }

    /**
     * 解析用HTMLを取得
     *
     * @param Crawler $crawler
     * @return string
     */
    public static function getHtml(Crawler $crawler){
        $html = '';
        foreach ($crawler as $domElement) {
            $domElement->ownerDocument->formatOutput = true;
            $html .= $domElement->ownerDocument->saveHTML();
        }
        return html_entity_decode($html, ENT_NOQUOTES, 'UTF-8');
    }
}
