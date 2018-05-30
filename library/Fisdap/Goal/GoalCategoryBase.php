<?php

namespace Fisdap\Goal;

/**
 *	Calculates student goals for each category
 *	One instance per unique student/goalSet/goalCategory(ex Ages, Skills, Complaints)
 *
 *	@author Maciej jmortenson
 */
class GoalCategoryBase
{
    /**
     *	[studentId][goalSetId][category][goalId] = results
     */
    protected $instances = array(); // W???W?W?W
    
    // current data run set:
    protected $curStudent;
    protected $curData;
    protected $curDataReq;
    protected $curGoalSet;
    protected $curStudentName;

    protected $data;
    /**
     * @var GoalDataRequirements
     */
    protected $dataReq;
    protected $subCategory;
    protected $student_id;
    protected $studentName;
    
    protected $results = array();
    
    /**
     *	Goals and Defs for this category/student/goalSet
     */
    protected $categoryDefs;
    protected $categoryGoals;

    // DEBUG
    protected $logger;

    public function __construct($studentId, $goalSet, &$data, $dataReqs, $studentName, $subCategory = null)
    {
        $this->logger = \Zend_Registry::get('logger');
        //$this->logger->debug('Memory usage start construct GoalCategoryBase: ' . round(memory_get_usage() / 1000 / 1000) . 'MB' );

        $this->student_id = $studentId;
        $this->studentName = $studentName;
        $this->data = $data;
        $this->dataReq = $dataReqs;
        $this->goalSet = $goalSet;
        //$this->ages = $this->goalSet->ages;
        $this->subCategory = $subCategory;
        
        // create lists of goalIds and goalDefIds
        foreach ($this->goalSet->goals as $id => $goal) {
            //$this->categoryDefs[$goal->def->id][] = $goal;
            $this->categoryDefs[$goal->def->id] = $goal->id;
        }
        
        $this->init();
        
        $this->countAllGoals();
    }
    
    protected function init()
    {
    }
    
    /**
     *	@todo Implement x out of y skills: order shifts and skills, then counter
     *		class will keep track of the rest
     *	WARNING: each category is respongible for not counting twice results:
     *		If category uses shifts for patients, it shouldn't use patient iterator too
     */
    protected function countAllGoals()
    {
        $sqlDelta = $eachPatientDelta = 0;
        if (!is_array($this->data->shifts)) {
            return;
        }
        
        // shifts
        //$shiftIds = array();
        foreach ($this->data->shifts as $shift) {
            $wasShiftUsed = $this->forEachShift($shift);
            //$shiftIds[] = $shift['Shift_id'];
        }
        $this->afterAllShifts();
        //$this->logger->debug('Memory usage GoalCategoryBase afterAllShifts: ' . round(memory_get_usage() / 1000 / 1000) . 'MB' );


        // patients
        $totalPatients = 0;
        //$patients = \Fisdap\Entity\ShiftLegacy::getPatientsSQL($shiftIds);
        //$mem = memory_get_usage();
        //foreach ($this->data->shifts as $shift) {
        foreach ($this->data->patients as $shiftId => $shiftPatients) {
            $totalPatients += count($shiftPatients);
            //$this->logger->debug('Memory usage delta after getPatientsSql: ' . round((memory_get_usage() - $mem) / 1000) . 'KB')
            //$sqlDelta += memory_get_usage() - $mem;
            foreach ($shiftPatients as $patient) {
                if ($this->isPatientTypeOK($patient)) {
                    //$this->logger->debug('Memory usage GCB before forEachPatient: ' . round(memory_get_usage() / 1000 / 1000) . 'MB' );
                    //$mem = memory_get_usage();
                    $wasPatientDataUsed = $this->forEachPatient($patient, $this->data);
                    //$this->logger->debug('Mem delta AFTER forEachPatient: ' . round((memory_get_usage() - $mem) / 1000)  . 'KB' );
                    //$this->logger->debug('Memory usage delta after forEachPatient: ' . round((memory_get_usage() - $mem) / 1000) . 'KB');
                    //$eachPatientDelta += memory_get_usage() - $mem;
                }
            }
        }
        //$this->logger->debug('Mem delta after looping thru Shifts to do forEachPatient(): ' . round((memory_get_usage() - $mem) / 1000) . 'KB');
        //$this->logger->debug('# of shifts: ' . count($this->data->shifts) . ', avg patients/shift: ' . ($totalPatients / count($this->data->shifts)));

        //$this->logger->debug('Memory usage GoalCategoryBase deltas before afterAllPatients, patientSQL: ' . round($sqlDelta / 1000) . 'KB, eachPatient: ' . round($eachPatientDelta / 1000) . 'KB; total usage now: ' . round(memory_get_usage() / 1000 / 1000) . 'MB' );
        $this->afterAllPatients();
        //$this->logger->debug('Memory usage GoalCategoryBase afterAllPatients: ' . round(memory_get_usage() / 1000 / 1000) . 'MB' );

        // set results as valid
        //foreach ($this->categoryGoals as $goalId => $goal) {
        foreach ($this->goalSet->goals as $id => $goal) {
            $this->getResultObject($goal)->resultValid();
        }
        //$this->logger->debug('Memory usage GoalCategoryBase after foreach(goals) getResultObject: ' . round(memory_get_usage() / 1000 / 1000) . 'MB' );
    }
    
