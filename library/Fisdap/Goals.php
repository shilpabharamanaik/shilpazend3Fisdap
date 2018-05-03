<?php

namespace Fisdap;

/**
 *	Runs Goal Set(s) against student data
 *	To get idea how to get results look at method: showResults
 */
class Goals
{
	protected $shiftIds;
	protected $data;	//shifts;

	protected $student_id;
	protected $studentName;
	
	protected $defs;

    protected $goalSet;
    protected $dataReqs;
    protected $goalCatBase;
    protected $time;

    // DEBUG
    protected $logger;

	public function __construct($student_id, $goalSet, $dataReqs=null, $studentName = null)
	{
        if (!empty($student_id) && intval($student_id) > 0) {
            $this->student_id = $student_id;
        } else {
            throw new \Exception('Cannot construct Goals class: $student_id provided is not a positive integer');
        }
		$this->studentName = $studentName;

        $this->goalSet = $goalSet;

		// data options
        $this->dataReqs = new Goal\GoalDataRequirements($dataReqs);

		// want data in object so it's passed by reference
		$this->data = new \stdClass();
		$this->data->shifts = array();

        $this->logger = \Zend_Registry::get('logger');

        //$this->goalCatBase = new \Fisdap\Goal\GoalCategoryBase($this->student_id, $this->goalSet, $this->data, $this->dataReqs, $this->studentName);

        // clear cached results data
		// WTF \Fisdap\Goal\GoalCategoryBase::wipeResults();

//        if (is_null(self::$time)) {
//            self::$time = time();
//        }
//        \Zend_Registry::get('logger')->debug(time() - self::$time);
	}


	public function getGoalSet()
	{
		return $this->goalSet;
	}

	/**
	 *	Return goals, runs them if not run
	 *		if no argument:
	 *			- if any results present, returns last set
	 *			- if no results, runs for self->$goalSet
	 *		argument given:
	 *			- runs that goals set
	 */
	public function getGoalsResultsArray($goalSet = null)
	{
		// which goal set?
		if(is_null($goalSet)) {
			// last result?
			$goalSet = ($this->lastGoalSet) ?
				$this->lastGoalSet :
				$this->goalSet;
		}
		$goalSetId = $goalSet->id;

		// results present?
		if(!$this->results[$goalSetId]) {
			// has goals?
			if (!$goalSet->goals) {
				return;
			}

			$this->loadSkillsData();
            //$this->logger->debug('Memory usage after loadSkillsData: ' . round(memory_get_usage() / 1000 / 1000) . 'MB' );


            $this->results[$goalSetId] = $this->runGoalsForGoalSet($goalSet);
            //$this->logger->debug('Memory usage after runGoalsForGoalSet: ' . round(memory_get_usage() / 1000 / 1000) . 'MB' );

			// save results
			$this->lastGoalSet = $goalSet;

			$this->goalSets[$goalSetId] = $goalSet;
		}

		return $this->results[$goalSetId];
	}


	/**
	 *	Run goals and returns results in one array
	 */
	public function runGoalsForGoalSet($goalSet)
	{
		$results = array();

		$goalCount = count($goalSet->goals);
        //$this->logger->debug('# of goals: ' . $goalCount);
		foreach ($goalSet->goals as $id => $goal) {
			$goalResults[$id] = $this->runGoal($goal);
            //$this->logger->debug('just ran goal: ' . $goal->def->def_category . '/' . $goal->name . ' mem usage: ' . round(memory_get_usage() / 1000 / 1000) . 'MB');
		}

		return $goalResults;
	}


	/**
	 *	Sorting method for results, grouping and sorting is done here
	 *	Right now just returning by category
	 */
	public function getGoalsResults($goalSet=null, $include_airway_management = false, $use_data_reqs_on_am = true)
	{
		$results = $this->getGoalsResultsArray($goalSet);
        //$this->logger->debug('Memory usage Goals after getGoalsResultsArray: ' . round(memory_get_usage() / 1000 / 1000) . 'MB' );

        if(!$results) {
			return;
		}

		// group by category
		$ret = array();
		uasort($results, array("self", "sortGoalDefs"));

		foreach ($results as $i => $goalResult) {

            // Airway Management gotcha:
            // We are NOT including it like a traditional goal, but we still want to store it that way
            // So if you find 'Airway Management' do not include it in the "Skills" category.
            if($goalResult->goal->def->id == 92) {
                if($include_airway_management){
                    $ret[$goalResult->goal->def->name] = $this->getAirwayManagementResultsArray($goalResult, $use_data_reqs_on_am);
                }
            }
            else {
                $ret[$goalResult->goal->def->category][] = $goalResult;
            }
		}

		return $ret;
	}

