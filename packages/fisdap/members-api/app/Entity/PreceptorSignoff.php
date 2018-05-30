<?php namespace Fisdap\Entity;

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
use Fisdap\EntityUtils;

/**
 * Entity class for Preceptor Signoffs
 *
 * @Entity
 * @Table(name="fisdap2_preceptor_signoffs")
 * @HasLifecycleCallbacks
 */
class PreceptorSignoff extends Timestampable
{
    /**
     * @var integer
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;
    
    /**
     * @ManyToOne(targetEntity="StudentLegacy")
     * @JoinColumn(name="student_id", referencedColumnName="Student_id")
     */
    protected $student;
    
    /**
     * @OneToOne(targetEntity="Run", inversedBy="signoff")
     */
    protected $run;
    
    /**
     * @OneToOne(targetEntity="ShiftLegacy", inversedBy="signoff")
     * @JoinColumn(name="shift_id", referencedColumnName="Shift_id")
     */
    protected $shift;
    
    /**
     * @OneToOne(targetEntity="Patient", inversedBy="signoff")
     */
    protected $patient;
    
    /**
     * @Column(type="text")
     */
    protected $summary;
    
    /**
     * @Column(type="text")
     */
    protected $plan;
    
    /**
     * @OneToMany(targetEntity="PreceptorRating", mappedBy="signoff", cascade={"persist","remove"})
     */
    protected $ratings;

    /**
     * @var Verification
     */
    private $verification;
    
    public function setVerification(Verification $verification = null)
    {
        $this->verification = $verification;
    }
    
    public function getVerification()
    {
        // 01/24/07:
        //
        // This is a stupid hack and a temporary fix. When a patient is unverified,
        // the verification record is deleted, but the verification_id field in the patient record
        // is not deleted. This hack takes the verification_id, tries to find a verification record,
        // and returns null if non is found. This should be handled by the foreign key constraint.
        //
        // - Nick
        //

        if ($this->verification) {
            $verification = EntityUtils::getRepository('Verification')->getById([$this->verification->getId()]);
            if ($verification) {
                return $this->verification;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }
    
    public function init()
    {
        $this->ratings = new ArrayCollection();
    }

    /**
     * @param StudentLegacy $student
     */
    public function setStudent(StudentLegacy $student)
    {
        $this->student = $student;
    }

    /**
     * @param Run $run
     */
    public function setRun(Run $run)
    {
        $this->run = $run;
    }

    /**
     * @param ShiftLegacy $shift
     */
    public function setShift(ShiftLegacy $shift)
    {
        $this->shift = $shift;
    }

    /**
     * @return ShiftLegacy|null
     */
    public function getShift()
    {
        return $this->shift;
    }

    /**
     * @param Patient $patient
     */
    public function setPatient(Patient $patient = null)
    {
        $this->patient = $patient;
    }

    /**
     * @return Patient|null
     */
    public function getPatient()
    {
        return $this->patient;
    }

    /**
     *
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;
    }
    
    public function getSummary()
    {
        return $this->summary;
    }
    
    /**
     * @param $plan
     */
    public function setPlan($plan)
    {
        $this->plan = $plan;
    }

    /**
     * @param PreceptorRating $rating
     */
    public function addRating(PreceptorRating $rating)
    {
        $this->ratings->add($rating);
        $rating->signoff = $this;
    }

    /**
     * @return array
     */
    public function getRatings()
    {
        return $this->ratings->toArray();
    }

    /**
     * @param $value
     * @throws \Exception
     * @deprecated
     */
    public function set_student($value)
    {
        $this->student = self::id_or_entity_helper($value, 'StudentLegacy');
    }

    /**
     * @param $value
     * @throws \Exception
     * @deprecated
     */
    public function set_run($value)
    {
        $this->run = self::id_or_entity_helper($value, 'Run');
    }

    /**
     * @param $value
     * @throws \Exception
     * @deprecated
     */
    public function set_patient($value)
    {
        $this->patient = self::id_or_entity_helper($value, 'Patient');
    }
    
    /**
     * @param $plan
     * @deprecated
     */
    public function set_plan($plan)
    {
        $this->plan = $plan;
    }
    
    /**
     * Clear the current ratings and set new ones
     *
     * @param array $values the returned values from the validated form
     * @return \Fisdap\Entity\PreceptorSignoff
     */
    public function set_ratings($values)
    {
        foreach ($this->ratings as $rating) {
            $this->ratings->removeElement($rating);
            $rating->delete(false);
        }
        
        foreach (PreceptorRatingRaterType::getFormOptions() as $raterId => $raterName) {
            foreach (PreceptorRatingType::getFormOptions() as $typeId => $typeName) {
                $value = $values[$raterName . "_" . $typeId];
                
                if (isset($value)) {
                    $rating = new PreceptorRating();
                    $rating->value = $value;
                    $rating->type = $typeId;
                    $rating->rater_type = $raterId;
                    $rating->signoff = $this;
                    $this->ratings->add($rating);
                }
            }
        }
        
        return $this;
    }

    public function toArray()
    {
        $rtv = [
            'uuid'          => $this->getUUID(),
            'id'            => $this->id,
            'summary'       => $this->getSummary(),
            'plan'          => $this->plan,
            'verification'  => $this->getVerification(),
            'ratings'       => $this->getRatings()
        ];
        
        return $rtv;
    }
}
