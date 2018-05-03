<?php namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Temperature was being stored as an integer. Fixing that.
 *
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
class Version20170417113412 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE `FISDAP`.`fisdap2_vitals` CHANGE COLUMN `temperature` `temperature` DECIMAL(5,2) NULL DEFAULT NULL ;');
    }


    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE `FISDAP`.`fisdap2_vitals` CHANGE COLUMN `temperature` `temperature` INTEGER NULL DEFAULT NULL ;');
    }
}
