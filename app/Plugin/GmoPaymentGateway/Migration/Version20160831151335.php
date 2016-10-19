<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

class Version20160831151335 extends AbstractMigration
{

    public function up(Schema $schema)
    {   
        $this->clearGmoMemberData();
    }
    
    public function down(Schema $schema)
    {

    }
    
    /**
     * Clear all data in dtb_gmo_member.
     */
    public function clearGmoMemberData() {
         $clearSql = "DELETE FROM dtb_gmo_member;";
         $this->connection->executeUpdate($clearSql);
    }

}
