<?php namespace User\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use User\EntityUtils;


/**
 * Entity class for Runs.
 * 
 * @Entity(repositoryClass="Fisdap\Data\Run\DoctrineRunRepository")
 * @Table(name="fisdap2_runs")
 * @HasLifecycleCallbacks
 *
 * @todo Write some unit tests!
 * @todo Write setters/getters and other core functionality
 */
class Run
{
	/**
	 * @var integer
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue
	 */
	protected $id;

	/**
	 * @ManyToOne(targetEntity="ShiftLegacy", inversedBy="runs")
	 * @JoinColumn(name="shift_id", referencedColumnName="Shift_id")
	 */
	protected $shift;
	
	/**
	 * @ManyToOne(targetEntity="StudentLegacy", inversedBy="runs")
	 * @JoinColumn(name="student_id", referencedColumnName="Student_id")
	 */
	protected $student;
	
	/**
	 * @OneToOne(targetEntity="PreceptorSignoff", mappedBy="run", cascade={"persist","remove"})
	 */
	protected $signoff;
	
	/**
	 * @OneToOne(targetEntity="Verification", inversedBy="run", cascade={"persist","remove"})
	 */
	protected $verification;
	
	/**
	 * @Column(type="boolean")
	 */
	protected $locked = false;
	
	/**
     * @Column(type="boolean")
     */
    protected $soft_deleted = false;

	/**
     * @var ArrayCollection
     * @OneToMany(targetEntity="Patient", mappedBy="run", cascade={"persist","remove"})
     */
    protected $patients;
	
	protected $invalidPatients;
	
	public function init()
    {
		//$this->deleted_time_value = new \DateTime("9999-00-00 00:00:00");
		$this->patients = new ArrayCollection();
    }
	
	/**
	 * Set the signoff data
	 * @param \Fisdap\Entity\PreceptorSignoff
	 */
	public function set_signoff($signoff)
	{
		$signoff->run = $this;
		$signoff->student = $this->student;
		$this->signoff = $signoff;
	}
	
	/**
	 * Set the verification for this patient
	 * @param Fisdap\Entity\Verification
	 */
	public function set_verification(Verification $ver = null)
	{
        if(!is_null($ver)){
            $ver->run = $this;
        }

		$this->verification = $ver;

        if (is_null($ver) && $this->verification) {
            foreach($this->patients as $patient) {
                $patient->unset_verification($ver);
            }

            $this->verification->delete();
        }
    }
	
	/**
	 * Add association between Patient and Run
	 *
	 * @param \Fisdap\Entity\Patient $patient
	 */
	public function addPatient(Patient $patient)
	{
		$this->patients->add($patient);
		$patient->run = $this;
		$patient->shift = $this->shift;
		$patient->student = $this->student;
	}
	
	/**
	 * Remove association between Patient and Run
	 *
	 * @param \Fisdap\Entity\Patient $patient
	 */
	public function removePatient(Patient $patient)
	{
		$this->patients->removeElement($patient);
		$patient->run = null;
		$patient->shift = null;
		$patient->student = null;
	}
	
	/**
	 * Determines if the given shift is editable
	 * Here are the conditions that must be met
	 * 1). User must be an instructor in the same program as student with edit permissions
	 * 2). User must be a student and shift must be unlocked
	 *
	 * @param User $user
	 *
*@return boolean
	 */
	public function isEditable($user = null)
	{
		if (is_null($user)) {
			$user = User::getLoggedInUser();
		}
		
		$allowed = true;
		
		if ($user->getCurrentRoleName() == "student") {
			//Run must be unlocked
			$allowed = $allowed && !$this->locked;
		}
		
		//Parent shift must be editable
		$allowed = $allowed && $this->shift->isEditable();
		
		return $allowed;
	}
	
	public static function canEditData($runId, $user)
	{
		$run = EntityUtils::getEntity('Run', $runId);
		
		if ($user->getCurrentRoleName() == 'student') {
			if ($run->locked) {
				return false;
			}
			
			if ($run->shift->locked) {
				return false;
			}
			
			if ($user->id != $run->student->user->id) {
				return false;
			}
			
		} else {
			if ($user->getProgramId() != $run->student->user->getProgramId()) {
				return false;
			}
		}
		
		return true;
	}
	
	public function isValid()
    {
        $valid = true;
        $invalidPatients = array();

        foreach ($this->patients as $patient) {
            if (!$patient->isValid()) {
                $invalidPatients[] = $patient;
                $valid = $valid && false;
            }
        }

        $this->invalidPatients = $invalidPatients;
        return $valid;
    }

    public function getInvalidPatients()
    {
        return $this->invalidPatients;
    }
}
