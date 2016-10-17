<?php
/*
 * Copyright(c) 2015 GMO Payment Gateway, Inc. All rights reserved.
 * http://www.gmo-pg.com/
 */

namespace Plugin\GmoPaymentGateway\Controller\Helper;
use Plugin\GmoPaymentGateway\GmoPaymentGateway;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Payment Edit render helper class
 */
class PageHelper_PaymentEdit
{

    public static function appendHTML($source, $insert)
    {
        $crawler = new Crawler($source);
        $html = GmoPaymentGateway::getHtml($crawler);
        try {
            $oldHtml = $crawler->filter('#aside_wrap .col-md-9 .box')->html();
            // 3.0.9以上
            if (version_compare('3.0.9', \Eccube\Common\Constant::VERSION, '<=')) {
                $oldHtml = '<div id="detail_box" class="box">' . $oldHtml . '</div>';
            } else {
                $oldHtml = '<div class="box">' . $oldHtml . '</div>';
            }
            $newHtml = $oldHtml . $insert;
            $html = str_replace($oldHtml, $newHtml, $html);
        } catch (\InvalidArgumentException $e) {
            
        }
        return html_entity_decode($html, ENT_NOQUOTES, 'UTF-8');

    }

    public static function renderFragment(\Eccube\Application $app, \Plugin\GmoPaymentGateway\Entity\GmoPaymentMethod $GmoPaymentMethod)
    {
        $insert = '';
        switch ($GmoPaymentMethod->getMemo03()) {

            //PayEasy
            case $app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_PAYEASY']:
                $insert = PageHelper_PaymentEdit::renderPayEasyFragment($app, $GmoPaymentMethod);
                break;

            // Cvs
            case $app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_CVS']:
                $insert = PageHelper_PaymentEdit::renderCvsFragment($app, $GmoPaymentMethod);
                break;

            // PayEasy ATM
            case $app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_ATM']:
                $insert = PageHelper_PaymentEdit::renderAtmFragment($app, $GmoPaymentMethod);
                break;

            // Credit
            case $app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_CREDIT']:
                $insert = PageHelper_PaymentEdit::renderCreditFragment($app, $GmoPaymentMethod);
                break;

            // RegistCredit
            case $app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_REGIST_CREDIT']:
                $insert = PageHelper_PaymentEdit::renderRegisCreditFragment($app, $GmoPaymentMethod);
                break;
            
            // RakutenID
            case $app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_RAKUTEN_ID']:
                $insert = PageHelper_PaymentEdit::renderRakutenIdFragment($app, $GmoPaymentMethod);
                break;

            // Token
            case $app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_TOKEN']:
                $insert = PageHelper_PaymentEdit::renderTokenFragment($app, $GmoPaymentMethod);
                break;

            default:

                break;
        }
        return $insert;
    }

    public static function renderCvsFragment(\Eccube\Application $app, \Plugin\GmoPaymentGateway\Entity\GmoPaymentMethod $GmoPaymentMethod)
    {

        $appendTwig = 'GmoPaymentGateway/View/admin/fragment_cvs.twig';
        $form = $app['form.factory']->createBuilder('payment_register')->getForm();

        if ($app['request']->getMethod() === 'POST') {
            $form->handleRequest($app['request']);
        } else {
            $memo05 = $GmoPaymentMethod->getMemo05();
            if (!empty($memo05)) {
                PageHelper_PaymentEdit::setCvsData($form, $memo05);
            } else {
                PageHelper_PaymentEdit::setDefaultDataCvs($form);
            }
        }

        $insert = $app['twig']->render($appendTwig, array(
            'form' => $form->createView()
        ));

        return $insert;
    }

    public static function renderPayEasyFragment(\Eccube\Application $app, \Plugin\GmoPaymentGateway\Entity\GmoPaymentMethod $GmoPaymentMethod)
    {

        $appendTwig = 'GmoPaymentGateway/View/admin/fragment_payeasy.twig';
        $form = $app['form.factory']->createBuilder('payment_register')->getForm();
        if ($app['request']->getMethod() === 'POST') {
            $form->handleRequest($app['request']);
        } else {
            $memo05 = $GmoPaymentMethod->getMemo05();
            if (!empty($memo05)) {
                PageHelper_PaymentEdit::setPayEasyData($form, $memo05);
            } else {
                PageHelper_PaymentEdit::setDefaultDataPayEasy($form);
            }
        }

        $insert = $app['twig']->render($appendTwig, array(
            'form' => $form->createView()
        ));

        return $insert;
    }

    public static function renderRegisCreditFragment(\Eccube\Application $app, \Plugin\GmoPaymentGateway\Entity\GmoPaymentMethod $GmoPaymentMethod)
    {

        $appendTwig = 'GmoPaymentGateway/View/admin/fragment_regist_credit.twig';
        $form = $app['form.factory']->createBuilder('payment_register')->getForm();
        if ($app['request']->getMethod() === 'POST') {
            $form->handleRequest($app['request']);
        } else {
            $memo05 = $GmoPaymentMethod->getMemo05();
            if (!empty($memo05)) {
                PageHelper_PaymentEdit::setRegisCreditData($form, $memo05);
            } else {
                PageHelper_PaymentEdit::setDefaultDataRegistCredit($form);
            }
        }

        $insert = $app['twig']->render($appendTwig, array(
            'form' => $form->createView()
        ));

        return $insert;
    }

    public static function renderAtmFragment(\Eccube\Application $app, \Plugin\GmoPaymentGateway\Entity\GmoPaymentMethod $GmoPaymentMethod)
    {

        $appendTwig = 'GmoPaymentGateway/View/admin/fragment_atm.twig';
        $form = $app['form.factory']->createBuilder('payment_register')->getForm();

        if ($app['request']->getMethod() === 'POST') {
            $form->handleRequest($app['request']);
        } else {
            $memo05 = $GmoPaymentMethod->getMemo05();
            if (!empty($memo05)) {
                PageHelper_PaymentEdit::setAtmData($form, $memo05);
            } else {
                // set default values on some fields
                PageHelper_PaymentEdit::setDefaultDataATMPayEasy($form);
            }
        }

        $insert = $app['twig']->render($appendTwig, array(
            'form' => $form->createView()
        ));

        return $insert;
    }

