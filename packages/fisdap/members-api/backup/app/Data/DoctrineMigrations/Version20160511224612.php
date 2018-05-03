<?php namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Creates table for storing LTI Tool Provider configurations
 *
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class Version20160511224612 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('
CREATE TABLE lti_tool_providers (
id INT AUTO_INCREMENT NOT NULL, 
launchUrl VARCHAR(255) NOT NULL, 
logoutUrl VARCHAR(255) DEFAULT NULL, 
logoUrl VARCHAR(255) DEFAULT NULL, 
oauthConsumerKey VARCHAR(255) DEFAULT NULL, 
secret VARCHAR(255) NOT NULL, 
resourceLinkTitle VARCHAR(255) DEFAULT NULL, 
resourceLinkDescription VARCHAR(255) DEFAULT NULL, 
contextId VARCHAR(255) DEFAULT NULL, 
contextTitle VARCHAR(255) DEFAULT NULL, 
customParameters LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', 
PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }


    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql('DROP TABLE lti_tool_providers');
    }
}
