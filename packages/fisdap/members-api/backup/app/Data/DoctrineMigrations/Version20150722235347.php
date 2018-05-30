<?php

namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150722235347 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        //Update practice definition defaults names / goals
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set name = 'Supraglottic Airway Device Adult' where name = 'Alternative Airway Device Adult' and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set name = 'Team Leader - Adult', peer_goal = 8, instructor_goal = 4, eureka_window = 0, eureka_goal = 0 where name = 'Team Leader' and certification_level_id = 3");

        //Insert two new practice items
        $this->addSql("INSERT into fisdap2_practice_definitions_defaults (category_id, certification_level_id, skillsheet_id, name, active, peer_goal, instructor_goal, eureka_window, eureka_goal) values (57, 3, 1634, 'Team Leader - Pediatric', 1, 6, 3, 0, 0)");
        $this->addSql("INSERT into fisdap2_practice_definitions_defaults (category_id, certification_level_id, skillsheet_id, name, active, peer_goal, instructor_goal, eureka_window, eureka_goal) values (57, 3, 1634, 'Team Leader - Geriatric', 1, 6, 3, 0, 0)");

        //update sheet ids
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 1632 WHERE skillsheet_id = 653 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 1606 WHERE skillsheet_id = 631 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 1608 WHERE skillsheet_id = 656 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 1629 WHERE skillsheet_id = 657 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 1603 WHERE skillsheet_id = 658 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 1604 WHERE skillsheet_id = 659 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 1626 WHERE skillsheet_id = 644 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 1617 WHERE skillsheet_id = 645 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 1625 WHERE skillsheet_id = 646 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 1623 WHERE skillsheet_id = 647 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 1624 WHERE skillsheet_id = 648 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 1622 WHERE skillsheet_id = 649 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 1620 WHERE skillsheet_id = 650 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 1621 WHERE skillsheet_id = 651 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 1619 WHERE skillsheet_id = 607 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 1614 WHERE skillsheet_id = 608 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 1615 WHERE skillsheet_id = 609 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 1618 WHERE skillsheet_id = 616 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 1605 WHERE skillsheet_id = 624 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 1607 WHERE skillsheet_id = 625 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 1631 WHERE skillsheet_id = 626 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 1611 WHERE skillsheet_id = 628 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 1613 WHERE skillsheet_id = 629 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 1612 WHERE skillsheet_id = 630 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 1628 WHERE skillsheet_id = 632 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 1634 WHERE skillsheet_id = 675 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 1633 WHERE skillsheet_id = 677 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 1616 WHERE skillsheet_id = 634 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 1630 WHERE skillsheet_id = 635 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 1609 WHERE skillsheet_id = 636 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 1610 WHERE skillsheet_id = 637 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 1627 WHERE skillsheet_id = 652 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 1601 WHERE skillsheet_id = 654 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 1602 WHERE skillsheet_id = 655 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 1599 WHERE skillsheet_id = 627 and certification_level_id = 3");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        // this up() migration is auto-generated, please modify it to your needs
        //Update practice definition defaults names / goals
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set name = 'Alternative Airway Device Adult' where name = 'Supraglottic Airway Device Adult' and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set name = 'Team Leader', peer_goal = 10, instructor_goal = 5, eureka_window = 20, eureka_goal = 18 where name = 'Team Leader - Adult' and certification_level_id = 3");

        //Insert two new practice items
        $this->addSql("DELETE from fisdap2_practice_definitions_defaults where name = 'Team Leader - Pediatric' and certification_level_id = 3 limit 1");
        $this->addSql("DELETE from fisdap2_practice_definitions_defaults where name = 'Team Leader - Geriatric' and certification_level_id = 3 limit 1");

        //update sheet ids
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 653 WHERE skillsheet_id = 1632 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 631 WHERE skillsheet_id = 1606 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 656 WHERE skillsheet_id = 1608 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 657 WHERE skillsheet_id = 1629 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 658 WHERE skillsheet_id = 1603 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 659 WHERE skillsheet_id = 1604 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 644 WHERE skillsheet_id = 1626 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 645 WHERE skillsheet_id = 1617 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 646 WHERE skillsheet_id = 1625 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 647 WHERE skillsheet_id = 1623 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 648 WHERE skillsheet_id = 1624 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 649 WHERE skillsheet_id = 1622 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 650 WHERE skillsheet_id = 1620 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 651 WHERE skillsheet_id = 1621 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 607 WHERE skillsheet_id = 1619 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 608 WHERE skillsheet_id = 1614 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 609 WHERE skillsheet_id = 1615 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 616 WHERE skillsheet_id = 1618 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 624 WHERE skillsheet_id = 1605 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 625 WHERE skillsheet_id = 1607 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 626 WHERE skillsheet_id = 1631 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 628 WHERE skillsheet_id = 1611 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 629 WHERE skillsheet_id = 1613 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 630 WHERE skillsheet_id = 1612 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 632 WHERE skillsheet_id = 1628 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 675 WHERE skillsheet_id = 1634 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 677 WHERE skillsheet_id = 1633 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 634 WHERE skillsheet_id = 1616 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 635 WHERE skillsheet_id = 1630 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 636 WHERE skillsheet_id = 1609 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 637 WHERE skillsheet_id = 1610 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 652 WHERE skillsheet_id = 1627 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 654 WHERE skillsheet_id = 1601 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 655 WHERE skillsheet_id = 1602 and certification_level_id = 3");
        $this->addSql("UPDATE fisdap2_practice_definitions_defaults set skillsheet_id = 627 WHERE skillsheet_id = 1599 and certification_level_id = 3");
    }
}
