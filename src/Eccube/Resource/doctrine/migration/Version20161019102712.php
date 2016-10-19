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

        if ($schema->hasTable('dtb_order')) { //�󒍃e�[�u���̑��݊m�F
            $table = $schema->getTable('dtb_order'); //�e�[�u���I�u�W�F�N�g���擾
            $table->addColumn('daily_order_seq', 	'smallint', array('NotNull' => true, 	'Default' => 0, 	'Comment' => '�����̘A��')); //�J������ǉ�
            $table->addColumn('pdf_file_name', 		'text', 	array('NotNull' => false, 	'Default' => NULL,	'Comment' => '���e�f�[�^�t�@�C����')); //�J������ǉ�
            $table->addColumn('reins_order_id', 	'text', 	array('NotNull' => false, 	'Default' => NULL,	'Comment' => '��V�X�e���󒍔ԍ�')); //�J������ǉ�
            $table->addColumn('custom_order_id', 	'text', 	array('NotNull' => false, 	'Default' => NULL,	'Comment' => '����ID')); //�J������ǉ�
        }

        if ($schema->hasTable('dtb_customer')) { //�󒍃e�[�u���̑��݊m�F
            $table = $schema->getTable('dtb_customer'); //�e�[�u���I�u�W�F�N�g���擾
            $table->addColumn('reins_customer_code', 'text', 	array('NotNull' => false, 	'Default' => NULL, 	'Comment' => '��V�X�e�������R�[�h')); //�J������ǉ�
            $table->addColumn('section_name', 		 'text', 	array('NotNull' => false, 	'Default' => NULL, 	'Comment' => '������')); //�J������ǉ�
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