    /**
     *	STUBS
     *	@return boolean if shift was used for counting results
     *		each child should implement this
     */
    protected function forEachShift(&$shift)
    {
        return false;
    }
    protected function afterAllShifts()
    {
    }
    
    protected function forEachPatient(&$patient)
    {
        return false;
    }
    protected function afterAllPatients()
    {
    }
    
    /**
     *	Get result object for each goal, instantiate if if needed
     *	@param object \Fisdap\Entity\Goal
     *	@return object \Fisdap\Goal\StudentGoal
     */
    public function getResultObject(&$goal)
    {
        if (!isset($this->results[$goal->id])) {
            $this->results[$goal->id] = new StudentGoal($goal, $this->student_id, $this->studentName);
        }
        //$this->logger->debug('Memory usage Goals AFTER new StudentGoal: ' . round(memory_get_usage() / 1000 / 1000) . 'MB' );

        return $this->results[$goal->id];
    }
    
    /**
     *	Adds count result to result object. Result object handles filtering logic
     *	contained in goal object to classify count as performed or observed
     *	@param integer $goalDefId
     *	@param integer $isPerformed, as far as logic so far does it qualify as performed
     *		(later this is passed by settings in exam, interview, team lead)
     *	@param array $examIntTeamArray object containing these three booleans
     *		if $considerShiftType = true, $examIntTeamObj expects shift object in it
     *	@param boolean $considerShiftType
     *	@param int $multiplier for counting things more than just incrementally (i.e. Hours)
     */
    public function add($goalDefId, $isPerformed, &$examIntTeamArray, $condition = true, $considerShiftType = true, $multiplier = 1)
    {
        // $mem = memory_get_usage();
        // is goal def in our goalSet?
        if (!isset($this->categoryDefs[$goalDefId])) {
            return;
        }
        //$this->logger->debug('Mem delta inside GCB->add() check categoryDefs, KBs: ' . round((memory_get_usage() - $mem) / 1000)); //' . print_r($memDeltas, TRUE) );

        // run against each goal for which goal category applies
        //foreach ($this->categoryDefs[$goalDefId] as $goal) {

        foreach ($this->goalSet->goals as $goal) {
            if ($goal->id == $this->categoryDefs[$goalDefId]) {
                if (!isset($this->results[$goal->id])) {
                    $this->getResultObject($goal);
                }
                //$this->logger->debug('Mem delta inside GCB->add() after getResultObject, KBs: ' . round((memory_get_usage() - $mem) / 1000)); //' . print_r($memDeltas, TRUE) );

                $this->results[$goal->id]->add($isPerformed, $examIntTeamArray, $condition, $goalDefId, $multiplier);

                //$this->logger->debug('Mem delta inside GCB->add() after adding to $this->results, KBs: ' . round((memory_get_usage() - $mem) / 1000)); //' . print_r($memDeltas, TRUE) );
            }
        }
    }

    
    /**
     *	Note: Subject types check is done in Skill entity
     */
    public function isPatientTypeOK($patient)
    {
        if (in_array($patient['subject_id'], $this->dataReq->subjectTypes)) {
            return true;
        } else {
            return false;
        }
    }
    
    
    public function dumpData($data = null)
    {
        echo "<pre>";
        print_r($this->debugDumpAllGoalsDataInDataSet($data));
        echo "</pre>";
    }
    
    //public function debugDumpAllGoalsDataInDataSet($data = null)
    //{
    //	if (is_null($data)) {
    //		$data = $this->data;
    //	}
    //
    //	if (!is_array($data->shifts)) {
    //		$ret['errors'] = 'No Shifts';
    //		return $ret;
    //	}
    //
    //	foreach ($data->shifts as $shift) {
    //		foreach ($shift->patients as $patient) {
    //			foreach ($patient->complaints as $complaint) {
    //				$ret['complaints'][$patient->id][$patient->age][$complaint->id] = $complaint->name;
    //			}
    //		}
    //	}
    //
    //	return $ret;
    //}
}
