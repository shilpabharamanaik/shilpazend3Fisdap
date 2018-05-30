<?php namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add a courseId column to the UserContexts table
 *
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class Version20160104164703 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE fisdap2_user_roles ADD courseId INT DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE fisdap2_user_roles DROP courseId');
    }
}
