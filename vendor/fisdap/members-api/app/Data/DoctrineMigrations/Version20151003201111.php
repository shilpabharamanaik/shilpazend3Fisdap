<?php

namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add ISBNs to our products, per MRAPI-364
 */
class Version20151003201111 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // add ISBN column to product table
        $this->addSql('ALTER TABLE fisdap2_product ADD COLUMN ISBN VARCHAR(25) DEFAULT NULL');

        // add ISBNs to all our products
        $this->addSql('UPDATE fisdap2_product SET ISBN = "9781284107487" WHERE id = 47 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product SET ISBN = "9781284107470" WHERE id = 4 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product SET ISBN = "9781284107463" WHERE id = 3 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product SET ISBN = "9781284107456" WHERE id = 20 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product SET ISBN = "9781284107449" WHERE id = 41 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product SET ISBN = "9781284107432" WHERE id = 42 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product SET ISBN = "9781284107425" WHERE id = 49 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product SET ISBN = "9781284107418" WHERE id = 46 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product SET ISBN = "9781284107401" WHERE id = 36 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product SET ISBN = "9781284107395" WHERE id = 2 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product SET ISBN = "9781284107586" WHERE id = 11 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product SET ISBN = "9781284107579" WHERE id = 44 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product SET ISBN = "9781284107562" WHERE id = 30 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product SET ISBN = "9781284107555" WHERE id = 26 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product SET ISBN = "9781284107548" WHERE id = 17 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product SET ISBN = "9781284107531" WHERE id = 28 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product SET ISBN = "9781284107524" WHERE id = 34 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product SET ISBN = "9781284107517" WHERE id = 22 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product SET ISBN = "9781284107500" WHERE id = 24 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product SET ISBN = "9781284107494" WHERE id = 19 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product SET ISBN = "9781284457124" WHERE id = 32 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product SET ISBN = "9781284457117" WHERE id = 38 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product SET ISBN = "9781284457100" WHERE id = 40 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product SET ISBN = "9781284457094" WHERE id = 48 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product SET ISBN = "9781284457087" WHERE id = 45 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product SET ISBN = "9781284457070" WHERE id = 35 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product SET ISBN = "9781284457063" WHERE id = 1 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product SET ISBN = "9781284457056" WHERE id = 10 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product SET ISBN = "9781284457049" WHERE id = 43 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product SET ISBN = "9781284457032" WHERE id = 29 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product SET ISBN = "9781284212327" WHERE id = 25 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product SET ISBN = "9781284131895" WHERE id = 16 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product SET ISBN = "9781284131888" WHERE id = 27 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product SET ISBN = "9781284131871" WHERE id = 33 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product SET ISBN = "9781284131864" WHERE id = 21 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product SET ISBN = "9781284131857" WHERE id = 23 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product SET ISBN = "9781284131840" WHERE id = 18 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product SET ISBN = "9781284131833" WHERE id = 31 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product SET ISBN = "9781284131826" WHERE id = 37 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product SET ISBN = "9781284131819" WHERE id = 39 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product SET ISBN = "9781284131802" WHERE id = 8 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product SET ISBN = "9781284158151" WHERE id = 7 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product SET ISBN = "9781284158144" WHERE id = 9 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product SET ISBN = "9781284158137" WHERE id = 15 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product SET ISBN = "9781284158120" WHERE id = 14 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product SET ISBN = "9781284158113" WHERE id = 13 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product SET ISBN = "9781284158106" WHERE id = 6 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product SET ISBN = "9781284158090" WHERE id = 5 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product SET ISBN = "9781284132656" WHERE id = 50 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product SET ISBN = "9781284132649" WHERE id = 51 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product SET ISBN = "9781284132632" WHERE id = 52 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product SET ISBN = "9781284132625" WHERE id = 53 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product SET ISBN = "9781284132618" WHERE id = 54 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product SET ISBN = "9781284132601" WHERE id = 55 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product SET ISBN = "9781284132595" WHERE id = 56 LIMIT 1');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // drop contact email column from sites table
        $this->addSql('ALTER TABLE fisdap2_product DROP COLUMN ISBN');
    }
}
