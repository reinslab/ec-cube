<?php
/*
 * Copyright(c) 2015 GMO Payment Gateway, Inc. All rights reserved.
 * http://www.gmo-pg.com/
 */
namespace Plugin\GmoPaymentGateway\Controller\Helper;

use Eccube\Application;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Plugin\GmoPaymentGateway\Controller\Helper\Collection\PdoCollection;

class Helper_OrganizeGmoMember
{
    /**
     *
     * @var type Application
     */
    public $app;
    
    /**
     * construct
     * @param Application $app
     */
    public function __construct(Application $app){
        $this->app = $app;
    }
    
    /**
     * Migrate gmo member
     * @return boolean
     */
    public function migrateMember(){
        // Each loop time, query data is changed because new data are inserted to table
        // This causes incorrect paging
        // Therefore, let's query those data that create_time < startTime 
        $startTime = date('Y-m-d H:i:s');
        $this->app['orm.em']->getConfiguration()->setSQLLogger(null);
        $qb = $this->app['eccube.plugin.gmo_pg.repository.gmo_member']->getAllCustomersQueryBuilder($startTime);

        // Count total of data
        $query = $qb->getQuery()
                ->setFirstResult(0)
                ->setMaxResults(10);
        $paginator = new Paginator($query, $fetchJoinCollection = true);

        if ($paginator->count() == 0) {
            return false;
        }

        $maxResult = 1000;
        $loopCnt = ceil($paginator->count() / $maxResult);
        $j = -$maxResult;
        gc_enable();
        $connection = $this->app['orm.em']->getConnection();
        
        for ($i = 0; $i < $loopCnt; $i++) {
            $j = $j + $maxResult;
            $query = $qb->getQuery()
                    ->setFirstResult($j)
                    ->setMaxResults($maxResult);
            
            $sql = $query->getSQL();

            $rows = $this->getAllCustomers($sql, $startTime);
 
            $this->generateGmoMember($rows, $connection);
        }
        $connection->commit();

        gc_collect_cycles();
    }
    
    /**
     * create new SQL get data from DB View
     * @param type $sql
     * @param type $param_values_array
     * @return $newSql get data from dtb view (dtb_netjapan_price_rate_view)
     */
    public function getAllCustomers($sql, $startTime) {
        
        $oldFrom = 'FROM dtb_customer d0_';
        $newFrom = 'FROM dtb_customer c';
        $oldSelect = 'SELECT d0_.customer_id AS customer_id0, d0_.name01 AS name011, d0_.name02 AS name022, d0_.kana01 AS kana013, d0_.kana02 AS kana024, d0_.company_name AS company_name5, d0_.zip01 AS zip016, d0_.zip02 AS zip027, d0_.zipcode AS zipcode8, d0_.addr01 AS addr019, d0_.addr02 AS addr0210, d0_.email AS email11, d0_.tel01 AS tel0112, d0_.tel02 AS tel0213, d0_.tel03 AS tel0314, d0_.fax01 AS fax0115, d0_.fax02 AS fax0216, d0_.fax03 AS fax0317, d0_.birth AS birth18, d0_.password AS password19, d0_.salt AS salt20, d0_.secret_key AS secret_key21, d0_.first_buy_date AS first_buy_date22, d0_.last_buy_date AS last_buy_date23, d0_.buy_times AS buy_times24, d0_.buy_total AS buy_total25, d0_.note AS note26, d0_.reset_key AS reset_key27, d0_.reset_expire AS reset_expire28, d0_.create_date AS create_date29, d0_.update_date AS update_date30, d0_.del_flg AS del_flg31, d0_.status AS status32, d0_.sex AS sex33, d0_.job AS job34, d0_.country_id AS country_id35, d0_.pref AS pref36';
        $newSelect = "SELECT c.customer_id, c.create_date ";
        $oldWhere = "WHERE (d0_.status = 2 AND (NOT (EXISTS (SELECT d1_.id FROM dtb_gmo_member d1_ WHERE d1_.create_date < '$startTime' AND d1_.customer_id = d0_.customer_id AND d1_.customer_create_date = d0_.create_date)))) AND (d0_.del_flg = 0)";
        $newWhere = "WHERE (c.status = 2 AND (NOT (EXISTS (SELECT gm.id FROM dtb_gmo_member gm WHERE gm.create_date < '$startTime' AND gm.customer_id = c.customer_id AND gm.customer_create_date = c.create_date)))) AND (c.del_flg = 0)";
        
        $sql = str_replace($oldFrom, $newFrom, $sql);
        $sql = str_replace($oldSelect, $newSelect, $sql);
        $sql = str_replace($oldWhere, $newWhere, $sql);

        return $this->getDataFromStmtPdo($sql);
    }
    
