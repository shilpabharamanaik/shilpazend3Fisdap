<?php namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Populate initial Shift Attachment Categories
 *
 * @author Ben Getsug <bgetsug@fisdap.net>
 */
class Version20150518233234 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql("INSERT INTO AttachmentCategories (name, type) VALUES ('ECG', 'shiftattachmentcategory')");
        $this->addSql("INSERT INTO AttachmentCategories (name, type) VALUES ('Notes', 'shiftattachmentcategory')");
        $this->addSql("INSERT INTO AttachmentCategories (name, type) VALUES ('Shift Documentation', 'shiftattachmentcategory')");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql("DELETE FROM AttachmentCategories WHERE name = 'Shift Documentation' and type = 'shiftattachmentcategory'");
        $this->addSql("DELETE FROM AttachmentCategories WHERE name = 'Notes' and type = 'shiftattachmentcategory'");
        $this->addSql("DELETE FROM AttachmentCategories WHERE name = 'ECG' and type = 'shiftattachmentcategory'");
    }
}
