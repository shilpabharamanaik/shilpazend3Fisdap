<?php

namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150922140637 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // update product names
        $this->addSql('UPDATE fisdap2_product SET name = "Skills Tracker (Limited)" WHERE name = "Skills Tracker - Limited"');
        $this->addSql('UPDATE fisdap2_product SET name = "Skills Tracker (Unlimited)" WHERE name = "Skills Tracker - Unlimited"');
        $this->addSql('UPDATE fisdap2_product SET name = "Scheduler (Limited)" WHERE name = "Scheduler - Limited"');
        $this->addSql('UPDATE fisdap2_product SET name = "Scheduler (Unlimited)" WHERE name = "Scheduler - Unlimited"');
        $this->addSql('UPDATE fisdap2_product SET name = "Paramedic Entrance Exam" WHERE name = "Entrance Exam"');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // update product names
        $this->addSql('UPDATE fisdap2_product SET name = "Skills Tracker - Limited" WHERE name = "Skills Tracker (Limited)"');
        $this->addSql('UPDATE fisdap2_product SET name = "Skills Tracker - Unlimited" WHERE name = "Skills Tracker (Unlimited)"');
        $this->addSql('UPDATE fisdap2_product SET name = "Scheduler - Limited" WHERE name = "Scheduler (Limited)"');
        $this->addSql('UPDATE fisdap2_product SET name = "Scheduler - Unlimited" WHERE name = "Scheduler (Unlimited)"');
        $this->addSql('UPDATE fisdap2_product SET name = "Entrance Exam" WHERE name = "Paramedic Entrance Exam"');
    }
}
