<?php

namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 * This migration is updating our prices for alternate professions as of March 2015
 */
class Version20150309145149 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // set Skills Tracker and Scheduler prices for Patient Care Tech
        $this->addSql("UPDATE fisdap2_product SET price = 20.00 WHERE profession_id = 8 AND configuration = 1 LIMIT 1");
        $this->addSql("UPDATE fisdap2_product SET price = 20.00 WHERE profession_id = 8 AND configuration = 2 LIMIT 1");
        $this->addSql("UPDATE fisdap2_product_quickbooks_info SET package_discount_price = 20.00 WHERE product_id = 27 LIMIT 1");
        $this->addSql("UPDATE fisdap2_product_quickbooks_info SET package_discount_price = 20.00 WHERE product_id = 28 LIMIT 1");
        $this->addSql("UPDATE fisdap2_product_package SET price = 40.00 WHERE id = 16 LIMIT 1");

        // set Skills Tracker and Scheduler prices for Respiratory Therapy
        $this->addSql("UPDATE fisdap2_product SET price = 75.00 WHERE profession_id = 3 AND configuration = 1 LIMIT 1");
        $this->addSql("UPDATE fisdap2_product SET price = 65.00 WHERE profession_id = 3 AND configuration = 2 LIMIT 1");
        $this->addSql("UPDATE fisdap2_product_quickbooks_info SET package_discount_price = 60.00 WHERE product_id = 18 LIMIT 1");
        $this->addSql("UPDATE fisdap2_product_quickbooks_info SET package_discount_price = 55.00 WHERE product_id = 19 LIMIT 1");
        $this->addSql("UPDATE fisdap2_product_package SET price = 115.00 WHERE id = 12 LIMIT 1");

        // add new profession, product and pricing for Health Information Tech
        $this->addSql('INSERT INTO fisdap2_profession SET id=15, name="Health Information Tech"');
        $this->addSql('INSERT INTO fisdap2_certification_levels SET id=20, name="health-info-tech", description="HIT", abbreviation="HIT", profession_id=15, bit_value=262144');
        $this->addSql('INSERT INTO fisdap2_practice_categories_defaults SET certification_level_id = 20, name = "New Category", profession_id=15');
        $this->addSql('INSERT INTO fisdap2_practice_categories_defaults SET certification_level_id = 20, name = "New Category", profession_id=15');
        $this->addSql('INSERT INTO fisdap2_practice_categories_defaults SET certification_level_id = 20, name = "New Category", profession_id=15');
        $this->addSql('INSERT INTO fisdap2_practice_categories_defaults SET certification_level_id = 20, name = "New Category", profession_id=15');
        $this->addSql('INSERT INTO fisdap2_product SET id=43, category_id=1, name="Skills Tracker - Unlimited", short_name="Sk Tr", description="Online documenting for patient care worksheets with graduation and goals reports.", configuration=1, price=55.00, has_multiple_attempts=0, staff_only=0, profession_id=15');
        $this->addSql('INSERT INTO fisdap2_product SET id=44, category_id=1, name="Scheduler - Unlimited", short_name="Schd", description="Schedule students\' clinical placements online where educators, hospital administrators, and students can all view and interact with the live calendar.", configuration=2, price=45.00, has_multiple_attempts=0, staff_only=0, profession_id=15');
        $this->addSql('INSERT INTO fisdap2_product_quickbooks_info SET id=42, product_id=43, item_name="Skills:HIT", package_item_name="Skills:HIT", package_discount_price=45');
        $this->addSql('INSERT INTO fisdap2_product_quickbooks_info SET id=43, product_id=44, item_name="Sched:HIT", package_item_name="Sched:HIT", package_discount_price=40');
        $this->addSql('INSERT INTO fisdap2_product_package SET id=24, certification_id=20, name="Clinical Placement Package", description="With the Clinical Placement Package, you can manage students\' clinical placements and compliance, and they can document all of their skills practice and patient interactions.", configuration=3, price = 85.00, products_description="Scheduler, Skills Tracker", quickbooks_name="Clinical Placement Package-HIT"');

        // update product descriptions for nursing
        $this->addSql('UPDATE fisdap2_product SET description="Online documenting for patient care worksheets with graduation and goals reports." WHERE profession_id = 2 AND configuration = 1 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product SET description="Schedule students\' clinical placements online where educators, hospital administrators, and students can all view and interact with the live calendar." WHERE profession_id = 2 AND configuration = 2 LIMIT 1');

    }

    public function down(Schema $schema)
    {
        // set Skills Tracker and Scheduler prices for Patient Care Tech
        $this->addSql("UPDATE fisdap2_product SET price = 15.00 WHERE profession_id = 8 AND configuration = 1 LIMIT 1");
        $this->addSql("UPDATE fisdap2_product SET price = 15.00 WHERE profession_id = 8 AND configuration = 2 LIMIT 1");
        $this->addSql("UPDATE fisdap2_product_quickbooks_info SET package_discount_price = 15.00 WHERE product_id = 27 LIMIT 1");
        $this->addSql("UPDATE fisdap2_product_quickbooks_info SET package_discount_price = 15.00 WHERE product_id = 28 LIMIT 1");
        $this->addSql("UPDATE fisdap2_product_package SET price = 30.00 WHERE id = 16 LIMIT 1");

        // set Skills Tracker and Scheduler prices for Respiratory Therapy
        $this->addSql("UPDATE fisdap2_product SET price = 65.00 WHERE profession_id = 3 AND configuration = 1 LIMIT 1");
        $this->addSql("UPDATE fisdap2_product SET price = 50.00 WHERE profession_id = 3 AND configuration = 2 LIMIT 1");
        $this->addSql("UPDATE fisdap2_product_quickbooks_info SET package_discount_price = 55.00 WHERE product_id = 18 LIMIT 1");
        $this->addSql("UPDATE fisdap2_product_quickbooks_info SET package_discount_price = 45.00 WHERE product_id = 19 LIMIT 1");
        $this->addSql("UPDATE fisdap2_product_package SET price = 100.00 WHERE id = 12 LIMIT 1");

        // remove new profession, product and pricing for Health Information Tech
        $this->addSql('DELETE FROM fisdap2_profession WHERE id=15 LIMIT 1');
        $this->addSql('DELETE FROM fisdap2_certification_levels WHERE id=20 LIMIT 1');
        $this->addSql('DELETE FROM fisdap2_practice_categories_defaults WHERE certification_level_id=20 LIMIT 4');
        $this->addSql('DELETE FROM fisdap2_product WHERE id=43 LIMIT 1');
        $this->addSql('DELETE FROM fisdap2_product WHERE id=44 LIMIT 1');
        $this->addSql('DELETE FROM fisdap2_product_quickbooks_info WHERE id=42 LIMIT 1');
        $this->addSql('DELETE FROM fisdap2_product_quickbooks_info WHERE id=43 LIMIT 1');
        $this->addSql('DELETE FROM fisdap2_product_package WHERE id=24 LIMIT 1');

        // update product descriptions for nursing
        $this->addSql('UPDATE fisdap2_product SET description="Online shift reports and evaluations where students document the patient care data for their entire field and clinical internship. Skills Tracker includes access to reports, accreditation assistance and the portfolio." WHERE profession_id = 2 AND configuration = 1 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product SET description="Schedule the students\' internship online where educators, clinicians, preceptors and students can all view and interact with the live calendar." WHERE profession_id = 2 AND configuration = 2 LIMIT 1');

    }
}
