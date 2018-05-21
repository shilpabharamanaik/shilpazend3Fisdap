<?php namespace User\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use User\EntityUtils;

/**
 * Entity class for Patients.
 *
 * @Entity(repositoryClass="Fisdap\Data\Patient\DoctrinePatientRepository")
 * @Table(name="fisdap2_patients")
 * @HasLifecycleCallbacks
 *
 * @todo Write some unit tests!
 * @todo Write setters/getters and other core functionality
 */
class Patient
{
    /**
     * @var array containing invalid fields from validation
     */
    public $invalidFields = array();

    /**
     * @var integer
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="ShiftLegacy", inversedBy="patients")
     * @JoinColumn(name="shift_id", referencedColumnName="Shift_id")
     */
    protected $shift;

    /**
     * @ManyToOne(targetEntity="Run", inversedBy="patients")
     * @codeCoverageIgnore
     * @deprecated
     */
    protected $run;

    /**
     * @ManyToOne(targetEntity="StudentLegacy", inversedBy="patients")
     * @JoinColumn(name="student_id", referencedColumnName="Student_id")
     */
    protected $student;

    /**
     * @OneToOne(targetEntity="PreceptorSignoff", mappedBy="patient", cascade={"persist","remove"})
     */
    protected $signoff;

    /**
     * @var Verification
     * @OneToOne(targetEntity="Verification", cascade={"persist","remove"})
     */
    protected $verification;

    /**
     * @Column(type="boolean")
     */
    protected $locked = false;

    /**
     * @var boolean
     * @Column(type="boolean", nullable=true)
     */
    protected $team_lead = false;

    /**
     * @var integer
     * @Column(type="integer", nullable=true)
     */
    protected $team_size;

    /**
     * @ManyToOne(targetEntity="PreceptorLegacy")
     * @JoinColumn(name="preceptor_id", referencedColumnName="Preceptor_id")
     */
    //protected $preceptor;

    /**
     * @ManyToOne(targetEntity="ResponseMode")
     */
    protected $response_mode;

    /**
     * @var integer
     * @Column(type="integer", nullable=true)
     */
    protected $age;

    /**
     * @var integer
     * @Column(type="integer", nullable=true)
     */
    protected $months;

    /**
     * @Column(type="integer", nullable=true)
     */
    protected $legacy_assessment_id;

    /**
     * @Column(type="integer", nullable=true)
     */
    protected $legacy_run_id;

    /**
     * @ManyToOne(targetEntity="Gender")
     */
    protected $gender;

    /**
     * @ManyToOne(targetEntity="Ethnicity")
     */
    protected $ethnicity;

    /**
     * @var integer
     * @ManyToOne(targetEntity="Impression")
     */
    protected $primary_impression;

    /**
     * @var integer
     * @ManyToOne(targetEntity="Impression")
     */
    protected $secondary_impression;

    /**
     * @ManyToMany(targetEntity="Complaint", inversedBy="patients")
     * @JoinTable(name="fisdap2_patients_complaints",
     *  joinColumns={@JoinColumn(name="patient_id", referencedColumnName="id")},
     *  inverseJoinColumns={@JoinColumn(name="complaint_id",referencedColumnName="id")})
     */
    protected $complaints;

    /**
     * @var integer|null
     * @ManyToOne(targetEntity="Witness")
     */
    protected $witness;

    /**
     * @var integer|null
     * @ManyToOne(targetEntity="PulseReturn")
     */
    protected $pulse_return;

    /**
     * @ManyToMany(targetEntity="Mechanism")
     * @JoinTable(name="fisdap2_patients_mechanisms",
     *  joinColumns={@JoinColumn(name="patient_id", referencedColumnName="id")},
     *  inverseJoinColumns={@JoinColumn(name="mechanism_id",referencedColumnName="id")})
     */
    protected $mechanisms;

    /**
     * @ManyToOne(targetEntity="Cause")
     */
    protected $cause;

    /**
     * @ManyToOne(targetEntity="Intent")
     */
    protected $intent;

    /**
     * @var boolean
     * @Column(type="boolean", nullable=true)
     */
    protected $interview;

    /**
     * @var boolean
     * @Column(type="boolean", nullable=true)
     */
    protected $exam;

    /**
     * @var boolean
     * @Column(type="boolean", nullable=true)
     */
    protected $airway_success = false;

    /**
     * @var AirwayManagement
     * @OneToOne(targetEntity="AirwayManagement", mappedBy="patient", cascade={"persist","remove"})
     */
    protected $airway_management;

    /**
     * @ManyToOne(targetEntity="PatientCriticality")
     */
    protected $patient_criticality;

    /**
     * @ManyToOne(targetEntity="PatientDisposition")
     */
    protected $patient_disposition;

    /**
     * @ManyToOne(targetEntity="ResponseMode")
     */
    protected $transport_mode;

    /**
     * @ManyToOne(targetEntity="MentalAlertness")
     */
    protected $msa_alertness;

    /**
     * @ManyToMany(targetEntity="MentalOrientation")
     * @JoinTable(name="fisdap2_patients_orientations",
     *  joinColumns={@JoinColumn(name="patient_id", referencedColumnName="id")},
     *  inverseJoinColumns={@JoinColumn(name="orientation_id",referencedColumnName="id")})
     */
    protected $msa_orientations;

