<?php namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Adding Attachments, ShiftAttachments, AttachmentCategories, ShiftAttachmentCategories, and relationship between
 * ShiftAttachments and fisdap2_verifications
 *
 * @author Ben Getsug <bgetsug@fisdap.net>
 */
class Version20150430154312 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        // NOTE: fisdap2_verifications was created with the 'latin1' character set and needs to be converted
        $this->addSql('ALTER TABLE fisdap2_verifications CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci');

        $this->addSql('CREATE TABLE Attachments (id VARBINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', userContextId INT NOT NULL, fileName VARCHAR(128) NOT NULL, size INT NOT NULL, mimeType VARCHAR(255) NOT NULL, urlRoot VARCHAR(255) NOT NULL, variationFileNames LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', nickname VARCHAR(128) DEFAULT NULL, notes LONGTEXT DEFAULT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, attachmentType VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');

        /*
         * NOTE: shiftId type is MEDIUMINT(8) to match legacy column definition on ShiftData table.
         * DOES NOT MATCH DOCTRINE METADATA!
         */
        $this->addSql('CREATE TABLE ShiftAttachments (id VARBINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', shiftId MEDIUMINT(8) DEFAULT NULL, INDEX IDX_8193B52B16B8B49D (shiftId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ShiftAttachmentCategories (ShiftAttachmentId VARBINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', ShiftAttachmentCategoryId INT NOT NULL, INDEX IDX_F82D689A1149FE40 (ShiftAttachmentId), INDEX IDX_F82D689A5EE92583 (ShiftAttachmentCategoryId), PRIMARY KEY(ShiftAttachmentId, ShiftAttachmentCategoryId)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE AttachmentCategories (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_5416E68DBF396750 (id), UNIQUE INDEX unique_name (name, type), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ShiftAttachments ADD CONSTRAINT FK_8193B52B16B8B49D FOREIGN KEY (shiftId) REFERENCES ShiftData (Shift_id)');
        $this->addSql('ALTER TABLE ShiftAttachments ADD CONSTRAINT FK_8193B52BBF396750 FOREIGN KEY (id) REFERENCES Attachments (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ShiftAttachmentCategories ADD CONSTRAINT FK_F82D689A1149FE40 FOREIGN KEY (ShiftAttachmentId) REFERENCES ShiftAttachments (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ShiftAttachmentCategories ADD CONSTRAINT FK_F82D689A5EE92583 FOREIGN KEY (ShiftAttachmentCategoryId) REFERENCES AttachmentCategories (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE fisdap2_verifications ADD shiftAttachment_id VARBINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE fisdap2_verifications ADD CONSTRAINT FK_893A6CFF1E535B1D FOREIGN KEY (shiftAttachment_id) REFERENCES ShiftAttachments (id)');
        $this->addSql('CREATE INDEX IDX_893A6CFF1E535B1D ON fisdap2_verifications (shiftAttachment_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ShiftAttachments DROP FOREIGN KEY FK_8193B52BBF396750');
        $this->addSql('ALTER TABLE fisdap2_verifications DROP FOREIGN KEY FK_893A6CFF1E535B1D');
        $this->addSql('ALTER TABLE ShiftAttachmentCategories DROP FOREIGN KEY FK_F82D689A1149FE40');
        $this->addSql('ALTER TABLE ShiftAttachmentCategories DROP FOREIGN KEY FK_F82D689A5EE92583');
        $this->addSql('DROP TABLE Attachments');
        $this->addSql('DROP TABLE ShiftAttachments');
        $this->addSql('DROP TABLE ShiftAttachmentCategories');
        $this->addSql('DROP TABLE AttachmentCategories');
        $this->addSql('DROP INDEX IDX_893A6CFF1E535B1D ON fisdap2_verifications');
        $this->addSql('ALTER TABLE fisdap2_verifications DROP shiftAttachment_id');
    }
}
