<?php namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;


/**
 * Fix primary key column name on ServiceAccount table
 * 
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class Version20160430014231 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE ServiceAccountPermissions DROP FOREIGN KEY FK_7F7B64BDF9D3407A');
        $this->addSql('ALTER TABLE ServiceAccount DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE ServiceAccount CHANGE oauth2ClientId id VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE ServiceAccount ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE ServiceAccountPermissions ADD CONSTRAINT FK_7F7B64BDF9D3407A FOREIGN KEY (serviceAccountClientId) REFERENCES ServiceAccount (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE ServiceAccountPermissions DROP FOREIGN KEY FK_7F7B64BDF9D3407A');
        $this->addSql('ALTER TABLE ServiceAccount DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE ServiceAccount CHANGE id oauth2ClientId VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE ServiceAccount ADD PRIMARY KEY (oauth2ClientId)');
        $this->addSql('ALTER TABLE ServiceAccountPermissions ADD CONSTRAINT FK_7F7B64BDF9D3407A FOREIGN KEY (serviceAccountClientId) REFERENCES ServiceAccount (oauth2ClientId) ON DELETE CASCADE');
    }
}
