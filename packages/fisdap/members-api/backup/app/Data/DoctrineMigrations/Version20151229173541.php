<?php namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;


/**
 * Make sure serial numbers are unique!
 */
class Version20151229173541 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('DROP INDEX `Number` ON SerialNumbers');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_696ACEA5913C1A62 ON SerialNumbers (Number)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql('DROP INDEX UNIQ_696ACEA5913C1A62 ON SerialNumbers');
        $this->addSql('CREATE INDEX `Number` ON SerialNumbers (Number)');
    }
}