    public function getAirwayManagementResultsArray($goalResult, $use_data_reqs_on_am)
    {
        $am_results = array();

        // do our weird Airway Management version, if getGoalsResults wants to
        $student_id = $goalResult->student_id;

        $filters = ($use_data_reqs_on_am) ? (array)$this->dataReqs : array();
        $am_data = \Fisdap\EntityUtils::getRepository('AirwayManagement')->getTotals($student_id, $goalResult->goal->goalSet, true, $filters);
		$et_rate_data = \Fisdap\EntityUtils::getRepository('AirwayManagement')->getETTotals($student_id, $goalResult->goal->goalSet, true, $filters);

        $coa_rate_data = $am_data['coa_success_rate_data'];

        $required = $goalResult->goal->number_required;
        $am_results['number_required'] = $required;

        $sim_count = 0;
        if($am_data['sims']){
            foreach($am_data['sims'] as $count){
                $sim_count = $sim_count + $count;
            }
        }

        $am_results['attempts'] = array();
        $am_results['attempts']['student_id'] = $student_id;
        $am_results['attempts']['successes'] = $am_data['total_successes'];
        $am_results['attempts']['performed'] = $am_data['total'];
        $am_results['attempts']['sims'] = $sim_count;
        $am_results['attempts']['neonate'] = $am_data['patients']['Neonate'];
        $am_results['attempts']['infant'] = $am_data['patients']['Infant'];
        $am_results['attempts']['pediatric'] = $am_data['patients']['Pediatric'];
        $am_results['attempts']['adult'] = $am_data['patients']['Adult'];
        $am_results['attempts']['unknown'] = $am_data['patients']['Unknown'];
        $am_results['attempts']['observed'] = $am_data['total_observed'];

		$am_results['et_success_rate'] = array();
		$am_results['et_success_rate']['student_id'] = $student_id;
		$am_results['et_success_rate']['window'] = $et_rate_data['window'];
		$am_results['et_success_rate']['success_count'] = $et_rate_data['success_count'];

        $am_results['coa_success_rate'] = array();
        $am_results['coa_success_rate']['student_id'] = $student_id;
        $am_results['coa_success_rate']['window'] = $coa_rate_data['window'];
        $am_results['coa_success_rate']['success_count'] = $coa_rate_data['success_count'];

        if($required > 0) {
            $goal_percent = floor(($am_data['total'] / $required) * 100);
            $goal_percent = ($goal_percent > 100) ? 100 : $goal_percent;
            $success_percent = floor(($am_data['total_successes'] / $required) * 100);
            $success_percent = ($success_percent > 100) ? 100 : $success_percent;
        }
        else {
            $success_percent = 100;
            $goal_percent = 100;
        }

        $am_results['attempts']['goal_percent'] = $goal_percent;
        $am_results['attempts']['success_percent'] = $success_percent;

		$et_success_rate = floor(($et_rate_data['success_count'] / $et_rate_data['window']) * 100);
		$et_success_rate = ($et_success_rate > 100) ? 100 : $et_success_rate;
		$am_results['et_success_rate']['success_rate'] = $et_success_rate;

        $coa_success_rate = floor(($coa_rate_data['success_count'] / $coa_rate_data['window']) * 100);
        $coa_success_rate = ($coa_success_rate > 100) ? 100 : $coa_success_rate;
        $am_results['coa_success_rate']['success_rate'] = $coa_success_rate;

        return $am_results;
    }


	/**
	 *	Dev / Debug report only
	 */
	public function getResultsArray($results)
	{	
		$arr = array();
		// handle subarrays of results
		if (is_array(current($results))) {
			foreach ($results as $key => $subResults) {
				$arr[$key] = $this->getResultsArray($subResults);
			}
		} else {
			// single results array
			foreach ($results as $i => $goalResult) {
				$arr[$i]['Id'] = $goalResult->goal->id;
				$arr[$i]['Goal<br/>Set<br/>Id'] = $goalResult->goal->goalSet->id;
				$arr[$i]['Goal Description'] = $goalResult->goal->getGoalSummary();
				$arr[$i]['Required'] = $goalResult->goal->number_required;
				$arr[$i]['Req Desc'] = $goalResult->requirementDesc;
				$arr[$i]['Compl'] = $goalResult->performedCountDesc;
				$arr[$i]['% Done'] = $goalResult->percentDoneDesc;
				$arr[$i]['Goal Met'] = $goalResult->metDesc;
			}
		}

		return $arr;
	}