    public static function renderCreditFragment(\Eccube\Application $app, \Plugin\GmoPaymentGateway\Entity\GmoPaymentMethod $GmoPaymentMethod)
    {

        $appendTwig = 'GmoPaymentGateway/View/admin/fragment_credit.twig';
        $form = $app['form.factory']->createBuilder('payment_register')->getForm();

        if ($app['request']->getMethod() === 'POST') {
            $form->handleRequest($app['request']);
        } else {
            $memo05 = $GmoPaymentMethod->getMemo05();
            if (!empty($memo05)) {
                PageHelper_PaymentEdit::setCreditData($form, $memo05);
            } else {
                PageHelper_PaymentEdit::setDefaultDataCredit($form);
            }
        }

        $insert = $app['twig']->render($appendTwig, array(
            'form' => $form->createView()
        ));

        return $insert;
    }

    public static function renderRegistCreditFragment(\Eccube\Application $app, \Plugin\GmoPaymentGateway\Entity\GmoPaymentMethod $GmoPaymentMethod)
    {

        $appendTwig = 'GmoPaymentGateway/View/admin/fragment_regist_credit.twig';
        $form = $app['form.factory']->createBuilder('payment_register')->getForm();

        if ($app['request']->getMethod() === 'POST') {
            $form->handleRequest($app['request']);
        } else {
            $memo05 = $GmoPaymentMethod->getMemo05();
            if (!empty($memo05)) {
                PageHelper_PaymentEdit::setAtmData($form, $memo05);
            }
        }

        $insert = $app['twig']->render($appendTwig, array(
            'form' => $form->createView()
        ));

        return $insert;
    }

    public static function renderRakutenIdFragment(\Eccube\Application $app, \Plugin\GmoPaymentGateway\Entity\GmoPaymentMethod $GmoPaymentMethod)
    {

        $appendTwig = 'GmoPaymentGateway/View/admin/fragment_rakuten_id.twig';
        $form = $app['form.factory']->createBuilder('payment_register')->getForm();

        if ($app['request']->getMethod() === 'POST') {
            $form->handleRequest($app['request']);
        } else {
            $memo05 = $GmoPaymentMethod->getMemo05();
            if (!empty($memo05)) {
                PageHelper_PaymentEdit::setRakutenIdData($form, $memo05);
            }
        }

        $insert = $app['twig']->render($appendTwig, array(
            'form' => $form->createView()
        ));

        return $insert;
    }

    public static function renderTokenFragment(\Eccube\Application $app, \Plugin\GmoPaymentGateway\Entity\GmoPaymentMethod $GmoPaymentMethod)
    {
        $appendTwig = 'GmoPaymentGateway/View/admin/fragment_token.twig';
        $form = $app['form.factory']->createBuilder('payment_register')->getForm();

        if ($app['request']->getMethod() === 'POST') {
            $form->handleRequest($app['request']);
        } else {
            $memo05 = $GmoPaymentMethod->getMemo05();
            if (!empty($memo05)) {
                PageHelper_PaymentEdit::setTokenData($form, $memo05);
            } else {
                PageHelper_PaymentEdit::setDefaultDataToken($form);
            }
        }

        $insert = $app['twig']->render($appendTwig, array(
            'form' => $form->createView()
        ));

        return $insert;
    }

    
    /////////////////////////////////Set/Get Data //////////////////////////////


    public static function getDataFromForm($app, &$form, $GmoPaymentMethod)
    {
        $memo03 = $GmoPaymentMethod->getMemo03();
        $memo05 = $GmoPaymentMethod->getMemo05();
        $data = array();
        switch ($memo03) {
            //PayEasy
            case $app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_PAYEASY']:
                $data = PageHelper_PaymentEdit::getPayEasyData($form, $memo05);
                break;

            // Cvs
            case $app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_CVS']:
                $data = PageHelper_PaymentEdit::getCvsData($form, $memo05);
                break;

            // PayEasy ATM
            case $app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_ATM']:
                $data = PageHelper_PaymentEdit::getAtmData($form, $memo05);
                break;

            // Credit
            case $app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_CREDIT']:
                $data = PageHelper_PaymentEdit::getCreditData($form, $memo05);
                break;

            // RegistCredit
            case $app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_REGIST_CREDIT']:
                $data = PageHelper_PaymentEdit::getRegistCreditData($form);
                break;
            
            // RakutenId
            case $app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_RAKUTEN_ID']:
                $data = PageHelper_PaymentEdit::getRakutenIdData($form);
                break;

            // Token
            case $app['config']['GmoPaymentGateway']['const']['PG_MULPAY_PAYID_TOKEN']:
                $data = PageHelper_PaymentEdit::getTokenData($form, $memo05);
                break;

            default:
                break;
        }
        return $data;
    }

    //////////PayEasy start////////////
    public static function setDefaultDataPayEasy(&$form)
    {
        $form['enable_mail']->setData(1);
        $form['enable_cvs_mails']->setData(0);
        $form['use_securitycd']->setData(0);
        $form['use_securitycd_option']->setData(0);
        $form['TdFlag']->setData(0);

        $form['order_mail_title1']->setData('お支払いについて');

        $path = __DIR__ . '/../../View/admin/mail/payeasy.twig';

        $content = file_get_contents($path);
        $form['order_mail_body1']->setData($content);
    }

    public static function setPayEasyData(&$form, $memo05)
    {
        $data = unserialize($memo05);

        $form['enable_mail']->setData($data['enable_mail']);
        $form['enable_cvs_mails']->setData($data['enable_cvs_mails']);

        $form['use_securitycd']->setData($data['use_securitycd']);
        $form['use_securitycd_option']->setData($data['use_securitycd_option']);

        $form['TdFlag']->setData($data['TdFlag']);
        $form['TdTenantName']->setData($data['TdTenantName']);

        $form['ClientField1']->setData($data['ClientField1']);
        $form['ClientField2']->setData($data['ClientField2']);

        $form['payment_term_day']->setData($data['PaymentTermDay']);
        $form['PaymentTermSec']->setData($data['PaymentTermSec']);

        $form['receipts_disp1']->setData($data['ReceiptsDisp1']);
        $form['receipts_disp2']->setData($data['ReceiptsDisp2']);
        $form['receipts_disp3']->setData($data['ReceiptsDisp3']);
        $form['receipts_disp4']->setData($data['ReceiptsDisp4']);
        $form['receipts_disp5']->setData($data['ReceiptsDisp5']);
        $form['receipts_disp6']->setData($data['ReceiptsDisp6']);
        $form['receipts_disp7']->setData($data['ReceiptsDisp7']);
        $form['receipts_disp8']->setData($data['ReceiptsDisp8']);
        $form['receipts_disp9']->setData($data['ReceiptsDisp9']);
        $form['receipts_disp10']->setData($data['ReceiptsDisp10']);
        $form['receipts_disp11']->setData($data['ReceiptsDisp11']);

        $form['receipts_disp12_1']->setData($data['ReceiptsDisp12_1']);
        $form['receipts_disp12_2']->setData($data['ReceiptsDisp12_2']);
        $form['receipts_disp12_3']->setData($data['ReceiptsDisp12_3']);

        $form['receipts_disp13_1']->setData($data['ReceiptsDisp13_1']);
        $form['receipts_disp13_2']->setData($data['ReceiptsDisp13_2']);
        $form['receipts_disp13_3']->setData($data['ReceiptsDisp13_3']);
        $form['receipts_disp13_4']->setData($data['ReceiptsDisp13_4']);

        $form['EdyAddInfo1']->setData($data['EdyAddInfo1']);
        $form['EdyAddInfo2']->setData($data['EdyAddInfo2']);

        $form['SuicaAddInfo1']->setData($data['SuicaAddInfo1']);
        $form['SuicaAddInfo2']->setData($data['SuicaAddInfo2']);
        $form['SuicaAddInfo3']->setData($data['SuicaAddInfo3']);
        $form['SuicaAddInfo4']->setData($data['SuicaAddInfo4']);

        $form['Currency']->setData($data['Currency']);
        $form['order_mail_title1']->setData($data['order_mail_title1']);
        $form['order_mail_body1']->setData($data['order_mail_body1']);

        $form['SelectPageCall_Mobile']->setData($data['SelectPageCall_Mobile']);
        $form['SelectPageCall_PC']->setData($data['SelectPageCall_PC']);
    }

