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

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version201603101200 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->createPlgTransportCSVimportB2Plugin($schema);
        $this->createPlgTransportCSVimportB2($schema);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('plg_transport_csv_import_b2_plugin');
        $schema->dropTable('plg_transport_csv_import_b2');
    }

    public function postUp(Schema $schema)
    {

        $app = new \Eccube\Application();
        $app->boot();

        $sql = "select max(template_id) as template_id from dtb_mail_template";
        $maxId = $this->connection->executeQuery($sql)->fetchColumn(0);
        $template_id = $maxId + 1;

        $pluginCode = 'TransportCSVimportB2';
        $pluginName = 'ヤマトB2・CSV登録プラグイン';
        $sub_data = array('user_settings' => array('order_status' => array(0 => 1)));
        $sub_data = serialize($sub_data);
        $datetime = date('Y-m-d H:i:s');
        $insert = "INSERT INTO plg_transport_csv_import_b2_plugin(
                            plugin_code, plugin_name, sub_data, mail_template_id, create_date, update_date)
                    VALUES ('$pluginCode', '$pluginName', '$sub_data', '$template_id', '$datetime', '$datetime'
                            );";
        $this->connection->executeUpdate($insert);

        $this->connection->insert('dtb_mail_template', array(
            'template_id' => $template_id,
            'creator_id' => '1',
            'name' => 'ヤマトB2発送メール',
            'file_name' => 'Mail/order.twig',
            'subject' => '商品を発送しました',
            'header' => 'この度はご注文いただきましてありがとうございました。
以下の通り商品を発送いたしましたので、ご確認ください。

送り状番号は以下の通りです。
◎お届け先

お名前　　：
送り状番号：
追跡ＵＲＬ：http://toi.kuronekoyamato.co.jp/cgi-bin/tneko

荷物の追跡は追跡URLよりお願いします。
※荷物が登録されるまで、若干のお時間をいただく場合があります。

以上となります。
よろしくお願いいたします。
',
            'footer' => '============================================


　※本メールは自動配信メールです。
　等幅フォント(MSゴシック12ポイント、Osaka-等幅など)で
　最適にご覧になれます。
',
            'del_flg' => '0',
            'create_date' => date('Y-m-d H:i:s'),
            'update_date' => date('Y-m-d H:i:s')
        ));
    }

    protected function createPlgTransportCSVimportB2Plugin(Schema $schema)
    {
        $table = $schema->createTable("plg_transport_csv_import_b2_plugin");
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

        $table->addColumn('mail_template_id', 'smallint', array(
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

    function getTransportCSVimportB2Code()
    {
        $config = \Eccube\Application::alias('config');

        return "";
    }

    protected function createPlgTransportCSVimportB2(Schema $schema)
    {
        $table = $schema->createTable("plg_transport_csv_import_b2");
        $table->addColumn('id', 'integer', array(
            'autoincrement' => true,
        ));

        $table->addColumn('shipping_id', 'integer', array(
            'notnull' => false,
        ));

        $table->addColumn('order_id', 'integer', array(
            'notnull' => false,
        ));

        $table->addColumn('invoice_number', 'text', array(
            'notnull' => false,
        ));
        
        $table->setPrimaryKey(array('id'));
    }

}