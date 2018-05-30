<?php

namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add package_ISBNs to product packages, per MRAPI-365
 */
class Version20151003213040 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // add package_ISBN column to product package table
        $this->addSql('ALTER TABLE fisdap2_product_package ADD COLUMN package_ISBN VARCHAR(25) DEFAULT NULL');

        // add package_ISBNs to our EMS packages
        $this->addSql('UPDATE fisdap2_product_package SET package_ISBN = "9781284132014" WHERE id = 5 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product_package SET package_ISBN = "9781284132007" WHERE id = 8 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product_package SET package_ISBN = "9781284131994" WHERE id = 1 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product_package SET package_ISBN = "9781284131987" WHERE id = 6 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product_package SET package_ISBN = "9781284131970" WHERE id = 3 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product_package SET package_ISBN = "9781284131963" WHERE id = 2 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product_package SET package_ISBN = "9781284131956" WHERE id = 7 LIMIT 1');
        $this->addSql('UPDATE fisdap2_product_package SET package_ISBN = "9781284131949" WHERE id = 4 LIMIT 1');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // drop package_ISBN column from fisdap2_product_package table
        $this->addSql('ALTER TABLE fisdap2_product_package DROP COLUMN package_ISBN');
    }
}