    /**
     * Get data from sql base on pdoCollection
     * @param type $sql
     * @param type $arrExecute
     * @return PdoCollection
     */
    public function getDataFromStmtPdo($sql) {
        $conn = $this->app['orm.em']->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $rows = new PdoCollection($stmt->getIterator());
        $stmt->closeCursor();
        $this->app['orm.em']->flush();
        $this->app['orm.em']->clear();
        return $rows;
    }
    
    /**
     * Generate Gmo member
     * @param type $rows
     * @param type $connection
     * @return boolean
     */
    public function generateGmoMember(&$rows, &$connection){
        gc_enable();
        
        // Get max id of gmo_member.
        $highest_id = $this->app['orm.em']->createQueryBuilder()
            ->select('MAX(m.id)')
            ->from('\Plugin\GmoPaymentGateway\Entity\GmoMember', 'm')
            ->getQuery()
            ->getSingleScalarResult();  
        $id = is_null($highest_id) ? 1 : $highest_id + 1;

        $data = array();
       
        foreach ($rows as $customer) {
            $create_date = date_format(date_create($customer['create_date']), 'YmdHis');
            $GmoMemberId = $this->createOldGmoMemberId($this->app, $customer['customer_id'], $create_date);
            $data[] = $this->createDataGmoMember($id, $customer, $GmoMemberId);
            $id++;
        }
        $values = implode(',', $data);
        if(empty($data)){
            return true;
        }
        $insert = "INSERT INTO dtb_gmo_member(
                            id, customer_id, customer_create_date, old_member_id, new_member_id, create_date, update_date)
                    VALUES $values";
        $connection->executeUpdate($insert);
        
        // Set new seq for posgress
        if ($connection->getDatabasePlatform()->getName() == "postgresql") {
            $updateSeq = "SELECT setval('dtb_gmo_member_id_seq', $id);";
            $connection->executeUpdate($updateSeq);
        } elseif ($connection->getDatabasePlatform()->getName() == "mysql") {
            $updateSeq = "ALTER TABLE dtb_gmo_member AUTO_INCREMENT = $id ;";
            $connection->executeUpdate($updateSeq);
        }
        return true;
    }
    
    /**
     * Create old GmoMemberId
     * @param type $app
     * @param type $customer_id
     * @param type $create_date
     * @param type $isNewAlgorithm
     * @return type
     */
    public function createOldGmoMemberId($app, $customer_id, $create_date) {
        $raw = $customer_id . '_' . $create_date;
        $GmoMemberId = hash($app['config']['GmoPaymentGateway']['const']['GMO_MEMBER_ID_ENCRYPTION'], $raw);
        
        return $GmoMemberId;
    }

    /**
     * Create new data GMO member.
     * @param type $id
     * @param type $customer
     * @param type $gmoMemberId
     * @return boolean
     */
    public function createDataGmoMember($id, $customer, $gmoMemberId){
        // Check null parameters
        if (is_null($customer) || is_null($gmoMemberId)) {
            return false;
            // Update or create            
        } else {
            $customer_id = $customer['customer_id'];
            $customer_create_date = date_format(date_create($customer['create_date']), 'Y-m-d H:i:s');
            $create_date = date('Y-m-d H:i:s');
            
            return "('$id', '$customer_id', '$customer_create_date', '$gmoMemberId', '$gmoMemberId', '$create_date', '$create_date')";
        }
    }
}