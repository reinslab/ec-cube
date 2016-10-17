<?php

/*
 * Copyright(c) 2015 GMO Payment Gateway, Inc. All rights reserved.
 * http://www.gmo-pg.com/
 */

namespace Plugin\GmoPaymentGateway\Controller;

use Eccube\Application;
use Plugin\GmoPaymentGateway\Controller\Util\PaymentUtil;
use Plugin\GmoPaymentGateway\Controller\Util\PluginUtil;
use Plugin\GmoPaymentGateway\Form\Type\ConfigType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;

/**
 * Controller to handle module setting screen
 */
class ConfigController {

    private $app;

    /**
     * Edit config
     *
     * @param Application $app
     * @param Request $request
     * @param type $id
     * @return type
     */
    public function edit(Application $app, Request $request) {
        $this->app = $app;

        $objMdl = & PluginUtil::getInstance($this->app);
        $objUtil = new PaymentUtil($this->app);
        $tpl_subtitle = $objMdl->getName();

        $objMdl->install();

        // Get module code from dtb_plugin
        $self = Yaml::parse(__DIR__ . '/../config.yml');
        $Plugin = $this->app['eccube.repository.plugin']->findOneBy(array('code' => $self['code']));

        if (is_null($Plugin)) {
            $error = "例外エラー<br />プラグインが存在しません。";
            $error_title = 'エラー';
            return $this->app['view']->render('error.twig', compact('error', 'error_title'));
        }

        $GmoPlugin = $this->app['eccube.plugin.gmo_pg.repository.gmo_plugin']->findOneBy(array('code' => $Plugin->getCode()));

        if (is_null($GmoPlugin)) {
            $error = "例外エラー<br />プラグインが存在しません。";
            $error_title = 'エラー';
            return $this->app['view']->render('error.twig', compact('error', 'error_title'));
        }

        $subData = $objMdl->getUserSettings();
        if (empty($subData) !== true) {
            if (array_search($this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_TOKEN'], $subData['enable_payment_type']) !== false) {
                array_push($subData['enable_payment_type'], $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_CREDIT']);
            }
        }

        $Payments = $objUtil->getPaymentTypeNames(-1);
        $configFrom = new ConfigType($this->app, $subData);
        $form = $this->app['form.factory']->createBuilder($configFrom)->getForm();

        if ('POST' === $this->app['request']->getMethod()) {
            $form->handleRequest($this->app['request']);
            if ($form->isValid()) {

                $formData = $form->getData();
                $creditKey = array_search($this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_CREDIT'], $formData['enable_payment_type']);
                $tokenKey = array_search($this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_TOKEN'], $formData['enable_payment_type']);
                $prevCreditId = 0;
                $afterCreditId = 0;
                // If credit method is selected
                if ($creditKey !== false) {
                    if ($formData['credit_token'] == 1) {
                        $prevCreditId = $formData['enable_payment_type'][$creditKey];
                        $formData['enable_payment_type'][$creditKey] = $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_TOKEN'];
                        $afterCreditId = $formData['enable_payment_type'][$creditKey];
                    }
                } else if ($tokenKey !== false) {
                    if ($formData['credit_token'] == 0) {
                        $prevCreditId = $formData['enable_payment_type'][$tokenKey];
                        $formData['enable_payment_type'][$tokenKey] = $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_CREDIT'];
                        $afterCreditId = $formData['enable_payment_type'][$tokenKey];
                    }
                }
                $this->app['orm.em']->getConnection()->beginTransaction();
                $this->savePaymentData($formData, $Payments);

                // If credit/token payment is selected
                // Check if credit <-> token exchange
                if (($prevCreditId != 0) && ($afterCreditId != 0) && ($prevCreditId != $afterCreditId)) {
                    $this->updatePaymentMethods($prevCreditId, $afterCreditId);
                    $this->updatePaymentOption($prevCreditId, $afterCreditId);
                }
                $this->app['orm.em']->getConnection()->commit();

                $app->addSuccess('admin.register.complete', 'admin');
                return $this->app->redirect($this->app['url_generator']->generate('plugin_GmoPaymentGateway_config'));
            }
        }

        return $this->app['view']->render('GmoPaymentGateway/View/admin/gmo_config.twig', array(
                    'form' => $form->createView(),
                    'tpl_subtitle' => $tpl_subtitle,
                    'recv_url' => $app->url('gmo_shopping_payment_recv'),
                    'subData' => $subData,
        ));
    }

    /**
     * Save subdata into dtb_gmo_plugin
     * Save payment selected into dtb_payment and dtb_gmo_payment_method
     * @param array $data
     * @param array payment method name $Payments
     */
    public function savePaymentData($data, $Payments) {
        $objMdl = & PluginUtil::getInstance($this->app);
        $data['enable_security_code'] = '1';
        // 接続先切替機能
        switch ($data['connect_server_type']) {
            case '1':
                $data['server_url'] = $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_SERVER_URL_TEST'];
                $data['kanri_server_url'] = $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_KANRI_URL_TEST'];
                break;
            case '2':
                $data['server_url'] = $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_SERVER_URL_PROD'];
                $data['kanri_server_url'] = $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_KANRI_URL_PROD'];
                break;
            case '3':
                break;
            default:
        }
        // Regist subdata
        $objMdl->registerUserSettings($data);

        // Delete all payment method at dtb dtb_gmo_payment_method
        $GmoPaymentRepo = $this->app['eccube.plugin.gmo_pg.repository.gmo_payment_method'];
        $GmoPaymentRepo->setConfig($this->app['config']['GmoPaymentGateway']['const']);
        // Get all payment Id of this module
        $listId = $GmoPaymentRepo->getPaymentByCode(true, $this->app);

        // チェックされた決済を登録
        $installedPayment = array();
        foreach ($data['enable_payment_type'] as $paymentTypeId) {
            //インストールされていなければ新規作成
            $id = $this->savePayment($paymentTypeId, $Payments);
            $this->saveGmoPayment($id, $paymentTypeId, $Payments);

            $installedPayment[] = $id;
        }

        // チェックされていない決済を削除
        if (!empty($listId)) {
            foreach ((array) $listId as $paymentId) {
                if (!in_array($paymentId["id"], $installedPayment)) {


                    $removeGmoPaymentMethod = $this->app['eccube.plugin.gmo_pg.repository.gmo_payment_method']->find($paymentId["id"]);
                    if (!empty($removeGmoPaymentMethod)) {
                        $removeGmoPaymentMethod->setDelFlg(1);
                        $this->app['orm.em']->persist($removeGmoPaymentMethod);
                    }

                    $removePayment = $this->app['eccube.repository.payment']->find($paymentId["id"]);
                    if (!empty($removePayment)) {
                        $removePayment->setDelFlg(1);
                        $this->app['orm.em']->persist($removePayment);
                    }

                    $this->app['orm.em']->flush();
                }
            }
        }

        // dtb_page_lauoutにも登録
        $this->registPaylayout();
    }

    /**
     * Get default value of payment setting
     * @param integer $payment_type_id
     * @return array default
     */
    function getDefaultPaymentConfig($payment_type_id) {
        $listData = array();
        $listData['charge'] = '0';
        $listData['rule_max'] = '1';

        switch ($payment_type_id) {
            case $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_CREDIT']:
            case $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_REGIST_CREDIT']:
            case $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_CREDIT_CHECK']:
            case $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_CREDIT_SAUTH']:
                $listData['upper_rule'] = $this->app['config']['GmoPaymentGateway']['const']['CREDIT_RULE_MAX'];
                break;
            case $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_CVS']:
                $listData['upper_rule'] = $this->app['config']['GmoPaymentGateway']['const']['CONVENI_RULE_MAX'];
                break;
            case $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_PAYEASY']:
            case $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_ATM']:
                $listData['upper_rule'] = $this->app['config']['GmoPaymentGateway']['const']['PAYEASY_RULE_MAX'];
                break;
            case $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_RAKUTEN_ID']:
                $listData['upper_rule'] = $this->app['config']['GmoPaymentGateway']['const']['RAKUTEN_ID_RULE_MAX'];
                break;
            case $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_MOBILEEDY']:
                $listData['upper_rule'] = $this->app['config']['GmoPaymentGateway']['const']['EDY_RULE_MAX'];
                break;
            case $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_MOBILESUICA']:
                $listData['upper_rule'] = $this->app['config']['GmoPaymentGateway']['const']['SUICA_RULE_MAX'];
                break;
            case $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_PAYPAL']:
                $listData['upper_rule'] = $this->app['config']['GmoPaymentGateway']['const']['PAYPAL_RULE_MAX'];
                break;
            case $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_IDNET']:
                $listData['upper_rule'] = $this->app['config']['GmoPaymentGateway']['const']['NETID_RULE_MAX'];
                break;
            case $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_WEBMONEY']:
                $listData['upper_rule'] = $this->app['config']['GmoPaymentGateway']['const']['WEBMONEY_RULE_MAX'];
                break;
            case $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_AU']:
            case $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_AUCONTINUANCE']:
                $listData['upper_rule'] = $this->app['config']['GmoPaymentGateway']['const']['AU_RULE_MAX'];
                break;
            case $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_DOCOMO']:
            case $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_DOCOMOCONTINUANCE']:
                $listData['upper_rule'] = $this->app['config']['GmoPaymentGateway']['const']['DOCOMO_RULE_MAX'];
                break;
            case $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_TOKEN']:
                $listData['upper_rule'] = $this->app['config']['GmoPaymentGateway']['const']['TOKEN_RULE_MAX'];
                break;
            case $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_SB']:
                $listData['upper_rule'] = $this->app['config']['GmoPaymentGateway']['const']['SB_RULE_MAX'];
        }
        $listData['upper_rule_max'] = $listData['upper_rule'];
        return $listData;
    }

    /**
     * Insert or update payment that user selected
     * @param integer $paymentTypeId
     * @param array $Payments
     * @return payment_id
     */
    public function savePayment($paymentTypeId, $Payments) {
        $arrDefault = $this->getDefaultPaymentConfig($paymentTypeId);

        //Get GmoPaymentMethod of this paymentTypeId without considering del_flg
        $Payment = $this->app['orm.em']->getRepository('\Plugin\GmoPaymentGateway\Entity\GmoPaymentMethod')->getPaymentByType($paymentTypeId, true, $this->app);

        // If no such data exists, create a new one
        if (is_null($Payment)) {
            $Payment = $this->app['orm.em']->getRepository('\Eccube\Entity\Payment')->findOrCreate(0);
        }

        //Get payment from dtb_payment by id, with option to including or excluding deleted record
        $PaymentMethods = $this->app['orm.em']->getRepository('\Plugin\GmoPaymentGateway\Entity\GmoPaymentMethod')->getAllPaymentMethods($Payment->getId(), true, $this->app);
        if (!is_null($PaymentMethods)) {
            if ($PaymentMethods->getDelFlg() == 1) {
                $PaymentMethods->setUpdateDate(new \DateTime());
                $PaymentMethods->setDelFlg(0);
            }
            if ($Payment->getDelFlg() == 1) {
                $Payment->setUpdateDate(new \DateTime());
                $Payment->setDelFlg(0);
            }

            return $Payment->getId();
        }

        // If data exists, update some info, but keep value in memo05
        $Payment->setMethod($Payments[$paymentTypeId]);
        $Payment->setFixFlg(1);
        // $Payment->setRuleMin(1);
        if ($paymentTypeId == $this->app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_RAKUTEN_ID']) {
            $Payment->setRuleMin(100);
        } else {
            $Payment->setRuleMin(1);
        }
        $Payment->setCharge($arrDefault['charge']);
        $Payment->setUpdateDate(new \DateTime());
        $Payment->setCreateDate(new \DateTime());
        $Payment->setDelFlg(0);
        if (!empty($arrDefault['upper_rule_max'])) {
            $Payment->setRuleMax($arrDefault['upper_rule_max']);
        }
        $this->app['orm.em']->persist($Payment);
        $this->app['orm.em']->flush();
        return $Payment->getId();
    }

    /**
     * Insert or update dtb_gmo_payment_method
     * @param integer $id
     * @param integer $paymentId
     * @param array $Payments
     */
    public function saveGmoPayment($id, $paymentTypeId, $Payments) {

        $objMdl = & PluginUtil::getInstance($this->app);
        $pluginCode = $objMdl->getCode(true);

        $GmoPayment = $this->app['orm.em']->getRepository('\Plugin\GmoPaymentGateway\Entity\GmoPaymentMethod')->getGmoPayment('id', $id, true, $this->app);

        if (is_null($GmoPayment)) {
            // Create new payment
            $GmoPayment = $this->app['eccube.plugin.gmo_pg.repository.gmo_payment_method']->findOrCreate(0);
        }

        $GmoPayment->setId($id);
        $GmoPayment->setMethod($Payments[$paymentTypeId]);
        $GmoPayment->setDelFlg(0);
        $GmoPayment->setUpdateDate(new \DateTime());
        $GmoPayment->setCreateDate(new \DateTime());
        $GmoPayment->setMemo03($paymentTypeId);
        $GmoPayment->setCode($pluginCode);
        $this->app['orm.em']->persist($GmoPayment);
        $this->app['orm.em']->flush();
    }

    /**
     * Register pay layout
     */
    public function registPaylayout() {
        $url = "gmo_shopping_payment";
        $url_rakuten = "gmo_shopping_rakuten_result";
        $DeviceType = $this->app['eccube.repository.master.device_type']->find(10);
        $PageLayout = $this->app['eccube.repository.page_layout']->findOneBy(array('url' => $url));
        if (is_null($PageLayout)) {
            $PageLayout = $this->app['eccube.repository.page_layout']->newPageLayout($DeviceType);
        }

        $PageLayout->setName('商品購入/GMOペイメント決済画面');
        $PageLayout->setUrl($url);
        $PageLayout->setMetaRobots('noindex');
        $PageLayout->setEditFlg('2');
        $this->app['orm.em']->persist($PageLayout);
        $this->app['orm.em']->flush();
        $this->registLayoutRakutenError($url_rakuten);
    }

    /**
     * Get pagelayout by  url 
     * @param type $url_rakuten
     * @return $object Paylayout
     */
    public function registLayoutRakutenError($url_rakuten) {
        $DeviceType = $this->app['eccube.repository.master.device_type']->find(10);
        $PageLayout = $this->app['eccube.repository.page_layout']->findOneBy(array('url' => $url_rakuten));
        if (is_null($PageLayout)) {
            $PageLayout = $this->app['eccube.repository.page_layout']->newPageLayout($DeviceType);
        }

        $PageLayout->setName('楽天決済エラー/GMOペイメント決済画面');
        $PageLayout->setUrl($url_rakuten);
        $PageLayout->setMetaRobots('noindex');
        $PageLayout->setEditFlg('2');
        $PageLayout->setFileName('/shopping/rakutenResult/0');
        $this->app['orm.em']->persist($PageLayout);
        $this->app['orm.em']->flush();
    }

    /**
     * If Token -> Credit : delete payment option of Token, insert payment option for Credit
     * If Credit -> Token : delete payment option of Credit, insert payment option for Token
     * @param type $prevCreditId
     * @param type $afterCreditId
     */
    protected function updatePaymentOption($prevCreditId, $afterCreditId) {
        //Get prev payment method
        $PrevPaymentMethod = $this->app['eccube.plugin.gmo_pg.repository.gmo_payment_method']->getPaymentByType($prevCreditId, false, $this->app);
        if (empty($PrevPaymentMethod)) {
            return;
        }
        //Get prev payment option
        $PrevPaymentOption = $this->app['eccube.repository.payment_option']->findBy(array('payment_id' => $PrevPaymentMethod->getId()));
        if (empty($PrevPaymentOption)) {
            return;
        }
        //Get after payment
        $AfterPayment = $this->app['eccube.plugin.gmo_pg.repository.gmo_payment_method']->getPaymentByType($afterCreditId, false, $this->app);        

        // Add payment option for after-credit payment
        foreach ($PrevPaymentOption as $option) {
            $Delivery = $option->getDelivery();

            $AfterPaymentOption = new \Eccube\Entity\PaymentOption();
            $AfterPaymentOption
                    ->setPaymentId($AfterPayment->getId())
                    ->setPayment($AfterPayment)
                    ->setDeliveryId($Delivery->getId())
                    ->setDelivery($Delivery);

            // Remove payment options for previous Credit
            $Delivery->removePaymentOption($option);

            // Add payment option for after Credit
            $Delivery->addPaymentOption($AfterPaymentOption);

            // Delete payment options of previous credit physically
            $this->app['orm.em']->remove($option);
            $this->app['orm.em']->persist($Delivery);
            //$this->app['orm.em']->persist($AfterPaymentOption);            
        }

        // 
        $this->app['orm.em']->flush();
    }
    
    /**
     * If Token -> Credit : Get data setting Token tranfer to Credit
     * If Credit -> Token : Get data setting Credit tranfer to Token
     * @param type $prevCreditId
     * @param type $afterCreditId
     * return void
     */
    protected function updatePaymentMethods($prevCreditId, $afterCreditId) {
        $PrevPaymentMethod = $this->app['eccube.plugin.gmo_pg.repository.gmo_payment_method']->getPaymentByType($prevCreditId, false, $this->app);
        if(empty($PrevPaymentMethod)){
            return;
        }
        //Get PrevPayment
        $PrevPayment = $this->app['eccube.plugin.gmo_pg.repository.gmo_payment_method']->getAllPaymentMethods($PrevPaymentMethod->getId(), true, $this->app);
        if(empty($PrevPayment)){
            return;
        }
        //Get AfterPayment
        $AfterPayment = $this->app['eccube.plugin.gmo_pg.repository.gmo_payment_method']->getPaymentByType($afterCreditId, false, $this->app);
        if(empty($AfterPayment)){
            return;
        }
        // Update data payment #dtb_payment 
        $AfterPayment
                ->setMethod($PrevPayment->getMethod())
                ->setCharge($PrevPayment->getCharge())
                ->setRuleMax($PrevPayment->getRuleMax())
                ->setFixFlg($PrevPayment->getFixFlg())
                ->setRuleMin($PrevPayment->getRuleMin())
                ->setRuleMax($PrevPayment->getRuleMax())
                ->setPaymentImage($PrevPayment->getPaymentImage())
                ->setCreator($PrevPayment->getCreator())
                ->setChargeFlg($PrevPayment->getChargeFlg());

        //Update data gmo payment #dtb_gmo_payment_method
        $PrevGmoPayment = $this->app['eccube.plugin.gmo_pg.repository.gmo_payment_method']->getGmoPayment('id', $PrevPaymentMethod->getId(), true, $this->app);
        $AfterGmoPayment = $this->app['eccube.plugin.gmo_pg.repository.gmo_payment_method']->findOneBy(array('id' => $AfterPayment->getId()));
        $AfterGmoPayment
                ->setMethod($PrevGmoPayment->getMethod())
                ->setMemo05($PrevGmoPayment->getMemo05());
        $this->app['orm.em']->persist($AfterPayment);
        $this->app['orm.em']->persist($AfterGmoPayment);
        $this->app['orm.em']->flush();
    }

}
