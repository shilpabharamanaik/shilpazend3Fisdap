<?php

namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150311114402 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // Create a new campaign in the mktg_Campaign_Data table
        $this->addSql("INSERT INTO mktg_Campaign_Data SET Campaign_id = 28, CampaignName = 'Medrills Student', StartDate = '2015-01-01', EndDate = '2016-01-01', Action = 'href=\"http://www.fisdap.net/what_we_make/marketplace#medrills\"', Priority = 1");
        $this->addSql("INSERT INTO mktg_Campaign_Data SET Campaign_id = 29, CampaignName = 'Medrills Instructor', StartDate = '2015-01-01', EndDate = '2016-01-01', Action = 'href=\"http://www.fisdap.net/what_we_make/marketplace#medrills\"', Priority = 1");

        //Set up audience(s) for the campaign
        $this->addSql("INSERT INTO mktg_Campaign_Audience_Data SET Campaign_id = 28, GroupNumber = 1, CriterionType = 'account_type', CriterionValue = 'student', NotFlag = 0");
        $this->addSql("INSERT INTO mktg_Campaign_Audience_Data SET Campaign_id = 29, GroupNumber = 1, CriterionType = 'account_type', CriterionValue = 'instructor', NotFlag = 0");

        //Link the campaign to appropriate billboard(s)
        $this->addSql("INSERT INTO mktg_Campaign_Billboard_Data SET Campaign_id = 28, Billboard_id = 8, Message = '<img src=\"https://inline-marketing.s3.amazonaws.com/medrills.png\">', Height = 139");
        $this->addSql("INSERT INTO mktg_Campaign_Billboard_Data SET Campaign_id = 29, Billboard_id = 7, Message = '<img src=\"https://inline-marketing.s3.amazonaws.com/medrills.png\">', Height = 139");

        //update Medrills product description in products table
        $this->addSql("UPDATE fisdap2_product set name = 'Medrills EMT', description = 'Access to online training videos' where id = 41 limit 1");
        $this->addSql("UPDATE fisdap2_product set name = 'Medrills Paramedic', description = 'Access to online training videos' where id = 42 limit 1");
    }

    public function down(Schema $schema)
    {
        //delete the new campaign
        $this->addSql("DELETE FROM mktg_Campaign_Data where Campaign_id IN (28, 29)");
        $this->addSql("DELETE FROM mktg_Campaign_Audience_Data where Campaign_id IN (28,29)");
        $this->addSql("DELETE FROM mktg_Campaign_Billboard_Data where Campaign_id IN (28,29)");

        $this->addSql("UPDATE fisdap2_product set name = 'EMT Medrills', description = 'Unlimited access to the Archie MD&#0153; collection of skills and patient care training videos, which are a great study aid for your students.' where id = 41 limit 1");
        $this->addSql("UPDATE fisdap2_product set name = 'Paramedic Medrills', description = 'Unlimited access to the Archie MD&#0153; collection of skills and patient care training videos, which are a great study aid for your students.' where id = 42 limit 1");

    }
}
