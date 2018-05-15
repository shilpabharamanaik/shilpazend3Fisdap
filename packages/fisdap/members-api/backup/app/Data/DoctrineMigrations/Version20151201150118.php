<?php

namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add new database columns to the Site accreditation info table
 */
class Version20151201150118 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        //add new columns
        $this->addSql('ALTER TABLE fisdap2_site_accreditation_info ADD number_of_runs INT DEFAULT NULL');
        $this->addSql('ALTER TABLE fisdap2_site_accreditation_info ADD number_of_trauma_calls INT DEFAULT NULL');
        $this->addSql('ALTER TABLE fisdap2_site_accreditation_info ADD number_of_critical_trauma_calls INT DEFAULT NULL');
        $this->addSql('ALTER TABLE fisdap2_site_accreditation_info ADD number_of_pediatric_calls INT DEFAULT NULL');
        $this->addSql('ALTER TABLE fisdap2_site_accreditation_info ADD number_of_cardiac_arrest_calls INT DEFAULT NULL');
        $this->addSql('ALTER TABLE fisdap2_site_accreditation_info ADD number_of_cardiac_calls INT DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        //remove new columns
        $this->addSql('ALTER TABLE fisdap2_site_accreditation_info DROP COLUMN number_of_runs');
        $this->addSql('ALTER TABLE fisdap2_site_accreditation_info DROP COLUMN number_of_trauma_calls');
        $this->addSql('ALTER TABLE fisdap2_site_accreditation_info DROP COLUMN number_of_critical_trauma_calls');
        $this->addSql('ALTER TABLE fisdap2_site_accreditation_info DROP COLUMN number_of_pediatric_calls');
        $this->addSql('ALTER TABLE fisdap2_site_accreditation_info DROP COLUMN number_of_cardiac_arrest_calls');
        $this->addSql('ALTER TABLE fisdap2_site_accreditation_info DROP COLUMN number_of_cardiac_calls');
    }
}
