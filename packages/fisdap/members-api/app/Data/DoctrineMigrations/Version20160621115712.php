<?php namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Creates table for required program evals, adds program setting column
 *
 * @author  Scott McIntyre <smcintyre@fisdap.net>
 */
class Version20160621115712 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE fisdap2_program_required_shift_evaluations (id INT AUTO_INCREMENT PRIMARY KEY NOT NULL, program_id INT, eval_def_id INT, shift_type VARCHAR(10))');
        $this->addSql('ALTER TABLE fisdap2_program_settings ADD require_shift_evals tinyint(1)');
    }


    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql('DROP TABLE fisdap2_program_required_shift_evaluations');
        $this->addSql('ALTER TABLE fisdap2_program_settings DROP require_shift_evals');
    }
}
