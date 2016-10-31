<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Eccube\Entity\PageLayout;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161031172945 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        // pageを追加
        $app = \Eccube\Application::getInstance();
        $em = $app["orm.em"];

        $DeviceType = $em->getRepository('\Eccube\Entity\Master\DeviceType')->find(10);

        $PageLayout = new PageLayout();
        $PageLayout->setDeviceType($DeviceType);
        $PageLayout->setName( '商品購入/確認');
        $PageLayout->setUrl('shopping_confirm');
        $PageLayout->setFileName('Shopping/index');
        $PageLayout->setEditFlg(2);
        $PageLayout->setMetaRobots('noindex');
        $PageLayout->setCreateDate(new \DateTime());
        $PageLayout->setUpdateDate(new \DateTime());
        $em->persist($PageLayout);
        $em->flush();


    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
