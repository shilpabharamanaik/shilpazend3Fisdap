<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\PrePersist;
use Fisdap\Api\Shifts\Patients\Skills\AbstractSkills;
use Fisdap\EntityUtils;


/**
 * Base class that will add several fields for 
 * @HasLifecycleCallbacks
 * @MappedSuperclass
 */
class Skill extends Timestampable
{
    /**
     * @Column(type="boolean")
     * true = I performed this
     * false = I observed this
     */
    protected $performed_by = false;
	
	/**
	 * @Column(type="integer", nullable=true)
	 */
	protected $skill_order;

	
    /**
     * @ManyToOne(targetEntity="Subject")
     */
    protected $subject;
    
    /**
	 * @ManyToOne(targetEntity="ShiftLegacy")
	 * @JoinColumn(name="shift_id", referencedColumnName="Shift_id")
	 */
	protected $shift;

    /**
	 * @ManyToOne(targetEntity="Run")
	 */
	protected $run;
    
    /**
	 * @ManyToOne(targetEntity="Patient")
	 */
	protected $patient;
    
    /**
	 * @ManyToOne(targetEntity="StudentLegacy")
	 * @JoinColumn(name="student_id", referencedColumnName="Student_id")
	 */
	protected $student;
	
	/**
	 * @OneToOne(targetEntity="Verification", cascade={"persist","remove"})
	 */
	protected $verification;
	
	/**
	 * @ManyToOne(targetEntity="PracticeItem")
	 */
	protected $practice_item;
	
	/**
     * @Column(type="boolean")
     */
    protected $soft_deleted = 0;
	
    // These two fields are only used when assigning skills to a scenario.
    // Not mapped to any DB field, but they need to be defined so that they
    // can be set.
    public $is_als;
    public $priority;
    
	public function init()
	{
		if (!$this->created) {
			$this->created = new \DateTime("now");
			$this->updated = new \DateTime("now");
		}
	}
	
	public function set_shift($value)
	{
		$this->shift = self::id_or_entity_helper($value, 'ShiftLegacy');

        // This check shouldn't be needed, but let's be safe
        if ($this->shift) {
            $this->student = $this->shift->getStudent();
        }
	}
	
	public function set_run($value)
	{
		$this->run = self::id_or_entity_helper($value, 'Run');
	}
	
	public function set_patient($value)
	{
		$this->patient = self::id_or_entity_helper($value, 'Patient');
	}
	
	public function set_student($value)
	{
		$this->student = self::id_or_entity_helper($value, 'StudentLegacy');
	}
	
	public function set_verification($value)
	{
		$this->verification = self::id_or_entity_helper($value, 'Verification');
	}
	
	/**
	 *	Currently this will set subject to 1-live-human if not set
	 */
	public function get_subject()
	{
		if (is_null($this->subject)) {
			$this->subject = EntityUtils::getEntity('Subject', 1);
		}
		
		return $this->subject;
	}
	
	public function set_subject($value)
	{
		$this->subject = self::id_or_entity_helper($value, 'Subject');
	}
	
	public function setPerformedBy($performedBy)
	{
		$this->performed_by = $performedBy;
	}

	/**
	 * For newly created skills, set the skill order if it has not been set
	 * manually by counting the number of existing skills.
	 * 
	 * @PrePersist
	 */
	public function setSkillOrder()
	{
        if (!$this->skill_order) {
            if ($this->patient) {
                $skills = EntityUtils::getRepository('Patient')->getSkillsByPatient($this->patient->id);
            } else if ($this->shift) {
                $skills = EntityUtils::getRepository('ShiftLegacy')->getSkillsByShift($this->shift->id);
            }

            if(empty($skills)) {
                $this->skill_order = 1;
            } else {
                $this->skill_order = array_pop($skills)->skill_order + 1;
            }
        }
	}
	
	/**
	 * Stubbing this in just so any call to a skill won't fail.
	 * 
	 * This function is used to get a detail line for this specific procedure,
	 * for display on pages listing off several procedures.
	 * 
	 * @return String containing the HTML markup for the procedure text. 
	 */
	public function getProcedureText($html=true){
		return '';
	}
	
	/**
	 *	Note: Return false if goal cannot be counted as either performed or observed only
	 */
	public function countsTowardGoal($dataReqs) {
		// must be performed by, and subject types agree.
		$subject = $this->get_subject();
		return (in_array($subject->id, $dataReqs->subjectTypes));
	}
	
	/**
	 *	Note: Return false if goal cannot be counted as either performed or observed only
	 */
	public static function countsTowardGoalSQL($skill, $dataReqs) {
		// must be performed by, and subject types agree.
		return (in_array($skill['subject_id'], $dataReqs->subjectTypes));
	}
	
	public function getPerformedByText()
	{
		return $this->performed_by ? "Performed" : "Observed";
	}
	
	/**
	 * This should be implemented by the children of this class
	 */
	public function getHookIds()
	{
		return array();
	}

	/**
	 * Largely not needed, but some validation is done for Ivs.
	 * @param $size
	 */
	public function setSize($size)
	{
		$this->size = $size;
	}

	/**
	 * @return integer
	 */
	public function getSize()
	{
		return $this->size;
	}

	public function setDefaultSkills(AbstractSkills $abstractSkills)
	{
		$this->setSize($abstractSkills->size);
		$this->success = $abstractSkills->success;
		$this->attempts = $abstractSkills->attempts;
		$this->skill_order = intval($abstractSkills->skillOrder);
		$this->setPerformedBy($abstractSkills->performed);
		if ($abstractSkills->getPatient() !== null) {
			$this->set_subject($abstractSkills->getPatient()->getSubject());
			$this->set_student($abstractSkills->getPatient()->getStudent());
			$this->set_shift($abstractSkills->getPatient()->getShift());
		}
	}

	public function toArray()
	{
		$skills = parent::toArray();
		$skills['skillOrder'] = $skills['skill_order'];
		$skills['performed'] = $skills['performed_by'];

		unset(
			$skills['skill_order'],
			$skills['performed_by'],
			$skills['soft_deleted'],
			$skills['created']
		);

		return $skills;
	}
}
    
