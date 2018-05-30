<?php namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Make serial number activation date nullable
 *
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class Version20160224154053 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE SerialNumbers MODIFY ActivationDate DATETIME');
        $this->addSql('ALTER TABLE SerialNumbers MODIFY OrderDate DATETIME');
        $this->addSql('ALTER TABLE SerialNumbers MODIFY Number VARCHAR(26) NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql("ALTER TABLE SerialNumbers ALTER COLUMN ActivationDate SET DEFAULT '0000-00-00 00:00:00'");
        $this->addSql("ALTER TABLE SerialNumbers ALTER COLUMN OrderDate SET DEFAULT '0000-00-00 00:00:00'");
        $this->addSql("ALTER TABLE SerialNumbers MODIFY Number VARCHAR(25) NOT NULL DEFAULT ''");
    }
}
