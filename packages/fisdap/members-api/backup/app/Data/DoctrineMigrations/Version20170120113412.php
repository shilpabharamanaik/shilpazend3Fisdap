<?php namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * signature_string_old should allow null values.
 *
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
class Version20170120113412 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE `FISDAP`.`fisdap2_signatures` CHANGE COLUMN `signature_string_old` `signature_string_old` LONGTEXT  NULL ;');
    }


    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE `FISDAP`.`fisdap2_signatures` CHANGE COLUMN `signature_string_old` `signature_string_old` LONGTEXT NOT NULL ;');
    }
}
