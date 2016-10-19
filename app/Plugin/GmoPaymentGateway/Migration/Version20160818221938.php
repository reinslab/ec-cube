<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20160818221938 extends AbstractMigration
{

    public function up(Schema $schema)
    {   
        $this->createDtbGmoCustomer($schema);
    }
    
    public function down(Schema $schema)
    {
        $names = array('dtb_gmo_member');
        foreach ($names as $name){
            if ($schema->hasTable($name)){
                $schema->dropTable($name);
            }
        }
    }
    
    /**
     * Create dtb_gmo_customer
     * @param Schema $schema
    */
    public function createDtbGmoCustomer(Schema $schema)
    {
        $table = $schema->createTable("dtb_gmo_member");
        $table->addColumn('id', 'integer', array(
            'autoincrement' => true,
        ));
        
        $table->addColumn('customer_id', 'integer', array(
            'notnull' => true,
        ));
        
        $table->addColumn('customer_create_date', 'datetime', array(
            'notnull' => true,
        ));
        
        $table->addColumn('old_member_id', 'text', array(
            'notnull' => false,
        ));
        
        $table->addColumn('new_member_id', 'text', array(
            'notnull' => false,
        ));
        
        $table->addColumn('create_date', 'datetime', array(
            'notnull' => true,
            'unsigned' => false,
        ));

        $table->addColumn('update_date', 'datetime', array(
            'notnull' => true,
            'unsigned' => false,
        ));
        
        // Add index
        $table->addIndex(array('customer_id', 'customer_create_date'), 'dtb_gmo_member_customer_id_customer_create_date_index');
        
        // Create foreign key between dtb_gmo_member and dtb_customer.
        $targetTable = $schema->getTable('dtb_customer');
        $table->addForeignKeyConstraint(
            $targetTable, array('customer_id'), array('customer_id')
        );
        $table->setPrimaryKey(array('id'));
    } 

}
