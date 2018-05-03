<?php namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;


/**
 * Add default program length days to certification levels, associate certification levels with products
 *
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class Version20160211131631 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE fisdap2_certification_levels ADD default_program_length_days INT DEFAULT NULL');
        $this->addSql('ALTER TABLE fisdap2_product ADD certification_level_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE fisdap2_product ADD CONSTRAINT FK_1C02BB2F905E3D30 FOREIGN KEY (certification_level_id) REFERENCES fisdap2_certification_levels (id)');
        $this->addSql('CREATE INDEX IDX_1C02BB2F905E3D30 ON fisdap2_product (certification_level_id)');

        $this->addSql('UPDATE fisdap2_certification_levels SET default_program_length_days=365 WHERE description = "EMT"');
        $this->addSql('UPDATE fisdap2_certification_levels SET default_program_length_days=730 WHERE description = "Paramedic"');
        $this->addSql('UPDATE fisdap2_certification_levels SET default_program_length_days=730 WHERE description = "AEMT"');

        $this->addSql('UPDATE fisdap2_product SET certification_level_id=1 WHERE profession_id = 1 AND name = "Skills Tracker (Limited)"');
        $this->addSql('UPDATE fisdap2_product SET certification_level_id=1 WHERE profession_id = 1 AND name = "Scheduler (Limited)"');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE fisdap2_certification_levels DROP default_program_length_days');
        $this->addSql('ALTER TABLE fisdap2_product DROP FOREIGN KEY FK_1C02BB2F905E3D30');
        $this->addSql('DROP INDEX IDX_1C02BB2F905E3D30 ON fisdap2_product');
        $this->addSql('ALTER TABLE fisdap2_product DROP certification_level_id');
    }
}
