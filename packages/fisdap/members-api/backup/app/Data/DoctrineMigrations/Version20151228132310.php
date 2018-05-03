<?php

namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151228132310 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fisdap2_mailing_list ADD COLUMN mailchimp_id INT DEFAULT NULL');
        $this->addSql('UPDATE fisdap2_mailing_list SET mailchimp_id = 395565 WHERE id = 1');
        $this->addSql('UPDATE fisdap2_mailing_list SET mailchimp_id = 466137 WHERE id = 2');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fisdap2_mailing_list DROP COLUMN mailchimp_id');
    }
}
