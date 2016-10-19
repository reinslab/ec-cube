<?php

namespace DoctrineMigrations;

use Eccube\Entity\Csv;
use Eccube\Common\Constant;
use Eccube\Entity\Master\CsvType;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Plugin\GmoPaymentGateway\Controller\Helper\Helper_CSV;

class Version20160822083341 extends AbstractMigration
{

    public function up(Schema $schema)
    {   
        $app = new \Eccube\Application();
        $app->initDoctrine();
        $app->boot();
        $this->registerCsvGmoMember($app);
    }
    
    public function down(Schema $schema)
    {
        $app = new \Eccube\Application();
        $app->initDoctrine();
        $app->boot();
        $this->removeCsvGmoMember($app);
    }
    
    /**
     * Register Gmo member Csv
     * @param type $app
     */
    public function registerCsvGmoMember($app){
        $name = 'GMOメンバーCSV';
        $CsvType = $app['orm.em']->getRepository('Eccube\Entity\Master\CsvType')->findOneBy(array('name' => $name));
        
        if(is_null($CsvType)){
            list($id, $rank) = $this->getLastCsvType($app);
            $CsvType = new CsvType();
            $CsvType->setId($id)
                    ->setName($name)
                    ->setRank($rank);
            $app['orm.em']->persist($CsvType);
        }
        
        $rank = 1;
        $gmoMemberFields = Helper_CSV::getFieldGmoMemberCSV();
        $Member = $app['orm.em']->getRepository('Eccube\Entity\Member')->find(1);
        foreach ($gmoMemberFields as $data){
            $Csv = new Csv();
            $Csv
                ->setCsvType($CsvType)
                ->setCreator($Member)
                ->setEntityName($data['entity_name'])
                ->setFieldName($data['field_name'])
                ->setDispName($data['disp_name'])
                ->setRank($rank)
                ->setEnableFlg(Constant::ENABLED)
                ->setCreateDate(new \DateTime())
                ->setUpdateDate(new \DateTime());
            if(isset($data['reference_field_name']) && $data['reference_field_name'] != ''){
                $Csv->setReferenceFieldName($data['reference_field_name']);
            }
            $app['orm.em']->persist($Csv);
            $rank++;
        }
        $app['orm.em']->flush();
    }

    /**
     * Remove gmo member csv format
     * @param type $app
     * @return boolean
     */
    public function removeCsvGmoMember($app){
        $name = 'GMOメンバーCSV';
        $CsvType = $app['orm.em']->getRepository('Eccube\Entity\Master\CsvType')->findOneBy(array('name' => $name));
        if(is_null($CsvType)){
            return false;
        }
        
        $gmoMemberCsv = $app['orm.em']->getRepository('Eccube\Entity\Csv')->findBy(array('CsvType' => $CsvType));
        foreach($gmoMemberCsv as $Csv){
            if(is_null($Csv)){
                continue;
            }
            $app['orm.em']->remove($Csv);
        }
        $app['orm.em']->remove($CsvType);
        $app['orm.em']->flush();
        return true;
    }
    
    /**
     * 
     * @param type $app
     * @return type
     */
    public static function getLastCsvType($app){
        $listCsvType = $app['orm.em']->getRepository('Eccube\Entity\Master\CsvType')->findAll();
        $nextId = 0;
        $nextRank = 0;
        if(count($listCsvType) >= 1){
            foreach ($listCsvType as $CsvType){
                $nextId = $CsvType->getId() > $nextId ? $CsvType->getId() : $nextId;
                $nextRank = $CsvType->getId() > $nextRank ? $CsvType->getRank() : $nextRank;
            }
        }
        return array($nextId + 1, $nextRank + 1);
    }
    
}
