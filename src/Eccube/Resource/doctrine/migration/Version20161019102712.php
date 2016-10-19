<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161019102712 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        if (!$schema->hasTable('mtb_nyukin_status')) {
	        $table = $schema->createTable('mtb_nyukin_status');
	        $table->addColumn('id', 	'smallint', 	array('NotNull' => true));
	        $table->addColumn('name', 	'text', 		array('NotNull' => false));
	        $table->addColumn('rank', 	'smallint', 	array('NotNull' => true));
	        $table->setPrimaryKey(array('id'));
        }

        if ($schema->hasTable('dtb_order')) { //受注テーブルの存在確認
            $table = $schema->getTable('dtb_order'); //テーブルオブジェクトを取得
            $table->addColumn('daily_order_seq', 	'smallint', array('NotNull' => true, 	'Default' => 0, 	'Comment' => '日毎の連番')); //カラムを追加
            $table->addColumn('pdf_file_name', 		'text', 	array('NotNull' => false, 	'Default' => NULL,	'Comment' => '入稿データファイル名')); //カラムを追加
            $table->addColumn('reins_order_id', 	'text', 	array('NotNull' => false, 	'Default' => NULL,	'Comment' => '基幹システム受注番号')); //カラムを追加
            $table->addColumn('custom_order_id', 	'text', 	array('NotNull' => false, 	'Default' => NULL,	'Comment' => '注文ID')); //カラムを追加
        }

        if ($schema->hasTable('dtb_customer')) { //受注テーブルの存在確認
            $table = $schema->getTable('dtb_customer'); //テーブルオブジェクトを取得
            $table->addColumn('reins_customer_code', 'text', 	array('NotNull' => false, 	'Default' => NULL, 	'Comment' => '基幹システム取引先コード')); //カラムを追加
            $table->addColumn('section_name', 		 'text', 	array('NotNull' => false, 	'Default' => NULL, 	'Comment' => '部署名')); //カラムを追加
        }

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