    public static function getPayEasyData(&$form)
    {
        $data = array();
        $data['enable_mail'] = $form['enable_mail']->getData();
        $data['enable_cvs_mails'] = $form['enable_cvs_mails']->getData();
        $data['use_securitycd'] = $form['use_securitycd']->getData();
        $data['use_securitycd_option'] = $form['use_securitycd_option']->getData();
        $data['TdFlag'] = $form['TdFlag']->getData();
        $data['TdTenantName'] = $form['TdTenantName']->getData();

        $data['ClientField1'] = $form['ClientField1']->getData();
        $data['ClientField2'] = $form['ClientField2']->getData();

        $data['PaymentTermDay'] = $form['payment_term_day']->getData();
        $data['PaymentTermSec'] = $form['PaymentTermSec']->getData();

        $data['ReceiptsDisp1'] = mb_convert_kana($form['receipts_disp1']->getData(), 'KASV', 'UTF-8');
        $data['ReceiptsDisp2'] = mb_convert_kana($form['receipts_disp2']->getData(), 'KASV', 'UTF-8');
        $data['ReceiptsDisp3'] = mb_convert_kana($form['receipts_disp3']->getData(), 'KASV', 'UTF-8');
        $data['ReceiptsDisp4'] = mb_convert_kana($form['receipts_disp4']->getData(), 'KASV', 'UTF-8');
        $data['ReceiptsDisp5'] = mb_convert_kana($form['receipts_disp5']->getData(), 'KASV', 'UTF-8');
        $data['ReceiptsDisp6'] = mb_convert_kana($form['receipts_disp6']->getData(), 'KASV', 'UTF-8');
        $data['ReceiptsDisp7'] = mb_convert_kana($form['receipts_disp7']->getData(), 'KASV', 'UTF-8');
        $data['ReceiptsDisp8'] = mb_convert_kana($form['receipts_disp8']->getData(), 'KASV', 'UTF-8');
        $data['ReceiptsDisp9'] = mb_convert_kana($form['receipts_disp9']->getData(), 'KASV', 'UTF-8');
        $data['ReceiptsDisp10'] = mb_convert_kana($form['receipts_disp10']->getData(), 'KASV', 'UTF-8');
        $data['ReceiptsDisp11'] = mb_convert_kana($form['receipts_disp11']->getData(), 'KV', 'UTF-8');

        $data['ReceiptsDisp12_1'] = $form['receipts_disp12_1']->getData();
        $data['ReceiptsDisp12_2'] = $form['receipts_disp12_2']->getData();
        $data['ReceiptsDisp12_3'] = $form['receipts_disp12_3']->getData();

        $data['ReceiptsDisp13_1'] = $form['receipts_disp13_1']->getData();
        $data['ReceiptsDisp13_2'] = $form['receipts_disp13_2']->getData();
        $data['ReceiptsDisp13_3'] = $form['receipts_disp13_3']->getData();
        $data['ReceiptsDisp13_4'] = $form['receipts_disp13_4']->getData();

        $data['EdyAddInfo1'] = $form['EdyAddInfo1']->getData();
        $data['EdyAddInfo2'] = $form['EdyAddInfo2']->getData();

        $data['SuicaAddInfo1'] = $form['SuicaAddInfo1']->getData();
        $data['SuicaAddInfo2'] = $form['SuicaAddInfo2']->getData();
        $data['SuicaAddInfo3'] = $form['SuicaAddInfo3']->getData();
        $data['SuicaAddInfo4'] = $form['SuicaAddInfo4']->getData();

        $data['Currency'] = $form['Currency']->getData();
        $data['Currency'] = 'JPY';
        $data['order_mail_title1'] = $form['order_mail_title1']->getData();
        $data['order_mail_body1'] = $form['order_mail_body1']->getData();

        $data['SelectPageCall_Mobile'] = $form['SelectPageCall_Mobile']->getData();
        $data['SelectPageCall_PC'] = $form['SelectPageCall_PC']->getData();

        return $data;
    }

    //////////PayEasy end////////////
    //
    //
    //////////CVS start////////////
    public static function setDefaultDataCvs(&$form)
    {
        $form['use_securitycd']->setData(0);
        $form['use_securitycd_option']->setData(0);
        $form['TdFlag']->setData(0);
        $form['Currency']->setData('JPY');

        $form['enable_mail']->setData(1);
        $form['order_mail_title_00001']->setData('ローソンでのお支払い');
        $form['order_mail_title_00002']->setData('ファミリーマートでのお支払い');
        $form['order_mail_title_00004']->setData('サークルKサンクスでのお支払い');
        $form['order_mail_title_00005']->setData('ミニストップでのお支払い');
        $form['order_mail_title_00006']->setData('デイリーヤマザキでのお支払い');
        $form['order_mail_title_00007']->setData('セブンイレブンでのお支払い');

        $path = __DIR__ . '/../../View/admin/mail/cvs_00001.twig';
        $content = file_get_contents($path);
        $form['order_mail_body_00001']->setData($content);

        $path = __DIR__ . '/../../View/admin/mail/cvs_00002.twig';
        $content = file_get_contents($path);
        $form['order_mail_body_00002']->setData($content);

        $path = __DIR__ . '/../../View/admin/mail/cvs_00004.twig';
        $content = file_get_contents($path);
        $form['order_mail_body_00004']->setData($content);

        $path = __DIR__ . '/../../View/admin/mail/cvs_00005.twig';
        $content = file_get_contents($path);
        $form['order_mail_body_00005']->setData($content);

        $path = __DIR__ . '/../../View/admin/mail/cvs_00006.twig';
        $content = file_get_contents($path);
        $form['order_mail_body_00006']->setData($content);

        $path = __DIR__ . '/../../View/admin/mail/cvs_00007.twig';
        $content = file_get_contents($path);
        $form['order_mail_body_00007']->setData($content);
    }

