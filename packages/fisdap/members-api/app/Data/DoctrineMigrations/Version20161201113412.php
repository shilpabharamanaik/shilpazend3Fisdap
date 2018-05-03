<?php namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Changes completed_exams column to TEXT. Serialized content was being cutoff.
 *
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
class Version20161201113412 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE fisdap2_portfolio_options MODIFY completed_exams TEXT');
    }


    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE fisdap2_portfolio_options MODIFY completed_exams VARCHAR(255)');
    }
}
