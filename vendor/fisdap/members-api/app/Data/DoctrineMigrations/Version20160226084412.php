<?php

namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160226084412 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // make the changing instructor nullable
        $this->addSql('ALTER TABLE InstPermHistory MODIFY Changing_Inst_id MEDIUMINT(8) NULL');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // make the changing instructor not nullable
        $this->addSql('ALTER TABLE InstPermHistory MODIFY Changing_Inst_id MEDIUMINT(8) NOT NULL');

    }
}