    public static function setCvsData(&$form, $memo05)
    {

        $data = unserialize($memo05);

        $form['conveni']->setData($data['conveni']);
        $form['payment_term_day']->setData($data['PaymentTermDay']);

        $form['enable_mail']->setData($data['enable_mail']);
        $form['enable_cvs_mails']->setData($data['enable_cvs_mails']);

        $form['PaymentTermSec']->setData($data['PaymentTermSec']);

        $form['register_disp1']->setData($data['RegisterDisp1']);
        $form['register_disp2']->setData($data['RegisterDisp2']);
        $form['register_disp3']->setData($data['RegisterDisp3']);
        $form['register_disp4']->setData($data['RegisterDisp4']);
        $form['register_disp5']->setData($data['RegisterDisp5']);
        $form['register_disp6']->setData($data['RegisterDisp6']);
        $form['register_disp7']->setData($data['RegisterDisp7']);
        $form['register_disp8']->setData($data['RegisterDisp8']);


        $form['receipts_disp1']->setData($data['ReceiptsDisp1']);
        $form['receipts_disp2']->setData($data['ReceiptsDisp2']);
        $form['receipts_disp3']->setData($data['ReceiptsDisp3']);
        $form['receipts_disp4']->setData($data['ReceiptsDisp4']);
        $form['receipts_disp5']->setData($data['ReceiptsDisp5']);
        $form['receipts_disp6']->setData($data['ReceiptsDisp6']);
        $form['receipts_disp7']->setData($data['ReceiptsDisp7']);
        $form['receipts_disp8']->setData($data['ReceiptsDisp8']);
        $form['receipts_disp9']->setData($data['ReceiptsDisp9']);
        $form['receipts_disp10']->setData($data['ReceiptsDisp10']);
        $form['receipts_disp11']->setData($data['ReceiptsDisp11']);

        $form['receipts_disp12_1']->setData($data['ReceiptsDisp12_1']);
        $form['receipts_disp12_2']->setData($data['ReceiptsDisp12_2']);
        $form['receipts_disp12_3']->setData($data['ReceiptsDisp12_3']);

        $form['receipts_disp13_1']->setData($data['ReceiptsDisp13_1']);
        $form['receipts_disp13_2']->setData($data['ReceiptsDisp13_2']);
        $form['receipts_disp13_3']->setData($data['ReceiptsDisp13_3']);
        $form['receipts_disp13_4']->setData($data['ReceiptsDisp13_4']);


        $form['order_mail_title1']->setData($data['order_mail_title1']);
        $form['order_mail_body1']->setData($data['order_mail_body1']);

        $form['ClientField1']->setData($data['ClientField1']);
        $form['ClientField2']->setData($data['ClientField2']);

        $form['order_mail_title_00001']->setData($data['order_mail_title_00001']);
        $form['order_mail_body_00001']->setData($data['order_mail_body_00001']);

        $form['order_mail_title_00002']->setData($data['order_mail_title_00002']);
        $form['order_mail_body_00002']->setData($data['order_mail_body_00002']);

        $form['order_mail_title_00004']->setData($data['order_mail_title_00004']);
        $form['order_mail_body_00004']->setData($data['order_mail_body_00004']);

        $form['order_mail_title_00005']->setData($data['order_mail_title_00005']);
        $form['order_mail_body_00005']->setData($data['order_mail_body_00005']);

        $form['order_mail_title_00006']->setData($data['order_mail_title_00006']);
        $form['order_mail_body_00006']->setData($data['order_mail_body_00006']);

        $form['order_mail_title_00007']->setData($data['order_mail_title_00007']);
        $form['order_mail_body_00007']->setData($data['order_mail_body_00007']);


        $form['use_securitycd']->setData($data['use_securitycd']);
        $form['use_securitycd_option']->setData($data['use_securitycd_option']);

        $form['TdFlag']->setData($data['TdFlag']);
        $form['TdTenantName']->setData($data['TdTenantName']);

        $form['EdyAddInfo1']->setData($data['EdyAddInfo1']);
        $form['EdyAddInfo2']->setData($data['EdyAddInfo2']);

        $form['SuicaAddInfo1']->setData($data['SuicaAddInfo1']);
        $form['SuicaAddInfo2']->setData($data['SuicaAddInfo2']);
        $form['SuicaAddInfo3']->setData($data['SuicaAddInfo3']);
        $form['SuicaAddInfo4']->setData($data['SuicaAddInfo4']);
        $form['Currency']->setData($data['Currency']);
    }

