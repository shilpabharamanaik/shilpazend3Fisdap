<?php

namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150624150756 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        //add the age goals to the template (goal_set_id = 2)
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (2, 7, 30, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (2, 8, 50, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (2, 9, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (2, 10, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (2, 11, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (2, 12, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (2, 13, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (2, 14, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (2, 15, 30, 0, 1, 1)');

        //add the age goals to all existing VA goals goal sets

        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (979, 7, 30, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (979, 8, 50, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (979, 9, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (979, 10, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (979, 11, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (979, 12, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (979, 13, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (979, 14, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (979, 15, 30, 0, 1, 1)');

        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (985, 7, 30, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (985, 8, 50, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (985, 9, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (985, 10, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (985, 11, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (985, 12, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (985, 13, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (985, 14, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (985, 15, 30, 0, 1, 1)');

        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (994, 7, 30, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (994, 8, 50, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (994, 9, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (994, 10, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (994, 11, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (994, 12, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (994, 13, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (994, 14, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (994, 15, 30, 0, 1, 1)');

        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (995, 7, 30, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (995, 8, 50, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (995, 9, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (995, 10, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (995, 11, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (995, 12, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (995, 13, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (995, 14, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (995, 15, 30, 0, 1, 1)');

        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (996, 7, 30, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (996, 8, 50, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (996, 9, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (996, 10, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (996, 11, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (996, 12, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (996, 13, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (996, 14, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (996, 15, 30, 0, 1, 1)');

        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1021, 7, 30, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1021, 8, 50, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1021, 9, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1021, 10, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1021, 11, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1021, 12, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1021, 13, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1021, 14, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1021, 15, 30, 0, 1, 1)');

        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1030, 7, 30, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1030, 8, 50, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1030, 9, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1030, 10, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1030, 11, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1030, 12, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1030, 13, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1030, 14, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1030, 15, 30, 0, 1, 1)');

        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1033, 7, 30, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1033, 8, 50, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1033, 9, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1033, 10, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1033, 11, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1033, 12, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1033, 13, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1033, 14, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1033, 15, 30, 0, 1, 1)');

        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1046, 7, 30, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1046, 8, 50, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1046, 9, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1046, 10, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1046, 11, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1046, 12, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1046, 13, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1046, 14, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1046, 15, 30, 0, 1, 1)');

        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1051, 7, 30, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1051, 8, 50, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1051, 9, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1051, 10, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1051, 11, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1051, 12, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1051, 13, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1051, 14, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1051, 15, 30, 0, 1, 1)');

        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1104, 7, 30, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1104, 8, 50, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1104, 9, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1104, 10, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1104, 11, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1104, 12, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1104, 13, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1104, 14, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1104, 15, 30, 0, 1, 1)');

        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1110, 7, 30, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1110, 8, 50, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1110, 9, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1110, 10, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1110, 11, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1110, 12, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1110, 13, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1110, 14, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1110, 15, 30, 0, 1, 1)');

        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1118, 7, 30, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1118, 8, 50, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1118, 9, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1118, 10, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1118, 11, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1118, 12, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1118, 13, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1118, 14, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1118, 15, 30, 0, 1, 1)');

        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1119, 7, 30, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1119, 8, 50, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1119, 9, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1119, 10, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1119, 11, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1119, 12, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1119, 13, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1119, 14, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1119, 15, 30, 0, 1, 1)');

        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1138, 7, 30, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1138, 8, 50, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1138, 9, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1138, 10, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1138, 11, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1138, 12, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1138, 13, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1138, 14, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1138, 15, 30, 0, 1, 1)');

        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1162, 7, 30, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1162, 8, 50, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1162, 9, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1162, 10, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1162, 11, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1162, 12, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1162, 13, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1162, 14, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1162, 15, 30, 0, 1, 1)');

        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1163, 7, 30, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1163, 8, 50, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1163, 9, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1163, 10, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1163, 11, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1163, 12, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1163, 13, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1163, 14, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1163, 15, 30, 0, 1, 1)');

        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1166, 7, 30, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1166, 8, 50, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1166, 9, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1166, 10, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1166, 11, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1166, 12, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1166, 13, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1166, 14, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1166, 15, 30, 0, 1, 1)');

        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1171, 7, 30, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1171, 8, 50, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1171, 9, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1171, 10, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1171, 11, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1171, 12, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1171, 13, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1171, 14, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1171, 15, 30, 0, 1, 1)');

        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1175, 7, 30, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1175, 8, 50, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1175, 9, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1175, 10, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1175, 11, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1175, 12, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1175, 13, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1175, 14, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1175, 15, 30, 0, 1, 1)');

        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1176, 7, 30, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1176, 8, 50, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1176, 9, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1176, 10, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1176, 11, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1176, 12, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1176, 13, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1176, 14, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1176, 15, 30, 0, 1, 1)');

        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1177, 7, 30, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1177, 8, 50, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1177, 9, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1177, 10, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1177, 11, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1177, 12, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1177, 13, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1177, 14, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1177, 15, 30, 0, 1, 1)');

        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1178, 7, 30, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1178, 8, 50, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1178, 9, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1178, 10, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1178, 11, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1178, 12, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1178, 13, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1178, 14, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1178, 15, 30, 0, 1, 1)');

        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1179, 7, 30, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1179, 8, 50, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1179, 9, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1179, 10, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1179, 11, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1179, 12, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1179, 13, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1179, 14, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1179, 15, 30, 0, 1, 1)');

        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1203, 7, 30, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1203, 8, 50, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1203, 9, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1203, 10, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1203, 11, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1203, 12, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1203, 13, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1203, 14, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1203, 15, 30, 0, 1, 1)');

        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1220, 7, 30, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1220, 8, 50, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1220, 9, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1220, 10, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1220, 11, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1220, 12, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1220, 13, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1220, 14, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1220, 15, 30, 0, 1, 1)');

        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1231, 7, 30, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1231, 8, 50, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1231, 9, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1231, 10, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1231, 11, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1231, 12, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1231, 13, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1231, 14, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1231, 15, 30, 0, 1, 1)');

        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1234, 7, 30, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1234, 8, 50, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1234, 9, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1234, 10, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1234, 11, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1234, 12, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1234, 13, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1234, 14, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1234, 15, 30, 0, 1, 1)');

        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1238, 7, 30, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1238, 8, 50, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1238, 9, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1238, 10, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1238, 11, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1238, 12, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1238, 13, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1238, 14, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1238, 15, 30, 0, 1, 1)');

        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1240, 7, 30, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1240, 8, 50, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1240, 9, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1240, 10, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1240, 11, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1240, 12, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1240, 13, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1240, 14, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1240, 15, 30, 0, 1, 1)');

        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1241, 7, 30, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1241, 8, 50, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1241, 9, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1241, 10, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1241, 11, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1241, 12, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1241, 13, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1241, 14, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1241, 15, 30, 0, 1, 1)');

        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1242, 7, 30, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1242, 8, 50, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1242, 9, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1242, 10, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1242, 11, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1242, 12, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1242, 13, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1242, 14, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1242, 15, 30, 0, 1, 1)');

        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1266, 7, 30, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1266, 8, 50, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1266, 9, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1266, 10, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1266, 11, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1266, 12, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1266, 13, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1266, 14, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1266, 15, 30, 0, 1, 1)');

        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1275, 7, 30, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1275, 8, 50, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1275, 9, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1275, 10, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1275, 11, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1275, 12, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1275, 13, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1275, 14, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1275, 15, 30, 0, 1, 1)');

        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1316, 7, 30, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1316, 8, 50, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1316, 9, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1316, 10, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1316, 11, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1316, 12, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1316, 13, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1316, 14, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1316, 15, 30, 0, 1, 1)');

        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1334, 7, 30, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1334, 8, 50, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1334, 9, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1334, 10, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1334, 11, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1334, 12, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1334, 13, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1334, 14, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1334, 15, 30, 0, 1, 1)');

        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1344, 7, 30, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1344, 8, 50, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1344, 9, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1344, 10, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1344, 11, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1344, 12, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1344, 13, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1344, 14, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1344, 15, 30, 0, 1, 1)');

        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1392, 7, 30, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1392, 8, 50, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1392, 9, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1392, 10, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1392, 11, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1392, 12, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1392, 13, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1392, 14, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1392, 15, 30, 0, 1, 1)');

        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1410, 7, 30, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1410, 8, 50, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1410, 9, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1410, 10, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1410, 11, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1410, 12, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1410, 13, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1410, 14, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1410, 15, 30, 0, 1, 1)');

        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1411, 7, 30, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1411, 8, 50, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1411, 9, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1411, 10, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1411, 11, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1411, 12, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1411, 13, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1411, 14, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1411, 15, 30, 0, 1, 1)');

        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1423, 7, 30, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1423, 8, 50, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1423, 9, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1423, 10, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1423, 11, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1423, 12, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1423, 13, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1423, 14, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1423, 15, 30, 0, 1, 1)');

        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1429, 7, 30, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1429, 8, 50, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1429, 9, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1429, 10, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1429, 11, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1429, 12, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1429, 13, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1429, 14, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1429, 15, 30, 0, 1, 1)');

        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1435, 7, 30, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1435, 8, 50, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1435, 9, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1435, 10, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1435, 11, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1435, 12, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1435, 13, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1435, 14, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1435, 15, 30, 0, 1, 1)');

        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1452, 7, 30, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1452, 8, 50, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1452, 9, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1452, 10, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1452, 11, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1452, 12, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1452, 13, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1452, 14, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1452, 15, 30, 0, 1, 1)');

        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1471, 7, 30, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1471, 8, 50, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1471, 9, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1471, 10, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1471, 11, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1471, 12, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1471, 13, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1471, 14, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1471, 15, 30, 0, 1, 1)');

        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1481, 7, 30, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1481, 8, 50, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1481, 9, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1481, 10, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1481, 11, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1481, 12, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1481, 13, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1481, 14, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1481, 15, 30, 0, 1, 1)');

        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1509, 7, 30, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1509, 8, 50, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1509, 9, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1509, 10, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1509, 11, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1509, 12, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1509, 13, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1509, 14, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1509, 15, 30, 0, 1, 1)');

        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1513, 7, 30, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1513, 8, 50, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1513, 9, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1513, 10, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1513, 11, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1513, 12, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1513, 13, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1513, 14, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1513, 15, 30, 0, 1, 1)');

        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1556, 7, 30, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1556, 8, 50, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1556, 9, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1556, 10, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1556, 11, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1556, 12, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1556, 13, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1556, 14, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1556, 15, 30, 0, 1, 1)');

        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1560, 7, 30, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1560, 8, 50, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1560, 9, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1560, 10, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1560, 11, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1560, 12, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1560, 13, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1560, 14, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1560, 15, 30, 0, 1, 1)');

        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1563, 7, 30, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1563, 8, 50, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1563, 9, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1563, 10, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1563, 11, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1563, 12, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1563, 13, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1563, 14, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1563, 15, 30, 0, 1, 1)');

        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1571, 7, 30, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1571, 8, 50, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1571, 9, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1571, 10, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1571, 11, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1571, 12, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1571, 13, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1571, 14, 2, 0, 1, 1)');
        $this->addSql('insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, team_lead, interview, exam) values (1571, 15, 30, 0, 1, 1)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

        //remove the age goal associations from the template
        $this->addSql('delete from fisdap2_goals where goal_set_id = 2 and goal_def_id in (7,8,9,10,11,12,13,14,15) limit 9');

        //remove age goals from all existing va goals

        $this->addSql('delete from fisdap2_goals where goal_set_id = 979 and goal_def_id in (7,8,9,10,11,12,13,14,15) limit 9');

        $this->addSql('delete from fisdap2_goals where goal_set_id = 985 and goal_def_id in (7,8,9,10,11,12,13,14,15) limit 9');

        $this->addSql('delete from fisdap2_goals where goal_set_id = 994 and goal_def_id in (7,8,9,10,11,12,13,14,15) limit 9');

        $this->addSql('delete from fisdap2_goals where goal_set_id = 995 and goal_def_id in (7,8,9,10,11,12,13,14,15) limit 9');

        $this->addSql('delete from fisdap2_goals where goal_set_id = 996 and goal_def_id in (7,8,9,10,11,12,13,14,15) limit 9');

        $this->addSql('delete from fisdap2_goals where goal_set_id = 1021 and goal_def_id in (7,8,9,10,11,12,13,14,15) limit 9');

        $this->addSql('delete from fisdap2_goals where goal_set_id = 1030 and goal_def_id in (7,8,9,10,11,12,13,14,15) limit 9');

        $this->addSql('delete from fisdap2_goals where goal_set_id = 1033 and goal_def_id in (7,8,9,10,11,12,13,14,15) limit 9');

        $this->addSql('delete from fisdap2_goals where goal_set_id = 1046 and goal_def_id in (7,8,9,10,11,12,13,14,15) limit 9');

        $this->addSql('delete from fisdap2_goals where goal_set_id = 1051 and goal_def_id in (7,8,9,10,11,12,13,14,15) limit 9');

        $this->addSql('delete from fisdap2_goals where goal_set_id = 1104 and goal_def_id in (7,8,9,10,11,12,13,14,15) limit 9');

        $this->addSql('delete from fisdap2_goals where goal_set_id = 1110 and goal_def_id in (7,8,9,10,11,12,13,14,15) limit 9');

        $this->addSql('delete from fisdap2_goals where goal_set_id = 1118 and goal_def_id in (7,8,9,10,11,12,13,14,15) limit 9');

        $this->addSql('delete from fisdap2_goals where goal_set_id = 1119 and goal_def_id in (7,8,9,10,11,12,13,14,15) limit 9');

        $this->addSql('delete from fisdap2_goals where goal_set_id = 1138 and goal_def_id in (7,8,9,10,11,12,13,14,15) limit 9');

        $this->addSql('delete from fisdap2_goals where goal_set_id = 1162 and goal_def_id in (7,8,9,10,11,12,13,14,15) limit 9');

        $this->addSql('delete from fisdap2_goals where goal_set_id = 1163 and goal_def_id in (7,8,9,10,11,12,13,14,15) limit 9');

        $this->addSql('delete from fisdap2_goals where goal_set_id = 1166 and goal_def_id in (7,8,9,10,11,12,13,14,15) limit 9');

        $this->addSql('delete from fisdap2_goals where goal_set_id = 1171 and goal_def_id in (7,8,9,10,11,12,13,14,15) limit 9');

        $this->addSql('delete from fisdap2_goals where goal_set_id = 1175 and goal_def_id in (7,8,9,10,11,12,13,14,15) limit 9');

        $this->addSql('delete from fisdap2_goals where goal_set_id = 1176 and goal_def_id in (7,8,9,10,11,12,13,14,15) limit 9');

        $this->addSql('delete from fisdap2_goals where goal_set_id = 1177 and goal_def_id in (7,8,9,10,11,12,13,14,15) limit 9');

        $this->addSql('delete from fisdap2_goals where goal_set_id = 1178 and goal_def_id in (7,8,9,10,11,12,13,14,15) limit 9');

        $this->addSql('delete from fisdap2_goals where goal_set_id = 1179 and goal_def_id in (7,8,9,10,11,12,13,14,15) limit 9');

        $this->addSql('delete from fisdap2_goals where goal_set_id = 1203 and goal_def_id in (7,8,9,10,11,12,13,14,15) limit 9');

        $this->addSql('delete from fisdap2_goals where goal_set_id = 1220 and goal_def_id in (7,8,9,10,11,12,13,14,15) limit 9');

        $this->addSql('delete from fisdap2_goals where goal_set_id = 1231 and goal_def_id in (7,8,9,10,11,12,13,14,15) limit 9');

        $this->addSql('delete from fisdap2_goals where goal_set_id = 1234 and goal_def_id in (7,8,9,10,11,12,13,14,15) limit 9');

        $this->addSql('delete from fisdap2_goals where goal_set_id = 1238 and goal_def_id in (7,8,9,10,11,12,13,14,15) limit 9');

        $this->addSql('delete from fisdap2_goals where goal_set_id = 1240 and goal_def_id in (7,8,9,10,11,12,13,14,15) limit 9');

        $this->addSql('delete from fisdap2_goals where goal_set_id = 1241 and goal_def_id in (7,8,9,10,11,12,13,14,15) limit 9');

        $this->addSql('delete from fisdap2_goals where goal_set_id = 1242 and goal_def_id in (7,8,9,10,11,12,13,14,15) limit 9');

        $this->addSql('delete from fisdap2_goals where goal_set_id = 1266 and goal_def_id in (7,8,9,10,11,12,13,14,15) limit 9');

        $this->addSql('delete from fisdap2_goals where goal_set_id = 1275 and goal_def_id in (7,8,9,10,11,12,13,14,15) limit 9');

        $this->addSql('delete from fisdap2_goals where goal_set_id = 1316 and goal_def_id in (7,8,9,10,11,12,13,14,15) limit 9');

        $this->addSql('delete from fisdap2_goals where goal_set_id = 1334 and goal_def_id in (7,8,9,10,11,12,13,14,15) limit 9');

        $this->addSql('delete from fisdap2_goals where goal_set_id = 1344 and goal_def_id in (7,8,9,10,11,12,13,14,15) limit 9');

        $this->addSql('delete from fisdap2_goals where goal_set_id = 1392 and goal_def_id in (7,8,9,10,11,12,13,14,15) limit 9');

        $this->addSql('delete from fisdap2_goals where goal_set_id = 1410 and goal_def_id in (7,8,9,10,11,12,13,14,15) limit 9');

        $this->addSql('delete from fisdap2_goals where goal_set_id = 1411 and goal_def_id in (7,8,9,10,11,12,13,14,15) limit 9');

        $this->addSql('delete from fisdap2_goals where goal_set_id = 1423 and goal_def_id in (7,8,9,10,11,12,13,14,15) limit 9');

        $this->addSql('delete from fisdap2_goals where goal_set_id = 1429 and goal_def_id in (7,8,9,10,11,12,13,14,15) limit 9');

        $this->addSql('delete from fisdap2_goals where goal_set_id = 1435 and goal_def_id in (7,8,9,10,11,12,13,14,15) limit 9');

        $this->addSql('delete from fisdap2_goals where goal_set_id = 1452 and goal_def_id in (7,8,9,10,11,12,13,14,15) limit 9');

        $this->addSql('delete from fisdap2_goals where goal_set_id = 1471 and goal_def_id in (7,8,9,10,11,12,13,14,15) limit 9');

        $this->addSql('delete from fisdap2_goals where goal_set_id = 1481 and goal_def_id in (7,8,9,10,11,12,13,14,15) limit 9');

        $this->addSql('delete from fisdap2_goals where goal_set_id = 1509 and goal_def_id in (7,8,9,10,11,12,13,14,15) limit 9');

        $this->addSql('delete from fisdap2_goals where goal_set_id = 1513 and goal_def_id in (7,8,9,10,11,12,13,14,15) limit 9');

        $this->addSql('delete from fisdap2_goals where goal_set_id = 1556 and goal_def_id in (7,8,9,10,11,12,13,14,15) limit 9');

        $this->addSql('delete from fisdap2_goals where goal_set_id = 1560 and goal_def_id in (7,8,9,10,11,12,13,14,15) limit 9');

        $this->addSql('delete from fisdap2_goals where goal_set_id = 1563 and goal_def_id in (7,8,9,10,11,12,13,14,15) limit 9');

        $this->addSql('delete from fisdap2_goals where goal_set_id = 1571 and goal_def_id in (7,8,9,10,11,12,13,14,15) limit 9');
    }
}
