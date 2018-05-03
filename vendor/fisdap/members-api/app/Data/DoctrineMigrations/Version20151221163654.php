<?php

namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151221163654 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("UPDATE fisdap2_product_package SET quickbooks_name = 'Internship Package-Polysomnogra' WHERE quickbooks_name = 'Internship Package-Polysomnography'");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("UPDATE fisdap2_product_package SET quickbooks_name = 'Internship Package-Polysomnography' WHERE quickbooks_name = 'Internship Package-Polysomnogra'");
    }
}