	/**
	 *	DEVS: START HERE: Showing how to get goals results 
	 */
	public function showResults($goalSet = null)
	{
		// 1st Get categorized results:
		$res = $this->getGoalsResults($goalSet);

		$resArray = $this->getResultsArray($res);
		foreach ($resArray as $category => $results) {
			echo "<br/><br/><h2>$category</h2>";
			\Util_Dev::showArray($results);
		}

		// 2nd Get unsorted results in one array:
		echo "<br/><br/><br/><h1>All Results in one array:</h1>";
		$results2 = $this->getGoalsResultsArray($goalSet);
		$resArray2 = $this->getResultsArray($results2);
		\Util_Dev::showArray($resArray2);

		// 3 dump results
		echo "<br/><br/><br/><h1>Dumping categorized results:</h1><pre>";
		echo "</pre>";
	}


	/**
	 *	Loads shifts data
	 */
	public function loadSkillsData()
	{
        // @todo combine into one query instead of two
        $this->data->shifts =  \Fisdap\Entity\StudentLegacy::getShiftsSQL($this->student_id, $this->dataReqs);
        $shiftIds = $patientIds = array();
        foreach ($this->data->shifts as $shift) {
            $shiftIds[] = $shift['Shift_id'];
        }
        if (!empty($shiftIds)) {
            $this->data->patients = \Fisdap\Entity\ShiftLegacy::getPatientsSQL($shiftIds);
        } else {
            $this->data->patients = array();
        }

        if (count($this->data->patients) > 0) {
            foreach($this->data->patients as $shift => $patients) {
                foreach($patients as $patient) {
                    $patientIds[] = $patient['id'];
                }
            }

            $complaints = \Fisdap\Entity\Patient::getComplaintsSQL($patientIds);
            foreach($complaints as $complaint) {
                $this->data->patients[$complaint['shift_id']][$complaint['patient_id']]['complaints'][] = $complaint['complaint_id'];
            }
            unset($complaints);
        }

        // get enumerated values for Complaints and Impressions
        $this->data->enumerated['complaints'] = \Fisdap\EntityUtils::getAllDataArray('Complaint');
        $this->data->enumerated['impressions'] = \Fisdap\EntityUtils::getAllDataArray('Impression');
    }

	/**
	 *	Call Goal Category classes to get results
	 */
	public function runGoal($goal)
	{
		//$newWayCategories = array('skills','leads','ages','enumerated');	//complaints & impressions
		//$category = $goal->def->def_category;
		//$newWay = in_array($category, $newWayCategories); if ($newWay) {}

		//$catResults = Goal\GoalCategoryBase::getInstance($goal, $this->data, self::$dataReqs);
        // result categories setup

        //return $this->goalCatBase->getResult($goal);	//$catResults->getResults($goal);

        $category = $goal;
        if (!is_string($goal)) {
            $category = $goal->def->def_category;
            $subCategory = $goal->def->def_sub_category;
        } else {
            $subCategory = NULL;
        }

        $className = '\\Fisdap\\Goal\\GoalCategory' . ucfirst($category);

        if (!@class_exists($className)) {
            $className = 'Fisdap\\Goal\\GoalCategoryBase';
        }

        //$this->logger->debug('Goals about to instantiate: ' . $className);
        //$this->logger->debug('Memory usage in runGoal before instantiate $goalCategory for ' . $className . ': ' . round(memory_get_usage() / 1000 / 1000) . 'MB' );
        $goalCategory = new $className($this->student_id, $this->goalSet, $this->data, $this->dataReqs, $this->studentName, $subCategory);
        //$this->logger->debug('Memory usage just instantiated $goalCategory for ' . $className . ': ' . round(memory_get_usage() / 1000 / 1000) . 'MB' );

        $result = $goalCategory->getResultObject($goal);
        unset($goalCategory);

        //$this->logger->debug('Memory usage just run getResultObject on ' . $className . ': ' . round(memory_get_usage() / 1000 / 1000) . 'MB' );

        return $result;
	}

	public static function sortGoalDefs($a, $b)
	{
		if ($a->goal->def->display_order == $b->goal->def->display_order) {
			return 0;
		}
		return ($a->goal->def->display_order < $b->goal->def->display_order) ? -1 : 1;
	}
}


?>