    /**
     * @ManyToMany(targetEntity="MentalResponse")
     * @JoinTable(name="fisdap2_patients_responses",
     *  joinColumns={@JoinColumn(name="patient_id", referencedColumnName="id")},
     *  inverseJoinColumns={@JoinColumn(name="response_id",referencedColumnName="id")})
     */
    protected $msa_responses;

    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="Med", mappedBy="patient", cascade={"persist","remove"})
     */
    protected $meds;

    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="OtherIntervention", mappedBy="patient", cascade={"persist","remove"})
     */
    protected $other_interventions;

    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="CardiacIntervention", mappedBy="patient", cascade={"persist","remove"})
     */
    protected $cardiac_interventions;

    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="Airway", mappedBy="patient", cascade={"persist","remove"})
     */
    protected $airways;

    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="Iv", mappedBy="patient", cascade={"persist","remove"})
     */
    protected $ivs;

    /**
     * @OneToOne(targetEntity="Narrative", mappedBy="patient", cascade={"persist","remove"})
     */
    protected $narrative;

    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="Vital", mappedBy="patient", cascade={"persist","remove"})
     */
    protected $vitals;

    /**
     * @ManyToOne(targetEntity="Subject")
     * TODO: Determine if this is declared correctly
     */
    protected $subject;

    private $missingFields;

    public function init()
    {
        $this->meds = new ArrayCollection();
        $this->airways = new ArrayCollection();
        $this->ivs = new ArrayCollection();
        $this->cardiac_interventions = new ArrayCollection();
        $this->other_interventions = new ArrayCollection();
        $this->complaints = new ArrayCollection();
        $this->mechanisms = new ArrayCollection();
        $this->vitals = new ArrayCollection();
        $this->msa_orientations = new ArrayCollection();
        $this->msa_responses = new ArrayCollection();

        $this->subject = EntityUtils::getEntity('Subject', 1);
    }

    /**
     *    Currently this will set subject to 1-live-human if not set
     */
    public function get_subject()
    {
        if (is_null($this->subject)) {
            $this->subject = EntityUtils::getEntity('Subject', 1);
        }

        return $this->subject;
    }

    /**
     * This function takes the subject id (example: id => 1, type => Animal)
     * and assigns it to all the Skills as the subjectId.
     *
     * @param $value
     * @throws \Exception
     * @codeCoverageIgnore
     * @deprecated
     */
    public function set_subject($value)
    {
        $this->subject = self::id_or_entity_helper($value, 'Subject');

        // if this is not a new patient, then set the subject for all the associated skills, too
        if ($this->id) {
            $skills = EntityUtils::getRepository('Patient')->getSkillsByPatient($this->id);
            foreach ($skills as $skill) {
                $skill->subject = $this->subject->id;
            }
        }
    }

    /**
     * Set the verification for this patient
     * @param Fisdap\Entity\Verification
     */
    public function set_verification(Verification $ver = null)
    {
        $this->verification = $ver;
    }

    /**
     * Set the Dispatch Service
     * @param mixed $value the ID or the entity itself
     */
    // Don't think this is in use any more...
    /*
    public function set_dispatch_service($value)
    {
        $this->dispatch_service = self::id_or_entity_helper($value, 'DispatchService');
    }
    */

    /**
     * Set the Ethnicity
     * @param mixed $value the ID or the entity itself
     * @codeCoverageIgnore
     * @deprecated
     */
    public function set_ethnicity($value)
    {
        $this->ethnicity = self::id_or_entity_helper($value, 'Ethnicity');
    }

    /**
     * Set the primary impression
     * @param integer $value the ID of the impression
     * @codeCoverageIgnore
     * @deprecated
     */
    public function set_primary_impression($value)
    {
        $this->primary_impression = self::id_or_entity_helper($value, 'Impression');
    }

    /**
     * Set the secondary impression
     * @param mixed $value the ID or the entity itself
     * @codeCoverageIgnore
     * @deprecated
     */
    public function set_secondary_impression($value)
    {
        $this->secondary_impression = self::id_or_entity_helper($value, 'Impression');
    }

    /**
     * Set the gender
     * @param mixed $value the ID or the entity itself
     * @codeCoverageIgnore
     * @deprecated
     */
    public function set_gender($value)
    {
        $this->gender = self::id_or_entity_helper($value, 'Gender');
    }

    /**
     * Set the preceptor
     * @param mixed $value the ID or the entity itself
     * @codeCoverageIgnore
     * @deprecated
     */
    public function set_preceptor($value)
    {
        $this->preceptor = self::id_or_entity_helper($value, 'PreceptorLegacy');
    }

    /**
     * Set the response mode
     * @param mixed $value the ID or the entity itself
     * @codeCoverageIgnore
     * @deprecated
     */
    public function set_response_mode($value)
    {
        $this->response_mode = self::id_or_entity_helper($value, 'ResponseMode');
    }

    /**
     * Set the mental alertness
     * @param mixed $value the ID or the entity itself
     * @codeCoverageIgnore
     * @deprecated
     */
    public function set_msa_alertness($value)
    {
        $this->msa_alertness = self::id_or_entity_helper($value, 'MentalAlertness');
    }

    /**
     * Set the cause of injury
     * @param mixed $value the ID or the entity itself
     * @codeCoverageIgnore
     * @deprecated
     */
    public function set_cause($value)
    {
        $this->cause = self::id_or_entity_helper($value, 'Cause');
    }

    /**
     * Set the intent of injury
     * @param mixed $value the ID or the entity itself
     * @codeCoverageIgnore
     * @deprecated
     */
    public function set_intent($value)
    {
        $this->intent = self::id_or_entity_helper($value, 'Intent');
    }

    /**
     * Set the patient criticality
     * @param mixed $value the ID or the entity itself
     * @codeCoverageIgnore
     * @deprecated
     */
    public function set_patient_criticality($value)
    {
        $this->patient_criticality = self::id_or_entity_helper($value, 'PatientCriticality');
    }

    /**
     * Set the patient disposition
     * @param mixed $value the ID or the entity itself
     * @codeCoverageIgnore
     * @deprecated
     */
    public function set_patient_disposition($value)
    {
        $this->patient_disposition = self::id_or_entity_helper($value, 'PatientDisposition');
    }

    /**
     * Set the transport mode
     * @param mixed $value the ID or the entity itself
     * @codeCoverageIgnore
     * @deprecated
     */
    public function set_transport_mode($value)
    {
        $this->transport_mode = self::id_or_entity_helper($value, 'ResponseMode');
    }

    /**
     * Set the arrest witness
     * @param mixed $value the ID or the entity itself
     * @codeCoverageIgnore
     * @deprecated
     */
    public function set_witness($value)
    {
        $this->witness = self::id_or_entity_helper($value, 'Witness');
    }

    /**
     * Set the pulse return
     * @param mixed $value the ID or the entity itself
     * @codeCoverageIgnore
     * @deprecated
     */
    public function set_pulse_return($value)
    {
        $this->pulse_return = self::id_or_entity_helper($value, 'PulseReturn');
    }

    /**
     * @param $value
     * @codeCoverageIgnore
     * @deprecated
     */
    public function set_age($value)
    {
        if ($value == "") {
            $this->age = null;
        } else {
            $this->age = $value;
        }
    }

    /**
     * @param $value
     * @codeCoverageIgnore
     * @deprecated
     */
    public function set_months($value)
    {
        if ($value == "") {
            $this->months = null;
        } else {
            $this->months = $value;
        }
    }

    /**
     * Set the narrative
     * @param \Fisdap\Entity\Narrative
     * @codeCoverageIgnore
     * @deprecated
     */
    public function set_narrative($narrative)
    {
        $narrative->patient = $this;
        $narrative->run = $this->run->id;
        $narrative->shift = $this->shift->id;
        $narrative->student = $this->student->id;
        $this->narrative = $narrative;
    }

    /**
     * Set the signoff data
     * @param \Fisdap\Entity\PreceptorSignoff
     * @codeCoverageIgnore
     * @deprecated
     */
    public function set_signoff($signoff)
    {
        $signoff->patient = $this;
        $signoff->student = $this->student;
        $this->signoff = $signoff;
    }

    public function setId($patientId)
    {
        $this->id = $patientId;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getRun()
    {
        return $this->run;
    }

    public function setShift(ShiftLegacy $shift)
    {
        $this->shift = $shift;
    }

    public function getShift()
    {
        return $this->shift;
    }

    public function setStudent(StudentLegacy $student)
    {
        $this->student = $student;
    }

    public function getStudent()
    {
        return $this->student;
    }

    public function setTeamLead($teamLead)
    {
        $this->team_lead = is_bool($teamLead) ? $teamLead : false;
    }

    public function getTeamLead()
    {
        return $this->team_lead;
    }

    public function setTeamSize($teamSize)
    {
        $this->team_size = is_integer($teamSize) ? $teamSize : 0;
    }

    public function getTeamSize()
    {
        return $this->team_size;
    }

    public function setLocked($locked)
    {
        $this->locked = is_bool($locked) ? $locked : false;
        if ($this->run) {
            $this->run->locked = is_bool($locked) ? $locked : false;
        }
    }

    public function getLocked()
    {
        return $this->locked;
    }

    /**
     * @param Subject $subject|null
     */
    public function setSubject(Subject $subject = null)
    {
        $this->subject = $subject;

        // if this is not a new patient, then set the subject for all the associated skills, too
        if ($this->id) {
            $skills = EntityUtils::getRepository('Patient')->getSkillsByPatient($this->id);
            foreach ($skills as $skill) {
                $skill->subject = $this->subject->id;
            }
        }
    }


    /**
     * Currently this will set subject to 1-live-human if not set
     * @return Subject
     */
    public function getSubject()
    {
        if (is_null($this->subject)) {
            $this->subject = EntityUtils::getEntity('Subject', 1);
        }

        return $this->subject;
    }

    /**
     * Set the Ethnicity
     * @param Ethnicity $ethnicity|null
     */
    public function setEthnicity(Ethnicity $ethnicity = null)
    {
        $this->ethnicity = $ethnicity;
    }

    /**
     * @return Ethnicity
     */
    public function getEthnicity()
    {
        return $this->ethnicity;
    }

    /**
     * Set the verification for this patient
     * @param Verification $ver
     */
    public function setVerification(Verification $ver = null)
    {
        if ($ver !== null) {
            $ver->patient = $this;
            $ver->run = $this->run;
            $ver->student = $this->student;
        }

        $this->verification = $ver;

        if (is_null($ver) && !is_null($this->verification)) {
            //$this->verification->delete();
        }
    }

    /**
     * @return array
     */
    public function getVerificationArray()
    {
        if ($this->verification == null) {
            return [];
        }
        return $this->verification->toArray();
    }

    /**
     *
     */
    public function unsetVerification()
    {
        if (!is_null($this->verification)) {
            $this->verification->signature = null;
            $this->verification->setVerified(false);
            $this->verification->setVerifiedBy(null);
        }
    }

    /**
     * Set the verification for this patient
     *
     * @return Verification
     */
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

    /**
     * Set the primary impression
     * @param Impression $impression
     */
    public function setPrimaryImpression(Impression $impression)
    {
        $this->primary_impression = $impression;
    }


    /**
     * Set the secondary impression
     * @param Impression $impression|null
     */
    public function setSecondaryImpression(Impression $impression = null)
    {
        $this->secondary_impression = $impression;
    }

    /**
     * Set the gender
     * @param Gender $gender
     */
    public function setGender(Gender $gender)
    {
        $this->gender = $gender;
    }

    /**
     * @return Gender
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Set the preceptor
     * @param PreceptorLegacy $preceptorLegacy|null
     */
    public function setPreceptor(PreceptorLegacy $preceptorLegacy = null)
    {
        $this->preceptor = $preceptorLegacy;
    }

    /**
     * Set the response mode
     * @param ResponseMode $responseMode|null
     */
    public function setResponseMode(ResponseMode $responseMode = null)
    {
        $this->response_mode = $responseMode;
    }

    /**
     * Set the mental alertness
     * @param MentalAlertness $mentalAlertness|null
     */
    public function setMsaAlertness(MentalAlertness $mentalAlertness = null)
    {
        $this->msa_alertness = $mentalAlertness;
    }

    /**
     * Set the mental orientations for this patient
     *
     * @param array $value
     */
    public function setMsaOrientations($value)
    {
        if (is_null($value)) {
            $value = array();
        } elseif (!is_array($value)) {
            $value = array($value);
        }

        $this->msa_orientations->clear();

        foreach ($value as $id) {
            $orientation = self::id_or_entity_helper($id, 'MentalOrientation');
            $this->msa_orientations->add($orientation);
        }
    }
    
    /**
     * Set the cause of injury
     * @param Cause $cause|null
     */
    public function setCause(Cause $cause = null)
    {
        $this->cause = $cause;
    }

    /**
     * Set the intent of injury
     * @param Intent $intent|null
     */
    public function setIntent(Intent $intent = null)
    {
        $this->intent = $intent;
    }

    /**
     * @param boolean $interview
     */
    public function setInterview($interview)
    {
        $this->interview = $interview;
    }

    /**
     * @param boolean $exam
     */
    public function setExam($exam)
    {
        $this->exam = $exam;
    }

    /**
     * @param boolean $airwaySuccess
     */
    public function setAirwaySuccess($airwaySuccess)
    {
        $this->airway_success = $airwaySuccess;
    }

    /**
     * Set the patient criticality
     * @param PatientCriticality $patientCriticality|null
     */
    public function setPatientCriticality(PatientCriticality $patientCriticality = null)
    {
        $this->patient_criticality = $patientCriticality;
    }

    /**
     * Set the patient disposition
     * @param PatientDisposition $patientDisposition|null
     */
    public function setPatientDisposition(PatientDisposition $patientDisposition = null)
    {
        $this->patient_disposition = $patientDisposition;
    }

    /**
     * Set the transport mode
     * @param ResponseMode $responseMode|null
     */
    public function setTransportMode(ResponseMode $responseMode = null)
    {
        $this->transport_mode = $responseMode;
    }

    /**
     * Set the arrest witness
     * @param Witness $witness|null
     */
    public function setWitness(Witness $witness = null)
    {
        $this->witness = $witness;
    }

    /**
     * Set the pulse return
     * @param PulseReturn $pulseReturn|null
     */
    public function setPulseReturn(PulseReturn $pulseReturn = null)
    {
        $this->pulse_return = $pulseReturn;
    }

    public function setAge($age)
    {
        if ($age < 0) {
            $this->age = null;
        } else {
            $this->age = $age;
        }
    }

    public function getAge()
    {
        return $this->age;
    }

    public function setMonths($month)
    {
        if ($month < 0) {
            $this->months = null;
        } else {
            $this->months = $month;
        }
    }

    public function getMonths()
    {
        return $this->months;
    }

    /**
     * Set the narrative
     * @param Narrative $narrative
     */
    public function setNarrative(Narrative $narrative)
    {
        $narrative->patient = $this;
        $narrative->run = $this->run->id;
        $narrative->shift = $this->shift->id;
        $narrative->student = $this->student->id;
        $this->narrative = $narrative;
    }

    /**
     * Set the narrative
     *
     * @return Narrative|null
     */
    public function getNarrative()
    {
        return $this->narrative ? $this->narrative : new Narrative;
    }

    /**
     * @return ArrayCollection
     */
    public function getVitals()
    {
        return $this->vitals;
    }

    /**
     * @param array $vitals
     */
    public function setVitals($vitals)
    {
        $this->vitals = $vitals;
    }

    /**
     * @return ArrayCollection
     */
    public function getIvs()
    {
        return $this->ivs;
    }

    /**
     * @param array $ivs
     */
    public function setIvs($ivs)
    {
        $this->ivs = $ivs;
    }

    /**
     * @return ArrayCollection
     */
    public function getAirways()
    {
        return $this->airways;
    }

    /**
     * @param array $airways
     */
    public function setAirways($airways)
    {
        $this->airways = $airways;
    }

    /**
     * @return ArrayCollection
     */
    public function getCardiacInterventions()
    {
        return $this->cardiac_interventions;
    }

    /**
     * @param array $cardiac_interventions
     */
    public function setCardiacInterventions($cardiac_interventions)
    {
        $this->cardiac_interventions = $cardiac_interventions;
    }

    /**
     * @return mixed
     */
    public function getComplaints()
    {
        return $this->complaints;
    }

    /**
     * @param mixed $complaints
     */
    public function setComplaints($complaints)
    {
        $this->complaints = $complaints;
    }

    /**
     * @return AirwayManagement
     */
    public function getAirwayManagement()
    {
        return $this->airway_management;
    }

    /**
     * @param AirwayManagement $airway_management
     */
    public function setAirwayManagement($airway_management)
    {
        $this->airway_management = $airway_management;
    }

    /**
     * @return ArrayCollection
     */
    public function getMeds()
    {
        return $this->meds;
    }

    /**
     * @param array $meds
     */
    public function setMeds($meds)
    {
        $this->meds = $meds;
    }

    /**
     * @return ArrayCollection
     */
    public function getOtherInterventions()
    {
        return $this->other_interventions;
    }

    /**
     * @param array $other_interventions
     */
    public function setOtherInterventions($other_interventions)
    {
        $this->other_interventions = $other_interventions;
    }

    /**
     * Set the signoff data
     * @param PreceptorSignoff $signoff
     */
    public function setSignoff(PreceptorSignoff $signoff)
    {
        $signoff->patient = $this;
        $signoff->student = $this->student;
        $this->signoff = $signoff;
    }

    /**
     * @return array
     */
    public function getSignoffArray()
    {
        if ($this->signoff == null) {
            return [];
        }
        return $this->signoff->toArray();
    }

    /**
     * Add association between Med and Patient
     *
     * @param \Fisdap\Entity\Med $med
     */
    public function addMed(Med $med)
    {
        $this->meds->add($med);
        $med->patient = $this;
        $med->run = $this->run->id;
        $med->shift = $this->shift->id;
        $med->student = $this->student->id;
        $med->subject = $this->subject->id;
    }

    /**
     * Add association between OtherIntervention and Patient
     *
     * @param \Fisdap\Entity\OtherIntervention $other
     */
    public function addOtherIntervention(OtherIntervention $other)
    {
        $this->other_interventions->add($other);
        $other->patient = $this;
        $other->run = $this->run->id;
        $other->shift = $this->shift->id;
        $other->student = $this->student->id;
        $other->subject = $this->subject->id;
    }

    /**
     * Add association between CardiacIntervention and Patient
     *
     * @param \Fisdap\Entity\CardiacIntervention $cardiac
     */
    public function addCardiacIntervention(CardiacIntervention $cardiac)
    {
        $this->cardiac_interventions->add($cardiac);
        $cardiac->patient = $this;
        $cardiac->run = $this->run->id;
        $cardiac->shift = $this->shift->id;
        $cardiac->student = $this->student->id;
        $cardiac->subject = $this->subject->id;
    }

    /**
     * Add association between Airway and Patient
     *
     * @param \Fisdap\Entity\Airway $air
     */
    public function addAirway(Airway $air)
    {
        $this->airways->add($air);
        $air->patient = $this;
        $air->run = $this->run->id;
        $air->shift = $this->shift->id;
        $air->student = $this->student->id;
        $air->subject = $this->subject->id;
    }

    /**
     * Add association between Iv and Patient
     *
     * @param \Fisdap\Entity\Iv $iv
     */
    public function addIv(Iv $iv)
    {
        $this->ivs->add($iv);
        $iv->patient = $this;
        $iv->run = $this->run->id;
        $iv->shift = $this->shift->id;
        $iv->student = $this->student->id;
        $iv->subject = $this->subject->id;
    }

    /**
     * Add association between Vital and Patient
     *
     * @param \Fisdap\Entity\Vital $vital
     */
    public function addVital(Vital $vital)
    {
        $this->vitals->add($vital);
        $vital->patient = $this;
        $vital->run = $this->run->id;
        $vital->shift = $this->shift->id;
        $vital->student = $this->student->id;
        $vital->subject = $this->subject->id;
    }

    /**
     * Set the complaints for this patient
     *
     * @param mixed $value
     */
    public function setComplaintIds($value)
    {
        if (is_null($value)) {
            $value = array();
        } elseif (!is_array($value)) {
            $value = array($value);
        }

        $this->complaints->clear();

        foreach ($value as $id) {
            $complaint = self::id_or_entity_helper($id, 'Complaint');
            $this->complaints->add($complaint);
        }
    }

    /**
     * Get an array of Complaint IDs
     *
     * @return array
     */
    public function getComplaintIds()
    {
        $complaints = array();

        foreach ($this->complaints as $complaint) {
            $complaints[] = $complaint->id;
        }

        return $complaints;
    }

    /**
     * Returns a list of the string names of all of the complaints for this
     * patient.  Used as shorthand probably mostly for use in views.
     *
     * @param Boolean $sort Determines whether or not to sort the array by name
     * before returning.  Defaults to true.
     *
     * @param String $delimiter String used to separate the elements in the
     * returned string.  Defaults to ', '.
     *
     * @return String containing a comma separated list of the current patient
     * complaints.
     */
    public function getComplaintNames($sortComplaints = true, $delimiter = ', ')
    {
        $complaints = array();

        foreach ($this->complaints as $complaint) {
            $complaints[] = $complaint->name;
        }

        if ($sortComplaints) {
            asort($complaints);
        }

        return implode($delimiter, $complaints);
    }

    /**
     * Set the mechanisms for this patient
     *
     * @param mixed $value
     */
    public function set_mechanisms($value)
    {
        if (is_null($value)) {
            $value = array();
        } elseif (!is_array($value)) {
            $value = array($value);
        }

        $this->mechanisms->clear();

        foreach ($value as $id) {
            $mechanism = self::id_or_entity_helper($id, 'Mechanism');
            $this->mechanisms->add($mechanism);
        }
    }

    /**
     * Get an array of Mechanism IDs
     *
     * @return array
     */
    public function get_mechanisms()
    {
        $mechanisms = array();

        foreach ($this->mechanisms as $mechanism) {
            $mechanisms[] = $mechanism->id;
        }

        return $mechanisms;
    }
    /**
     * Get an array of Mechanism IDs
     *
     * @return array
     */
    public function getMechanismIds()
    {
        $mechanisms = array();

        foreach ($this->mechanisms as $mechanism) {
            $mechanisms[] = $mechanism->id;
        }

        return $mechanisms;
    }

    /**
     * Get a string containing the mechanism names
     *
     * @param Boolean $sort Determines whether or not to sort the array by name
     * before returning.  Defaults to true.
     *
     * @param String $delimiter String used to separate the elements in the
     * returned string.  Defaults to ', '.
     *
     * @return String containing a comma separated list of the current patient
     * complaints.
     */
    public function getMechanismNames($sort = true, $delimiter = ', ')
    {
        $mechanisms = array();

        foreach ($this->mechanisms as $mechanism) {
            $mechanisms[] = $mechanism->name;
        }

        if ($sort) {
            asort($mechanisms);
        }

        return implode($delimiter, $mechanisms);
    }

    /**
     * Set the mental orientations for this patient
     *
     * @param mixed $value
     */
    public function set_msa_orientations($value)
    {
        if (is_null($value)) {
            $value = array();
        } elseif (!is_array($value)) {
            $value = array($value);
        }

        $this->msa_orientations->clear();

        foreach ($value as $id) {
            $orientation = self::id_or_entity_helper($id, 'MentalOrientation');
            $this->msa_orientations->add($orientation);
        }
    }

    /**
     * Get an array of mental orientation IDs
     *
     * @return array
     */
    public function get_msa_orientations()
    {
        $orientations = array();

        foreach ($this->msa_orientations as $orientation) {
            $orientations[] = $orientation->id;
        }

        return $orientations;
    }

    /**
     * Set the mental responses for this patient
     *
     * @param mixed $value
     */
    public function set_msa_responses($value)
    {
        if (is_null($value)) {
            $value = array();
        } elseif (!is_array($value)) {
            $value = array($value);
        }

        $this->msa_responses->clear();

        foreach ($value as $id) {
            $response = self::id_or_entity_helper($id, 'MentalResponse');
            $this->msa_responses->add($response);
        }
    }

    /**
     * Get an array of mental response IDs
     *
     * @return array
     */
    public function get_msa_responses()
    {
        $responses = array();

        foreach ($this->msa_responses as $response) {
            $responses[] = $response->id;
        }

        return $responses;
    }

    public function getSummaryLine()
    {
        $line = '';
        $ageSummary = '';

        if ($this->age !== null) {
            $ageSummary .= $this->age . " y";

            //$line .= $this->age . " yo ";
        }

        if ($this->months !== null) {
            $ageSummary .= " " . $this->months . " m";
        }

        if ($ageSummary != '') {
            $line .= $ageSummary . "o ";
        }

        if ($this->ethnicity->name != '') {
            $line .= $this->ethnicity->name . " ";
        }

        if ($this->gender->name != '') {
            $line .= $this->gender->name . " ";
        }

        if (trim($line) == '') {
            $line = "No patient information";
        }

        return trim($line);
    }

    /**
     * Given a key array containing pertinent patient info, return a formatted patient summary
     * @param $info
     * @return string
     */
    public static function formatSummaryLine($info)
    {
        //var_export($info);
        $line = '';
        $ageSummary = '';

        if ($info['age'] !== null) {
            $ageSummary .= $info['age'] . " y";
        }

        if ($info['months'] !== null) {
            $ageSummary .= " " . $info['months'] . " m";
        }

        if ($ageSummary != '') {
            $line .= $ageSummary . "o ";
        }

        if ($info['ethnicity'] != '') {
            $line .= $info['ethnicity'] . " ";
        }

        if ($info['gender'] != '') {
            $line .= $info['gender'] . " ";
        }

        if (trim($line) == '') {
            $line = "No patient information";
        }

        return trim($line);
    }

    public function generateSummarySeed()
    {
        $seed = "";

        //Patient Info
        $seed .= "Patient Info:\n";
        $seed .= $this->getSummaryLine();
        $seed .= "\nPrimary Impression: {$this->primary_impression->name}";

        $seed .= "\n\n";

        $skills = EntityUtils::getRepository('Patient')->getSkillsByPatient($this->id);

        foreach ($skills as $skill) {
            $seed .= $skill->getProcedureText(false);
        }

        return $seed;
    }

    public function isValid()
    {
        $valid = true;
        $invalidFields = array();

        if (!$this->primary_impression->id) {
            $invalidFields[] = "Primary Impression";
            $valid = false;
        }

        if ($this->age === null && $this->months === null) {
            $invalidFields[] = "Age";
            $valid = false;
        }

        if (!$this->gender->id) {
            $invalidFields[] = "Gender";
            $valid = false;
        }

        // only check for a preceptor if this is a field contact
        if ($this->shift->type == "field") {
            if (!$this->preceptor->id) {
                $invalidFields[] = "Preceptor";
                $valid = false;
            }
        }

        $this->invalidFields = $invalidFields;
        return $valid;
    }

    /**
     * @deprecated Don't use this! It's no longer accurate. Use isALSPatientSQL() instead!
     */
    public function isALSPatient()
    {
        // med except O2
        foreach ($this->meds as $med) {
            if ($med->medication->id != 25) {
                return true;
            }
        }

        // OR anyEKG and (IV, IV w/ blood draw or IO)
        if (count($this->cardiac_interventions)) {
            foreach ($this->ivs as $iv) {
                $ivProcedureId = $iv->procedure->id;
                // iv or io or iv w/ blood draw
                if ($ivProcedureId == 1 || $ivProcedureId == 2 || $ivProcedureId == 8) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Determines if a patient is considered an ALS call
     *
     * @param integer $id the Patient ID
     * @return boolean
     */
    public static function isALSPatientSQL($id, $alsType = "fisdap")
    {
        $db = \Zend_Registry::get('db');

        if ($alsType == 'fisdap') {
            /*
             * Using Fisdap's definition, a call will be considered an ALS call if either:
             *    A medication other than oxygen is administered (by anyone on the team)
             *  or
             *    An ECG monitor and an IV (attempt) are performed together (by anyone on the team).
             */
            $meds = $db->query("SELECT * FROM fisdap2_meds WHERE patient_id = " . $id)->fetchAll();

            // med except O2
            foreach ($meds as $med) {
                if ($med['medication_id'] != 25) {
                    return true;
                }
            }

            $cardiacs = $db->query("SELECT * FROM fisdap2_cardiac_interventions WHERE patient_id = " . $id . " AND soft_deleted = 0")->fetchAll();
            $ivs = $db->query("SELECT * FROM fisdap2_ivs WHERE patient_id = " . $id . " AND soft_deleted = 0")->fetchAll();

            // OR any EKG and any IV
            if (count($cardiacs)) {
                foreach ($ivs as $iv) {
                    $ivProcedureId = $iv['procedure_id'];
                    if ($ivProcedureId) {
                        return true;
                    }
                }
            }

            return false;
        } elseif ($alsType == 'als_skill') {
            /*
             * Using the ALS Skill definition, a call will be considered an ALS call if any ALS skill is
             * performed (by anyone on the team).
             */

            $patients = $db->query("SELECT * FROM fisdap2_patients WHERE id = " . $id . " AND (primary_impression_id = 4 OR secondary_impression_id = 4)")->fetchAll();
            if (count($patients)) {
                return true;
            }

            $airways = $db->query("SELECT * FROM fisdap2_airways WHERE patient_id = " . $id . " AND id IS NOT NULL AND procedure_id IN (1,3,5,6,9,10,11,14,15,17,18,19,20,21,22,23,25) AND id > 0 AND soft_deleted = 0")->fetchAll();
            if (count($airways)) {
                return true;
            }

            $cardiacs = $db->query("SELECT * FROM fisdap2_cardiac_interventions WHERE patient_id = " . $id . " AND id IS NOT NULL AND id > 0 AND soft_deleted = 0")->fetchAll();
            if (count($cardiacs)) {
                return true;
            }

            $ivs = $db->query("SELECT * FROM fisdap2_ivs WHERE patient_id = " . $id . " AND soft_deleted = 0")->fetchAll();
            if (count($ivs)) {
                return true;
            }

            $meds = $db->query("SELECT * FROM fisdap2_meds WHERE patient_id = " . $id . " AND medication_id != 25 AND medication_id IS NOT NULL AND medication_id > 0 AND soft_deleted = 0")->fetchAll();
            if (count($meds)) {
                return true;
            }

            $others = $db->query("SELECT * FROM fisdap2_other_interventions WHERE patient_id = " . $id . " AND procedure_id NOT IN (27,30,31,32,33,35,36,37,38,39,40,41,42,43,44,45,46) AND id IS NOT NULL AND id > 0 AND soft_deleted = 0")->fetchAll();
            if (count($others)) {
                return true;
            }

            return false;
        } elseif ($alsType == "california") {
            /*
             * Using the California definition, a call will be considered an ALS call if any ALS skill other
             * than 12-lead EKG and Blood Glucose is performed (by the student)
             */

            $airways = $db->query("SELECT * FROM fisdap2_airways WHERE patient_id = " . $id . " AND id IS NOT NULL AND procedure_id IN (1,3,5,6,9,10,11,14,15,17,18,19,20,23,25) AND id > 0 AND soft_deleted = 0 AND performed_by = 1")->fetchAll();
            if (count($airways)) {
                return true;
            }

            $cardiacs = $db->query("SELECT * FROM fisdap2_cardiac_interventions WHERE patient_id = " . $id . " AND id IS NOT NULL AND id > 0 AND soft_deleted = 0 AND performed_by = 1 AND id != 1")->fetchAll();
            if (count($cardiacs)) {
                return true;
            }

            $ivs = $db->query("SELECT * FROM fisdap2_ivs WHERE patient_id = " . $id . " AND id > 0 AND soft_deleted = 0 AND performed_by = 1")->fetchAll();
            if (count($ivs)) {
                return true;
            }

            $meds = $db->query("SELECT * FROM fisdap2_meds WHERE patient_id = " . $id . " AND medication_id != 25 AND medication_id IS NOT NULL AND medication_id > 0 AND soft_deleted = 0 AND performed_by = 1")->fetchAll();
            if (count($meds)) {
                return true;
            }

            $others = $db->query("SELECT * FROM fisdap2_other_interventions WHERE patient_id = " . $id . " AND procedure_id IN (3,25,47) AND id IS NOT NULL AND id > 0 AND soft_deleted = 0 AND performed_by = 1")->fetchAll();
            if (count($others)) {
                return true;
            }

            return false;
        } else {
            return false;
        }
    }

    /**
     * Does this patient count as unconscious for goal purposes
     *
     * @return boolean is the patient unconscious or not
     */
    public function isUnconscious()
    {
        if ($this->msa_alertness->id == 4) {
            return true;
        }

        return false;
    }

    public static function isUnconsciousSQL($id)
    {
        $db = \Zend_Registry::get('db');
        $patient = $db->query("SELECT * FROM fisdap2_patients WHERE id = " . $id)->fetch();

        if ($patient['msa_alertness_id'] == 4) {
            return true;
        }

        return false;
    }


    /**
     * Todo - Remove. This does not appear to be used by anything ~bgetsug
     */
    public function isALSPatientTest()
    {
        // med except O2
        foreach ($this->meds as $med) {
            if ($med->medication->id != 25) {
                $medCount++;
            }
        }

        // OR anyEKG and (IV, IV w/ blood draw or IO)
        $ekgs = count($this->cardiac_interventions);

        foreach ($this->ivs as $iv) {
            $ivProcedureId = $iv->procedure->id;
            if ($ivProcedureId == 1 || $ivProcedureId == 8) {
                $ivs++;
            }
            if ($ivProcedureId == 2) {
                $io++;
            }
        }

        return array(
            array('medCount', (int)$medCount),
            array('ivs', (int)$ivs),
            array('ios', (int)$ios),
        );
    }

    public function getInvalidFields()
    {
        return $this->invalidFields;
    }

    public static function getComplaintsSQL(array $patientIds)
    {
        $query = "SELECT c.*, p.shift_id FROM fisdap2_patients_complaints c
                  INNER JOIN fisdap2_patients p ON c.patient_id = p.id
                  WHERE patient_id IN (" . implode(', ', $patientIds) . ")";
        return \Zend_Registry::get('db')->query($query)->fetchAll();
    }

    public static function getExamInterviewTeamLeadArray($id)
    {
        if (is_null($id)) {
            return array(
                'Type' => null,
                'team_lead' => null,
                'exam' => null,
                'interview' => null
            );
        }

        $query = "SELECT team_lead, exam, interview, Type FROM fisdap2_patients p, ShiftData sd WHERE p.shift_id = sd.Shift_id AND p.id = " . $id;
        return \Zend_Registry::get('db')->query($query)->fetch();
    }

    /**
     * Get the narrative in an array
     * TODO - Get rid of this.
     * @return array containing one narrative or empty
     */
    public function getNarrativeArray()
    {
        $narratives = array();
        if ($this->narrative) {
            $narratives[] = $this->narrative;
        }

        return $narratives;
    }

    /**
     * Get all hook ids related to the patient
     */
    public function getHookIds()
    {
        //Clinical
        $clinical_array = array(19, 40, 41, 42, 83, 84, 85, 86, 87, 88, 89, 90);

        //Lab
        $lab_array = array(60, 61, 62, 63, 91, 92, 93, 94, 95, 96, 97, 98);

        //Field
        $field_array = array(1, 2, 23, 24);

        switch ($this->shift->type) {
            case 'field':
                return $field_array;
            case 'clinical':
                return $clinical_array;
            case 'lab':
                return $lab_array;
            default:
                return array();
        }
    }

    private function collectionToObjectArray($collection)
    {
        $rtv = array();

        foreach ($collection as $intervention) {
            array_push($rtv, $intervention->toArray());
        }

        return $rtv;
    }

    private function collectionToArray($collection)
    {
        if ($collection !== null) {
            return $collection->toArray();
        }

        return null;
    }

    public function canBeLocked($shiftType = 'field')
    {
        $rtv = true;
        $this->missingFields = array();

        if ($this->student == null) {
            $rtv = false;
            array_push($this->missingFields, 'studentId');
        }

        if ($shiftType === 'field' && $this->preceptor == null) {
            $rtv = false;
            array_push($this->missingFields, 'preceptorId');
        }

        if ($this->age < 0 && $this->months < 0) {
            $rtv = false;
            array_push($this->missingFields, 'age/months');
        }

        if ($this->gender == null) {
            $rtv = false;
            array_push($this->missingFields, 'genderId');
        }

        if ($this->primary_impression == null) {
            $rtv = false;
            array_push($this->missingFields, 'primaryImpressionId');
        }

        return $rtv;
    }

    public function getMissingFields()
    {
        return $this->missingFields;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $array = [
            'uuid' => $this->getUUID(),
            'id' => $this->getId(),
            'student' => $this->student,
            'signoff' => $this->signoff,
            'run' => $this->run,
            'verification' => $this->getVerification(),
            'locked' => $this->getLocked(),
            'teamLead' => $this->getTeamLead(),
            'teamSize' => $this->team_size,
            'preceptor' => $this->preceptor,
            'responseMode' => $this->response_mode,
            'age' => $this->age,
            'months' => $this->months,
            'legacyAssessmentId' => $this->legacy_assessment_id,
            'legacyRunId' => $this->legacy_run_id,
            'gender' => $this->gender,
            'ethnicity' => $this->ethnicity,
            'primaryImpression' => $this->primary_impression,
            'secondaryImpression' => $this->secondary_impression,
            'complaints' => $this->complaints,
            'witness' => $this->witness,
            'pulseReturn' => $this->pulse_return,
            'mechanisms' => $this->mechanisms,
            'cause' => $this->cause,
            'intent' => $this->intent,
            'interview' => $this->interview,
            'exam' => $this->exam,
            'airwaySuccess' => $this->airway_success,
            'airwayManagement' => $this->collectionToArray($this->getAirwayManagement()),
            'patientCriticality' => $this->patient_criticality,
            'patientDisposition' => $this->patient_disposition,
            'transportMode' => $this->transport_mode,
            'mentalAlertness' => $this->msa_alertness,
            'mentalOrientations' => $this->msa_orientations,
            'narrative' => $this->getNarrative()->getSectionsArray(),
            'subject' => $this->subject,
            'shift' => $this->shift,
            'patientId' => $this->id,
            'updated' => $this->getUpdated(),
            'created' => $this->getCreated(),
            'medications' => $this->collectionToObjectArray($this->meds),
            'otherInterventions' => $this->collectionToObjectArray($this->other_interventions),
            'cardiacInterventions' => $this->collectionToObjectArray($this->cardiac_interventions),
            'airways' => $this->collectionToObjectArray($this->airways),
            'ivs' => $this->collectionToObjectArray($this->ivs),
            'vitals' => $this->collectionToObjectArray($this->vitals),
        ];

        return $array;
    }
}
