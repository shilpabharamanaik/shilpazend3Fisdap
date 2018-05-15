<?php

namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * New cert level for dental profession
 */
class Version20150903113929 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // add the new cert level for Community Dental Health Coordinator
        $this->addSql('INSERT INTO fisdap2_certification_levels SET id=27, name="community-dental-health-coordinator", description="Community Dental Health Coordinator", abbreviation="CDHC", profession_id=12, bit_value=33554432');

        // add a couple practice categories for the cert level
        $this->addSql('INSERT INTO fisdap2_practice_categories_defaults SET certification_level_id = 27, name = "New Category", profession_id=12');
        $this->addSql('INSERT INTO fisdap2_practice_categories_defaults SET certification_level_id = 27, name = "New Category", profession_id=12');

        // add the products
        $this->addSql('INSERT INTO fisdap2_product SET id=55, category_id=1, name="Skills Tracker - Unlimited", short_name="Sk Tr", description="Online documenting for patient care worksheets with graduation and goals reports.", configuration=1, price=30.00, has_multiple_attempts=0, staff_only=0, profession_id=12');
        $this->addSql('INSERT INTO fisdap2_product SET id=56, category_id=1, name="Scheduler - Unlimited", short_name="Schd", description="Schedule the students\' clinical placements online where educators, hospital administrators, and students can all view and interact with the live calendar.", configuration=2, price=30.00, has_multiple_attempts=0, staff_only=0, profession_id=12');

        // add the products to quickbooks
        $this->addSql('INSERT INTO fisdap2_product_quickbooks_info SET id=54, product_id=54, item_name="Sched:NurseRefresher", package_item_name="Sched:NurseRefresher", package_discount_price=20');
        $this->addSql('INSERT INTO fisdap2_product_quickbooks_info SET id=55, product_id=55, item_name="Skills:CDHC", package_item_name="Skills:CDHC_Pkg", package_discount_price=30');
        $this->addSql('INSERT INTO fisdap2_product_quickbooks_info SET id=56, product_id=56, item_name="Sched:CDHC", package_item_name="Sched:CDHC_Pkg", package_discount_price=25');

        // add a product package for the certification level
        $this->addSql('INSERT INTO fisdap2_product_package SET id=31, certification_id=27, name="Clinical Placement Package", description="With the Clinical Placement Package, you can manage students\' clinical placements and compliance, and they can document all of their skills practice and patient interactions.", configuration=3, price = 55.00, products_description="Scheduler, Skills Tracker", quickbooks_name="Clinical Placement Package-CDHC"');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // remove the cert level for Community Dental Health Coordinator
        $this->addSql('DELETE FROM fisdap2_certification_levels WHERE id=27 LIMIT 1');

        // remove the practice categories
        $this->addSql('DELETE FROM fisdap2_practice_categories_defaults WHERE certification_level_id = 27 AND profession_id=12  LIMIT 2');

        // remove the products
        $this->addSql('DELETE FROM fisdap2_product WHERE id=55 LIMIT 1');
        $this->addSql('DELETE FROM fisdap2_product WHERE id=56 LIMIT 1');

        // remove the products from quickbooks
        $this->addSql('DELETE FROM fisdap2_product_quickbooks_info WHERE id=54 LIMIT 1');
        $this->addSql('DELETE FROM fisdap2_product_quickbooks_info WHERE id=55 LIMIT 1');
        $this->addSql('DELETE FROM fisdap2_product_quickbooks_info WHERE id=56 LIMIT 1');

        // remove product package
        $this->addSql('DELETE FROM fisdap2_product_package WHERE id=31 LIMIT 1');
    }
}
