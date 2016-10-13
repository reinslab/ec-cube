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
    const CSVB2_NAME = 'B2CSV_TransportCSVexportB2';
    
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->createPlgTransportCSVexportB2Plugin($schema);
        $this->createPlgTransportCSVexportB2DelivType($schema);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('plg_transport_csv_export_b2_plugin');
        $schema->dropTable('plg_transport_csv_export_b2_deliv_type');
        $this->deleteCsvB2Id($schema);
    }

    public function postUp(Schema $schema)
    {

        $app = new \Eccube\Application();
        $app->boot();
        
        $sql = "select payment_id from dtb_payment where del_flg = 0 ORDER BY rank desc";
        $param = array();
        $ids = $this->connection->fetchAll($sql, $param);

        $data = array(
                    'billing_code' => null,
                    'fare_number' => null,
                    'yamato_deliv_type' => null,
                    'order_status' => array(0 => 1)
                    );
 
        foreach ($ids as $v) {
            $data['payment_type'.$v['payment_id']] = null;
        }

        $pluginCode = 'TransportCSVexportB2';
        $pluginName = 'ヤマトB2・CSV出力プラグイン';
        $sub_data = array('user_settings' => $data);
        $sub_data = serialize($sub_data);
        $datetime = date('Y-m-d H:i:s');
        $insert = "INSERT INTO plg_transport_csv_export_b2_plugin(
                            plugin_code, plugin_name, sub_data, create_date, update_date)
                    VALUES ('$pluginCode', '$pluginName', '$sub_data', '$datetime', '$datetime'
                            );";
        $this->connection->executeUpdate($insert);
        $insert = "INSERT INTO plg_transport_csv_export_b2_deliv_type(
                            id, name, rank)
                    VALUES (1010, '発払', 0),
                           (1011, '発払・クール冷凍', 1),
                           (1012, '発払・クール冷蔵', 2),
                           (1020, 'コレクト', 3),
                           (1021, 'コレクト・クール冷凍', 4),
                           (1022, 'コレクト・クール冷蔵', 5),
                           (1030, 'DM便', 6),
                           (1040, 'タイム', 7),
                           (1050, '着払', 8),
                           (1051, '着払・クール冷凍', 9),
                           (1052, '着払・クール冷蔵', 10),
                           (1062, 'クール冷蔵', 11),
                           (1070, 'ネコポス', 12);";
        $this->connection->executeUpdate($insert);
        // CSV
        $this->insertCsvB2($schema);
        // CSV
    }

    protected function createPlgTransportCSVexportB2Plugin(Schema $schema)
    {
        $table = $schema->createTable("plg_transport_csv_export_b2_plugin");
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

    protected function createPlgTransportCSVexportB2DelivType(Schema $schema)
    {
        $table = $schema->createTable("plg_transport_csv_export_b2_deliv_type");
        $table->addColumn('id', 'smallint', array(
            'notnull' => true,
        ));

        $table->addColumn('name', 'text', array(
            'notnull' => true,
        ));

        $table->addColumn('rank', 'smallint', array(
            'notnull' => true,
        ));

        $table->setPrimaryKey(array('id'));
    }

    function getTransportCSVexportB2Code()
    {
        $config = \Eccube\Application::alias('config');

        return "";
    }

    protected function createPlgTransportCSVexportB2(Schema $schema)
    {
        $table = $schema->createTable("plg_transport_csv_export_b2");
        $table->addColumn('csv_id', 'integer', array(
            'autoincrement' => true,
        ));

        $table->addColumn('csv_type', 'smallint', array(
            'notnull' => true,
        ));

        $table->addColumn('creator_id', 'integer', array(
            'notnull' => true,
        ));

        $table->addColumn('entity_name', 'text', array(
            'notnull' => true,
        ));

        $table->addColumn('field_name', 'text', array(
            'notnull' => true,
        ));

        $table->addColumn('reference_field_name', 'text', array(
            'notnull' => false,
        ));

        $table->addColumn('disp_name', 'text', array(
            'notnull' => true,
        ));

        $table->addColumn('rank', 'smallint', array(
            'notnull' => true,
        ));

        $table->addColumn('enable_flg', 'smallint', array(
            'notnull' => true,
        ));

        $table->addColumn('create_date', 'datetime', array(
            'notnull' => true,
            'unsigned' => false,
        ));

        $table->addColumn('update_date', 'datetime', array(
            'notnull' => true,
            'unsigned' => false,
        ));

        $table->setPrimaryKey(array('csv_id'));
    }

    function insertCsvB2(Schema $schema)
    {
        $data = array(
                    // お客様管理番号
                    array(
                        "entity_name" => "Eccube\\\\Entity\\\\Order",
                        "field_name" => "customer_code",
                        "reference_field_name" => "",
                        "disp_name" => "お客様管理番号",
                    ),
                    // 送り状種別
                    array(
                        "entity_name" => "Eccube\\\\Entity\\\\Order",
                        "field_name" => "deliv_class",
                        "reference_field_name" => "",
                        "disp_name" => "送り状種別",
                    ),
                    // クール区分
                    array(
                        "entity_name" => "Eccube\\\\Entity\\\\Order",
                        "field_name" => "cool_class",
                        "reference_field_name" => "",
                        "disp_name" => "クール区分",
                    ),
                    // 伝票番号
                    array(
                        "entity_name" => "Eccube\\\\Entity\\\\Order",
                        "field_name" => "denpyou_number",
                        "reference_field_name" => "",
                        "disp_name" => "伝票番号",
                    ),
                    // 出荷予定日
                    array(
                        "entity_name" => "Eccube\\\\Entity\\\\Order",
                        "field_name" => "send_date",
                        "reference_field_name" => "",
                        "disp_name" => "出荷予定日",
                    ),
                    // お届け予定（指定）日
                    array(
                        "entity_name" => "Eccube\\\\Entity\\\\Shipping",
                        "field_name" => "shipping_delivery_date",
                        "reference_field_name" => "",
                        "disp_name" => "お届け予定（指定）日",
                    ),
                    // 配達時間帯
                    array(
                        "entity_name" => "Eccube\\\\Entity\\\\Shipping",
                        "field_name" => "shipping_delivery_time",
                        "reference_field_name" => "",
                        "disp_name" => "配達時間帯",
                    ),
                    // お届け先コード
                    array(
                        "entity_name" => "Eccube\\\\Entity\\\\Order",
                        "field_name" => "shipping_code",
                        "reference_field_name" => "",
                        "disp_name" => "お届け先コード",
                    ),
                    // お届け先電話番号
                    array(
                        "entity_name" => "Eccube\\\\Entity\\\\Shipping",
                        "field_name" => "tel01",
                        "reference_field_name" => "",
                        "disp_name" => "お届け先電話番号",
                    ),
                    // お届け先電話番号枝番
                    array(
                        "entity_name" => "Eccube\\\\Entity\\\\Shipping",
                        "field_name" => "tel_eda",
                        "reference_field_name" => "",
                        "disp_name" => "お届け先電話番号枝番",
                    ),
                    // お届け先郵便番号
                    array(
                        "entity_name" => "Eccube\\\\Entity\\\\Shipping",
                        "field_name" => "zip01",
                        "reference_field_name" => "",
                        "disp_name" => "お届け先郵便番号",
                    ),
                    // お届け先住所
                    array(
                        "entity_name" => "Eccube\\\\Entity\\\\Shipping",
                        "field_name" => "addr01",
                        "reference_field_name" => "",
                        "disp_name" => "お届け先住所",
                    ),
                    // お届け先住所（アパートマンション名）
                    array(
                        "entity_name" => "Eccube\\\\Entity\\\\Shipping",
                        "field_name" => "addr02",
                        "reference_field_name" => "",
                        "disp_name" => "お届け先住所（アパートマンション名）",
                    ),
                    // お届け先会社・部門名１
                    array(
                        "entity_name" => "Eccube\\\\Entity\\\\Shipping",
                        "field_name" => "company_name",
                        "reference_field_name" => "",
                        "disp_name" => "お届け先会社・部門名１",
                    ),
                    // お届け先会社・部門名２
                    array(
                        "entity_name" => "Eccube\\\\Entity\\\\Shipping",
                        "field_name" => "company_name_xxx",
                        "reference_field_name" => "",
                        "disp_name" => "お届け先会社・部門名２",
                    ),
                    // お届け先名
                    array(
                        "entity_name" => "Eccube\\\\Entity\\\\Shipping",
                        "field_name" => "name01",
                        "reference_field_name" => "",
                        "disp_name" => "お届け先名",
                    ),
                    // お届け先名略称カナ
                    array(
                        "entity_name" => "Eccube\\\\Entity\\\\Shipping",
                        "field_name" => "kana01",
                        "reference_field_name" => "",
                        "disp_name" => "お届け先名略称カナ",
                    ),
                    // 敬称
                    array(
                       "entity_name" => "Eccube\\\\Entity\\\\Order",
                       "field_name" => "keisyou",
                        "reference_field_name" => "",
                        "disp_name" => "敬称",
                    ),
                    // ご依頼主コード
                    array(
                        "entity_name" => "Eccube\\\\Entity\\\\BaseInfo",
                        "field_name" => "code",
                        "reference_field_name" => "",
                        "disp_name" => "ご依頼主コード",
                    ),
                    // ご依頼主電話番号
                    array(
                        "entity_name" => "Eccube\\\\Entity\\\\BaseInfo",
                        "field_name" => "tel01",
                        "reference_field_name" => "",
                        "disp_name" => "ご依頼主電話番号",
                    ),
                    // ご依頼主電話番号枝番
                    array(
                        "entity_name" => "Eccube\\\\Entity\\\\BaseInfo",
                        "field_name" => "shipping_tel_eda",
                        "reference_field_name" => "",
                        "disp_name" => "ご依頼主電話番号枝番",
                    ),
                    // ご依頼主郵便番号
                    array(
                        "entity_name" => "Eccube\\\\Entity\\\\BaseInfo",
                        "field_name" => "zip01",
                        "reference_field_name" => "",
                        "disp_name" => "ご依頼主郵便番号",
                    ),
                    // ご依頼主住所
                    array(
                        "entity_name" => "Eccube\\\\Entity\\\\BaseInfo",
                        "field_name" => "addr01",
                        "reference_field_name" => "",
                        "disp_name" => "ご依頼主住所",
                    ),
                    // ご依頼主住所（アパートマンション名）
                    array(
                        "entity_name" => "Eccube\\\\Entity\\\\BaseInfo",
                        "field_name" => "addr02",
                        "reference_field_name" => "",
                        "disp_name" => "ご依頼主住所（アパートマンション名）",
                    ),
                    // ご依頼主名
                    array(
                        "entity_name" => "Eccube\\\\Entity\\\\BaseInfo",
                        "field_name" => "campany_name",
                        "reference_field_name" => "",
                        "disp_name" => "ご依頼主名",
                    ),
                    // ご依頼主略称カナ
                    array(
                        "entity_name" => "Eccube\\\\Entity\\\\BaseInfo",
                        "field_name" => "campany_kana",
                        "reference_field_name" => "",
                        "disp_name" => "ご依頼主略称カナ",
                    ),
                    // 品名コード１
                    array(
                        "entity_name" => "Eccube\\\\Entity\\\\ShipmentItem",
                        "field_name" => "product_code01",
                        "reference_field_name" => "",
                        "disp_name" => "品名コード１",
                    ),
                    // 品名１
                    array(
                        "entity_name" => "Eccube\\\\Entity\\\\ShipmentItem",
                        "field_name" => "product_name01",
                        "reference_field_name" => "",
                        "disp_name" => "品名１",
                    ),
                    // 品名コード２
                    array(
                        "entity_name" => "Eccube\\\\Entity\\\\ShipmentItem",
                        "field_name" => "product_code02",
                        "reference_field_name" => "",
                        "disp_name" => "品名コード２",
                    ),
                    // 品名２
                    array(
                        "entity_name" => "Eccube\\\\Entity\\\\ShipmentItem",
                        "field_name" => "product_name02",
                        "reference_field_name" => "",
                        "disp_name" => "品名２",
                    ),
                    // 荷扱い１
                    array(
                        "entity_name" => "Eccube\\\\Entity\\\\Order",
                        "field_name" => "item01",
                        "reference_field_name" => "",
                        "disp_name" => "荷扱い１",
                    ),
                    // 荷扱い２
                    array(
                        "entity_name" => "Eccube\\\\Entity\\\\Order",
                        "field_name" => "item02",
                        "reference_field_name" => "",
                        "disp_name" => "荷扱い２",
                    ),
                    // 記事
                    array(
                        "entity_name" => "Eccube\\\\Entity\\\\Order",
                        "field_name" => "news",
                        "reference_field_name" => "",
                        "disp_name" => "記事",
                    ),
                    // コレクト代金引換額（税込）
                    array(
                        "entity_name" => "Eccube\\\\Entity\\\\Order",
                        "field_name" => "daibiki_price",
                        "reference_field_name" => "",
                        "disp_name" => "コレクト代金引換額（税込）",
                    ),
                    // コレクト内消費税額等
                    array(
                        "entity_name" => "Eccube\\\\Entity\\\\Order",
                        "field_name" => "daibiki_tax",
                        "reference_field_name" => "",
                        "disp_name" => "コレクト内消費税額等",
                    ),
                    // 営業所止置き
                    array(
                        "entity_name" => "Eccube\\\\Entity\\\\Order",
                        "field_name" => "stop",
                        "reference_field_name" => "",
                        "disp_name" => "営業所止置き",
                    ),
                    // 営業所コード
                    array(
                        "entity_name" => "Eccube\\\\Entity\\\\Order",
                        "field_name" => "code",
                        "reference_field_name" => "",
                        "disp_name" => "営業所コード",
                    ),
                    // 発行枚数
                    array(
                        "entity_name" => "Eccube\\\\Entity\\\\Order",
                        "field_name" => "cnt",
                        "reference_field_name" => "",
                        "disp_name" => "発行枚数",
                    ),
                    // 個数口枠の印字
                    array(
                        "entity_name" => "Eccube\\\\Entity\\\\Order",
                        "field_name" => "inji",
                        "reference_field_name" => "",
                        "disp_name" => "個数口枠の印字",
                    ),
                    // ご請求先顧客コード
                    array(
                        "entity_name" => "Eccube\\\\Entity\\\\Order",
                        "field_name" => "cstmr_code",
                        "reference_field_name" => "",
                        "disp_name" => "ご請求先顧客コード",
                    ),
                    // ご請求先分類コード
                    array(
                        "entity_name" => "Eccube\\\\Entity\\\\Order",
                        "field_name" => "bunrui_code",
                        "reference_field_name" => "",
                        "disp_name" => "ご請求先分類コード",
                    ),
                    // 運賃管理番号
                    array(
                        "entity_name" => "Eccube\\\\Entity\\\\Order",
                        "field_name" => "kanri",
                        "reference_field_name" => "",
                        "disp_name" => "運賃管理番号",
                    ),
                );
        // 
        $sql = "select max(id) as id from mtb_csv_type";
        $maxId = $this->connection->executeQuery($sql)->fetchColumn(0);
        $sql = "select max(rank) as rank from mtb_csv_type";
        $maxRank = $this->connection->executeQuery($sql)->fetchColumn(0);
        $csv_type = $id = $maxId + 1;
        $rank = $maxRank + 1;
        
        // 
        $name = self::CSVB2_NAME;
        $insert = "INSERT INTO mtb_csv_type(
                            id, name, rank)
                    VALUES ({$id}, '{$name}', {$rank});";
        $this->connection->executeUpdate($insert);
        // 
        $sql = "select max(csv_id) as csv_id from dtb_csv";
        $maxCsvId = $this->connection->executeQuery($sql)->fetchColumn(0);
        $creator_id = 1;
        $enable_flg = 1;
        $datetime = date('Y-m-d H:i:s');
        $values = array();
        foreach ($data as $key => $val) {
            $csv_id = $maxCsvId + $key + 1;
            $rank = $key + 1;
            $entity_name = $val['entity_name'];
            $field_name = $val['field_name'];
            $reference_field_name = ($val['reference_field_name']) ? "'" . $val['reference_field_name'] . "'" : 'NULL' ;
            $disp_name = $val['disp_name'];
            $values[] = "({$csv_id}, {$csv_type}, {$creator_id}, '{$entity_name}', '{$field_name}', {$reference_field_name}, '{$disp_name}', {$rank}, {$enable_flg}, '{$datetime}', '{$datetime}')";
        }
        $values = implode(',', $values);
/*
        $insert = "INSERT INTO plg_transport_csv_export_b2(
                            csv_id, csv_type, creator_id, entity_name, field_name, reference_field_name, disp_name, rank, enable_flg, create_date, update_date)
                    VALUES {$values};";
*/
        $insert = "INSERT INTO dtb_csv(
                            csv_id, csv_type, creator_id, entity_name, field_name, reference_field_name, disp_name, rank, enable_flg, create_date, update_date)
                    VALUES {$values};";
        $this->connection->executeUpdate($insert);
    }

    function deleteCsvB2Id(Schema $schema)
    {
        $sql = "select id from mtb_csv_type where name = ?";
        $param = array(self::CSVB2_NAME);
        $id = $this->connection->fetchColumn($sql, $param);
        
        $sql = "delete from dtb_csv where csv_type = {$id}";
        $this->connection->beginTransaction();
        $this->connection->exec($sql);
        $this->connection->commit();
        $sql = "delete from mtb_csv_type where id = {$id}";
        $this->connection->exec($sql);
    }
    
}
