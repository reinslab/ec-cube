<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161027100738 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        //初期データ投入
        $this->addSql("DELETE FROM mtb_nyukin_status;");
        $this->addSql("INSERT INTO mtb_nyukin_status (id, name, rank) VALUES (0, '入金有', 1);");
        $this->addSql("INSERT INTO mtb_nyukin_status (id, name, rank) VALUES (1, '入金無', 2);");

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("DELETE FROM mtb_nyukin_status;");
    }
}
