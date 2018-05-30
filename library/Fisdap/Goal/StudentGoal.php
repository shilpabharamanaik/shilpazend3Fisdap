<?php

namespace Fisdap\Goal;

/**
 *	Goal result, and anything that applies to 1 goal of 1 student
 *
 *	@author Maciej
 */
class StudentGoal
{
    // goal pointer
    public $goal;	// $goal->goalSet->number_required;
    public $student_id;
    public $studentName;
    
    // params
    //public $numberRequired	= $goal->number_required;
    //public $maxLastData		= $goal->max_last_data;
    //public $percentSuccessful = $goal->percent_successful;
    
    // results
    protected $performedCount = 0;
    protected $observedCount = 0;
    
    protected $validResult = false;

    // DEBUG
    //protected $logger;

    /**
     *	Getters, plus check for valid result: returns '?' if not valid result
     */
    protected $allGetters = array(
        'met',
        'metDesc',
        'percentDoneDesc',
        'requirementDesc',
        'performedCountDesc',
        'performedCount',
        'observedCountDesc',
        'observedCount',
        'weightDone',
        'percentDone',
        'pointsTowardGoal',
    );

    protected $alwaysReturnGetters = array('requirementDesc');

    public function resultValid($val = true)
    {
        $this->validResult = $val;
    }
    
    public function setPerformedCount($count)
    {
        $this->performedCount = (int) $count;
    }
    
    public function setObservedCount($count)
    {
        $this->observedCount = (int) $count;
    }
    
    public function __construct(&$goal, $student_id, $studentName = null)
    {
        $this->goal = $goal;
        $this->student_id = $student_id;
        $this->studentName = $studentName;

        //$this->logger= \Zend_Registry::get('logger');
    }
    
    /**
     *		if $considerShiftType = true, $examIntTeamObj expects shift object in it
     *	@param boolean $considerShiftType
     *	@param boolean $multiplier multiply the number of performed or observed, not just increment by one
     */
    public function add($isPerformed, &$examIntTeam, $condition=true, $considerShiftType=true, $multiplier = 1)
    {
        // goal object specifies what is required, $examInTeam is data
        if ($condition) {
            // team lead only required for field shifts
            $teamLead = ($considerShiftType && $examIntTeam['Type'] == 'field') ? $examIntTeam['team_lead'] : true;
            // performed/observed according to goals settings
            $performed = $isPerformed
                && !($this->goal->team_lead && !$teamLead)
                && !($this->goal->interview && !$examIntTeam['interview'])
                && !($this->goal->exam && !$examIntTeam['exam']);
            if ($performed) {
                $this->performedCount += (1 * $multiplier);
            } else {
                $this->observedCount += (1 * $multiplier);
            }
        }
    }
    
    protected function requirementDesc()
    {
        return $this->goal->number_required;
    }

    /**
     *	By default 'met' means met for any goal even if goal is 0:
     *	@param boolean $meaningful, returns true only if requirement > 0
     */
    public function met($meaningful=false)
    {
        $meaningfulCond = $meaningful ? $this->goal->number_required > 0 : true;
        return ($this->performedCount >= $this->goal->number_required && $meaningfulCond);
    }
    
    /**
     *	By default 'met' means met for any goal even if goal is 0:
     *	@param boolean $meaningful, returns true only if requirement > 0
     */
    public function metDesc($meaningful=false)
    {
        return $this->yn($this->met($meaningful));
    }
    
    protected function performedCount()
    {
        return $this->performedCount;
    }
    
    protected function performedCountDesc()
    {
        return $this->performedCount;
    }
    
    protected function observedCount()
    {
        return $this->observedCount;
    }
    
    protected function observedCountDesc()
    {
        return $this->observedCount;
    }
    
    /**
     *	Returns number - fraction of 1
     */
    protected function percentDone()
    {
        if ($this->goal->number_required==0) {
            return 1;
        }
        $ratio = ($this->performedCount / $this->goal->number_required);
        return ($ratio > 1) ? 1 : $ratio;
    }
    
    protected function percentDoneDesc()
    {
        $ratioDone = $this->percentDone();
        return number_format($ratioDone * 100, 0) . '%';
    }
    
    public function yn($bool)
    {
        return ($bool) ? 'YES' : 'NO';
    }
    
    protected function pointsTowardGoal()
    {
        // smaller one of: required and performed
        return ($this->goal->number_required > $this->performedCount) ?
            $this->performedCount : $this->goal->number_required;
    }
    
    /**
     *	Used to calculate category totals totals
     *	(sum up 'weights' then divide but total required)
     */
    public function weightDone()
    {
        // smaller one of: required and performed
        $count = ($this->goal->number_required > $this->performedCount) ?
            $this->performedCount : $this->goal->number_required;
            
        return $this->percentDone() * $count;
    }
    
    public function __get($property)
    {
        if (in_array($property, $this->allGetters)) {
            if ($this->validResult || in_array($property, $this->alwaysReturnGetters)) {
                return $this->$property();
            } else {
                // not valid result
                return '?';
            }
        } else {
            throw new \Exception("No getter/setter for $property");
        }
    }
}
