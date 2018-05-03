<?php

namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Migrations for new Polysomnography profession
 */
class Version20150827152713 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // add new Polysomnography profession
        $this->addSql('INSERT INTO fisdap2_profession SET id=18, name="Polysomnography"');

        // add the new cert level for Polysomnography
        $this->addSql('INSERT INTO fisdap2_certification_levels SET id=25, name="sleep-disorder-specialist", description="Sleep Disorder Specialist", abbreviation="SDS", profession_id=18, bit_value=8388608');

        // add a couple practice categories for the cert level
        $this->addSql('INSERT INTO fisdap2_practice_categories_defaults SET certification_level_id = 25, name = "New Category", profession_id=18');
        $this->addSql('INSERT INTO fisdap2_practice_categories_defaults SET certification_level_id = 25, name = "New Category", profession_id=18');

        // add the products
        $this->addSql('INSERT INTO fisdap2_product SET id=50, category_id=1, name="Skills Tracker - Unlimited", short_name="Sk Tr", description="Online documenting for patient care worksheets with graduation and goals reports.", configuration=1, price=30.00, has_multiple_attempts=0, staff_only=0, profession_id=18');
        $this->addSql('INSERT INTO fisdap2_product SET id=51, category_id=1, name="Scheduler - Unlimited", short_name="Schd", description="Schedule students\' clinical placements online where educators, hospital administrators, and students can all view and interact with the live calendar.", configuration=2, price=30.00, has_multiple_attempts=0, staff_only=0, profession_id=18');

        // add the products to quickbooks
        $this->addSql('INSERT INTO fisdap2_product_quickbooks_info SET id=50, product_id=50, item_name="Skills:Polysomnography", package_item_name="Skills:Polysomnography_Pkg", package_discount_price=30');
        $this->addSql('INSERT INTO fisdap2_product_quickbooks_info SET id=51, product_id=51, item_name="Sched:Polysomnography", package_item_name="Sched:Polysomnography_Pkg", package_discount_price=25');

        // add a product package for the certification level
        $this->addSql('INSERT INTO fisdap2_product_package SET id=29, certification_id=25, name="Internship Package", description="Everything Fisdap has to offer-- with a savings of $5!", configuration=3, price = 55.00, products_description="Scheduler, Skills Tracker", quickbooks_name="Internship Package-Polysomnography"');

        // add categories for Polysomnography reports
        $this->addSql("INSERT INTO fisdap2_report_category SET id = 97, name = 'Accreditation', profession_id = 18;");
        $this->addSql("INSERT INTO fisdap2_report_category SET id = 98, name = 'Diagnostic', profession_id = 18");
        $this->addSql("INSERT INTO fisdap2_report_category SET id = 99, name = 'Evals', profession_id = 18;");
        $this->addSql("INSERT INTO fisdap2_report_category SET id = 100, name = 'Goals', profession_id = 18;");
        $this->addSql("INSERT INTO fisdap2_report_category SET id = 101, name = 'Instructor', profession_id = 18;");
        $this->addSql("INSERT INTO fisdap2_report_category SET id = 102, name = 'Internship', profession_id = 18;");
        $this->addSql("INSERT INTO fisdap2_report_category SET id = 103, name = 'Preceptor', profession_id = 18;");
        $this->addSql("INSERT INTO fisdap2_report_category SET id = 104, name = 'Program', profession_id = 18;");
        $this->addSql("INSERT INTO fisdap2_report_category SET id = 105, name = 'Shifts', profession_id = 18;");
        $this->addSql("INSERT INTO fisdap2_report_category SET id = 106, name = 'Site', profession_id = 18;");
        $this->addSql("INSERT INTO fisdap2_report_category SET id = 107, name = 'Skills', profession_id = 18;");
        $this->addSql("INSERT INTO fisdap2_report_category SET id = 108, name = 'Student', profession_id = 18;");

        // add reports to Polysomnography categories
        // add skills report to Internship, Skills, Student categories
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 1, category_id = 102;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 1, category_id = 107;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 1, category_id = 108;");

        // add skills finder report to Internship, Skills, Student and Diagnostic categories
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 2, category_id = 98;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 2, category_id = 102;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 2, category_id = 107;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 2, category_id = 108;");

        // add narrative report to Internship, Skills, Student categories
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 3, category_id = 102;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 3, category_id = 107;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 3, category_id = 108;");

        // add productivity report to accreditation, diagnostic, preceptor, site
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 4, category_id = 97;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 4, category_id = 98;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 4, category_id = 103;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 4, category_id = 106;");

        // add Lab practice report
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 9, category_id = 99;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 9, category_id = 100;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 9, category_id = 108;");

        // add observed team lead report
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 10, category_id = 98;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 10, category_id = 102;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 10, category_id = 108;");

        // add shift requests report
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 11, category_id = 105;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 11, category_id = 108;");

        // add attendance report
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 12, category_id = 98;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 12, category_id = 105;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 12, category_id = 108;");

        // add eureka report
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 13, category_id = 99;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 13, category_id = 102;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 13, category_id = 107;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 13, category_id = 108;");

        // add shift comments report
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 14, category_id = 101;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 14, category_id = 105;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 14, category_id = 108;");

        // add late entry data report
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 15, category_id = 98;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 15, category_id = 105;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 15, category_id = 108;");

        // add preceptor signoff report
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 16, category_id = 103;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 16, category_id = 105;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 16, category_id = 108;");

        // add hours report
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 18, category_id = 102;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 18, category_id = 105;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 18, category_id = 108;");

        // add evals report
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 19, category_id = 99;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 19, category_id = 101;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 19, category_id = 103;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 19, category_id = 104;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 19, category_id = 106;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 19, category_id = 107;");
        $this->addSql("INSERT INTO fisdap2_category_report SET report_id = 19, category_id = 108;");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // remove new Polysomnography profession
        $this->addSql('DELETE FROM fisdap2_profession WHERE id=18 LIMIT 1');

        // remove the cert level for Polysomnography
        $this->addSql('DELETE FROM fisdap2_certification_levels WHERE id=25 LIMIT 1');

        // remove the practice categories
        $this->addSql('DELETE FROM fisdap2_practice_categories_defaults WHERE certification_level_id = 25 AND profession_id=18  LIMIT 2');

        // remove the products
        $this->addSql('DELETE FROM fisdap2_product WHERE id=50 LIMIT 1');
        $this->addSql('DELETE FROM fisdap2_product WHERE id=51 LIMIT 1');

        // remove the products from quickbooks
        $this->addSql('DELETE FROM fisdap2_product_quickbooks_info WHERE id=50 LIMIT 1');
        $this->addSql('DELETE FROM fisdap2_product_quickbooks_info WHERE id=51 LIMIT 1');

        // remove product package
        $this->addSql('DELETE FROM fisdap2_product_package WHERE id=29 LIMIT 1');

        // remove categories and links
        $this->addSql("DELETE FROM fisdap2_report_category WHERE profession_id = 18 LIMIT 12;");
        $this->addSql("DELETE FROM fisdap2_category_report WHERE category_id IN (97, 98, 99, 100, 101, 102, 103, 104, 105, 106, 107, 108)");
    }
}
