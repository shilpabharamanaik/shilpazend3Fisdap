<?php

namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Migrations for Sales Report
 */
class Version20151202143723 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // add report
        $id = 30;
        $name = "Annual Sales Report";
        $class = "AnnualSales";
        $description = "Annual sales by program.";
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
        $this->addSql('delete from fisdap2_reports where id = 30');
    }
}
