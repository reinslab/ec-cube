<?php
/*
 * Copyright(c) 2015 GMO Payment Gateway, Inc. All rights reserved.
 * http://www.gmo-pg.com/
 */

namespace Plugin\GmoPaymentGateway\Controller\Util;

/**
 * 決済モジュール基本クラス
 */
class PluginUtil
{

    private $app;

    public function __construct(\Eccube\Application $app)
    {
        $this->app = $app;
    }

    /** サブデータを保持する変数 */
    var $subData = null;

    /** モジュール情報 */
    var $pluginInfo = array(
        'paymentName' => 'PGマルチペイメントサービス',
        'pluginName' => 'PGマルチペイメントサービス決済モジュール',
        'pluginCode' => 'GmoPaymentGateway',
        'gmoPluginVersion' => '4.2.1',
    );

    /**
     * Enter description here...
     *
     * @var unknown_type
     */
    var $updateFile = array();

    /**
     * GmoPaymentGateway:install()を呼んだ際にdtb_moduleのsub_dataカラムへ登録される値
     * シリアライズされて保存される.
     *
     * master_settings => 初期データなど
     * user_settings => 設定情報など、ユーザの入力によるデータ
     */
    var $installSubData = array(
        // 初期データなどを保持する
        'master_settings' => array(),
        // 設定情報など、ユーザの入力によるデータを保持する
        'user_settings' => array(),
    );
    private $pluginCode;

    /**
     * コンストラクタ
     *
     * @return void
     */
    function PluginUtil()
    {

    }

    /**
     * PG_MULPAYのインスタンスを取得する
     *
     * @return PluginUtil
     */
    static function &getInstance($app)
    {
        static $paymentUtil;
        if (empty($paymentUtil)) {
            $paymentUtil = new PluginUtil($app);
        }
        $paymentUtil->init();
        return $paymentUtil;
    }

    /**
     * 初期化処理.
     */
    function init()
    {
        foreach ($this->pluginInfo as $k => $v) {
            $this->$k = $v;
        }

    }

    /**
     * 終了処理.
     */
    function destroy()
    {
    }

    /**
     * モジュール表示用名称を取得する
     *
     * @return string
     */
    function getName()
    {
        return $this->pluginName;
    }

    /**
     * 支払い方法名(決済モジュールの場合のみ)
     *
     * @return string
     */
    function getPaymentName()
    {
        return $this->paymentName;
    }

    /**
     * モジュールコードを取得する
     *
     * @param boolean $toLower trueの場合は小文字へ変換する.デフォルトはfalse.
     * @return string
     */
    function getCode($toLower = false)
    {
        $pluginCode = $this->pluginCode;
        return $pluginCode;
    }

    /**
     * モジュールバージョンを取得する
     *
     * @return string
     */
    function getVersion()
    {
        return $this->gmoPluginVersion;
    }

    /**
     * サブデータを取得する.
     *
     * @return mixed|null
     */
    function getSubData($key = null)
    {
        if (isset($this->subData)) {
            if (is_null($key)) {
                return $this->subData;
            } else {
                return $this->subData[$key];
            }
        }

        $pluginCode = $this->getCode(true);

        $ret = $this->app['orm.em']->getRepository('Plugin\GmoPaymentGateway\Entity\GmoPlugin')
            ->getSubData($pluginCode);
        if (isset($ret)) {

            $this->subData = unserialize($ret);

            if (is_null($key)) {
                return $this->subData;
            } else {
                return $this->subData[$key];
            }
        }
        return null;
    }

    /**
     * サブデータをDBへ登録する
     * $keyがnullの時は全データを上書きする
     *
     * @param mixed $data
     * @param string $key
     */
    function registerSubData($data, $key = null)
    {
        $subData = $this->getSubData();

        if (is_null($key)) {
            $subData = $data;
        } else {
            $subData[$key] = $data;
        }
        $subDataSer = serialize($subData);

        $pluginCode = $this->getCode(true);
        $GmoPlugin = $this->app['orm.em']->getRepository('Plugin\GmoPaymentGateway\Entity\GmoPlugin')
            ->findOneBy(array('code' => $pluginCode));
        if (!is_null($GmoPlugin)) {
            $GmoPlugin->setSubData($subDataSer);
            $this->app['orm.em']->persist($GmoPlugin);
            $this->app['orm.em']->flush();
        }

        $this->subData = $subData;
    }

    function getUserSettings($key = null)
    {
        $subData = $this->getSubData();
        $returnData = null;

        if (is_null($key)) {
            $returnData = isset($subData['user_settings'])
                ? $subData['user_settings']
                : null;
        } else {
            $returnData = isset($subData['user_settings'][$key])
                ? $subData['user_settings'][$key]
                : null;
        }

        return $returnData;
    }

    function registerUserSettings($data)
    {
        $this->registerSubData($data, 'user_settings');
    }

    /**
     * ログを出力.
     *
     * @param string $msg
     * @param mixed $data
     */
    function printLog($msg, $date = null)
    {
        $path = $this->app['config']['root_dir'] . "/app/log/" . $this->getCode(true) . '_' . date('Ymd') . '.log';

        $text = '';
        if (is_array($msg)) {
            $text = print_r($msg, true);
        } else {
            $text = $msg;
        }
        CommonUtil::printLog($this->app, $text, $path);
    }

    /**
     * インストール処理
     *
     * @param boolean $force true時、上書き登録を行う
     */
    function install($force = false)
    {
        $subData = $this->getSubData();
        if (is_null($subData) || $force) {
            $this->registerSubdata(
                $this->installSubData['master_settings'],
                'master_settings'
            );
        }
    }

    /**
     * When uninstalling update del_flg of gmo payment method and update del_flg of page layout
     */
    public function uninstallPayment()
    {
        $arrPayment = $this->app['orm.em']->getRepository('\Plugin\GmoPaymentGateway\Entity\GmoPaymentMethod')->getPaymentList();
        foreach ($arrPayment as $payment_id) {
            // Update del_flg of gmo payment method
            $Payment = $this->app['orm.em']->getRepository('\Eccube\Entity\Payment')->findOneBy(array('id' => $payment_id));
            $Payment->setUpdateDate(new \DateTime());
            $Payment->setDelFlg(1);
            // Update del_flg of page layout
            $DeviceType = $this->app['eccube.repository.master.device_type']->find(10);
            $PageLayout = $this->app['eccube.repository.page_layout']->getByUrl($DeviceType, 'shopping/gmo_payment');
            $PageLayout->setEditFlg('1');

            $this->app['orm.em']->persist($Payment);
            $this->app['orm.em']->persist($PageLayout);
            $this->app['orm.em']->flush();
        }
    }
}
