<?php namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Allow serial numbers to be associated with user contexts
 *
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class Version20151228193614 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE SerialNumbers ADD userContext_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE SerialNumbers ADD CONSTRAINT FK_696ACEA56921EEE6 FOREIGN KEY (userContext_id) REFERENCES fisdap2_user_roles (id)');
        $this->addSql('CREATE INDEX IDX_696ACEA56921EEE6 ON SerialNumbers (userContext_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE SerialNumbers DROP FOREIGN KEY FK_696ACEA56921EEE6');
        $this->addSql('DROP INDEX IDX_696ACEA56921EEE6 ON SerialNumbers');
        $this->addSql('ALTER TABLE SerialNumbers DROP userContext_id');
    }
}
