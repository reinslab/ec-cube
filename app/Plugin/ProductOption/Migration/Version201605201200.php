<?php
/*
 * Plugin Name : ProductOption
 *
 * Copyright (C) 2015 BraTech Co., Ltd. All Rights Reserved.
 * http://www.bratech.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version201605201200 extends AbstractMigration
{
    
    public function up(Schema $schema)
    {
        if ($schema->hasTable('plg_productoption_dtb_optioncategory')) {
            $targetTable = $schema->getTable('plg_productoption_dtb_optioncategory');
            if(!$targetTable->hasColumn('delivery_free_flg')){
                $targetTable->addColumn('delivery_free_flg', 'smallint', array(
                    'notnull' => false,
                    'unsigned' => false,
                    'default' => 0,
                ));
            }
        }
        
        if($schema->hasTable('plg_productoption_mtb_action')){
            $schema->dropTable('plg_productoption_mtb_action');
        }
        
        if($schema->hasTable('plg_productoption_dtb_option')){
            $targetTable = $schema->getTable('plg_productoption_dtb_option');
            if($targetTable->hasColumn('action_id'))$targetTable->dropColumn('action_id');
        }
    }
    
    public function down(Schema $schema)
    {
        
    }
}
