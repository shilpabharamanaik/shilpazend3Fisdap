<?php

namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Class Version20140929163542
 *
 * Adding new tables for notification related entities
 *
 * @package Fisdap\DoctrineMigrations
 */
class Version20140929163542 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE fisdap2_notification_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL,
            class VARCHAR(50) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');

        $this->addSql('CREATE TABLE fisdap2_notifications (id INT AUTO_INCREMENT NOT NULL, notification_type_id INT DEFAULT NULL,
            title VARCHAR(140) NOT NULL, message LONGTEXT NOT NULL, date_posted DATETIME NOT NULL,
            recipient_params LONGTEXT NOT NULL COMMENT "(DC2Type:array)", INDEX IDX_65BC3B6AD0520624 (notification_type_id),
            PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');

        $this->addSql('CREATE TABLE fisdap2_notification_user_views (id INT AUTO_INCREMENT NOT NULL, notification_id
            INT DEFAULT NULL, user_role_id INT DEFAULT NULL, viewed TINYINT(1) NOT NULL, INDEX IDX_4DC210D3EF1A9D84 (notification_id),
            INDEX IDX_4DC210D38E0E3CA6 (user_role_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');

        $this->addSql('INSERT INTO fisdap2_notification_type (id, name, class) VALUES  (null, "Software Update", "update"), (null, "Downtime", "downtime")');
    }

    public function down(Schema $schema)
    {
        $this->addSql('DROP TABLE fisdap2_notification_type');

        $this->addSql('DROP TABLE fisdap2_notifications');

        $this->addSql('DROP TABLE fisdap2_notification_user_views');
    }
}
