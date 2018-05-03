<?php

namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Adds a set of ordering permissions for programs: can order, credit card only, and cannot order
 */
class Version20150311132043 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // add order permission table
        $this->addSql("CREATE TABLE fisdap2_order_permission (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;");

        // create the permissions
        $this->addSql(("INSERT INTO fisdap2_order_permission SET name = 'Can Order Accounts'"));
        $this->addSql(("INSERT INTO fisdap2_order_permission SET name = 'Credit Card Only'"));
        $this->addSql(("INSERT INTO fisdap2_order_permission SET name = 'Cannot Order Accounts'"));

        // make ALL programs able to order accounts by default;
        // we're not losing any data by doing this because we're not currently using this flag at all
        $this->addSql(("UPDATE ProgramData SET CanBuyAccounts = 1"));
    }

    public function down(Schema $schema)
    {
        // drop order permission table
        $this->addSql("DROP TABLE fisdap2_order_permission");
    }
}
