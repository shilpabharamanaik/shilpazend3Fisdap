<?php

namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Changing column data type for "notes" to TEXT to address problem on ticket TT-561
 */
class Version20141030152527 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("ALTER TABLE fisdap2_scenarios MODIFY notes TEXT");
    }

    public function down(Schema $schema)
    {
        $this->addSql("ALTER TABLE fisdap2_scenarios MODIFY notes VARCHAR(255)");
    }
}
