<?php

namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 * Adding new table to store ethn.io screener information
 */
class Version20150317104134 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("CREATE TABLE fisdap2_ethnio_screeners (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, screener_id INT NOT NULL, url VARCHAR(255) NOT NULL, active TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;");
        $this->addSql("INSERT INTO fisdap2_ethnio_screeners set name = 'Dashboard', screener_id = 0, url = '/my-fisdap', active = false");
        $this->addSql("INSERT INTO fisdap2_ethnio_screeners set name = 'Portfolios', screener_id = 0, url = '/portfolio/index/about/', active = false");
        $this->addSql("INSERT INTO fisdap2_ethnio_screeners set name = 'Scheduler', screener_id = 0, url = '/scheduler', active = false");
        $this->addSql("INSERT INTO fisdap2_ethnio_screeners set name = 'Edit Compliance Status', screener_id = 0, url = '/scheduler/compliance/edit-status', active = false");
        $this->addSql("INSERT INTO fisdap2_ethnio_screeners set name = 'Skills Tracker', screener_id = 0, url = '/skills-tracker/shifts', active = false");
        $this->addSql("INSERT INTO fisdap2_ethnio_screeners set name = 'Learning Center', screener_id = 0, url = '/learning-center', active = false");
        $this->addSql("INSERT INTO fisdap2_ethnio_screeners set name = 'Test Schedule', screener_id = 0, url = '/learning-center/index/schedule', active = false");
        $this->addSql("INSERT INTO fisdap2_ethnio_screeners set name = 'Retrieve Scores', screener_id = 0, url = '/learning-center/index/retrieve', active = false");
        $this->addSql("INSERT INTO fisdap2_ethnio_screeners set name = 'New Reports', screener_id = 0, url = '/reports', active = false");
        $this->addSql("INSERT INTO fisdap2_ethnio_screeners set name = 'Account', screener_id = 0, url = '/account', active = false");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("DROP TABLE fisdap2_ethnio_screeners");
    }
}
