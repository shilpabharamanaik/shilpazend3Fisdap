<?php

namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add these research related program fields
 */
class Version20150414143252 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE ProgramData ADD COLUMN coaemsp_program_id INT DEFAULT NULL, ADD COLUMN year_accredited INT DEFAULT NULL;");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE ProgramData DROP COLUMN coaemsp_program_id, DROP COLUMN year_accredited;");
    }
}
