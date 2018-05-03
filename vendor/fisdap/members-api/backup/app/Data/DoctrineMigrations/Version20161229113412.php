<?php namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Changes summary and plan columns to allow null since those values are not required.
 *
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
class Version20161229113412 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE `FISDAP`.`fisdap2_preceptor_signoffs` CHANGE COLUMN `summary` `summary` LONGTEXT CHARACTER SET \'utf8\' NULL ;');
        $this->addSql('ALTER TABLE `FISDAP`.`fisdap2_preceptor_signoffs` CHANGE COLUMN `plan` `plan` LONGTEXT CHARACTER SET \'utf8\' NULL ;');
    }


    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE `FISDAP`.`fisdap2_preceptor_signoffs` CHANGE COLUMN `summary` `summary` LONGTEXT CHARACTER SET \'utf8\' NOT NULL ;');
        $this->addSql('ALTER TABLE `FISDAP`.`fisdap2_preceptor_signoffs` CHANGE COLUMN `plan` `plan` LONGTEXT CHARACTER SET \'utf8\' NOT NULL ;');
    }
}
