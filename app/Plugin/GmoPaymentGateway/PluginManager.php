<?php

/*
 * Copyright(c) 2015 GMO Payment Gateway, Inc. All rights reserved.
 * http://www.gmo-pg.com/
 */

namespace Plugin\GmoPaymentGateway;

use Eccube\Plugin\AbstractPluginManager;
use Eccube\Util\EntityUtil;
use Plugin\GmoPaymentGateway\Controller\Util\PluginUtil;

class PluginManager extends AbstractPluginManager {

    /**
     * Image folder path (cop source)
     * @var type
     */
    protected $imgSrc;

    /**
     * Image folder path (copy destination)
     * @var type
     */
    protected $imgDst;

    public function __construct() {
        $this->imgSrc = __DIR__ . '/Resource/img/';
        $this->imgDst = __DIR__ . '/../../../html/plugin/gmo_pg';
    }

    public function install($config, $app) {
        $this->migrationSchema($app, __DIR__ . '/Migration', $config['code'], 0);
        $this->migrationSchema($app, __DIR__ . '/Migration', $config['code']);
        // Copy images from GmoPaymentGateway/Resource/img   to   /html/user_data/packages/default/img/
        $this->copyImages($this->imgSrc, $this->imgDst);
    }

    public function uninstall($config, $app) {
        $this->migrationSchema($app, __DIR__ . '/Migration', $config['code'], 0);
        // Remove images in /html/user_data/packages/default/img/
        $this->removeImages($this->imgDst);
    }

    /**
     * Handle event enable plugin
     * @param type $config
     * @param type $app
     */
    public function enable($config, $app) {        
        // Update payment status when enable plugin
        if(!$this->updateStatusGmoPayment($config['const']['GMO_ENABLE_PAYMENT_VALUE'], $app)){
            return;
        }
    }
    
    /**
     * Handle event disable plugin
     * @param type $config
     * @param type $app
     */
    public function disable($config, $app) {
        // Update payment status when enable plugin
        if(!$this->updateStatusGmoPayment($config['const']['GMO_DISABLE_PAYMENT_VALUE'], $app)){
            return;
        }
    }
    
    /**
     * update status for gmo payment
     * @param type $status
     * @param type $app
     * @return boolean
     */
    protected function updateStatusGmoPayment($status, $app1){        
        // Handle version 3.0.4 due to 'enable' is also called at install time
        $exceptVersions = array('3.0.4');
        $app = $app1;
        if (in_array(\Eccube\Common\Constant::VERSION, $exceptVersions)){
            if (!empty ($app['config'])){
                if (empty($app['config']['GmoPaymentGateway'])) {
                    $app = new \Eccube\Application();
                    $app->initDoctrine();
                    $app->register(new \Silex\Provider\FormServiceProvider());
                    $app->register(new \Eccube\ServiceProvider\EccubeServiceProvider());
                    $app->boot();
                }
            }
        }
        // End of handling v3.0.4
                
        // Get gmo config sub data
        $objMdl = & PluginUtil::getInstance($app);
        $GmoSubData = $objMdl->getUserSettings();
        // Check GmoSubData and enable_payment_type are exist
        if(empty($GmoSubData) 
           || (!isset($GmoSubData['enable_payment_type']) && empty($GmoSubData['enable_payment_type']))){
            return false;
        }
        // Create gmo payment repository
        $Repo = $app['orm.em']->getRepository('\Plugin\GmoPaymentGateway\Entity\GmoPaymentMethod');
        // Get all gmo payment method
        $ListPayment = array();
        //Get list payment
        foreach($GmoSubData['enable_payment_type'] as $PaymentType){
            $ListPayment[] = $Repo->getPaymentByType($PaymentType, true, $app);
        }
        // Check list payment is empty
        if (empty($ListPayment)) {
            return false;
        }
        // Update payment status (enable: 0, disable: 1) each list payment
        foreach ($ListPayment as $Payment) {
            if (EntityUtil::isEmpty($Payment)){
                continue;
            }
            $Payment->setDelFlg($status);
            $app['orm.em']->persist($Payment);
            $GmoPayment = $Repo->getGmoPayment('id', $Payment->getId(), true, $app);
            if (EntityUtil::isNotEmpty($GmoPayment)) {
                $GmoPayment->setDelFlg($status);
                $app['orm.em']->persist($GmoPayment);
            }
        }
        $app['orm.em']->flush();
        return true;
    }

    public function update($config, $app) {
        $this->migrationSchema($app, __DIR__ . '/Migration', $config['code']);
        $this->removeUnusedFiles();
        $this->copyImages($this->imgSrc, $this->imgDst);
    }

    /**
     * Recursively copy images from $src path to $dst path
     * @param string $src
     * @param string $dst
     */
    protected function copyImages($src, $dst) {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    $this->copyImages($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    /**
     * Recursively delete images in a folder path
     * @param string $dir
     * @return boolean
     */
    function removeImages($dir) {
        if (!file_exists($dir))
            return true;
        if (!is_dir($dir) || is_link($dir))
            return unlink($dir);
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..')
                continue;
            if (!$this->removeImages($dir . "/" . $item)) {
                chmod($dir . "/" . $item, 0777);
                if (!$this->removeImages($dir . "/" . $item))
                    return false;
            };
        }
        return rmdir($dir);
    }
    
    /**
     * Fix #54392 : Remove Plugin.GmoPaymentGateway.Entity.Card.dcm.yml
     */
    function removeUnusedFiles(){
        $cardYml = __DIR__ . '/Resource/doctrine/Plugin.GmoPaymentGateway.Entity.Card.dcm.yml';
        $this->removeImages($cardYml);
        
        
    }
}
