<?php

namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141007205110 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // make/populate the categories table
        $this->addSql('CREATE TABLE fisdap2_medrill_category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;');
        $this->addSql('INSERT INTO fisdap2_medrill_category SET name="Anatomy illustrations"');
        $this->addSql('INSERT INTO fisdap2_medrill_category SET name="Skills demonstrations"');

        // make the videos table
        $this->addSql('CREATE TABLE fisdap2_medrill_video (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, vimeo_id INT NOT NULL, title VARCHAR(60) NOT NULL, caption VARCHAR(200) NOT NULL, description LONGTEXT NOT NULL, INDEX IDX_9DF8EF9512469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;');

        // make the video->product linking table
        $this->addSql('CREATE TABLE fisdap2_medrill_video_product_association (medrill_video_id INT NOT NULL, product_id INT NOT NULL, INDEX IDX_52CAC19C27365106 (medrill_video_id), INDEX IDX_52CAC19C4584665A (product_id), PRIMARY KEY(medrill_video_id, product_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;');
    }

    public function down(Schema $schema)
    {
        $this->addSql('DROP TABLE fisdap2_medrill_category;');
        $this->addSql('DROP TABLE fisdap2_medrill_video;');
        $this->addSql('DROP TABLE fisdap2_medrill_video_product_association;');
    }
}
