<?php

namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add a active status for notifications, per MAIN-2421
 */
class Version20150729154515 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // add active status for notifications
        $this->addSql('ALTER TABLE fisdap2_notifications ADD COLUMN active BOOLEAN NULL DEFAULT 1');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // remove active status from notifications
        $this->addSql('ALTER TABLE fisdap2_notifications DROP COLUMN active');
    }
}
