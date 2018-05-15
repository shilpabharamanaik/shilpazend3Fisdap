<?php

namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * This migration is adding a profession for Australian programs, since their pricing is different
 */
class Version20150414121229 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // add new profession, product and pricing for Australian schools
        $this->addSql('INSERT INTO fisdap2_profession SET id=16, name="EMS (AU)"');

        $this->addSql('INSERT INTO fisdap2_certification_levels SET id=21, name="year1", description="Year 1", abbreviation="Y1", profession_id=16, bit_value=524288, display_order = 1');
        $this->addSql('INSERT INTO fisdap2_certification_levels SET id=22, name="year2", description="Year 2", abbreviation="Y2", profession_id=16, bit_value=1048576, display_order = 2');
        $this->addSql('INSERT INTO fisdap2_certification_levels SET id=23, name="year3", description="Year 3", abbreviation="Y3", profession_id=16, bit_value=2097152, display_order = 3');

        // add a couple practice categories for each cert level
        $this->addSql('INSERT INTO fisdap2_practice_categories_defaults SET certification_level_id = 21, name = "New Category", profession_id=16');
        $this->addSql('INSERT INTO fisdap2_practice_categories_defaults SET certification_level_id = 21, name = "New Category", profession_id=16');
        $this->addSql('INSERT INTO fisdap2_practice_categories_defaults SET certification_level_id = 22, name = "New Category", profession_id=16');
        $this->addSql('INSERT INTO fisdap2_practice_categories_defaults SET certification_level_id = 22, name = "New Category", profession_id=16');
        $this->addSql('INSERT INTO fisdap2_practice_categories_defaults SET certification_level_id = 23, name = "New Category", profession_id=16');
        $this->addSql('INSERT INTO fisdap2_practice_categories_defaults SET certification_level_id = 23, name = "New Category", profession_id=16');

        // add the three products
        $this->addSql('INSERT INTO fisdap2_product SET id=45, category_id=1, name="Skills Tracker - Unlimited", short_name="Sk Tr", description="Online documenting for patient care worksheets with graduation and goals reports.", configuration=1, price=90.00, has_multiple_attempts=0, staff_only=0, profession_id=16');
        $this->addSql('INSERT INTO fisdap2_product SET id=46, category_id=1, name="Scheduler - Unlimited", short_name="Schd", description="Schedule students\' clinical placements online where educators, hospital administrators, and students can all view and interact with the live calendar.", configuration=2, price=65.00, has_multiple_attempts=0, staff_only=0, profession_id=16');
        $this->addSql('INSERT INTO fisdap2_product SET id=47, category_id=2, name="Comprehensive Exams", short_name="Comp-AUS", description="Secure summative final exam to assess students\' terminal competency. This exam must be given in a secure, proctored environment.", configuration=2097152, price=55.00, has_multiple_attempts=1, staff_only=0, profession_id=16, moodle_context = "secure_testing", moodle_course_id = 18');

        // add the three products to quickbooks
        $this->addSql('INSERT INTO fisdap2_product_quickbooks_info SET id=44, product_id=45, item_name="Skills:AUS", package_item_name="Skills:AUS", package_discount_price=75');
        $this->addSql('INSERT INTO fisdap2_product_quickbooks_info SET id=45, product_id=46, item_name="Sched:AUS", package_item_name="Sched:AUS", package_discount_price=55');
        $this->addSql('INSERT INTO fisdap2_product_quickbooks_info SET id=46, product_id=47, item_name="CompExams:AUS", package_item_name="CompExams:AUS", package_discount_price=45');

        // add a product package for each certification level
        $this->addSql('INSERT INTO fisdap2_product_package SET id=25, certification_id=21, name="OZ Package", description="Everything Fisdap has to offer-- with a savings of $35!", configuration=2097155, price = 175.00, products_description="Scheduler, Skills Tracker, Comprehensive Exams", quickbooks_name="OZ Package"');
        $this->addSql('INSERT INTO fisdap2_product_package SET id=26, certification_id=22, name="OZ Package", description="Everything Fisdap has to offer-- with a savings of $35!", configuration=2097155, price = 175.00, products_description="Scheduler, Skills Tracker, Comprehensive Exams", quickbooks_name="OZ Package"');
        $this->addSql('INSERT INTO fisdap2_product_package SET id=27, certification_id=23, name="OZ Package", description="Everything Fisdap has to offer-- with a savings of $35!", configuration=2097155, price = 175.00, products_description="Scheduler, Skills Tracker, Comprehensive Exams", quickbooks_name="OZ Package"');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // remove new profession, product and pricing for Australian schools
        $this->addSql('DELETE FROM fisdap2_profession WHERE id=16 LIMIT 1');

        $this->addSql('DELETE FROM fisdap2_certification_levels WHERE id=21 LIMIT 1');
        $this->addSql('DELETE FROM fisdap2_certification_levels WHERE id=22 LIMIT 1');
        $this->addSql('DELETE FROM fisdap2_certification_levels WHERE id=23 LIMIT 1');

        $this->addSql('DELETE FROM fisdap2_practice_categories_defaults WHERE certification_level_id=21 LIMIT 2');
        $this->addSql('DELETE FROM fisdap2_practice_categories_defaults WHERE certification_level_id=22 LIMIT 2');
        $this->addSql('DELETE FROM fisdap2_practice_categories_defaults WHERE certification_level_id=23 LIMIT 2');

        $this->addSql('DELETE FROM fisdap2_product WHERE id=45 LIMIT 1');
        $this->addSql('DELETE FROM fisdap2_product WHERE id=46 LIMIT 1');
        $this->addSql('DELETE FROM fisdap2_product WHERE id=47 LIMIT 1');

        $this->addSql('DELETE FROM fisdap2_product_quickbooks_info WHERE id=44 LIMIT 1');
        $this->addSql('DELETE FROM fisdap2_product_quickbooks_info WHERE id=45 LIMIT 1');
        $this->addSql('DELETE FROM fisdap2_product_quickbooks_info WHERE id=46 LIMIT 1');

        $this->addSql('DELETE FROM fisdap2_product_package WHERE id=25 LIMIT 1');
        $this->addSql('DELETE FROM fisdap2_product_package WHERE id=26 LIMIT 1');
        $this->addSql('DELETE FROM fisdap2_product_package WHERE id=27 LIMIT 1');
    }
}
