<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161104204404 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
		$this->addSql("INSERT INTO dtb_mail_template (template_id, creator_id, name, file_name, subject, header, footer, del_flg, create_date, update_date) VALUES (2, 1, '印刷開始メール', 'Mail/order.twig', '印刷を開始いたします。', NULL, NULL, 0, '2016-10-12 19:19:22', '2016-10-12 19:19:22');
");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
