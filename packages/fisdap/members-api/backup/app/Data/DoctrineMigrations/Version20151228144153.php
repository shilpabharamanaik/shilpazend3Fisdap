<?php namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;


/**
 * Add an 'email' field to fisdap2_user_roles (UserContext)
 *
 * @package Fisdap\Data\DoctrineMigrations
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class Version20151228144153 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE fisdap2_user_roles ADD email VARCHAR(255) DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE fisdap2_user_roles DROP email');
    }
}
