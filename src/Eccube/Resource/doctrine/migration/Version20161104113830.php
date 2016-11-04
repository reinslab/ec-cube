<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161104113830 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        if ($schema->hasTable('dtb_shipping')) { //受注テーブルの存在確認
            $table = $schema->getTable('dtb_shipping'); //テーブルオブジェクトを取得

			//既存テーブルに追加するときは、カラムの有無チェックが必要
            if ( !$table->hasColumn('delivery_count') ) {
	            $table->addColumn('delivery_count', 'smallint', array('NotNull' => false, 	'Default' => 1, 	'Comment' => '発送個数'));
	        }
        }

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        if ($schema->hasTable('dtb_shipping')) { //受注テーブルの存在確認
            $table = $schema->getTable('dtb_shipping'); //テーブルオブジェクトを取得

			//カラムの有無チェック後に削除
            if ( $table->hasColumn('delivery_count') ) { $table->dropColumn('delivery_count');  }
        }

    }
}