    public static function getCvsData(&$form)
    {
        $data = array();
        $data['enable_mail'] = $form['enable_mail']->getData();
        $data['enable_cvs_mails'] = $form['enable_cvs_mails']->getData();

        $data['use_securitycd'] = $form['use_securitycd']->getData();

        $data['use_securitycd_option'] = $form['use_securitycd_option']->getData();


        $data['TdFlag'] = $form['TdFlag']->getData();

        $data['TdTenantName'] = $form['TdTenantName']->getData();

        $data['ClientField1'] = $form['ClientField1']->getData();
        $data['ClientField2'] = $form['ClientField2']->getData();

        $data['PaymentTermDay'] = $form['payment_term_day']->getData();
        $data['PaymentTermSec'] = $form['PaymentTermSec']->getData();

        $data['RegisterDisp1'] = mb_convert_kana($form['register_disp1']->getData(), 'KASV', 'UTF-8');
        $data['RegisterDisp2'] = mb_convert_kana($form['register_disp2']->getData(), 'KASV', 'UTF-8');
        $data['RegisterDisp3'] = mb_convert_kana($form['register_disp3']->getData(), 'KASV', 'UTF-8');
        $data['RegisterDisp4'] = mb_convert_kana($form['register_disp4']->getData(), 'KASV', 'UTF-8');
        $data['RegisterDisp5'] = mb_convert_kana($form['register_disp5']->getData(), 'KASV', 'UTF-8');
        $data['RegisterDisp6'] = mb_convert_kana($form['register_disp6']->getData(), 'KASV', 'UTF-8');
        $data['RegisterDisp7'] = mb_convert_kana($form['register_disp7']->getData(), 'KASV', 'UTF-8');
        $data['RegisterDisp8'] = mb_convert_kana($form['register_disp8']->getData(), 'KASV', 'UTF-8');

        $data['ReceiptsDisp1'] = mb_convert_kana($form['receipts_disp1']->getData(), 'KASV', 'UTF-8');
        $data['ReceiptsDisp2'] = mb_convert_kana($form['receipts_disp2']->getData(), 'KASV', 'UTF-8');
        $data['ReceiptsDisp3'] = mb_convert_kana($form['receipts_disp3']->getData(), 'KASV', 'UTF-8');
        $data['ReceiptsDisp4'] = mb_convert_kana($form['receipts_disp4']->getData(), 'KASV', 'UTF-8');
        $data['ReceiptsDisp5'] = mb_convert_kana($form['receipts_disp5']->getData(), 'KASV', 'UTF-8');
        $data['ReceiptsDisp6'] = mb_convert_kana($form['receipts_disp6']->getData(), 'KASV', 'UTF-8');
        $data['ReceiptsDisp7'] = mb_convert_kana($form['receipts_disp7']->getData(), 'KASV', 'UTF-8');
        $data['ReceiptsDisp8'] = mb_convert_kana($form['receipts_disp8']->getData(), 'KASV', 'UTF-8');
        $data['ReceiptsDisp9'] = mb_convert_kana($form['receipts_disp9']->getData(), 'KASV', 'UTF-8');
        $data['ReceiptsDisp10'] = mb_convert_kana($form['receipts_disp10']->getData(), 'KASV', 'UTF-8');
        $data['ReceiptsDisp11'] = mb_convert_kana($form['receipts_disp11']->getData(), 'KV', 'UTF-8');

        $data['ReceiptsDisp12_1'] = $form['receipts_disp12_1']->getData();
        $data['ReceiptsDisp12_2'] = $form['receipts_disp12_2']->getData();
        $data['ReceiptsDisp12_3'] = $form['receipts_disp12_3']->getData();

        $data['ReceiptsDisp13_1'] = $form['receipts_disp13_1']->getData();
        $data['ReceiptsDisp13_2'] = $form['receipts_disp13_2']->getData();
        $data['ReceiptsDisp13_3'] = $form['receipts_disp13_3']->getData();
        $data['ReceiptsDisp13_4'] = $form['receipts_disp13_4']->getData();

        $data['EdyAddInfo1'] = $form['EdyAddInfo1']->getData();
        $data['EdyAddInfo2'] = $form['EdyAddInfo2']->getData();

        $data['SuicaAddInfo1'] = $form['SuicaAddInfo1']->getData();
        $data['SuicaAddInfo2'] = $form['SuicaAddInfo2']->getData();
        $data['SuicaAddInfo3'] = $form['SuicaAddInfo3']->getData();
        $data['SuicaAddInfo4'] = $form['SuicaAddInfo4']->getData();

        $data['Currency'] = $form['Currency']->getData();

        $data['order_mail_title1'] = $form['order_mail_title1']->getData();
        $data['order_mail_body1'] = $form['order_mail_body1']->getData();

        $data['order_mail_title_00001'] = $form['order_mail_title_00001']->getData();
        $data['order_mail_body_00001'] = $form['order_mail_body_00001']->getData();

        $data['order_mail_title_00002'] = $form['order_mail_title_00002']->getData();
        $data['order_mail_body_00002'] = $form['order_mail_body_00002']->getData();

        $data['order_mail_title_00004'] = $form['order_mail_title_00004']->getData();
        $data['order_mail_body_00004'] = $form['order_mail_body_00004']->getData();

        $data['order_mail_title_00005'] = $form['order_mail_title_00005']->getData();
        $data['order_mail_body_00005'] = $form['order_mail_body_00005']->getData();

        $data['order_mail_title_00006'] = $form['order_mail_title_00006']->getData();
        $data['order_mail_body_00006'] = $form['order_mail_body_00006']->getData();

        $data['order_mail_title_00007'] = $form['order_mail_title_00007']->getData();
        $data['order_mail_body_00007'] = $form['order_mail_body_00007']->getData();

        $data['conveni'] = $form['conveni']->getData();

        return $data;
    }
    //////////CVS end////////////
    //
    //
    //////////Credit start////////////
    public static function setDefaultDataCredit(&$form)
    {
        $form['order_mail_title1']->setData('お支払いについて');
        $form['use_securitycd']->setData(0);
        $form['use_securitycd_option']->setData(1);
        $form['TdFlag']->setData(0);
        $form['Currency']->setData('JPY');
    }

    public static function setCreditData(&$form, $memo05)
    {
        $data = unserialize($memo05);

        $form['JobCd']->setData($data['JobCd']);
        $form['credit_pay_methods']->setData($data['credit_pay_methods']);
        $form['use_securitycd']->setData($data['use_securitycd']);
        $form['use_securitycd_option']->setData($data['use_securitycd_option']);
        $form['TdFlag']->setData($data['TdFlag']);
        $form['TdTenantName']->setData($data['TdTenantName']);
        $form['order_mail_title1']->setData($data['order_mail_title1']);
        $form['order_mail_body1']->setData($data['order_mail_body1']);
        $form['ClientField1']->setData($data['ClientField1']);
        $form['ClientField2']->setData($data['ClientField2']);

        $form['enable_mail']->setData($data['enable_mail']);
        $form['enable_cvs_mails']->setData($data['enable_cvs_mails']);
        $form['PaymentTermDay']->setData($data['PaymentTermDay']);
        $form['PaymentTermSec']->setData($data['PaymentTermSec']);
        $form['EdyAddInfo1']->setData($data['EdyAddInfo1']);
        $form['EdyAddInfo2']->setData($data['EdyAddInfo2']);
        $form['SuicaAddInfo1']->setData($data['SuicaAddInfo1']);
        $form['SuicaAddInfo2']->setData($data['SuicaAddInfo2']);
        $form['SuicaAddInfo3']->setData($data['SuicaAddInfo3']);
        $form['SuicaAddInfo4']->setData($data['SuicaAddInfo4']);
        $form['Currency']->setData($data['Currency']);
    }

