<?php namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Change column type for psg_user_id for proper UUID storage
 *
 * @package Fisdap\Data\DoctrineMigrations
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class Version20160208165843 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE fisdap2_users CHANGE psg_user_id psg_user_id VARBINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE fisdap2_users CHANGE psg_user_id psg_user_id VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci');
    }
}
