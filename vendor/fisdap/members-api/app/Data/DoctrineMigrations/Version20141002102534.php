<?php

namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141002102534 extends AbstractMigration
{
    /**
     * Add site staff member table and ssm-base linking table; add contact email to site table
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // create site staff member table
        $this->addSql('CREATE TABLE fisdap2_site_staff_member
          (id INT AUTO_INCREMENT NOT NULL,
          site_id INT DEFAULT NULL,
          program_id INT DEFAULT NULL,
          first_name VARCHAR(40) NOT NULL,
          last_name VARCHAR(40) NOT NULL,
          title VARCHAR(60) DEFAULT NULL,
          phone VARCHAR(30) DEFAULT NULL,
          pager VARCHAR(30) DEFAULT NULL,
          email VARCHAR(60) DEFAULT NULL,
          notes VARCHAR(200) DEFAULT NULL,
          INDEX IDX_B13353AF6BD1646 (site_id), INDEX IDX_B13353A3EB8070A (program_id), PRIMARY KEY(id))
          DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;');

        // create site staff member - base linking table
        $this->addSql('CREATE TABLE fisdap2_site_staff_member_base_association
          (id INT AUTO_INCREMENT NOT NULL,
          staff_member_id INT DEFAULT NULL,
          base_id INT DEFAULT NULL,
          INDEX IDX_C87BEBD144DB03B1 (staff_member_id), INDEX IDX_C87BEBD16967DF41 (base_id), PRIMARY KEY(id))
          DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;');

        // add contact email column to sites table
        $this->addSql('ALTER TABLE AmbulanceServices ADD COLUMN contact_email  VARCHAR(60) DEFAULT NULL');
    }

    /**
     * Drop site staff member table and ssm-base linking table; drop contact email from site table
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // drop site staff member table
        $this->addSql('DROP TABLE fisdap2_site_staff_member');

        // drop site staff member - base linking table
        $this->addSql('DROP TABLE fisdap2_site_staff_member_base_association');

        // drop contact email column from sites table
        $this->addSql('ALTER TABLE AmbulanceServices DROP COLUMN contact_email');

    }
}
