<?php

namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Migrations for RADT Accreditation Report
 */
class Version20150714112248 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // add report
        $id = 28;
        $name = "RADT Accreditation Hours Report";
        $class = "RadHours";
        $description = "View a list of each student's scheduled hours and find out whether they meet JRCERT's off-hours requirement.";
        $studentDescription = "View your scheduled hours and find out whether you meet JRCERT's off-hours requirement.";
        $addReportSql = "INSERT INTO fisdap2_reports (id, name, class, description, student_description, standalone) ".
                        "VALUES ($id, \"$name\", \"$class\", \"$description\", \"$studentDescription\", 0)";
        $this->addSql($addReportSql);

        // add report to Accreditation, Shifts, Student and Site categories
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = $id, category_id = 49;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = $id, category_id = 57;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = $id, category_id = 60;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = $id, category_id = 58;");

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // remove report from categories
        $this->addSql('delete from fisdap2_category_report where report_id = 28 limit 4');

        // remove report
        $this->addSql('delete from fisdap2_reports where id = 28');
    }
}
