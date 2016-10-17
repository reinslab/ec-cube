<?php

namespace Plugin\GmoPaymentGateway\Controller\Helper;

use Plugin\GmoPaymentGateway\Service\client\PG_MULPAY_Client_RakutenId;
use Plugin\GmoPaymentGateway\Controller\Util\PaymentUtil;

/**
 * 決済モジュール 決済画面ヘルパー：楽天ID決済
 */
class PageHelper_RakutenId {
    public $errors;
    public $isComplete = false;
    public $orderId;
    private $results;

    /**
     * 画面モード毎のアクションを行う
     *
     * @param text $mode Mode値
     * @param FormParam $objFormParam FormParam インスタンス
     * @param array $arrOrder 受注情報
     * @param Page $objPage 呼出元ページオブジェクト
     * @return void
     */
    function modeAction($mode, $listParam, \Eccube\Entity\Order $Order,
            \Plugin\GmoPaymentGateway\Controller\DataObj\PaymentExtension $PaymentExtension,
            \Eccube\Application $app) 
        {
        $this->app = $app;
        $objUtil = new PaymentUtil($app);
        $OrderExtension = $objUtil->getOrderPayData($Order->getId());
        $objRakuten = new PG_MULPAY_Client_RakutenId($app);
        $result = false;
        switch($mode) {
            case 'next':
                // 決済実行
                $ret = $objRakuten->doPaymentRequest($OrderExtension, $listParam, $PaymentExtension);
                if ($ret) {
                    $results = $objRakuten->getResults();
                    $this->setResults($results);
                    $result = true;
                    $this->isComplete = true;
                } else {
                    $errors = $objRakuten->getError();
                    $this->setErrors($errors);
                    $result = false;
                }
                break;
            default:
                $result = false;
            break;
        }
        return $result;
    }
    /**
     * get array of results
     * @return array
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * set results
     * @param  results
     */
    public function setResults($results)
    {
        return $this->results = $results;
    }
    
    /**
     * get array of errors
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * set errors
     * @param  $errors
     */
    public function setErrors($errors)
    {
        return $this->errors = $errors;
    }


}
