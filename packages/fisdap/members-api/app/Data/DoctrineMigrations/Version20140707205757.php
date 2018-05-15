<?php

namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Class Version20140707205757
 *
 * Fixes association between fisdap2_users and StaffData, allowing join on fisdap2_users primary key (id)
 *
 * @package Fisdap\DoctrineMigrations
 */
class Version20140707205757 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE StaffData ADD COLUMN user_id INT DEFAULT NULL');

        $this->addSql('CREATE UNIQUE INDEX user_id ON StaffData (user_id)');

        $this->addSql('UPDATE StaffData JOIN fisdap2_users ON StaffData.username = fisdap2_users.username SET StaffData.user_id = fisdap2_users.id');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE StaffData DROP COLUMN user_id');
    }
}