    public static function getCreditData(&$form)
    {
        $data = array();

        $data['JobCd'] = $form['JobCd']->getData();
        $data['credit_pay_methods'] = $form['credit_pay_methods']->getData();
        $data['use_securitycd'] = $form['use_securitycd']->getData();
        $data['use_securitycd_option'] = $form['use_securitycd_option']->getData();
        $data['TdFlag'] = $form['TdFlag']->getData();
        $data['TdTenantName'] = $form['TdTenantName']->getData();
        $data['order_mail_title1'] = $form['order_mail_title1']->getData();
        $data['order_mail_body1'] = $form['order_mail_body1']->getData();
        $data['ClientField1'] = $form['ClientField1']->getData();
        $data['ClientField2'] = $form['ClientField2']->getData();
        $data['enable_mail'] = $form['enable_mail']->getData();
        $data['enable_cvs_mails'] = $form['enable_cvs_mails']->getData();
        $data['PaymentTermDay'] = $form['PaymentTermDay']->getData();
        $data['PaymentTermSec'] = $form['PaymentTermSec']->getData();
        $data['EdyAddInfo1'] = $form['EdyAddInfo1']->getData();
        $data['EdyAddInfo2'] = $form['EdyAddInfo2']->getData();
        $data['SuicaAddInfo1'] = $form['SuicaAddInfo1']->getData();
        $data['SuicaAddInfo2'] = $form['SuicaAddInfo2']->getData();
        $data['SuicaAddInfo3'] = $form['SuicaAddInfo3']->getData();
        $data['SuicaAddInfo4'] = $form['SuicaAddInfo4']->getData();
        $data['Currency'] = $form['Currency']->getData();

        return $data;
    }
    //////////Credit start////////////
    //
    //
    //////////Regis Credit start////////////
    public static function setDefaultDataRegistCredit(&$form)
    {
        $form['order_mail_title1']->setData('お支払いについて');
        $form['use_securitycd']->setData(0);
        $form['use_securitycd_option']->setData(0);
        $form['TdFlag']->setData(0);
        $form['Currency']->setData('JPY');
        $form['TdFlag']->setData(0);
        $form['enable_mail']->setData(0);
        $form['enable_cvs_mails']->setData(0);
    }

    public static function setRegisCreditData(&$form, $memo05)
    {
        $data = unserialize($memo05);

        $form['JobCd']->setData($data['JobCd']);
        $form['credit_pay_methods']->setData($data['credit_pay_methods']);
        $form['use_securitycd']->setData($data['use_securitycd']);
        $form['use_securitycd_option']->setData($data['use_securitycd_option']);
        $form['TdFlag']->setData($data['TdFlag']);
        $form['TdTenantName']->setData($data['TdTenantName']);
        $form['order_mail_title1']->setData($data['order_mail_title1']);
        $form['order_mail_body1']->setData($data['order_mail_body1']);
        $form['ClientField1']->setData($data['ClientField1']);
        $form['ClientField2']->setData($data['ClientField2']);

        $form['enable_mail']->setData($data['enable_mail']);
        $form['enable_cvs_mails']->setData($data['enable_cvs_mails']);
        $form['PaymentTermDay']->setData($data['PaymentTermDay']);
        $form['PaymentTermSec']->setData($data['PaymentTermSec']);
        $form['EdyAddInfo1']->setData($data['EdyAddInfo1']);
        $form['EdyAddInfo2']->setData($data['EdyAddInfo2']);
        $form['SuicaAddInfo1']->setData($data['SuicaAddInfo1']);
        $form['SuicaAddInfo2']->setData($data['SuicaAddInfo2']);
        $form['SuicaAddInfo3']->setData($data['SuicaAddInfo3']);
        $form['SuicaAddInfo4']->setData($data['SuicaAddInfo4']);
        $form['Currency']->setData($data['Currency']);
    }

    public static function getRegistCreditData(&$form)
    {
        $data = array();

        $data['JobCd'] = $form['JobCd']->getData();
        $data['credit_pay_methods'] = $form['credit_pay_methods']->getData();
        $data['use_securitycd'] = $form['use_securitycd']->getData();
        $data['use_securitycd_option'] = $form['use_securitycd_option']->getData();
        $data['TdFlag'] = $form['TdFlag']->getData();
        $data['TdTenantName'] = $form['TdTenantName']->getData();
        $data['order_mail_title1'] = $form['order_mail_title1']->getData();
        $data['order_mail_body1'] = $form['order_mail_body1']->getData();
        $data['ClientField1'] = $form['ClientField1']->getData();
        $data['ClientField2'] = $form['ClientField2']->getData();

        $data['enable_mail'] = $form['enable_mail']->getData();
        $data['enable_cvs_mails'] = $form['enable_cvs_mails']->getData();

        $data['PaymentTermDay'] = $form['PaymentTermDay']->getData();
        $data['PaymentTermSec'] = $form['PaymentTermSec']->getData();
        $data['EdyAddInfo1'] = $form['EdyAddInfo1']->getData();
        $data['EdyAddInfo2'] = $form['EdyAddInfo2']->getData();
        $data['SuicaAddInfo1'] = $form['SuicaAddInfo1']->getData();
        $data['SuicaAddInfo2'] = $form['SuicaAddInfo2']->getData();
        $data['SuicaAddInfo3'] = $form['SuicaAddInfo3']->getData();
        $data['SuicaAddInfo4'] = $form['SuicaAddInfo4']->getData();
        $data['Currency'] = $form['Currency']->getData();

        return $data;
    }
    //////////Regis Credit end////////////
    //
    //
    //////////Regis Credit start////////////
    public static function setAtmData(&$form, $memo05)
    {
        $data = unserialize($memo05);

        $form['enable_mail']->setData($data['enable_mail']);
        $form['enable_cvs_mails']->setData($data['enable_cvs_mails']);

        $form['use_securitycd']->setData($data['use_securitycd']);
        $form['use_securitycd_option']->setData($data['use_securitycd_option']);

        $form['TdFlag']->setData($data['TdFlag']);
        $form['TdTenantName']->setData($data['TdTenantName']);

        $form['ClientField1']->setData($data['ClientField1']);
        $form['ClientField2']->setData($data['ClientField2']);

        $form['payment_term_day']->setData($data['PaymentTermDay']);
        $form['PaymentTermSec']->setData($data['PaymentTermSec']);

        $form['register_disp1']->setData($data['RegisterDisp1']);
        $form['register_disp2']->setData($data['RegisterDisp2']);
        $form['register_disp3']->setData($data['RegisterDisp3']);
        $form['register_disp4']->setData($data['RegisterDisp4']);
        $form['register_disp5']->setData($data['RegisterDisp5']);
        $form['register_disp6']->setData($data['RegisterDisp6']);
        $form['register_disp7']->setData($data['RegisterDisp7']);
        $form['register_disp8']->setData($data['RegisterDisp8']);

        $form['receipts_disp1']->setData($data['ReceiptsDisp1']);
        $form['receipts_disp2']->setData($data['ReceiptsDisp2']);
        $form['receipts_disp3']->setData($data['ReceiptsDisp3']);
        $form['receipts_disp4']->setData($data['ReceiptsDisp4']);
        $form['receipts_disp5']->setData($data['ReceiptsDisp5']);
        $form['receipts_disp6']->setData($data['ReceiptsDisp6']);
        $form['receipts_disp7']->setData($data['ReceiptsDisp7']);
        $form['receipts_disp8']->setData($data['ReceiptsDisp8']);
        $form['receipts_disp9']->setData($data['ReceiptsDisp9']);
        $form['receipts_disp10']->setData($data['ReceiptsDisp10']);
        $form['receipts_disp11']->setData($data['ReceiptsDisp11']);

        $form['receipts_disp12_1']->setData($data['ReceiptsDisp12_1']);
        $form['receipts_disp12_2']->setData($data['ReceiptsDisp12_2']);
        $form['receipts_disp12_3']->setData($data['ReceiptsDisp12_3']);

        $form['receipts_disp13_1']->setData($data['ReceiptsDisp13_1']);
        $form['receipts_disp13_2']->setData($data['ReceiptsDisp13_2']);
        $form['receipts_disp13_3']->setData($data['ReceiptsDisp13_3']);
        $form['receipts_disp13_4']->setData($data['ReceiptsDisp13_4']);

        $form['EdyAddInfo1']->setData($data['EdyAddInfo1']);
        $form['EdyAddInfo2']->setData($data['EdyAddInfo2']);

        $form['SuicaAddInfo1']->setData($data['SuicaAddInfo1']);
        $form['SuicaAddInfo2']->setData($data['SuicaAddInfo2']);
        $form['SuicaAddInfo3']->setData($data['SuicaAddInfo3']);
        $form['SuicaAddInfo4']->setData($data['SuicaAddInfo4']);

        $form['Currency']->setData($data['Currency']);
        $form['order_mail_title1']->setData($data['order_mail_title1']);
        $form['order_mail_body1']->setData($data['order_mail_body1']);
    }

