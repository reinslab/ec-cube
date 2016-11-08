<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Eccube\Entity\Master\CsvType;
use Eccube\Entity\Csv;
//use Eccube\Entity\Member;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161108103827 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $app = \Eccube\Application::getInstance();
        $em = $app["orm.em"];

        $Member = $em->getRepository('Eccube\Entity\Member')->find(1);
        if ( is_null($Member) ) {
	        $Member = $em->getRepository('Eccube\Entity\Member')->find(2);
        }
        
		//受注
        $CsvType_Order = $em->getRepository('Eccube\Entity\Master\CsvType')->find(CsvType::CSV_TYPE_ORDER);

        //受注CSV
        $Csv_Order = new Csv();
        $Csv_Order->setEntityName('Eccube\Entity\Order');
        $Csv_Order->setCsvType($CsvType_Order);
        $Csv_Order->setCreator($Member);
        $Csv_Order->setFieldName('custom_order_id');
        $Csv_Order->setDispName('カスタム注文ID');
        $Csv_Order->setRank(40);
        $Csv_Order->setEnableFlg(1);
        $Csv_Order->setCreateDate(new \DateTime());
        $Csv_Order->setUpdateDate(new \DateTime());
        $em->persist($Csv_Order);
        $em->flush($Csv_Order);

		//配送
        $CsvType_Shipping = $em->getRepository('Eccube\Entity\Master\CsvType')->find(CsvType::CSV_TYPE_SHIPPING);

        //配送CSV
        $Csv_Shipping = new Csv();
        $Csv_Shipping->setEntityName('Eccube\Entity\Order');
        $Csv_Shipping->setCsvType($CsvType_Shipping);
        $Csv_Shipping->setCreator($Member);
        $Csv_Shipping->setFieldName('custom_order_id');
        $Csv_Shipping->setDispName('カスタム注文ID');
        $Csv_Shipping->setRank(40);
        $Csv_Shipping->setEnableFlg(1);
        $Csv_Shipping->setCreateDate(new \DateTime());
        $Csv_Shipping->setUpdateDate(new \DateTime());
        $em->persist($Csv_Shipping);
        $em->flush($Csv_Shipping);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
