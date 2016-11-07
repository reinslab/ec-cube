<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161107111138 extends AbstractMigration
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
            if ( !$table->hasColumn('print_start_mail_status') ) {
	            $table->addColumn('print_start_mail_status', 	'smallint', array('NotNull' => false, 	'Default' => 0, 	'Comment' => '印刷開始案内メール送信状況')); //カラムを追加
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
            if ( $table->hasColumn('print_start_mail_status') ) { $table->dropColumn('print_start_mail_status');  }
        }

    }
}
