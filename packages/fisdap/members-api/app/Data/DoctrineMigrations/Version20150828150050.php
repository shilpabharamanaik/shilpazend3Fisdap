<?php

namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150828150050 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // add the product
        $name = "Nurse Refresher Scheduler (Limited)";
        $shortName = "Sch - L";
        $description = "Schedule Nurse Refresher students' clinical placements online where educators, hospital administrators, and students can all view and interact with the live calendar.";
        $this->addSql('INSERT INTO fisdap2_product SET id=54, category_id=1, name="'.$name.'", short_name="'.$shortName.'", description="'.$description.'", configuration=8192, price=20.00, has_multiple_attempts=0, staff_only=0, profession_id=2');

        // add limited scheduler to the configuration blacklist for LPNs and AASNs
        $this->addSql('UPDATE fisdap2_certification_levels SET configuration_blacklist=8192 WHERE id in (9, 7) LIMIT 2');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // remove the products
        $this->addSql('DELETE FROM fisdap2_product WHERE id=54 LIMIT 1');

        // reset the configuration blacklist for LPNs and AASNs
        $this->addSql('UPDATE fisdap2_certification_levels SET configuration_blacklist=0 WHERE id in (9, 7) LIMIT 2');
    }
}
