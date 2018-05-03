<?php

namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 *
 * Add ArchieMD as a sellable product in the ordering system
 */
class Version20140926130134 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('INSERT INTO fisdap2_product_category SET id=5, name="Supplemental Learning"');
        $this->addSql('INSERT INTO fisdap2_product SET id=41, category_id=5, name="EMT Medrills", short_name="Medrills-E", description="Unlimited access to Archie MD(TM)’s collection of skills and patient care training videos, which are a great study aid for your students.", configuration=524288, price=15.00, has_multiple_attempts=0, staff_only=0, profession_id=1');
        $this->addSql('INSERT INTO fisdap2_product_quickbooks_info SET id=40, product_id=41, item_name="ArchieMD:EMT", package_item_name="Market:ArchieMD:Medrills EMT", package_discount_price=15');
        $this->addSql('INSERT INTO fisdap2_product SET id=42, category_id=5, name="Paramedic Medrills", short_name="Medrills-P", description="Unlimited access to Archie MD(TM)’s collection of skills and patient care training videos, which are a great study aid for your students.", configuration=1048576, price=15.00, has_multiple_attempts=0, staff_only=0, profession_id=1');
        $this->addSql('INSERT INTO fisdap2_product_quickbooks_info SET id=41, product_id=42, item_name="ArchieMD:Paramedic", package_item_name="Market:ArchieMD:Medrills EMT", package_discount_price=15');
    }

    public function down(Schema $schema)
    {
        $this->addSql('DELETE FROM fisdap2_product_category WHERE id = 5');
        $this->addSql('DELETE FROM fisdap2_product WHERE id = 41');
        $this->addSql('DELETE FROM fisdap2_product WHERE id = 42');
        $this->addSql('DELETE FROM fisdap2_product_quickbooks_info WHERE id = 40');
        $this->addSql('DELETE FROM fisdap2_product_quickbooks_info WHERE id = 41');
    }
}
