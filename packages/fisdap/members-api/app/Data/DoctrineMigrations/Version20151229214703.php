<?php namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * psg_user_id should be a unique string
 *
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class Version20151229214703 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE fisdap2_users CHANGE psg_user_id psg_user_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5C98DDBA6DDDC68 ON fisdap2_users (psg_user_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql('DROP INDEX UNIQ_5C98DDBA6DDDC68 ON fisdap2_users');
        $this->addSql('ALTER TABLE fisdap2_users CHANGE psg_user_id psg_user_id INT DEFAULT NULL');
    }
}
