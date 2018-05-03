<?php

namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Migrations for Program Professions Report
 */
class Version20150724134837 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // add report
        $id = 29;
        $name = "Program Professions Report";
        $class = "Professions";
        $description = "View a list of all the programs in a given profession.";
        $addReportSql = "INSERT INTO fisdap2_reports (id, name, class, description, student_description, standalone) " .
            "VALUES ($id, \"$name\", \"$class\", \"$description\", \"\", 1)";
        $this->addSql($addReportSql);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // remove report
        $this->addSql('delete from fisdap2_reports where id = 29');
    }
}