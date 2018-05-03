<?php namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Need to reconcile locked between Patient and Run
 *
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
class Version20170217113412 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // Get the patients that were locked from mobile and update the corresponding run records.
        $this->addSql('UPDATE fisdap2_patients AS p LEFT JOIN fisdap2_runs AS r ON (r.id = p.run_id) SET r.locked = p.locked WHERE p.locked != r.locked AND p.locked = 1;');

        // Get the runs that were locked from members and update the corresponding patient records.
        $this->addSql('UPDATE fisdap2_patients AS p LEFT JOIN fisdap2_runs AS r ON (r.id = p.run_id) SET p.locked = r.locked WHERE p.locked != r.locked AND r.locked = 1;');
    }


    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // No good way to undo this...
    }
}
