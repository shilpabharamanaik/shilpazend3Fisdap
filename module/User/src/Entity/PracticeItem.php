<?php namespace User\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use User\EntityUtils;


/**
 * Practice Item
 *
 * An instance of a Practice Definition associated with a student's shift
 *
 * @Entity(repositoryClass="Fisdap\Data\Practice\DoctrinePracticeItemRepository")
 * @Table(name="fisdap2_practice_items")
 */
class PracticeItem extends EntityBaseClass
{
    /**
	 * @var integer
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue
	 */
	protected $id;
    
    /**
     * @var StudentLegacy
     * @ManyToOne(targetEntity="StudentLegacy")
     * @JoinColumn(name="student_id", referencedColumnName="Student_id")
     */
    protected $student;
    
    /**
     * @var ShiftLegacy
     * @ManyToOne(targetEntity="ShiftLegacy")
     * @JoinColumn(name="shift_id", referencedColumnName="Shift_id")
     */
    protected $shift;
    
    /**
     * @var PracticeDefinition
     * @ManyToOne(targetEntity="PracticeDefinition", inversedBy="practice_items")
     */
    protected $practice_definition;
    
    /**
     * @var EvalSessionLegacy
     * @ManyToOne(targetEntity="EvalSessionLegacy")
     * @JoinColumn(name="eval_session_id", referencedColumnName="EvalSession_id")
     */
    protected $eval_session;

    /**
     * @var Subject
     * @ManyToOne(targetEntity="Subject")
     */
    protected $patient_type;
    
    /**
     * @var boolean
     * @Column(type="boolean")
     */
    protected $passed = false;
    
    /**
     * @var boolean
     * @Column(type="boolean")
     */
    protected $confirmed = false;
    
    /**
     * @var \DateTime
     * @Column(type="time", nullable=true)
     */
    protected $time;
    
    /**
     * @var \Fisdap\Entity\EvaluatorType
     * @ManyToOne(targetEntity="EvaluatorType")
     */
    protected $evaluator_type;
    
    /**
     * @var integer
     * @Column(type="integer", nullable=true)
     */
    protected $evaluator_id;
	
