<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161027100613 extends AbstractMigration
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
            if ( !$table->hasColumn('pdf_upload_flg') ) {
	            $table->addColumn('pdf_upload_flg', 'smallint', array('NotNull' => false, 	'Default' => 0, 	'Comment' => '入稿データ登録済みフラグ(0:未登録 1:登録済み)'));
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
            if ( $table->hasColumn('pdf_upload_flg') ) { $table->dropColumn('pdf_upload_flg');  }
        }

    }
}