    public static function getAtmData(&$form)
    {
        $data = array();

        $data['enable_mail'] = $form['enable_mail']->getData();
        $data['enable_cvs_mails'] = $form['enable_cvs_mails']->getData();
        // $data['enable_cvs_mails'] = 0;

        $data['use_securitycd'] = $form['use_securitycd']->getData();
        // $data['use_securitycd'] = 0;
        $data['use_securitycd_option'] = $form['use_securitycd_option']->getData();
        // $data['use_securitycd_option'] = 0;

        $data['TdFlag'] = $form['TdFlag']->getData();
        // $data['TdFlag'] = 0;
        $data['TdTenantName'] = $form['TdTenantName']->getData();

        $data['ClientField1'] = $form['ClientField1']->getData();
        $data['ClientField2'] = $form['ClientField2']->getData();

        $data['PaymentTermDay'] = $form['payment_term_day']->getData();
        $data['PaymentTermSec'] = $form['PaymentTermSec']->getData();

        $data['RegisterDisp1'] = mb_convert_kana($form['register_disp1']->getData(), 'KASV', 'UTF-8');
        $data['RegisterDisp2'] = mb_convert_kana($form['register_disp2']->getData(), 'KVSA', 'UTF-8');
        $data['RegisterDisp3'] = mb_convert_kana($form['register_disp3']->getData(), 'KVSA', 'UTF-8');
        $data['RegisterDisp4'] = mb_convert_kana($form['register_disp4']->getData(), 'KVSA', 'UTF-8');
        $data['RegisterDisp5'] = mb_convert_kana($form['register_disp5']->getData(), 'KVSA', 'UTF-8');
        $data['RegisterDisp6'] = mb_convert_kana($form['register_disp6']->getData(), 'KVSA', 'UTF-8');
        $data['RegisterDisp7'] = mb_convert_kana($form['register_disp7']->getData(), 'KVSA', 'UTF-8');
        $data['RegisterDisp8'] = mb_convert_kana($form['register_disp8']->getData(), 'KVSA', 'UTF-8');

        $data['ReceiptsDisp1'] = mb_convert_kana($form['receipts_disp1']->getData(), 'KVSA', 'UTF-8');
        $data['ReceiptsDisp2'] = mb_convert_kana($form['receipts_disp2']->getData(), 'KVSA', 'UTF-8');
        $data['ReceiptsDisp3'] = mb_convert_kana($form['receipts_disp3']->getData(), 'KVSA', 'UTF-8');
        $data['ReceiptsDisp4'] = mb_convert_kana($form['receipts_disp4']->getData(), 'KVSA', 'UTF-8');
        $data['ReceiptsDisp5'] = mb_convert_kana($form['receipts_disp5']->getData(), 'KVSA', 'UTF-8');
        $data['ReceiptsDisp6'] = mb_convert_kana($form['receipts_disp6']->getData(), 'KVSA', 'UTF-8');
        $data['ReceiptsDisp7'] = mb_convert_kana($form['receipts_disp7']->getData(), 'KVSA', 'UTF-8');
        $data['ReceiptsDisp8'] = mb_convert_kana($form['receipts_disp8']->getData(), 'KVSA', 'UTF-8');
        $data['ReceiptsDisp9'] = mb_convert_kana($form['receipts_disp9']->getData(), 'KVSA', 'UTF-8');
        $data['ReceiptsDisp10'] = mb_convert_kana($form['receipts_disp10']->getData(), 'KVSA', 'UTF-8');
        $data['ReceiptsDisp11'] = mb_convert_kana($form['receipts_disp11']->getData(), 'KV', 'UTF-8');

        $data['ReceiptsDisp12_1'] = $form['receipts_disp12_1']->getData();
        $data['ReceiptsDisp12_2'] = $form['receipts_disp12_2']->getData();
        $data['ReceiptsDisp12_3'] = $form['receipts_disp12_3']->getData();

        $data['ReceiptsDisp13_1'] = $form['receipts_disp13_1']->getData();
        $data['ReceiptsDisp13_2'] = $form['receipts_disp13_2']->getData();
        $data['ReceiptsDisp13_3'] = $form['receipts_disp13_3']->getData();
        $data['ReceiptsDisp13_4'] = $form['receipts_disp13_4']->getData();

        $data['EdyAddInfo1'] = $form['EdyAddInfo1']->getData();
        $data['EdyAddInfo2'] = $form['EdyAddInfo2']->getData();

        $data['SuicaAddInfo1'] = $form['SuicaAddInfo1']->getData();
        $data['SuicaAddInfo2'] = $form['SuicaAddInfo2']->getData();
        $data['SuicaAddInfo3'] = $form['SuicaAddInfo3']->getData();
        $data['SuicaAddInfo4'] = $form['SuicaAddInfo4']->getData();

        $data['Currency'] = 'JPY';
        $data['order_mail_title1'] = $form['order_mail_title1']->getData();
        $data['order_mail_body1'] = $form['order_mail_body1']->getData();

        return $data;
    }

