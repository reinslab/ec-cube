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
class Version201603252300 extends AbstractMigration
{

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // create table
        $this->createPlgProductOptionOption($schema);
        $this->createPlgProductOptionOptionCategory($schema);
        $this->createPlgProductOptionProductOption($schema);
        $this->createPlgProductOptionOrderOption($schema);
        $this->createPlgProductOptionOrderOptionItem($schema);
        $this->createPlgProductOptionOrderDetail($schema);
        $this->createPlgProductOptionShipmentItem($schema);
        $this->createPlgProductOptionMtbType($schema);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->dropPlgProductOptionOption($schema);
        $this->dropPlgProductOptionOptionCategory($schema);
        $this->dropPlgProductOptionProductOption($schema);
        $this->dropPlgProductOptionOrderOption($schema);
        $this->dropPlgProductOptionOrderOptionItem($schema);
        $this->dropPlgProductOptionOrderDetail($schema);
        $this->dropPlgProductOptionShipmentItem($schema);
        $this->dropPlgProductOptionMtbType($schema);
    }

    public function postUp(Schema $schema)
    {

        $app = new \Eccube\Application();
        $app->boot();

        $this->connection->insert('plg_productoption_mtb_type' , array('id' => 1 , 'name' => 'プルダウン', 'rank' => 1));
        $this->connection->insert('plg_productoption_mtb_type' , array('id' => 2 , 'name' => 'ラジオボタン', 'rank' => 2));
        $this->connection->insert('plg_productoption_mtb_type' , array('id' => 3 , 'name' => 'テキストボックス', 'rank' => 3));
        $this->connection->insert('plg_productoption_mtb_type' , array('id' => 4 , 'name' => 'テキストエリア', 'rank' => 4));
    }

    protected function createPlgProductOptionOption(Schema $schema)
    {
        if ($schema->hasTable("plg_productoption_dtb_option")) {
            return true;
        }
        $table = $schema->createTable("plg_productoption_dtb_option");
        $table->addColumn('option_id', 'integer', array(
            'autoincrement' => true,
        ));

        $table->addColumn('name', 'text', array(
        ));

        $table->addColumn('manage_name', 'text', array(
        ));

        $table->addColumn('description', 'text', array(
            'notnull' => false,
        ));

        $table->addColumn('type_id', 'smallint', array(
            'notnull' => false,
            'unsigned' => false,
            'default' => 1,
        ));

        $table->addColumn('description_flg', 'smallint', array(
            'notnull' => false,
            'default' => 0,
        ));

        $table->addColumn('pricedisp_flg', 'boolean', array(
            'notnull' => false,
            'default' => false,
        ));

        $table->addColumn('is_required', 'boolean', array(
            'notnull' => false,
            'default' => false,
        ));

        $table->addColumn('rank', 'integer', array(
            'notnull' => false,
            'unsigned' => false,
        ));

        $table->addColumn('creator_id', 'integer', array(
            'notnull' => false,
            'unsigned' => false,
        ));

        $table->addColumn('del_flg', 'smallint', array(
            'notnull' => true,
            'unsigned' => false,
            'default' => 0,
        ));

        $table->addColumn('create_date', 'datetime', array(
            'notnull' => true,
            'unsigned' => false,
        ));

        $table->addColumn('update_date', 'datetime', array(
            'notnull' => true,
            'unsigned' => false,
        ));

        $table->setPrimaryKey(array('option_id'));
    }
    
    protected function dropPlgProductOptionOption(Schema $schema)
    {
        if (!$schema->hasTable("plg_productoption_dtb_option")) {
            return true;
        }
        $schema->dropTable('plg_productoption_dtb_option');
        if ($schema->hasSequence('plg_productoption_dtb_option_option_id_seq')) {
            $schema->dropSequence('plg_productoption_dtb_option_option_id_seq');
        }
    }

    protected function createPlgProductOptionOptionCategory(Schema $schema)
    {
        if ($schema->hasTable("plg_productoption_dtb_optioncategory")) {
            return true;
        }
        $table = $schema->createTable("plg_productoption_dtb_optioncategory");
        $table->addColumn('optioncategory_id', 'integer', array(
            'autoincrement' => true,
        ));

        $table->addColumn('option_id', 'integer', array(
            'notnull' => true,
        ));

        $table->addColumn('name', 'text', array(
            'notnull' => false,
        ));

        $table->addColumn('value', 'decimal', array(
            'notnull' => false,
        ));

        $table->addColumn('description', 'text', array(
            'notnull' => false,
        ));

        $table->addColumn('option_image', 'text', array(
            'notnull' => false,
        ));

        $table->addColumn('disable_flg', 'boolean', array(
            'notnull' => false,
            'default' => false,
        ));

        $table->addColumn('init_flg', 'boolean', array(
            'notnull' => false,
            'default' => false,
        ));

        $table->addColumn('rank', 'integer', array(
            'notnull' => false,
            'unsigned' => false,
        ));

        $table->addColumn('creator_id', 'integer', array(
            'notnull' => false,
            'unsigned' => false,
        ));

        $table->addColumn('del_flg', 'smallint', array(
            'notnull' => true,
            'unsigned' => false,
            'default' => 0,
        ));

        $table->addColumn('create_date', 'datetime', array(
            'notnull' => true,
            'unsigned' => false,
        ));

        $table->addColumn('update_date', 'datetime', array(
            'notnull' => true,
            'unsigned' => false,
        ));

        $table->setPrimaryKey(array('optioncategory_id'));
    }
    
    protected function dropPlgProductOptionOptionCategory(Schema $schema)
    {
        if (!$schema->hasTable("plg_productoption_dtb_optioncategory")) {
            return true;
        }
        $schema->dropTable('plg_productoption_dtb_optioncategory');
        if ($schema->hasSequence('plg_productoption_dtb_optioncategory_optioncategory_id_seq')) {
            $schema->dropSequence('plg_productoption_dtb_optioncategory_optioncategory_id_seq');
        }
    }

    protected function createPlgProductOptionProductOption(Schema $schema)
    {
        if ($schema->hasTable("plg_productoption_dtb_product_option")) {
            return true;
        }
        $table = $schema->createTable("plg_productoption_dtb_product_option");
        $table->addColumn('product_option_id', 'integer', array(
            'autoincrement' => true,
        ));
        $table->addColumn('product_id', 'integer', array(
            'notnull' => true,
        ));

        $table->addColumn('option_id', 'integer', array(
            'notnull' => true,
        ));
        
        $table->addColumn('rank', 'integer', array(
            'notnull' => false,
            'unsigned' => false,
        ));
        
        $table->setPrimaryKey(array('product_option_id'));
    }
    
    protected function dropPlgProductOptionProductOption(Schema $schema)
    {
        if (!$schema->hasTable("plg_productoption_dtb_product_option")) {
            return true;
        }
        $schema->dropTable('plg_productoption_dtb_product_option');
        if ($schema->hasSequence('plg_productoption_dtb_product_option_product_option_id_seq')) {
            $schema->dropSequence('plg_productoption_dtb_product_option_product_option_id_seq');
        }
    }
    
    protected function createPlgProductOptionOrderOption(Schema $schema)
    {
        if ($schema->hasTable("plg_productoption_dtb_order_option")) {
            return true;
        }
        $table = $schema->createTable("plg_productoption_dtb_order_option");
        $table->addColumn('order_option_id', 'integer', array(
            'autoincrement' => true,
        ));
        $table->addColumn('serial', 'text', array(
            'notnull' => false,
        ));

        $table->addColumn('order_id', 'integer', array(
            'notnull' => false,
        ));
        
        $table->setPrimaryKey(array('order_option_id'));
    }
    
    protected function dropPlgProductOptionOrderOption(Schema $schema)
    {
        if (!$schema->hasTable("plg_productoption_dtb_order_option")) {
            return true;
        }
        $schema->dropTable('plg_productoption_dtb_order_option');
        if ($schema->hasSequence('plg_productoption_dtb_order_option_order_option_id_seq')) {
            $schema->dropSequence('plg_productoption_dtb_order_option_order_option_id_seq');
        }
    }
    
    protected function createPlgProductOptionOrderOptionItem(Schema $schema)
    {
        if ($schema->hasTable("plg_productoption_dtb_order_option_item")) {
            return true;
        }
        $table = $schema->createTable("plg_productoption_dtb_order_option_item");
        $table->addColumn('order_option_id', 'integer', array(
            'notnull' => true,
        ));
        $table->addColumn('option_id', 'integer', array(
            'notnull' => true,
        ));
        $table->addColumn('optioncategory_id', 'integer', array(
            'notnull' => false,
        ));

        $table->addColumn('value', 'text', array(
            'notnull' => false,
        ));
        
        $table->addColumn('option_name', 'text', array(
            'notnull' => false,
        ));
        
        $table->addColumn('option_category_name', 'text', array(
            'notnull' => false,
        ));
        
        $table->addColumn('price', 'decimal', array(
            'notnull' => false,
        ));
    }
    
    protected function dropPlgProductOptionOrderOptionItem(Schema $schema)
    {
        if (!$schema->hasTable("plg_productoption_dtb_order_option_item")) {
            return true;
        }
        $schema->dropTable('plg_productoption_dtb_order_option_item');
    }
    
    protected function createPlgProductOptionOrderDetail(Schema $schema)
    {
        if ($schema->hasTable("plg_productoption_dtb_order_detail")) {
            return true;
        }
        $table = $schema->createTable("plg_productoption_dtb_order_detail");
        $table->addColumn('order_detail_id', 'integer', array(
            'notnull' => true,
        ));

        $table->addColumn('order_option_id', 'integer', array(
            'notnull' => true,
        ));
    }
    
    protected function dropPlgProductOptionOrderDetail(Schema $schema)
    {
        if (!$schema->hasTable("plg_productoption_dtb_order_detail")) {
            return true;
        }
        $schema->dropTable('plg_productoption_dtb_order_detail');
    }
    
    protected function createPlgProductOptionShipmentItem(Schema $schema)
    {
        if ($schema->hasTable("plg_productoption_dtb_shipment_item")) {
            return true;
        }
        $table = $schema->createTable("plg_productoption_dtb_shipment_item");
        $table->addColumn('item_id', 'integer', array(
            'notnull' => true,
        ));

        $table->addColumn('order_option_id', 'integer', array(
            'notnull' => true,
        ));
    }
    
    protected function dropPlgProductOptionShipmentItem(Schema $schema)
    {
        if (!$schema->hasTable("plg_productoption_dtb_shipment_item")) {
            return true;
        }
        $schema->dropTable('plg_productoption_dtb_shipment_item');
    }

    protected function createPlgProductOptionMtbType(Schema $schema)
    {
        if ($schema->hasTable("plg_productoption_mtb_type")) {
            return true;
        }
        $table = $schema->createTable("plg_productoption_mtb_type");
        $table->addColumn('id', 'smallint', array(
            'notnull' => true,
        ));

        $table->addColumn('name', 'text', array(
            'notnull' => false,
        ));

        $table->addColumn('rank', 'smallint', array(
            'notnull' => true,
        ));

        $table->setPrimaryKey(array('id'));
    }
    
    protected function dropPlgProductOptionMtbType(Schema $schema)
    {
        if (!$schema->hasTable("plg_productoption_mtb_type")) {
            return true;
        }
        $schema->dropTable('plg_productoption_mtb_type');
    }
}
