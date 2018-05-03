<?php

namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160121141553 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // add a better gender linking column (the legacy one is a string)
        $this->addSql('ALTER TABLE fisdap2_users ADD gender_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE fisdap2_users ADD CONSTRAINT FK_5C98DDBA708A0E0 FOREIGN KEY (gender_id) REFERENCES fisdap2_gender (id)');
        $this->addSql('CREATE INDEX IDX_5C98DDBA708A0E0 ON fisdap2_users (gender_id)');

        // make the deprecated column nullable
        $this->addSql('ALTER TABLE StudentData MODIFY Gender char(1) null');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // drop the better gender linking column ( the legacy one is a string)
        $this->addSql('ALTER TABLE fisdap2_users DROP FOREIGN KEY FK_5C98DDBA708A0E0');
        $this->addSql('DROP INDEX IDX_5C98DDBA708A0E0 ON fisdap2_users');
        $this->addSql('ALTER TABLE fisdap2_users DROP gender_id');

        // make Gender not nullable
        $this->addSql('ALTER TABLE StudentData MODIFY Gender char(1) NOT NULL');
    }
}
