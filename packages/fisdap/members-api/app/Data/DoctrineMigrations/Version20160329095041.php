<?php
namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 * This migration is updating our prices for alternate professions as of March 2015
 */

class Version20160329095041 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("ALTER TABLE ProgramData MODIFY CanBuyAccounts TINYINT(1)");
    }

    public function down(Schema $schema)
    {
        $this->addSql("ALTER TABLE ProgramData MODIFY CanBuyAccounts TINYINT(1) NOT NULL DEFAULT 1");
    }
}
