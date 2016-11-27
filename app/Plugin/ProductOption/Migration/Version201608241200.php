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
class Version201608241200 extends AbstractMigration
{

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        if ($this->platform->getName() == 'postgresql') {
            if ($schema->hasTable('plg_productoption_dtb_product_option')) {
                $targetTable = $schema->getTable('plg_productoption_dtb_product_option');
                if (!$targetTable->hasIndex('plg_productoption_dtb_product_option_product_id')) {
                    $targetTable->addIndex(['product_id'],'plg_productoption_dtb_product_option_product_id');
                }
            }

            if ($schema->hasTable('plg_productoption_dtb_optioncategory')) {
                $targetTable = $schema->getTable('plg_productoption_dtb_optioncategory');
                if (!$targetTable->hasIndex('plg_productoption_dtb_optioncategory_option_id')) {
                    $targetTable->addIndex(['option_id'],'plg_productoption_dtb_optioncategory_option_id');
                }
            }

            if ($schema->hasTable('plg_productoption_dtb_order_detail')) {
                $targetTable = $schema->getTable('plg_productoption_dtb_order_detail');
                if (!$targetTable->hasIndex('plg_productoption_dtb_order_detail_order_detail_id')) {
                    $targetTable->addIndex(['order_detail_id'],'plg_productoption_dtb_order_detail_order_detail_id');
                }
                if (!$targetTable->hasIndex('plg_productoption_dtb_order_detail_order_option_id')) {
                    $targetTable->addIndex(['order_option_id'],'plg_productoption_dtb_order_detail_order_option_id');
                }
            }

            if ($schema->hasTable('plg_productoption_dtb_shipment_item')) {
                $targetTable = $schema->getTable('plg_productoption_dtb_shipment_item');
                if (!$targetTable->hasIndex('plg_productoption_dtb_shipment_item_item_id')) {
                    $targetTable->addIndex(['item_id'],'plg_productoption_dtb_shipment_item_item_id');
                }
                if (!$targetTable->hasIndex('plg_productoption_dtb_shipment_item_order_option_id')) {
                    $targetTable->addIndex(['order_option_id'],'plg_productoption_dtb_shipment_item_order_option_id');
                }
            }

            if ($schema->hasTable('plg_productoption_dtb_order_option_item')) {
                $targetTable = $schema->getTable('plg_productoption_dtb_order_option_item');
                if (!$targetTable->hasIndex('plg_productoption_dtb_order_option_item_order_option_id')) {
                    $targetTable->addIndex(['order_option_id'],'plg_productoption_dtb_order_option_item_order_option_id');
                }
            }
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {

    }
}