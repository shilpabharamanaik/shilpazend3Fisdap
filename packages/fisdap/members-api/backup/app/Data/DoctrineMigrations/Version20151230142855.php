<?php namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Make sure usernames are unique!
 *
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class Version20151230142855 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('DROP INDEX username ON fisdap2_users');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5C98DDBAF85E0677 ON fisdap2_users (username)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql('DROP INDEX UNIQ_5C98DDBAF85E0677 ON fisdap2_users');
        $this->addSql('CREATE INDEX username ON fisdap2_users (username)');
    }
}
