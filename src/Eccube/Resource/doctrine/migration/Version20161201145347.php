<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161201145347 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        if ($schema->hasTable('dtb_order')) { //受注テーブルの存在確認
            $table = $schema->getTable('dtb_order'); //テーブルオブジェクトを取得

			//既存テーブルに追加するときは、カラムの有無チェックが必要
            if ( !$table->hasColumn('data_file_original_name') ) {
	            $table->addColumn('data_file_original_name', 	'text', array('NotNull' => false, 	'Default' => NULL, 	'Comment' => '入稿データオリジナルファイル名'));
	        }
        }


    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        if ($schema->hasTable('dtb_order')) { //受注テーブルの存在確認
            $table = $schema->getTable('dtb_order'); //テーブルオブジェクトを取得

			//カラムの有無チェック後に削除
            if ( $table->hasColumn('data_file_original_name') ) { $table->dropColumn('data_file_original_name');  }
        }

    }
}
