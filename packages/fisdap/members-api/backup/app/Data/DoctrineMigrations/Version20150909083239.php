<?php

namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Kill Medrills
 */
class Version20150909083239 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // update medrills to no profession
        $this->addSql('UPDATE fisdap2_product SET profession_id=-1 WHERE category_id = 5 AND id IN (41, 42) LIMIT 2');

        // update medrills marketing campaigns to end on 9/8/2015
        $this->addSql('UPDATE mktg_Campaign_Data SET EndDate="2015-09-08" WHERE Campaign_id IN (28, 29) LIMIT 2');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // update medrills to no profession
        $this->addSql('UPDATE fisdap2_product SET profession_id=1 WHERE category_id = 5 AND id IN (41, 42) LIMIT 2');

        // update medrills marketing campaigns to end on 1/1/2016
        $this->addSql('UPDATE mktg_Campaign_Data SET EndDate="2016-01-01" WHERE Campaign_id IN (28, 29) LIMIT 2');
    }
}