	/**
     * @var AirwayManagement
     * @OneToOne(targetEntity="AirwayManagement", mappedBy="practice_item", cascade={"persist","remove"})
     */
    protected $airway_management;
    
    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="Med", mappedBy="practice_item", cascade={"persist","remove"})
     */
    protected $meds;
	
	/**
     * @var ArrayCollection
     * @OneToMany(targetEntity="Airway", mappedBy="practice_item", cascade={"persist","remove"})
     */
    protected $airways;
	
	/**
     * @var ArrayCollection
     * @OneToMany(targetEntity="Iv", mappedBy="practice_item", cascade={"persist","remove"})
     */
    protected $ivs;
	
	/**
     * @var ArrayCollection
     * @OneToMany(targetEntity="CardiacIntervention", mappedBy="practice_item", cascade={"persist","remove"})
     */
    protected $cardiac_interventions;
	
	/**
     * @var ArrayCollection
     * @OneToMany(targetEntity="OtherIntervention", mappedBy="practice_item", cascade={"persist","remove"})
     */
    protected $other_interventions;
	
	/**
     * @var ArrayCollection
     * @OneToMany(targetEntity="Vital", mappedBy="practice_item", cascade={"persist","remove"})
     */
    protected $vitals;
    
    public function init()
    {
        //Set default patient_type to live human
        $this->meds = new ArrayCollection();
		$this->airways = new ArrayCollection();
		$this->ivs = new ArrayCollection();
		$this->cardiac_interventions = new ArrayCollection();
		$this->other_interventions = new ArrayCollection();
		$this->vitals = new ArrayCollection();
        $this->set_patient_type(5);
    }
    
	
    public function set_passed($value)
    {
        // Has the passed field changed?
        $changed = $this->passed != $value;
        $this->passed = $value;
        
		// if they've updated the modal to say they have failed, remove any skills they may have received credit for
		if($changed){
			
			$am = $this->getAirwayManagement();
			if($am !== false){
				// update the existing airway management record
				$am->success = $this->passed;
				$am->save();
			}
			
			if(!$this->passed){
				$this->deleteSkills();
			}
		}
    }

    /**
     * Set confirmation for this practice item, if an eval is attached, set the confirmation for the eval too
     *
     * @param $confirmed
     */
    public function set_confirmed($confirmed)
    {
        $this->confirmed = $confirmed;
        if ($this->eval_session->id) {
            $this->eval_session->confirmed = $confirmed;
        }
    }
    
    
    public function set_student($value)
    {
        $this->student = self::id_or_entity_helper($value, "StudentLegacy");
        return $this;
    }
    
    public function set_shift($value)
    {
        $this->shift = self::id_or_entity_helper($value, "ShiftLegacy");
        $this->student = $this->shift->student;
        return $this;
    }
    
    public function set_practice_definition($value)
    {
        $this->practice_definition = self::id_or_entity_helper($value, "PracticeDefinition");
        return $this;
    }
    
    public function set_eval_session($value)
    {
        $this->eval_session = self::id_or_entity_helper($value, "EvalSessionLegacy");
        return $this;
    }
    
    public function set_patient_type($value)
    {
        $this->patient_type = self::id_or_entity_helper($value, "Subject");
        
        //Loop through all attached skills and change the subject (patient type)
        foreach ($this->airways as $skill) {
            $skill->subject = $this->patient_type;
        }
        foreach ($this->meds as $skill) {
            $skill->subject = $this->patient_type;
        }
        foreach ($this->ivs as $skill) {
            $skill->subject = $this->patient_type;
        }
        foreach ($this->cardiac_interventions as $skill) {
            $skill->subject = $this->patient_type;
        }
        foreach ($this->other_interventions as $skill) {
            $skill->subject = $this->patient_type;
        }
        foreach ($this->vitals as $skill) {
            $skill->subject = $this->patient_type;
        }
        return $this;
    }
    
    public function set_evaluator_type($value)
    {
        $this->evaluator_type = self::id_or_entity_helper($value, "EvaluatorType");
        return $this;
    }
    
    public function set_time($value)
    {
        $this->time = new \DateTime($value);
        return $this;
    }
	
	// we need to wait until this function is called to attach skills
	public function confirmAttachSkills($attach_skills, $passed)
	{		
		// give them airway management credit if this practice item came from a definition that wants to track that
		if($this->practice_definition->airway_management_credit === true){
			$this->attachAirwayManagement($passed);
		}
		
		// Give the student credit for the skills for this practice item
		if($attach_skills){
			$this->attachSkills($passed);
		}
		
	}

    /**
     * Trigger the deletion of airway management credit and skills associated with this practice item upon unconfirming
     */
    public function unconfirmDeleteSkills(){
        if($this->practice_definition->airway_management_credit === true){
            $this->deleteAirwayManagement();
        }

        $this->attachSkills(false);
    }
    
	public function getAirwayManagement()
	{
		return ($this->airway_management) ? $this->airway_management : false;
	}
	
    /**
     * Return the name of the evaluator
     * @return string
     */
    public function getEvaluatorName()
    {
    	if($this->evaluator_id){
	        $roleData = EntityUtils::getEntity($this->evaluator_type->entity_name, $this->evaluator_id);
	        
	        if($roleData->user){
	        	return $roleData->user->getName();
	        }else{
	        	return "[Deleted " . $this->evaluator_type->name . "]";
	        }
    	}else{
    		return "[No Evaluator Set]";
    	}
    }
    
    public function getSummary()
    {
		$summary = "<span class='summary-header {$this->shift->type}'>"
                 . ($this->passed ? "Successful" : "Unsuccessful") . " "
                 . $this->practice_definition->name . "</span><br />";
                 
        $summary .= "<span class='summary-details'>" . "Evaluator: " . $this->getEvaluatorName() . "</span>";
        
        if (count($this->practice_definition->practice_skills)) {
            $summary .= "<br />Skills: ";
            $skills = array();
            foreach($this->practice_definition->practice_skills as $skill) {
                $skills[] = $skill->name;
            }
            $summary .= implode(", ", $skills);
        }
        
        return $summary;
    }
    
    public function getDateTime()
    {
        $shiftDate = $this->shift->start_datetime;
        
        if ($this->time) {
            $shiftDate->setTime($this->time->format("H"), $this->time->format("i"));            
        }
        return $shiftDate;
    }
	
	public function attachAirwayManagement($passed)
	{
		$am = $this->getAirwayManagement();
		if($am !== false){
			// update the existing airway management record
			$am->success = $passed;
		}
		else {
			$am = EntityUtils::getEntity('AirwayManagement');
			$am->shift = $this->shift;
			$am->practice_item = $this;
			$am->subject = EntityUtils::getEntity('Subject', $this->patient_type->id);
			$am->performed_by = true;
			$am->success = $passed;
			$am->airway_management_source = EntityUtils::getEntity('AirwayManagementSource', 1);
		}
		
		$this->save();
		$am->save();
	}

    /**
     * Delete the airway management associated with this practice item, if it exists.
     */
    public function deleteAirwayManagement(){
        $am = $this->getAirwayManagement();

        if($am !== false){
            $am->delete();
            $this->airway_management = null;
            $this->save();
        }
    }
    
    /**
     * Attach or delete skills depending on whether or not the practice item was passed.
     * @throws \Exception when $this->passed is set before the item has been attached to a shift/student
     */
     private function attachSkills($passed)
    {
        if ($passed) {
            foreach($this->practice_definition->practice_skills as $practiceSkill) {
                $skill = EntityUtils::getEntity($practiceSkill->entity_name);
                
                foreach($practiceSkill->fields as $field => $value) {
                    $skill->$field = $value;
                }
                
                if (!$this->student || !$this->shift) {
                    throw new \Exception("The practice item must be attached to a shift/student before pass/fail can be set.");
                }
                
                $skill->student = $this->student;
                $skill->shift = $this->shift;
                $skill->subject = $this->patient_type;
                $skill->practice_item = $this;
                $skill->save(false);
            }
        } else {
            $this->deleteSkills();            
        }
    }
    
    /**
     * Delete all the skills attached to this practice item
     * @return void
     */
    private function deleteSkills()
    {
        foreach ($this->airways as $skill) {
            $skill->delete(false);
        }
        foreach ($this->meds as $skill) {
            $skill->delete(false);
        }
        foreach ($this->ivs as $skill) {
            $skill->delete(false);
        }
        foreach ($this->cardiac_interventions as $skill) {
            $skill->delete(false);
        }
        foreach ($this->other_interventions as $skill) {
            $skill->delete(false);
        }
        foreach ($this->vitals as $skill) {
            $skill->delete(false);
        }
        
        $this->airways->clear();
        $this->ivs->clear();
        $this->vitals->clear();
        $this->meds->clear();
        $this->other_interventions->clear();
        $this->cardiac_interventions->clear();
    }
}