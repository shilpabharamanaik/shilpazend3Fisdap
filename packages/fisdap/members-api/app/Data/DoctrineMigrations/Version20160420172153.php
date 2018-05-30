<?php
namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 * This migration adds the supporting pieces for the ohio goals report
 */

class Version20160420172153 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        //move existing goal set id 3 to id 4
        $this->addSql("update fisdap2_goal_sets set id = 4 where id = 3");

        //update the associated goals for goal set id 3 to goal set id 4
        $this->addSql("update fisdap2_goals set goal_set_id = 4 where goal_set_id = 3");

        //change column data types to account for large hour requirements
        $this->addSql("alter table fisdap2_goals modify column number_required SMALLINT(4) unsigned NOT NULL default 0;");
        $this->addSql("alter table fisdap2_goal_defs modify column def_goal_number_required SMALLINT(4) unsigned NOT NULL default 0");

        //new goal set
        $this->addSql("insert into fisdap2_goal_sets (id, program_id, name, account_type, infant_start_age, toddler_start_age, preschooler_start_age, school_age_start_age, adolescent_start_age, adult_start_age, geriatric_start_age, default_goalset, goalset_template_id) values (3, 0, 'Ohio Goals', 'paramedic', 1, 1, 4, 6, 13, 18, 65, 0, 3)");

        //new goal defs
        $this->addSql("insert into fisdap2_goal_defs (id, def_id, type, def_category, def_sub_category, name, short_name, category, program_id, team_lead, interview, exam, def_goal_number_required, def_goal_max_last_data, def_goal_percent_successful) values (118, 118, 'cardiac', 'skills', null, 'ECG Interpretation', 'ECG Interpretation', 'Skills', 0, 0, 0, 0, 30, null, null)");
        $this->addSql("insert into fisdap2_goal_defs (id, def_id, type, def_category, def_sub_category, name, short_name, category, program_id, team_lead, interview, exam, def_goal_number_required, def_goal_max_last_data, def_goal_percent_successful) values (119, 119, 'ios', 'skills', null, 'IO', 'IO', 'Skills', 0, 0, 0, 0, 2, null, null)");
        $this->addSql("insert into fisdap2_goal_defs (id, def_id, type, def_category, def_sub_category, name, short_name, category, program_id, team_lead, interview, exam, def_goal_number_required, def_goal_max_last_data, def_goal_percent_successful) values (120, 120, 'cardiac', 'skills', null, 'Manual Defibrillation', 'Manual Defibrillation', 'Skills', 0, 0, 0, 0, 1, null, null)");
        $this->addSql("insert into fisdap2_goal_defs (id, def_id, type, def_category, def_sub_category, name, short_name, category, program_id, team_lead, interview, exam, def_goal_number_required, def_goal_max_last_data, def_goal_percent_successful) values (121, 121, 'cardiac', 'skills', null, 'Chest Compressions', 'Chest Compressions', 'Skills', 0, 0, 0, 0, 1, null, null)");
        $this->addSql("insert into fisdap2_goal_defs (id, def_id, type, def_category, def_sub_category, name, short_name, category, program_id, team_lead, interview, exam, def_goal_number_required, def_goal_max_last_data, def_goal_percent_successful, use_op) values (122, 122, 'hours', 'hours', 'hours', 'Respiratory', 'Respiratory', 'Hours', 0, 0, 0, 0, 8, null, null, 1)");
        $this->addSql("insert into fisdap2_goal_defs (id, def_id, type, def_category, def_sub_category, name, short_name, category, program_id, team_lead, interview, exam, def_goal_number_required, def_goal_max_last_data, def_goal_percent_successful, use_op) values (123, 123, 'hours', 'hours', 'hours', 'Psychiatric', 'Psychiatric', 'Hours', 0, 0, 0, 0, 8, null, null, 1)");
        $this->addSql("insert into fisdap2_goal_defs (id, def_id, type, def_category, def_sub_category, name, short_name, category, program_id, team_lead, interview, exam, def_goal_number_required, def_goal_max_last_data, def_goal_percent_successful, use_op) values (124, 124, 'hours', 'hours', 'hours', 'Pediatric ED', 'Peds ED', 'Hours', 0, 0, 0, 0, 16, null, null, 1)");
        $this->addSql("insert into fisdap2_goal_defs (id, def_id, type, def_category, def_sub_category, name, short_name, category, program_id, team_lead, interview, exam, def_goal_number_required, def_goal_max_last_data, def_goal_percent_successful, use_op) values (125, 125, 'hours', 'hours', 'hours', 'Total Field', 'Total Field', 'Hours', 0, 0, 0, 0, 272, null, null, 1)");
        $this->addSql("insert into fisdap2_goal_defs (id, def_id, type, def_category, def_sub_category, name, short_name, category, program_id, team_lead, interview, exam, def_goal_number_required, def_goal_max_last_data, def_goal_percent_successful, use_op) values (126, 126, 'hours', 'hours', 'hours', 'Field + Clinical', 'Field + Clinical', 'Hours', 0, 0, 0, 0, 400, null, null, 1)");
        $this->addSql("insert into fisdap2_goal_defs (id, def_id, type, def_category, def_sub_category, name, short_name, category, program_id, team_lead, interview, exam, def_goal_number_required, def_goal_max_last_data, def_goal_percent_successful) values (127, 127, 'ohio', 'ohio', 'ohio', 'IV', 'IV', 'Med Admin', 0, 0, 0, 0, 2, null, null)");
        $this->addSql("insert into fisdap2_goal_defs (id, def_id, type, def_category, def_sub_category, name, short_name, category, program_id, team_lead, interview, exam, def_goal_number_required, def_goal_max_last_data, def_goal_percent_successful) values (128, 128, 'ohio', 'ohio', 'ohio', 'Subcutaneous', 'Subcutaneous', 'Med Admin', 0, 0, 0, 0, 1, null, null)");
        $this->addSql("insert into fisdap2_goal_defs (id, def_id, type, def_category, def_sub_category, name, short_name, category, program_id, team_lead, interview, exam, def_goal_number_required, def_goal_max_last_data, def_goal_percent_successful) values (129, 129, 'ohio', 'ohio', 'ohio', 'Intramuscular', 'Intramuscular', 'Med Admin', 0, 0, 0, 0, 3, null, null)");
        $this->addSql("insert into fisdap2_goal_defs (id, def_id, type, def_category, def_sub_category, name, short_name, category, program_id, team_lead, interview, exam, def_goal_number_required, def_goal_max_last_data, def_goal_percent_successful) values (130, 130, 'ohio', 'ohio', 'ohio', 'Bronchodilator', 'Bronchodilator', 'Med Admin', 0, 0, 0, 0, 5, null, null)");
        $this->addSql("insert into fisdap2_goal_defs (id, def_id, type, def_category, def_sub_category, name, short_name, category, program_id, team_lead, interview, exam, def_goal_number_required, def_goal_max_last_data, def_goal_percent_successful) values (131, 131, 'ohio', 'ohio', 'ohio', 'Oral', 'Oral', 'Med Admin', 0, 0, 0, 0, 2, null, null)");
        $this->addSql("insert into fisdap2_goal_defs (id, def_id, type, def_category, def_sub_category, name, short_name, category, program_id, team_lead, interview, exam, def_goal_number_required, def_goal_max_last_data, def_goal_percent_successful) values (132, 132, 'ohio', 'ohio', 'ohio', 'Total Med Admin', 'Total Med Admin', 'Med Admin', 0, 0, 0, 0, 15, null, null)");
        $this->addSql("insert into fisdap2_goal_defs (id, def_id, type, def_category, def_sub_category, name, short_name, category, program_id, team_lead, interview, exam, def_goal_number_required, def_goal_max_last_data, def_goal_percent_successful) values (133, 133, 'ages', 'ages', null, 'Total Patient Assessments', 'Total Patient Assessments', 'Ages', 0, 0, 0, 0, 2, null, null)");

        //ages goals
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 7, 30, null, null, 0, 1, 1)");
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 8, 50, null, null, 0, 1, 1)");
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 9, 2, null, null, 0, 1, 1)");
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 10, 2, null, null, 0, 1, 1)");
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 11, 2, null, null, 0, 1, 1)");
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 12, 2, null, null, 0, 1, 1)");
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 13, 2, null, null, 0, 1, 1)");
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 14, 2, null, null, 0, 1, 1)");
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 15, 30, null, null, 0, 1, 1)");
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 133, 0, null, null, 0, 1, 1)");

        //complaints goals
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 27, 8, null, null, 1, 1, 1)");
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 28, 30, null, null, 1, 1, 1)");
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 29, 20, null, null, 1, 1, 1)");
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 30, 20, null, null, 1, 1, 1)");
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 31, 20, null, null, 1, 1, 1)");
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 32, 0, null, null, 1, 1, 1)");
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 33, 0, null, null, 1, 1, 1)");
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 34, 0, null, null, 1, 1, 1)");
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 35, 10, null, null, 1, 1, 1)");

        //Impressions goals
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 16, 20, null, null, 0, 1, 1)");
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 17, 40, null, null, 0, 1, 1)");
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 18, 10, null, null, 0, 1, 1)");
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 19, 10, null, null, 0, 1, 1)");
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 21, 0, null, null, 0, 1, 1)");
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 22, 0, null, null, 0, 1, 1)");
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 23, 0, null, null, 0, 1, 1)");
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 24, 0, null, null, 0, 1, 1)");
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 25, 0, null, null, 0, 1, 1)");
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 26, 0, null, null, 0, 1, 1)");

        //Skills goals
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 2, 25, null, null, 0, 0, 0)");
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 6, 20, null, null, 0, 0, 0)");
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 54, 5, null, null, 0, 0, 0)");
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 75, 0, null, null, 0, 0, 0)");
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 118, 30, null, null, 0, 0, 0)");
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 119, 2, null, null, 0, 0, 0)");
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 120, 1, null, null, 0, 0, 0)");
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 121, 1, null, null, 0, 0, 0)");

        //Hours goals
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 96, 88, null, null, 0, 0, 0)");
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 101, 16, null, null, 0, 0, 0)");
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 102, 16, null, null, 0, 0, 0)");
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 103, 6, null, null, 0, 0, 0)");
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 106, 50, null, null, 0, 0, 0)");
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 122, 8, null, null, 0, 0, 0)");
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 123, 8, null, null, 0, 0, 0)");
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 124, 16, null, null, 0, 0, 0)");
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 125, 272, null, null, 0, 0, 0)");
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 126, 400, null, null, 0, 0, 0)");

        //Med Admin goals
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 127, 2, null, null, 0, 1, 1)");
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 128, 1, null, null, 0, 1, 1)");
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 129, 3, null, null, 0, 1, 1)");
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 130, 5, null, null, 0, 1, 1)");
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 131, 2, null, null, 0, 1, 1)");
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 132, 15, null, null, 0, 1, 1)");

        //Airway Management Goals
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 92, 50, null, null, 0, 0, 0)");

        //Team Lead goals
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 77, 0, null, null, 1, 1, 1)");
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 78, 0, null, null, 1, 1, 1)");
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 76, 50, null, null, 1, 1, 1)");
        $this->addSql("insert into fisdap2_goals (goal_set_id, goal_def_id, number_required, max_last_data, percent_successful, team_lead, interview, exam) values (3, 79, 0, null, null, 1, 1, 1)");
    }

    public function down(Schema $schema)
    {

        //remove new default ohio goalset
        $this->addSql("delete from fisdap2_goal_sets where id = 3");
        $this->addSql("delete from fisdap2_goals where goal_set_id = 3");

        //revert existing id 4 back to id 3
        $this->addSql("update fisdap2_goal_sets set id = 3 where id = 4");
        $this->addSql("update fisdap2_goals set goal_set_id = 3 where goal_set_id = 4");

        //revert table alters
        $this->addSql("alter table fisdap2_goals modify column number_required TINYINT(3) unsigned NOT NULL default 0;");
        $this->addSql("alter table fisdap2_goal_defs modify column def_goal_number_required TINYINT(4) unsigned NOT NULL default 0");

        //delete the added goal_defs
        $this->addSql("delete from fisdap2_goal_defs where id in (118, 119, 120, 121, 122, 123, 124, 125, 126, 127, 128, 129, 130, 131, 132, 133)");
    }
}
