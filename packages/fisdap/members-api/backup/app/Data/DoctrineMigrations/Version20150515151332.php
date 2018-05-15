<?php

namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Adding reports categories and links for the Australian EMS profession and changing the name of the testing product
 */
class Version20150515151332 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // update the names for things that I messed up
        $this->addSql('UPDATE fisdap2_product SET name="Australian Comprehensive Exams" WHERE id=47 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product_quickbooks_info SET package_item_name="Skills:AUS_pkg" WHERE id=44 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product_quickbooks_info SET package_item_name="Sched:AUS_pkg" WHERE id=45 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product_quickbooks_info SET package_item_name="CompExams:AUS_pkg" WHERE id=46 LIMIT 1');

        // add categories for Australian EMS reports
        $this->addSql("INSERT INTO fisdap2_report_category SET id = 73, name = 'Accreditation', profession_id = 16;");
        $this->addSql("INSERT INTO fisdap2_report_category SET id = 74, name = 'Diagnostic', profession_id = 16");
        $this->addSql("INSERT INTO fisdap2_report_category SET id = 75, name = 'Evals', profession_id = 16;");
        $this->addSql("INSERT INTO fisdap2_report_category SET id = 76, name = 'Goals', profession_id = 16;");
        $this->addSql("INSERT INTO fisdap2_report_category SET id = 77, name = 'Instructor', profession_id = 16;");
        $this->addSql("INSERT INTO fisdap2_report_category SET id = 78, name = 'Internship', profession_id = 16;");
        $this->addSql("INSERT INTO fisdap2_report_category SET id = 79, name = 'Preceptor', profession_id = 16;");
        $this->addSql("INSERT INTO fisdap2_report_category SET id = 80, name = 'Program', profession_id = 16;");
        $this->addSql("INSERT INTO fisdap2_report_category SET id = 81, name = 'Shifts', profession_id = 16;");
        $this->addSql("INSERT INTO fisdap2_report_category SET id = 82, name = 'Site', profession_id = 16;");
        $this->addSql("INSERT INTO fisdap2_report_category SET id = 83, name = 'Skills', profession_id = 16;");
        $this->addSql("INSERT INTO fisdap2_report_category SET id = 84, name = 'Student', profession_id = 16;");

        // add reports to AU categories
        // add skills report to Internship, Skills, Student categories
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 1, category_id = 78;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 1, category_id = 83;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 1, category_id = 84;");

        // add skills finder report to Internship, Skills, Student and Diagnostic categories
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 2, category_id = 74;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 2, category_id = 78;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 2, category_id = 83;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 2, category_id = 84;");

        // add narrative report to Internship, Skills, Student categories
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 3, category_id = 78;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 3, category_id = 83;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 3, category_id = 84;");

        // add productivity report to accreditation, diagnostic, preceptor, site
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 4, category_id = 73;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 4, category_id = 74;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 4, category_id = 79;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 4, category_id = 82;");

        // add GRR
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 6, category_id = 73;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 6, category_id = 76;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 6, category_id = 78;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 6, category_id = 83;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 6, category_id = 84;");

        // add G/H
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 7, category_id = 73;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 7, category_id = 84;");

        // add E/F
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 8, category_id = 73;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 8, category_id = 82;");

        // add Lab practice report
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 9, category_id = 75;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 9, category_id = 76;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 9, category_id = 84;");

        // add observed team lead report
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 10, category_id = 74;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 10, category_id = 78;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 10, category_id = 84;");

        // add shift requests report
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 11, category_id = 81;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 11, category_id = 84;");

        // add attendance report
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 12, category_id = 74;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 12, category_id = 81;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 12, category_id = 84;");

        // add eureka report
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 13, category_id = 75;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 13, category_id = 78;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 13, category_id = 83;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 13, category_id = 84;");

        // add shift comments report
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 14, category_id = 77;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 14, category_id = 81;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 14, category_id = 84;");

        // add late entry data report
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 15, category_id = 74;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 15, category_id = 81;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 15, category_id = 84;");

        // add preceptor signoff report
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 16, category_id = 79;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 16, category_id = 81;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 16, category_id = 84;");

        // add als runs report
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 17, category_id = 78;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 17, category_id = 81;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 17, category_id = 83;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 17, category_id = 84;");

        // add hours report
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 18, category_id = 78;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 18, category_id = 81;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 18, category_id = 84;");

        // add evals report
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 19, category_id = 75;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 19, category_id = 77;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 19, category_id = 79;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 19, category_id = 80;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 19, category_id = 82;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 19, category_id = 83;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 19, category_id = 84;");

        // add preceptor training report
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 21, category_id = 73;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 21, category_id = 79;");

        // add airway management report
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 24, category_id = 73;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 24, category_id = 76;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 24, category_id = 78;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 24, category_id = 83;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 24, category_id = 84;");

        // add test item analysis report
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 25, category_id = 73;");

        // add patient acuity report
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 26, category_id = 74;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 26, category_id = 78;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 26, category_id = 83;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 26, category_id = 84;");
    }

    public function down(Schema $schema)
    {
        // update the names for things that I messed up
        $this->addSql('UPDATE fisdap2_product SET name="Comprehensive Exams" WHERE id=47 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product_quickbooks_info SET package_item_name="Skills:AUS" WHERE id=44 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product_quickbooks_info SET package_item_name="Sched:AUS" WHERE id=45 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product_quickbooks_info SET package_item_name="CompExams:AUS" WHERE id=46 LIMIT 1');

        // remove categories and links for Australian EMS reports
        $this->addSql("DELETE FROM fisdap2_report_category WHERE profession_id = 16 LIMIT 12;");
        $this->addSql("DELETE FROM fisdap2_category_report WHERE category_id IN (73, 74, 75, 76, 77, 78, 79, 80, 81, 82, 83, 84)");
    }
}
