<?php

namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Migrations for new Athletic Trainer profession
 */
class Version20150722161733 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // add new Athletic Trainer profession
        $this->addSql('INSERT INTO fisdap2_profession SET id=17, name="Athletic Trainer"');

        // add the new cert level for athletic trainers
        $this->addSql('INSERT INTO fisdap2_certification_levels SET id=24, name="athletic-trainer", description="Athletic Trainer", abbreviation="AT", profession_id=17, bit_value=4194304');

        // add a couple practice categories for the cert level
        $this->addSql('INSERT INTO fisdap2_practice_categories_defaults SET certification_level_id = 24, name = "New Category", profession_id=17');
        $this->addSql('INSERT INTO fisdap2_practice_categories_defaults SET certification_level_id = 24, name = "New Category", profession_id=17');

        // add the products
        $this->addSql('INSERT INTO fisdap2_product SET id=48, category_id=1, name="Skills Tracker - Unlimited", short_name="Sk Tr", description="Online documenting for patient care worksheets with graduation and goals reports.", configuration=1, price=60.00, has_multiple_attempts=0, staff_only=0, profession_id=17');
        $this->addSql('INSERT INTO fisdap2_product SET id=49, category_id=1, name="Scheduler - Unlimited", short_name="Schd", description="Schedule students\' clinical placements online where educators, hospital administrators, and students can all view and interact with the live calendar.", configuration=2, price=50.00, has_multiple_attempts=0, staff_only=0, profession_id=17');

        // add the products to quickbooks
        $this->addSql('INSERT INTO fisdap2_product_quickbooks_info SET id=47, product_id=48, item_name="Skills:AthleticTrainer", package_item_name="Skills:AthleticTrainer_Pkg", package_discount_price=55');
        $this->addSql('INSERT INTO fisdap2_product_quickbooks_info SET id=48, product_id=49, item_name="Sched:AthleticTrainer", package_item_name="Sched:AthleticTrainer_Pkg", package_discount_price=45');

        // add a product package for the certification level
        $this->addSql('INSERT INTO fisdap2_product_package SET id=28, certification_id=24, name="Internship Package", description="Everything Fisdap has to offer-- with a savings of $10!", configuration=3, price = 100.00, products_description="Scheduler, Skills Tracker", quickbooks_name="Clinical Placement-AthleticTrainer"');

        // add categories for Athletic Trainer reports
        $this->addSql("INSERT INTO fisdap2_report_category SET id = 85, name = 'Accreditation', profession_id = 17;");
        $this->addSql("INSERT INTO fisdap2_report_category SET id = 86, name = 'Diagnostic', profession_id = 17");
        $this->addSql("INSERT INTO fisdap2_report_category SET id = 87, name = 'Evals', profession_id = 17;");
        $this->addSql("INSERT INTO fisdap2_report_category SET id = 88, name = 'Goals', profession_id = 17;");
        $this->addSql("INSERT INTO fisdap2_report_category SET id = 89, name = 'Instructor', profession_id = 17;");
        $this->addSql("INSERT INTO fisdap2_report_category SET id = 90, name = 'Internship', profession_id = 17;");
        $this->addSql("INSERT INTO fisdap2_report_category SET id = 91, name = 'Preceptor', profession_id = 17;");
        $this->addSql("INSERT INTO fisdap2_report_category SET id = 92, name = 'Program', profession_id = 17;");
        $this->addSql("INSERT INTO fisdap2_report_category SET id = 93, name = 'Shifts', profession_id = 17;");
        $this->addSql("INSERT INTO fisdap2_report_category SET id = 94, name = 'Site', profession_id = 17;");
        $this->addSql("INSERT INTO fisdap2_report_category SET id = 95, name = 'Skills', profession_id = 17;");
        $this->addSql("INSERT INTO fisdap2_report_category SET id = 96, name = 'Student', profession_id = 17;");

        // add reports to Athletic TrainerS categories
        // add skills report to Internship, Skills, Student categories
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 1, category_id = 90;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 1, category_id = 95;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 1, category_id = 96;");

        // add skills finder report to Internship, Skills, Student and Diagnostic categories
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 2, category_id = 86;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 2, category_id = 90;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 2, category_id = 95;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 2, category_id = 96;");

        // add narrative report to Internship, Skills, Student categories
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 3, category_id = 90;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 3, category_id = 95;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 3, category_id = 96;");

        // add productivity report to accreditation, diagnostic, preceptor, site
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 4, category_id = 85;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 4, category_id = 86;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 4, category_id = 91;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 4, category_id = 94;");

        // add Lab practice report
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 9, category_id = 87;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 9, category_id = 88;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 9, category_id = 96;");

        // add observed team lead report
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 10, category_id = 86;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 10, category_id = 90;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 10, category_id = 96;");

        // add shift requests report
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 11, category_id = 93;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 11, category_id = 96;");

        // add attendance report
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 12, category_id = 86;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 12, category_id = 93;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 12, category_id = 96;");

        // add eureka report
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 13, category_id = 87;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 13, category_id = 90;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 13, category_id = 95;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 13, category_id = 96;");

        // add shift comments report
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 14, category_id = 89;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 14, category_id = 93;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 14, category_id = 96;");

        // add late entry data report
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 15, category_id = 86;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 15, category_id = 93;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 15, category_id = 96;");

        // add preceptor signoff report
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 16, category_id = 91;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 16, category_id = 93;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 16, category_id = 96;");

        // add hours report
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 18, category_id = 90;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 18, category_id = 93;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 18, category_id = 96;");

        // add evals report
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 19, category_id = 87;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 19, category_id = 89;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 19, category_id = 91;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 19, category_id = 92;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 19, category_id = 94;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 19, category_id = 95;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 19, category_id = 96;");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // remove new Athletic Trainer profession
        $this->addSql('DELETE FROM fisdap2_profession WHERE id=17 LIMIT 1');

        // remove the cert level for athletic trainers
        $this->addSql('DELETE FROM fisdap2_certification_levels WHERE id=24 LIMIT 1');

        // remove the practice categories
        $this->addSql('DELETE FROM fisdap2_practice_categories_defaults WHERE certification_level_id = 24 AND profession_id=17  LIMIT 2');

        // remove the products
        $this->addSql('DELETE FROM fisdap2_product WHERE id=48 LIMIT 1');
        $this->addSql('DELETE FROM fisdap2_product WHERE id=49 LIMIT 1');

        // remove the products from quickbooks
        $this->addSql('DELETE FROM fisdap2_product_quickbooks_info WHERE id=47 LIMIT 1');
        $this->addSql('DELETE FROM fisdap2_product_quickbooks_info WHERE id=48 LIMIT 1');

        // remove product package
        $this->addSql('DELETE FROM fisdap2_product_package WHERE id=28 LIMIT 1');

        // remove categories and links
        $this->addSql("DELETE FROM fisdap2_report_category WHERE profession_id = 17 LIMIT 12;");
        $this->addSql("DELETE FROM fisdap2_category_report WHERE category_id IN (85, 86, 87, 88, 89, 90, 91, 92, 93, 94, 95, 96)");
    }
}
