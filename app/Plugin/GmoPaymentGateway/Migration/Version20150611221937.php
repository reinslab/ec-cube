<?php
/*
 * Copyright(c) 2015 GMO Payment Gateway, Inc. All rights reserved.
 * http://www.gmo-pg.com/
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Eccube\Common\Constant;
/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150611221937 extends AbstractMigration
{

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->createDtbGmoPlugin($schema);
        $this->createDtbGmoOrderPayment($schema);
        $this->createDtbGmoPaymentMethod($schema);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // dtb_paymentは受注データと紐付いているため削除しない
        $this->deleteFromDtbPayment();
        $this->deletePageLayout();
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('dtb_gmo_plugin');
        $schema->dropTable('dtb_gmo_payment_method');
        $schema->dropTable('dtb_gmo_order_payment');
    
    }

    public function postUp(Schema $schema)
    {

        $app = new \Eccube\Application();
        $app->initDoctrine();
        $app->boot();
//        $config = $app['config'];
//        $pluginName  = $config['GmoPaymentGateway']['const']['PG_MULPAY_MODULE_NAME'];
//        $pluginCode = $config['GmoPaymentGateway']['code'];
        // Insert module information into dtb_gmo_plugin
        $pluginCode = 'GmoPaymentGateway';
        $pluginName = 'PGマルチペイメントサービス決済';
        $datetime = date('Y-m-d H:i:s');
        $insert = "INSERT INTO dtb_gmo_plugin(
                            plugin_code, plugin_name, create_date, update_date)
                    VALUES ('$pluginCode', '$pluginName', '$datetime', '$datetime'
                            );";
        // $this->createPagelayout($app, 'gmo_mypage_change_card', 10, 'MYページ/カード情報編集');
        $this->connection->executeUpdate($insert);
    }

    protected function createDtbGmoPlugin(Schema $schema)
    {
        $table = $schema->createTable("dtb_gmo_plugin");
        $table->addColumn('plugin_id', 'integer', array(
            'autoincrement' => true,
        ));

        $table->addColumn('plugin_code', 'text', array(
            'notnull' => true,
        ));

        $table->addColumn('plugin_name', 'text', array(
            'notnull' => true,
        ));

        $table->addColumn('sub_data', 'text', array(
            'notnull' => false,
        ));

        $table->addColumn('auto_update_flg', 'smallint', array(
            'notnull' => true,
            'unsigned' => false,
            'default' => 0,
        ));

        $table->addColumn('del_flg', 'smallint', array(
            'notnull' => true,
            'unsigned' => false,
            'default' => 0,
        ));

        $table->addColumn('create_date', 'datetime', array(
            'notnull' => true,
            'unsigned' => false,
        ));

        $table->addColumn('update_date', 'datetime', array(
            'notnull' => true,
            'unsigned' => false,
        ));

        $table->setPrimaryKey(array('plugin_id'));
    }

    protected function createDtbGmoOrderPayment(Schema $schema)
    {
        $table = $schema->createTable("dtb_gmo_order_payment");
        $table->addColumn('order_id', 'integer', array(
            'notnull' => true,
        ));
        $table->addColumn('memo01', 'text', array(
            'notnull' => false,
        ));
        $table->addColumn('memo02', 'text', array(
            'notnull' => false,
        ));
        $table->addColumn('memo03', 'text', array(
            'notnull' => false,
        ));
        $table->addColumn('memo04', 'text', array(
            'notnull' => false,
        ));
        $table->addColumn('memo05', 'text', array(
            'notnull' => false,
        ));
        $table->addColumn('memo06', 'text', array(
            'notnull' => false,
        ));
        $table->addColumn('memo07', 'text', array(
            'notnull' => false,
        ));
        $table->addColumn('memo08', 'text', array(
            'notnull' => false,
        ));
        $table->addColumn('memo09', 'text', array(
            'notnull' => false,
        ));
        $table->addColumn('memo10', 'text', array(
            'notnull' => false,
        ));

        $table->setPrimaryKey(array('order_id'));
    }

    protected function createDtbGmoPaymentMethod(Schema $schema)
    {
        $table = $schema->createTable("dtb_gmo_payment_method");

        //id
        $table->addColumn('payment_id', 'integer', array(
            'notnull' => true,
        ));

        // method
        $table->addColumn('payment_method', 'text', array(
            'notnull' => true,
        ));

        // delete flg
        $table->addColumn('del_flg', 'smallint', array(
            'notnull' => true,
            'unsigned' => false,
            'default' => 0,
        ));

        // create date
        $table->addColumn('create_date', 'datetime', array(
            'notnull' => true,
            'unsigned' => false,
        ));

        // update date
        $table->addColumn('update_date', 'datetime', array(
            'notnull' => true,
            'unsigned' => false,
        ));

        $table->addColumn('memo01', 'text', array(
            'notnull' => false,
        ));
        $table->addColumn('memo02', 'text', array(
            'notnull' => false,
        ));
        $table->addColumn('memo03', 'text', array(
            'notnull' => false,
        ));
        $table->addColumn('memo04', 'text', array(
            'notnull' => false,
        ));
        $table->addColumn('memo05', 'text', array(
            'notnull' => false,
        ));
        $table->addColumn('memo06', 'text', array(
            'notnull' => false,
        ));
        $table->addColumn('memo07', 'text', array(
            'notnull' => false,
        ));
        $table->addColumn('memo08', 'text', array(
            'notnull' => false,
        ));
        $table->addColumn('memo09', 'text', array(
            'notnull' => false,
        ));
        $table->addColumn('memo10', 'text', array(
            'notnull' => false,
        ));
        // plugin_code
        $table->addColumn('plugin_code', 'text', array(
            'notnull' => false,
        ));

        $table->setPrimaryKey(array('payment_id'));
    }

    protected function deleteFromDtbPayment()
    {
        $select = "SELECT p.payment_id FROM dtb_gmo_payment_method as gmo 
                JOIN dtb_payment as p ON gmo.payment_id = p.payment_id
                WHERE gmo.plugin_code =  'GmoPaymentGateway'";
        
        $paymentIds = $this->connection->executeQuery($select)->fetchAll();        
        $ids = array();
        
        foreach ($paymentIds as $item){
            $ids[]=$item['payment_id'];
        }        
        
        if (!empty($ids)){
            $param = implode(",", $ids);
            $update = "UPDATE dtb_payment SET del_flg = 1 WHERE payment_id in ($param)";
            $this->connection->executeUpdate($update);
        }
        
    }

    protected function deletePagelayout()
    {
        $sql_delete = " DELETE FROM dtb_page_layout WHERE url = 'gmo_shopping_payment' OR url = 'gmo_shopping_rakuten_result' ";
        $this->connection->executeUpdate($sql_delete);
        
    }
    
    function getGmoPaymentCode()
    {
        $config = \Eccube\Application::alias('config');

        return $paymentCodes;
    }

    /**
     * create a Page layout entry in dtb_page_layout
     *
     * @param    Eccube\Application     $app
     * @param    string                 $url
     * @param    int                    $deviceId
     * @param    string                 $name
     */
    protected function createPagelayout($app, $url, $deviceId, $name)
    {
        $deviceTypeRepo = $app['orm.em']->getRepository('Eccube\Entity\Master\DeviceType');
        $pageLayoutRepo = $app['orm.em']->getRepository('Eccube\Entity\PageLayout');
        $listOldVersion = array('3.0.1', '3.0.2', '3.0.3', '3.0.4', '3.0.5','3.0.6');
        in_array(Constant::VERSION, $listOldVersion) ? $pageLayoutRepo->setApp($app) : $pageLayoutRepo->setApplication($app);
        $deviceType = $deviceTypeRepo->find($deviceId);
        $pageLayout = $pageLayoutRepo->findOneBy(array('url' => $url));
        if (is_null($pageLayout)) {
            $pageLayout = $pageLayoutRepo->newPageLayout($deviceType);
        }
        $pageLayout->setCreateDate(new \DateTime());
        $pageLayout->setUpdateDate(new \DateTime());
        $pageLayout->setName($name);
        $pageLayout->setUrl($url);
        $pageLayout->setMetaRobots('noindex');
        $pageLayout->setEditFlg('2');
        $app['orm.em']->persist($pageLayout);
        $app['orm.em']->flush();
    }
}
