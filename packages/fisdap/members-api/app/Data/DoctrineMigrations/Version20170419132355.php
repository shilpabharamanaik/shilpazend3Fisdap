<?php

namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170419132355 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs

        $this->addSql("INSERT INTO fisdap2_airway_procedure (name,type,require_success,require_attempts,require_size,is_als) VALUES ('Extubation','Extubation',0,0,0,0)");
        $this->addSql("INSERT INTO fisdap2_airway_procedure (name,type,require_success,require_attempts,require_size,is_als) VALUES ('Impedance Threshold Device','Impedance Threshold Device',0,0,0,0)");

        $this->addSql("INSERT INTO fisdap2_med_type (name) VALUES ('Keppra (Levetiracetam)')");
        $this->addSql("INSERT INTO fisdap2_med_type (name) VALUES ('Hydrocortisone Sodium Succinate (Solu-Cortef)')");
        $this->addSql("INSERT INTO fisdap2_med_type (name) VALUES ('Rocuronium bromide')");
        $this->addSql("INSERT INTO fisdap2_med_type (name) VALUES ('Magnesium')");
        $this->addSql("INSERT INTO fisdap2_med_type (name) VALUES ('Racemic Epinephrine')");
        $this->addSql("INSERT INTO fisdap2_med_type (name) VALUES ('Calcium gluconate gel')");
        $this->addSql("INSERT INTO fisdap2_med_type (name) VALUES ('Dextrose (D50) -> Dextrose')");
        $this->addSql("INSERT INTO fisdap2_med_type (name) VALUES ('Rocephin')");
        $this->addSql("INSERT INTO fisdap2_med_type (name) VALUES ('Propofol')");
        $this->addSql("INSERT INTO fisdap2_med_type (name) VALUES ('Lovenox')");
        $this->addSql("INSERT INTO fisdap2_med_type (name) VALUES ('Bentyl')");
        $this->addSql("INSERT INTO fisdap2_med_type (name) VALUES ('Pepcid (Famotidine)')");
        $this->addSql("INSERT INTO fisdap2_med_type (name) VALUES ('Enalapril (Vasotec)')");
        $this->addSql("INSERT INTO fisdap2_med_type (name) VALUES ('Normal Saline')");
        $this->addSql("INSERT INTO fisdap2_med_type (name) VALUES ('Tranexamic Acid')");
        $this->addSql("INSERT INTO fisdap2_med_type (name) VALUES ('Duoneb')");
        $this->addSql("INSERT INTO fisdap2_med_type (name) VALUES ('Clopidogrel (Plavix)')");


        $this->addSql("INSERT INTO fisdap2_other_procedure (name,require_success,require_attempts,require_size) VALUES ('Short Board',0,0,0)");
        $this->addSql("INSERT INTO fisdap2_other_procedure (name,require_success,require_attempts,require_size) VALUES ('Morgan\'s Lens',0,0,0)");
        $this->addSql("INSERT INTO fisdap2_other_procedure (name,require_success,require_attempts,require_size) VALUES ('Glucometer',0,0,0)");
        $this->addSql("INSERT INTO fisdap2_other_procedure (name,require_success,require_attempts,require_size) VALUES ('Tourniquet',0,0,0)");
        $this->addSql("INSERT INTO fisdap2_other_procedure (name,require_success,require_attempts,require_size) VALUES ('Ice Pack (cooling)',0,0,0)");


        /* We need nsc_type' and goal_def_id' */
        $this->addSql("INSERT INTO fisdap2_impression (name,nsc_type,goal_def_id) VALUES ('GI Bleed','abdomen','19')");
        $this->addSql("INSERT INTO fisdap2_impression (name,nsc_type,goal_def_id) VALUES ('Trauma - Unspecified','trauma','17')");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
