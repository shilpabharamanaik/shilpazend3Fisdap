<?php namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Bug fix to unlock Patients that were incorrectly locked
 *
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
class Version20170307113412 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('UPDATE fisdap2_patients AS p LEFT JOIN fisdap2_runs AS r ON (r.id = p.run_id) SET p.locked = r.locked WHERE p.locked != r.locked AND p.locked = 1;');
    }


    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // No good way to undo this...
    }
}
