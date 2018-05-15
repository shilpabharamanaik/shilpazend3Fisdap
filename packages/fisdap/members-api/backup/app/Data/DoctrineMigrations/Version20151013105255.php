<?php

namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add a new test type for NREMT results and edit the name of another
 */
class Version20151013105255 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // add test type, update test name.
        $this->addSql('Update TestTypes set TestName = "National Registry Written" where TestType_id = 21 limit 1');
        $this->addSql('Insert into TestTypes (TestName, CertificationLevel) VALUES ("National Registry Practical", "aemt")');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        //remove test type, revert name
        $this->addSql('update TestTypes set TestName = "National Registry AEMT" where TestType_id = 21 limit 1');
        $this->addSql('delete from TestTypes where TestName = "National Registry Practical" and CertificationLevel = "aemt" limit 1');
    }
}
