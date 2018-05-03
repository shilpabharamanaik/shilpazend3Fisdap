<?php

namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Adding attachments as a valid form of shift level signoff
 */
class Version20150401165116 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE fisdap2_program_settings ADD allow_educator_signoff_attachment TINYINT AFTER allow_educator_signoff_email");

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE fisdap2_program_settings DROP allow_educator_signoff_attachment");
    }
}
