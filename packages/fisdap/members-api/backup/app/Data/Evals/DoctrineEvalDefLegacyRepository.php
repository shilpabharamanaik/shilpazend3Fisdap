<?php namespace Fisdap\Data\Evals;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Fisdap\Data\Repository\DoctrineRepository;

/**
 * Class DoctrineEvalDefLegacyRepository
 *
 * @package Fisdap\Data\Evals
 * @copyright 1996-2014 Headwaters Software, Inc.
 */
class DoctrineEvalDefLegacyRepository extends DoctrineRepository implements EvalDefLegacyRepository
{
    public function getScenarioTopicAreas($studentId)
    {
        $topicAreas = array(
            "N/A" => 0,
            "Respiratory Distress/Failure" => 0,
            "Chest Pain" => 0,
            "Cardiac (Rhythm Disturbance including Cardiac Arrest)" => 0,
            "Stroke" => 0,
            "Overdose" => 0,
            "Abdominal Pain" => 0,
            "Allergic Reaction/Anaphylaxis" => 0,
            "Diabetic Emergencies" => 0,
            "Psychiatric Condition" => 0,
            "Seizure" => 0,
            "OB/GYN" => 0,
            "Blunt Trauma" => 0,
            "Penetrating Trauma" => 0,
            "Burns" => 0,
            "Hemorrhage" => 0
        );

        $query = "SELECT
					Score
					FROM
						Eval_ItemSessions i
						INNER JOIN Eval_Session s ON i.EvalSession_id = s.EvalSession_id
					WHERE
						ItemDef_id IN (32317, 32318, 34818, 34921, 37094, 37112, 38869)
						AND s.Passed = 1 
						AND Subject = " . $studentId;

        $conn = $this->_em->getConnection();
        $results = $conn->query($query);

        foreach ($results as $result => $topic) {
            switch ($topic['Score']) {
                case 0:
                    $topicAreas['N/A']++;
                    break;
                case 1:
                    $topicAreas['Respiratory Distress/Failure']++;
                    break;
                case 2:
                    $topicAreas['Chest Pain']++;
                    break;
                case 3:
                    $topicAreas['Cardiac (Rhythm Disturbance including Cardiac Arrest)']++;
                    break;
                case 4:
                    $topicAreas['Stroke']++;
                    break;
                case 5:
                    $topicAreas['Overdose']++;
                    break;
                case 6:
                    $topicAreas['Abdominal Pain']++;
                    break;
                case 7:
                    $topicAreas['Allergic Reaction/Anaphylaxis']++;
                    break;
                case 8:
                    $topicAreas['Diabetic Emergencies']++;
                    break;
                case 9:
                    $topicAreas['Psychiatric Condition']++;
                    break;
                case 10:
                    $topicAreas['Seizure']++;
                    break;
                case 11:
                    $topicAreas['OB/GYN']++;
                    break;
                case 12:
                    $topicAreas['Blunt Trauma']++;
                    break;
                case 13:
                    $topicAreas['Penetrating Trauma']++;
                    break;
                case 14:
                    $topicAreas['Burns']++;
                    break;
                case 15:
                    $topicAreas['Hemorrhage']++;
                    break;
                default:
                    $topicAreas['N/A']++;
                    break;
            }
        }

        return $topicAreas;
    }

    public function getStudentEvals($studentId, $filterSkillsheets = false, $filterAffectiveEvals = false)
    {
        $query = "
			SELECT 
				*,
				(SELECT 
					sum(id.Points) 
				FROM 
					Eval_ItemSessions eis 
					INNER JOIN Eval_Item_def id ON id.ItemDef_id = eis.ItemDef_id 
				WHERE 
					eis.EvalSession_id = es.EvalSession_id
					AND NOT(id.AllowNotApplicable = 1 AND eis.Score = -1)) AS total_points,
				(SELECT 
					sum(eis.Score) 
				FROM 
					Eval_ItemSessions eis 
					INNER JOIN Eval_Item_def id ON id.ItemDef_id = eis.ItemDef_id
				WHERE 
					eis.EvalSession_id = es.EvalSession_id
					AND NOT(id.AllowNotApplicable = 1 AND eis.Score = -1)) AS scored_points
			FROM 
				Eval_Session es 
				LEFT JOIN Eval_def ed ON es.EvalDef_id = ed.EvalDef_id
			WHERE 
				es.Subject = " . ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $studentId) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : "")) . "
		";
        
        if ($filterSkillsheets) {
            $query .= " AND ed.is_skillsheet = 1";
        }
        
        if ($filterAffectiveEvals) {
            $query .= " AND ed.is_affective_evaluation = 1";
        }
        
        $query .= " ORDER BY EvalTitle ";
        
        $conn = $this->_em->getConnection();
        $result = $conn->query($query);
        
        return $result;
    }
    
    public function getEvalsByHook($hook_ids, $program_id)
    {
        if (empty($hook_ids)) {
            return 0;
        }
        
        if (!is_array($hook_ids)) {
            $hook_ids = array($hook_ids);
        }
        
        $query = "SELECT e.EvalDef_id as id, e.EvalTitle as name FROM Eval_def e, Eval_Program_Hooks eph WHERE e.EvalDef_id = eph.EvalDef_id AND eph.Program_id = $program_id AND eph.EvalHookDef_id IN (" . implode(",", $hook_ids) . ") ORDER BY e.EvalTitle";
        
        
        $db = \Zend_Registry::get('db');
        $result = $db->query($query)->fetchAll();
        
        if (count($result) > 0) {
            return $result;
        }
        
        //If we didn't get any results from the program, check default settings
        $query = "SELECT e.EvalDef_id as id, e.EvalTitle as name FROM Eval_def e, Eval_Hook_Defaults ehd WHERE e.EvalDef_id = ehd.EvalDef_id AND ehd.EvalHookDef_id IN (" . implode(",", $hook_ids) . ") ORDER BY e.EvalTitle";
        
        $result = $db->query($query)->fetchAll();
        
        return $result;
    }
    
    public function getEvalsByProgram($program_id)
    {
        // first get all the hooks
        $query = 'SELECT EvalHookDef_id as id FROM Eval_Hook_def';
        $db = \Zend_Registry::get('db');
        $hooks = $db->query($query)->fetchAll();

        // loop through and get the evals for each hook
        // we have to do this one at a time because some hooks might be customa dn some might use the defaults
        $evals = array();
        foreach ($hooks as $hook) {
            $hook_evals = $this->getEvalsByHook($hook['id'], $program_id);
            foreach ($hook_evals as $eval) {
                $evals[$eval['id']] = $eval['name'];
            }
        }

        // return an alphebetized array of evals, keyed by eval def id
        asort($evals);
        return $evals;
    }
}
