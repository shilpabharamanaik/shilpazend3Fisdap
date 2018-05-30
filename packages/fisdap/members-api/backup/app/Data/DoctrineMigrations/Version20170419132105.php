<?php

namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170419132105 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('insert into fisdap2_practice_skill (id, name, entity_name, fields) values (69, "Subcutaneous Med Admin", "Med", "a:3:{s:9:\"procedure\";i:43;s:12:\"performed_by\";i:1;s:8:\"route_id\";i:2;}")');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('delete from fisdap2_practice_skill where id = 69 limit 1');
    }
}
