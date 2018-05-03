<?php

namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Migrations for new Medical Assistant profession
 */
class Version20150828101051 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // add new Medical Assistant profession
        $this->addSql('INSERT INTO fisdap2_profession SET id=19, name="Medical Assistant"');

        // add the new cert level for Medical Assistant
        $this->addSql('INSERT INTO fisdap2_certification_levels SET id=26, name="medical-assistant", description="Medical Assistant", abbreviation="MA", profession_id=19, bit_value=16777216');

        // add a couple practice categories for the cert level
        $this->addSql('INSERT INTO fisdap2_practice_categories_defaults SET certification_level_id = 26, name = "New Category", profession_id=19');
        $this->addSql('INSERT INTO fisdap2_practice_categories_defaults SET certification_level_id = 26, name = "New Category", profession_id=19');

        // add the products
        $this->addSql('INSERT INTO fisdap2_product SET id=52, category_id=1, name="Skills Tracker - Unlimited", short_name="Sk Tr", description="Online documenting for patient care worksheets with graduation and goals reports.", configuration=1, price=55.00, has_multiple_attempts=0, staff_only=0, profession_id=19');
        $this->addSql('INSERT INTO fisdap2_product SET id=53, category_id=1, name="Scheduler - Unlimited", short_name="Schd", description="Schedule students\' clinical placements online where educators, hospital administrators, and students can all view and interact with the live calendar.", configuration=2, price=40.00, has_multiple_attempts=0, staff_only=0, profession_id=19');

        // add the products to quickbooks
        $this->addSql('INSERT INTO fisdap2_product_quickbooks_info SET id=52, product_id=52, item_name="Skills:MedicalAssistant", package_item_name="Skills:MedicalAssistant_Pkg", package_discount_price=45');
        $this->addSql('INSERT INTO fisdap2_product_quickbooks_info SET id=53, product_id=53, item_name="Sched:MedicalAssistant", package_item_name="Sched:MedicalAssistant_Pkg", package_discount_price=35');

        // add a product package for the certification level
        $this->addSql('INSERT INTO fisdap2_product_package SET id=30, certification_id=26, name="Internship Package", description="Everything Fisdap has to offer-- with a savings of $15!", configuration=3, price = 80.00, products_description="Scheduler, Skills Tracker", quickbooks_name="Internship Package-MedicalAssistant"');

        // add categories for Medical Assistant reports
        $this->addSql("INSERT INTO fisdap2_report_category SET id = 109, name = 'Accreditation', profession_id = 19;");
        $this->addSql("INSERT INTO fisdap2_report_category SET id = 110, name = 'Diagnostic', profession_id = 19");
        $this->addSql("INSERT INTO fisdap2_report_category SET id = 111, name = 'Evals', profession_id = 19;");
        $this->addSql("INSERT INTO fisdap2_report_category SET id = 112, name = 'Goals', profession_id = 19;");
        $this->addSql("INSERT INTO fisdap2_report_category SET id = 113, name = 'Instructor', profession_id = 19;");
        $this->addSql("INSERT INTO fisdap2_report_category SET id = 114, name = 'Internship', profession_id = 19;");
        $this->addSql("INSERT INTO fisdap2_report_category SET id = 115, name = 'Preceptor', profession_id = 19;");
        $this->addSql("INSERT INTO fisdap2_report_category SET id = 116, name = 'Program', profession_id = 19;");
        $this->addSql("INSERT INTO fisdap2_report_category SET id = 117, name = 'Shifts', profession_id = 19;");
        $this->addSql("INSERT INTO fisdap2_report_category SET id = 118, name = 'Site', profession_id = 19;");
        $this->addSql("INSERT INTO fisdap2_report_category SET id = 119, name = 'Skills', profession_id = 19;");
        $this->addSql("INSERT INTO fisdap2_report_category SET id = 120, name = 'Student', profession_id = 19;");

        // add reports to Medical Assistant categories
        // add skills report to Internship, Skills, Student categories
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 1, category_id = 114;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 1, category_id = 119;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 1, category_id = 120;");

        // add skills finder report to Internship, Skills, Student and Diagnostic categories
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 2, category_id = 110;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 2, category_id = 114;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 2, category_id = 119;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 2, category_id = 120;");

        // add narrative report to Internship, Skills, Student categories
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 3, category_id = 114;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 3, category_id = 119;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 3, category_id = 120;");

        // add productivity report to accreditation, diagnostic, preceptor, site
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 4, category_id = 109;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 4, category_id = 110;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 4, category_id = 115;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 4, category_id = 118;");

        // add Lab practice report
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 9, category_id = 111;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 9, category_id = 112;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 9, category_id = 120;");

        // add observed team lead report
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 10, category_id = 110;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 10, category_id = 114;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 10, category_id = 120;");

        // add shift requests report
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 11, category_id = 117;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 11, category_id = 120;");

        // add attendance report
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 12, category_id = 110;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 12, category_id = 117;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 12, category_id = 120;");

        // add eureka report
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 13, category_id = 111;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 13, category_id = 114;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 13, category_id = 119;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 13, category_id = 120;");

        // add shift comments report
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 14, category_id = 113;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 14, category_id = 117;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 14, category_id = 120;");

        // add late entry data report
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 15, category_id = 110;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 15, category_id = 117;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 15, category_id = 120;");

        // add preceptor signoff report
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 16, category_id = 115;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 16, category_id = 117;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 16, category_id = 120;");

        // add hours report
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 18, category_id = 114;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 18, category_id = 117;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 18, category_id = 120;");

        // add evals report
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 19, category_id = 111;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 19, category_id = 113;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 19, category_id = 115;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 19, category_id = 116;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 19, category_id = 118;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 19, category_id = 119;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 19, category_id = 120;");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // remove new Medical Assistant profession
        $this->addSql('DELETE FROM fisdap2_profession WHERE id=19 LIMIT 1');

        // remove the cert level for Medical Assistant
        $this->addSql('DELETE FROM fisdap2_certification_levels WHERE id=26 LIMIT 1');

        // remove the practice categories
        $this->addSql('DELETE FROM fisdap2_practice_categories_defaults WHERE certification_level_id = 26 AND profession_id=19  LIMIT 2');

        // remove the products
        $this->addSql('DELETE FROM fisdap2_product WHERE id=52 LIMIT 1');
        $this->addSql('DELETE FROM fisdap2_product WHERE id=53 LIMIT 1');

        // remove the products from quickbooks
        $this->addSql('DELETE FROM fisdap2_product_quickbooks_info WHERE id=52 LIMIT 1');
        $this->addSql('DELETE FROM fisdap2_product_quickbooks_info WHERE id=53 LIMIT 1');

        // remove product package
        $this->addSql('DELETE FROM fisdap2_product_package WHERE id=30 LIMIT 1');

        // remove categories and links
        $this->addSql("DELETE FROM fisdap2_report_category WHERE profession_id = 19 LIMIT 12;");
        $this->addSql("DELETE FROM fisdap2_category_report WHERE category_id IN (109, 110, 111, 112, 113, 114, 115, 116, 117, 118, 119, 120)");
    }
}
