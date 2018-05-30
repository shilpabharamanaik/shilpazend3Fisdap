<?php

namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150512105801 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('insert into fisdap2_reports (name, class, description, student_description, standalone) values ("Surgical Rotation Case Report", "SurgicalRotationCase", "See your students\' number of first scrub, second scrub, and observation only cases.", "See your number of first scrub, second scrub, and observation only cases", 0)');
        $this->addSql("insert into fisdap2_category_report (category_id, report_id) values (61,27)");
        $this->addSql("insert into fisdap2_category_report (category_id, report_id) values (66,27)");
        $this->addSql("insert into fisdap2_category_report (category_id, report_id) values (72,27)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("delete from fisdap2_reports where id = 27 limit 1");
        $this->addSql("delete from fisdap2_category_report where report_id = 27 limit 3");
    }
}
