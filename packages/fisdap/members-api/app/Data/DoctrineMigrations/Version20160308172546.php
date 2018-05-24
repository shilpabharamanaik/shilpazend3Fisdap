<?php namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;


/**
 * Fixing nullability of Program phone/fax
 * 
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class Version20160308172546 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE ProgramData CHANGE ProgramPhone ProgramPhone VARCHAR(255) DEFAULT NULL, CHANGE ProgramFax ProgramFax VARCHAR(255) DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE ProgramData CHANGE ProgramPhone ProgramPhone VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE ProgramFax ProgramFax VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
    }
}
