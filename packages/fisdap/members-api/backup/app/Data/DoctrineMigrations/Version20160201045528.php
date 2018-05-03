<?php namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;


/**
 * Correct unique index on StaffData table
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class Version20160201045528 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE StaffData DROP INDEX user_id, ADD UNIQUE INDEX UNIQ_4C575CCDA76ED395 (user_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE StaffData DROP INDEX user_id, ADD INDEX IDX_4C575CCDA76ED395 (user_id)');
    }
}
