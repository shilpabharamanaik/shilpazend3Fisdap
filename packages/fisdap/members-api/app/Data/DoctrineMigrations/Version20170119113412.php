<?php namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Sets patient_id for existing preceptor_signoff records.
 *
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
class Version20170119113412 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('UPDATE fisdap2_preceptor_signoffs AS ps LEFT JOIN fisdap2_patients AS p ON p.run_id = ps.run_id SET ps.patient_id = p.id WHERE ps.shift_id IS NULL');
    }


    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
    }
}