    public static function setDefaultDataATMPayEasy($form)
    {
        $form['enable_mail']->setData(1);
        $form['order_mail_title1']->setData('お支払いについて');

        $path = __DIR__ . '/../../View/admin/mail/atm.twig';
        $content = file_get_contents($path);
        $form['order_mail_body1']->setData($content);
    }
    //////////Regis Credit end////////////
    //
    //
    //////////RakutenID start////////////
    public static function setRakutenIdData(&$form, $memo05){
        $data = unserialize($memo05);
        
        $form['JobCd']->setData($data['JobCd']);
        $form['ClientField1']->setData($data['ClientField1']);
        $form['ClientField2']->setData($data['ClientField2']);
        
        $form['order_mail_title1']->setData($data['order_mail_title1']);
        $form['order_mail_body1']->setData($data['order_mail_body1']);
        // Hidden fields
        $form['RetURL']->setData($data['RetURL']);
        $form['ErrorRcvURL']->setData($data['ErrorRcvURL']);
        $form['Version']->setData($data['Version']);
        $form['ItemId']->setData($data['ItemId']);
        $form['ItemSubId']->setData($data['ItemSubId']);
        $form['ItemName']->setData($data['ItemName']);
        $form['PaymentTermSec']->setData($data['PaymentTermSec']);
        
    }
    
    public static function getRakutenIdData(&$form){
        $data = array();
        $data['JobCd'] = $form['JobCd']->getData();
        $data['ClientField1'] = $form['ClientField1']->getData();
        $data['ClientField2'] = $form['ClientField2']->getData();
        
        $data['order_mail_title1'] = $form['order_mail_title1']->getData();
        $data['order_mail_body1'] = $form['order_mail_body1']->getData();
        // Hidden fields
        $data['RetURL'] = $form['RetURL']->getData();
        $data['ErrorRcvURL'] = $form['ErrorRcvURL']->getData();
        
        $data['Version'] = $form['Version']->getData();
        $data['ItemId'] = $form['ItemId']->getData();
        $data['ItemSubId'] = $form['ItemSubId']->getData();
        $data['ItemName'] = $form['ItemName']->getData();
        $data['PaymentTermSec'] = $form['PaymentTermSec']->getData();
        
        return $data;
        
    }
    //////////RakutenID end////////////
    //
    //
    //////////Token start////////////
    public static function setTokenData(&$form, $memo05)
    {
        $data = unserialize($memo05);

        $form['JobCd']->setData($data['JobCd']);
        $form['credit_pay_methods']->setData($data['credit_pay_methods']);
        $form['use_securitycd']->setData($data['use_securitycd']);
        $form['use_securitycd_option']->setData($data['use_securitycd_option']);
        $form['TdFlag']->setData($data['TdFlag']);
        $form['TdTenantName']->setData($data['TdTenantName']);
        $form['order_mail_title1']->setData($data['order_mail_title1']);
        $form['order_mail_body1']->setData($data['order_mail_body1']);
        $form['ClientField1']->setData($data['ClientField1']);
        $form['ClientField2']->setData($data['ClientField2']);

        $form['enable_mail']->setData($data['enable_mail']);
        $form['enable_cvs_mails']->setData($data['enable_cvs_mails']);
        $form['PaymentTermDay']->setData($data['PaymentTermDay']);
        $form['PaymentTermSec']->setData($data['PaymentTermSec']);
        $form['EdyAddInfo1']->setData($data['EdyAddInfo1']);
        $form['EdyAddInfo2']->setData($data['EdyAddInfo2']);
        $form['SuicaAddInfo1']->setData($data['SuicaAddInfo1']);
        $form['SuicaAddInfo2']->setData($data['SuicaAddInfo2']);
        $form['SuicaAddInfo3']->setData($data['SuicaAddInfo3']);
        $form['SuicaAddInfo4']->setData($data['SuicaAddInfo4']);
        $form['Currency']->setData($data['Currency']);
    }

    public static function getTokenData(&$form)
    {
        $data = array();

        $data['JobCd'] = $form['JobCd']->getData();
        $data['credit_pay_methods'] = $form['credit_pay_methods']->getData();
        $data['use_securitycd'] = $form['use_securitycd']->getData();
        $data['use_securitycd_option'] = $form['use_securitycd_option']->getData();
        $data['TdFlag'] = $form['TdFlag']->getData();
        $data['TdTenantName'] = $form['TdTenantName']->getData();
        $data['order_mail_title1'] = $form['order_mail_title1']->getData();
        $data['order_mail_body1'] = $form['order_mail_body1']->getData();
        $data['ClientField1'] = $form['ClientField1']->getData();
        $data['ClientField2'] = $form['ClientField2']->getData();
        $data['enable_mail'] = $form['enable_mail']->getData();
        $data['enable_cvs_mails'] = $form['enable_cvs_mails']->getData();
        $data['PaymentTermDay'] = $form['PaymentTermDay']->getData();
        $data['PaymentTermSec'] = $form['PaymentTermSec']->getData();
        $data['EdyAddInfo1'] = $form['EdyAddInfo1']->getData();
        $data['EdyAddInfo2'] = $form['EdyAddInfo2']->getData();
        $data['SuicaAddInfo1'] = $form['SuicaAddInfo1']->getData();
        $data['SuicaAddInfo2'] = $form['SuicaAddInfo2']->getData();
        $data['SuicaAddInfo3'] = $form['SuicaAddInfo3']->getData();
        $data['SuicaAddInfo4'] = $form['SuicaAddInfo4']->getData();
        $data['Currency'] = $form['Currency']->getData();

        return $data;
    }
    
     public static function setDefaultDataToken(&$form)
    {
        $form['order_mail_title1']->setData('お支払いについて');
        $form['use_securitycd']->setData(0);
        $form['use_securitycd_option']->setData(1);
        $form['TdFlag']->setData(0);
        $form['Currency']->setData('JPY');
    }
    //////////Token end//////////////
}

?>
