<?php namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Create LTI tables
 *
 * @package Fisdap\Data\DoctrineMigrations
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class Version20160206011815 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE lti_consumer (consumer_key VARCHAR(255) NOT NULL, name VARCHAR(45) NOT NULL, secret VARCHAR(32) NOT NULL, lti_version VARCHAR(12) DEFAULT NULL, consumer_name VARCHAR(255) DEFAULT NULL, consumer_version VARCHAR(255) DEFAULT NULL, consumer_guid VARCHAR(255) DEFAULT NULL, css_path VARCHAR(255) DEFAULT NULL, protected TINYINT(1) NOT NULL, enabled TINYINT(1) NOT NULL, enable_from DATETIME DEFAULT NULL, enable_until DATETIME DEFAULT NULL, last_access DATE DEFAULT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, PRIMARY KEY(consumer_key)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lti_context (context_id VARCHAR(255) NOT NULL, consumer_key VARCHAR(255) DEFAULT NULL, primary_consumer_key VARCHAR(255) DEFAULT NULL, primary_context_id VARCHAR(255) DEFAULT NULL, lti_context_id VARCHAR(255) DEFAULT NULL, lti_resource_id VARCHAR(255) DEFAULT NULL, title VARCHAR(255) NOT NULL, settings LONGTEXT NOT NULL, share_approved TINYINT(1) DEFAULT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, INDEX IDX_CD1C1C64D2702362 (consumer_key), INDEX IDX_CD1C1C64486B7FB4 (primary_consumer_key), INDEX IDX_CD1C1C6455A42DA9 (primary_context_id), PRIMARY KEY(context_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lti_nonce (consumer_key VARCHAR(255) NOT NULL, value VARCHAR(32) NOT NULL, expires DATETIME NOT NULL, INDEX IDX_E3B42F80D2702362 (consumer_key), PRIMARY KEY(consumer_key, value)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lti_share_key (share_key_id VARCHAR(255) NOT NULL, primary_consumer_key VARCHAR(255) DEFAULT NULL, primary_context_id VARCHAR(255) DEFAULT NULL, auto_approve TINYINT(1) NOT NULL, expires DATETIME NOT NULL, INDEX IDX_C991D023486B7FB4 (primary_consumer_key), INDEX IDX_C991D02355A42DA9 (primary_context_id), PRIMARY KEY(share_key_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lti_user (consumer_key VARCHAR(255) NOT NULL, context_id VARCHAR(255) NOT NULL, user_id VARCHAR(255) NOT NULL, lti_result_sourcedid VARCHAR(255) NOT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, INDEX IDX_C32E9178D2702362 (consumer_key), INDEX IDX_C32E91786B00C1CF (context_id), PRIMARY KEY(consumer_key, context_id, user_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE lti_context ADD CONSTRAINT FK_CD1C1C64D2702362 FOREIGN KEY (consumer_key) REFERENCES lti_consumer (consumer_key)');
        $this->addSql('ALTER TABLE lti_context ADD CONSTRAINT FK_CD1C1C64486B7FB4 FOREIGN KEY (primary_consumer_key) REFERENCES lti_consumer (consumer_key)');
        $this->addSql('ALTER TABLE lti_context ADD CONSTRAINT FK_CD1C1C6455A42DA9 FOREIGN KEY (primary_context_id) REFERENCES lti_context (context_id)');
        $this->addSql('ALTER TABLE lti_nonce ADD CONSTRAINT FK_E3B42F80D2702362 FOREIGN KEY (consumer_key) REFERENCES lti_consumer (consumer_key)');
        $this->addSql('ALTER TABLE lti_share_key ADD CONSTRAINT FK_C991D023486B7FB4 FOREIGN KEY (primary_consumer_key) REFERENCES lti_consumer (consumer_key)');
        $this->addSql('ALTER TABLE lti_share_key ADD CONSTRAINT FK_C991D02355A42DA9 FOREIGN KEY (primary_context_id) REFERENCES lti_context (context_id)');
        $this->addSql('ALTER TABLE lti_user ADD CONSTRAINT FK_C32E9178D2702362 FOREIGN KEY (consumer_key) REFERENCES lti_consumer (consumer_key)');
        $this->addSql('ALTER TABLE lti_user ADD CONSTRAINT FK_C32E91786B00C1CF FOREIGN KEY (context_id) REFERENCES lti_context (context_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE lti_context DROP FOREIGN KEY FK_CD1C1C64D2702362');
        $this->addSql('ALTER TABLE lti_context DROP FOREIGN KEY FK_CD1C1C64486B7FB4');
        $this->addSql('ALTER TABLE lti_nonce DROP FOREIGN KEY FK_E3B42F80D2702362');
        $this->addSql('ALTER TABLE lti_share_key DROP FOREIGN KEY FK_C991D023486B7FB4');
        $this->addSql('ALTER TABLE lti_user DROP FOREIGN KEY FK_C32E9178D2702362');
        $this->addSql('ALTER TABLE lti_context DROP FOREIGN KEY FK_CD1C1C6455A42DA9');
        $this->addSql('ALTER TABLE lti_share_key DROP FOREIGN KEY FK_C991D02355A42DA9');
        $this->addSql('ALTER TABLE lti_user DROP FOREIGN KEY FK_C32E91786B00C1CF');
        $this->addSql('DROP TABLE lti_consumer');
        $this->addSql('DROP TABLE lti_context');
        $this->addSql('DROP TABLE lti_nonce');
        $this->addSql('DROP TABLE lti_share_key');
        $this->addSql('DROP TABLE lti_user');
    }
}
