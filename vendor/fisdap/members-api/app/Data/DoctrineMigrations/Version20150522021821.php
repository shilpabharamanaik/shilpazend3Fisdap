<?php namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Changing Attachment urlRoot to savePath; adding processed flag
 *
 * @author Ben Getsug <bgetsug@fisdap.net>
 */
class Version20150522021821 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Attachments ADD processed TINYINT(1) NOT NULL, CHANGE urlRoot savePath VARCHAR(255) NOT NULL');
    }


    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Attachments DROP processed, CHANGE savePath urlRoot VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
    }
}
