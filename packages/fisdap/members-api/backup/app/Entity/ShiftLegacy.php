<?php namespace Fisdap\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\OrderBy;
use Doctrine\ORM\Mapping\PostLoad;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;
use Doctrine\ORM\Mapping\Table;
use Fisdap\Attachments\Associations\Entities\EntityAttachmentsSupport;
use Fisdap\Attachments\Associations\Entities\HasAttachments;
use Fisdap\EntityUtils;

/**
 * Entity class for Legacy Shifts.
 *
 * @Entity(repositoryClass="Fisdap\Data\Shift\DoctrineShiftLegacyRepository")
 * @Table(name="ShiftData")
 * @HasLifecycleCallbacks
 *
 * @todo Write some unit tests!
 * @todo Write setters/getters and other core functionality
 */
class ShiftLegacy extends Timestampable implements HasAttachments
{
    use EntityAttachmentsSupport;


    /**
     * @Id
     * @Column(name="Shift_id", type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @var StudentLegacy
     * @ManyToOne(targetEntity="StudentLegacy", inversedBy="shifts")
     * @JoinColumn(name="Student_id", referencedColumnName="Student_id")
     */
    protected $student;

    /**
     * @var SiteLegacy
     * @ManyToOne(targetEntity="SiteLegacy")
     * @JoinColumn(name="AmbServ_id", referencedColumnName="AmbServ_id")
     */
    protected $site;

    /**
     * @var BaseLegacy
     * @ManyToOne(targetEntity="BaseLegacy")
     * @JoinColumn(name="StartBase_id", referencedColumnName="Base_id")
     */
    protected $base;

    /**
     * @var \DateTime
     * @Column(name="StartDate", type="date")
     */
    protected $start_date;

    /**
     * This is the old column/field, which is now deprecated. Use $start_datetime instead.
     * @codeCoverageIgnore
     * @deprecated
     * @Column(name="StartTime", type="string")
     */
    protected $start_time;

    /**
     * @Column(name="EndTime", type="string")
     */
    protected $end_time;

    /**
     * @Column(name="Hours"), type="float", precision=5, scale=2)
     */
    protected $hours = 0.00;

    /**
     * @var \DateTime
     * @Column(name="EntryTime", type="datetime", nullable=true)
     */
    protected $entry_time;

    /**
     * @Column(name="Completed", type="boolean")
     */
    protected $locked = 0;

    /**
     * @var PreceptorSignoff
     * @OneToOne(targetEntity="PreceptorSignoff", mappedBy="shift", cascade={"persist","remove"})
     */
    protected $signoff;

    /**
     * @var Verification
     * @OneToOne(targetEntity="Verification", mappedBy="shift", cascade={"persist","remove"})
     */
    protected $verification;

    /**
     * @var string
     * @Column(name="Type", type="string")
     */
    protected $type = "field";

    /**
     * @var int
     * @Column(name="Event_id", type="integer")
     */
    protected $event_id = -1;

    /**
     * @Column(name="Trade", type="boolean")
     */
    protected $trade = 0;

    /**
     * @Column(name="TradeStatus", type="boolean")
     */
    protected $trade_status = 0;

    /**
     * @Column(name="Audited", type="boolean")
     */
    protected $audited = 0;

    /**
     * @codeCoverageIgnore
     * @deprecated
     * @Column(name="Tardy", type="boolean")
     */
    protected $tardy = 0;

    /**
     * @var \DateTime
     * @Column(name="FirstCompleted", type="datetime", nullable=false)
     */
    protected $first_locked;

    /**
     * @codeCoverageIgnore
     * @deprecated
     * @Column(name="CompletedFrom", type="string")
     */
    protected $completed_from = "web";

    /**
     * @Column(name="PDAShift_id", type="integer")
     */
    protected $pda_shift_id = -1;

    //New Fields added--------------------------------------------------------//

    /**
     * @Column(type="boolean")
     */
    protected $late = 0;

    /**
     * @var ShiftAttendence
     * @ManyToOne(targetEntity="ShiftAttendence")
     */
    protected $attendence;

    /**
     * @var string
     * @Column(type="text", nullable=true)
     */
    protected $attendence_comments;

    /**
     * @var bool
     * @Column(type="boolean")
     */
    protected $soft_deleted = false;

    /**
     * @var \DateTime
     * @Column(type="datetime")
     */
    protected $start_datetime;

    /**
     * @var \DateTime
     * @Column(type="datetime")
     */
    protected $end_datetime;

    /**
     * @var UserContext
     * @ManyToOne(targetEntity="UserContext")
     * @JoinColumn(name="creator_id", referencedColumnName="id")
     */
    protected $creator;

    //------------------------------------------------------------------------//

    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="Run",mappedBy="shift", cascade={"persist","remove"})
     */
    protected $runs;

    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="Patient", mappedBy="shift", cascade={"persist","remove"})
     */
    protected $patients;

    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="Med", mappedBy="shift", cascade={"persist","remove"})
     */
    protected $meds;

    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="Airway", mappedBy="shift", cascade={"persist","remove"})
     */
    protected $airways;

    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="Iv", mappedBy="shift", cascade={"persist","remove"})
     */
    protected $ivs;

    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="CardiacIntervention", mappedBy="shift", cascade={"persist","remove"})
     */
    protected $cardiac_interventions;

    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="OtherIntervention", mappedBy="shift", cascade={"persist","remove"})
     */
    protected $other_interventions;

    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="Narrative", mappedBy="shift", cascade={"persist","remove"})
     */
    protected $narratives;

    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="Vital", mappedBy="shift", cascade={"persist","remove"})
     */
    protected $vitals;

    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="LabSkill", mappedBy="shift", cascade={"persist","remove"})
     */
    protected $lab_skills;

    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="ShiftHistory", mappedBy="shift", cascade={"persist","remove"})
     */
    protected $histories;

    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="PracticeItem", mappedBy="shift", cascade={"persist", "remove"})
     * @OrderBy({"time" = "ASC"})
     */
    protected $practice_items;

    /**
     * @var SlotAssignment
     * @OneToOne(targetEntity="SlotAssignment", inversedBy="shift", cascade={"persist", "remove"})
     */
    protected $slot_assignment;

    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="EvalSessionLegacy", mappedBy="shift", cascade={"persist","remove"})
     */
    protected $eval_sessions;

    /**
     * @var array containing any patients that failed validation
     */
    public $invalidPatients = [];

    /**
     * @var array containing evals that are missing for the shift
     */
    public $missingEvals = [];


    /**
     * @inheritdoc
     * @OneToMany(targetEntity="Fisdap\Api\Shifts\Attachments\Entities\ShiftAttachment", mappedBy="associatedEntity", cascade={"persist", "remove"})
     */
    protected $attachments;


    public function __construct()
    {
        $this->patients = new ArrayCollection();
        $this->runs = new ArrayCollection();
        $this->meds = new ArrayCollection();
        $this->airways = new ArrayCollection();
        $this->ivs = new ArrayCollection();
        $this->cardiac_interventions = new ArrayCollection();
        $this->other_interventions = new ArrayCollection();
        $this->narratives = new ArrayCollection();
        $this->vitals = new ArrayCollection();
        $this->histories = new ArrayCollection();
        $this->practice_items = new ArrayCollection();
        $this->attachments = new ArrayCollection();

        //Set default attendence to "On Time"
        $this->set_attendence(1);

        //Set first locked to a null date
        $this->first_locked = new \DateTime("0000-00-00 00:00:00");
    }


    /**
     * Set the 'start' DateTime
     *
     * @param \DateTime $dateTime
     */
    public function setStart_datetime(\DateTime $dateTime)
    {
        $this->start_date = new \DateTime($dateTime->format('Y-m-d'));
        $this->start_time = $dateTime->format('Hi');
        $this->start_datetime = $dateTime;
    }


    /**
     * @param \DateTime $dateTime
     * @codeCoverageIgnore
     * @deprecated
     */
    public function set_start_datetime(\DateTime $dateTime)
    {
        $this->setStart_datetime($dateTime);
    }


    /**
     * @return \DateTime
     */
    public function get_start_datetime()
    {
        if ($this->start_datetime) {
            return $this->start_datetime;
        }

        $datetime = new \DateTime($this->start_date);
        $time = Util_FisdapTime::create_from_military_time($this->start_time);
        $datetime->setTime($time->get_hours(), $time->get_minutes());

        return $datetime;
    }


    /**
     * Set the 'end' DateTime
     * @param \DateTime $dateTime
     */
    public function setEnd_datetime(\DateTime $dateTime)
    {
        $this->end_time = $dateTime->format('Hi');
        $this->end_datetime = $dateTime;
    }


    /**
     * @param \DateTime $dateTime
     * @codeCoverageIgnore
     * @deprecated
     */
    public function set_end_datetime(\DateTime $dateTime)
    {
        $this->setEnd_datetime($dateTime);
    }


    /**
     * @return \DateTime
     */
    public function get_end_datetime()
    {
        if ($this->end_datetime) {
            return $this->end_datetime;
        }

        $datetime = $this->get_start_datetime();
        $datetime->add(new \DateInterval("PT" . (int)($this->hours * 3600) . "S"));

        return $datetime;
    }

    /**
     * Set the attendance of this shift
     *
     * @param mixed $value
     *
     * @return $this
     */
    public function set_attendence($value)
    {
        $this->attendence = self::id_or_entity_helper($value, 'ShiftAttendence');
        return $this;
    }


    /**
     * Set the student for this shift
     *
     * @param mixed $value
     *
     * @return $this
     */
    public function set_student($value)
    {
        $this->student = self::id_or_entity_helper($value, 'StudentLegacy');
        return $this;
    }

    /**
     * @param StudentLegacy $student
     */
    public function setStudent($student)
    {
        $this->student = $student;
    }

    public function getStudent()
    {
        return $this->student;
    }

    /**
     * Set the creator for this shift
     * @param mixed $value
     */
    public function set_creator($value)
    {
        $this->creator = self::id_or_entity_helper($value, 'UserContext');
    }


    /**
     * @return string
     */
    public function getShortSummary()
    {
        return $this->start_datetime->format("m-d-Y; Hi") . "-" . $this->end_datetime->format("Hi") . " " . $this->base->name . ", " . $this->site->name;
    }


    /**
     * @param \DateTime $dateTime
     */
    public function setFirst_locked(\DateTime $dateTime)
    {
        $this->first_locked = $dateTime;
    }

    /**
     * @param SiteLegacy $site
     */
    public function setSite(SiteLegacy $site)
    {
        $this->site = $site;
    }

    /**
     * @return SiteLegacy
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * @param BaseLegacy $base
     */
    public function setBase(BaseLegacy $base)
    {
        $this->base = $base;
    }

    /**
     * @return BaseLegacy
     */
    public function getBase()
    {
        return $this->base;
    }

    /**
     * @return mixed
     */
    public function getHours()
    {
        return $this->hours;
    }

    /**
     * @param mixed $hours
     */
    public function setHours($hours)
    {
        $this->hours = $hours;
    }

    /**
     * @return string
     */
    public function getAttendanceComments()
    {
        return $this->attendence_comments;
    }

    /**
     * @param string $comments
     */
    public function setAttendanceComments($comments)
    {
        $this->attendence_comments = $comments;
    }

    /**
     * @param ShiftAttendence $attendance
     */
    public function setAttendance(ShiftAttendence $attendance)
    {
        $this->attendence = $attendance;
    }

    /**
     * @return ShiftAttendence
     */
    public function getAttendance()
    {
        return $this->attendence;
    }


    /**
     * Lock this shift
     *
     * @param boolean $lock lock or unlock the shift
     * @deprecated
     * @todo refactor into event listener
     */
    public function lockShift($lock)
    {
        if ($lock != $this->locked) {
            $changeType = ($lock) ? 1 : 2;
            $this->recordHistory($changeType); //todo - handle in event listener

            //only set the first locked timestamp if it's currently a null date
            if ($this->first_locked == new \DateTime("0000-00-00 00:00:00")) {
                $this->setFirst_locked(new \DateTime("now"));
            }

            //If we're locking the shift, fire off Critical Thinking and Narrative emails - todo: in event listener
            if ($lock) {
                $program = ProgramLegacy::getCurrentProgram();

                // don't send the emails for lab shifts
                if ($program->send_critical_thinking_emails && $this->type != 'lab') {
                    $this->sendCriticalThinkingEmail();
                }

                $this->sendNarratives();
            } else {
                //If we're unlocking a shift, make sure the shift loses it's audited status
                $this->audited = false;
            }
        }
        
        $this->locked = $lock;
    }


    /**
     * @param bool $locked
     */
    public function setLocked($locked)
    {
        if ($locked !== $this->locked) {
            if ($this->first_locked == new \DateTime("0000-00-00 00:00:00")) {
                $this->setFirst_locked(new \DateTime("now"));
            }
        }
        
        if ($locked === false) {
            $this->audited = false;
        }
        
        $this->locked = $locked;
    }


    /**
     * @return mixed
     */
    public function getLocked()
    {
        return $this->locked;
    }
    

    /**
     * Add association between Patient and Shift
     *
     * @param Patient $patient
     */
    public function addPatient(Patient $patient)
    {
        $this->patients->add($patient);
        $patient->shift = $this;
        $patient->student = $this->student;
    }


    /**
     * Remove association between Patient and Shift
     *
     * @param Patient $patient
     */
    public function removePatient(Patient $patient)
    {
        $this->patients->removeElement($patient);
        $patient->shift = null;
        $patient->student = null;
    }


    /**
     * Add association between Run and Shift
     *
     * @param \Fisdap\Entity\Run $run
     */
    public function addRun(Run $run)
    {
        $this->runs->add($run);
        $run->shift = $this;
        $run->student = $this->student;
    }


    /**
     * Remove association between Run and Shift
     *
     * @param \Fisdap\Entity\Run $run
     */
    public function removeRun(Run $run)
    {
        $this->runs->removeElement($run);
        $run->shift = null;
        $run->student = null;
    }


    /**
     * Add association between Med and Shift
     *
     * @param \Fisdap\Entity\Med $med
     */
    public function addMed(Med $med)
    {
        $this->meds->add($med);
        $med->shift = $this;
        $med->student = $this->student;
    }


    /**
     * Add association between CardiacIntervention and Shift
     *
     * @param CardiacIntervention $cardiac
     */
    public function addCardiacIntervention(CardiacIntervention $cardiac)
    {
        $this->cardiac_interventions->add($cardiac);
        $cardiac->shift = $this;
        $cardiac->student = $this->student;
    }


    /**
     * Add association between OtherIntervention and Shift
     *
     * @param OtherIntervention $other
     */
    public function addOtherIntervention(OtherIntervention $other)
    {
        $this->other_interventions->add($other);
        $other->shift = $this;
        $other->student = $this->student;
    }


    /**
     * Add association between Airway and Shift
     *
     * @param Airway $air
     */
    public function addAirway(Airway $air)
    {
        $this->airways->add($air);
        $air->shift = $this;
        $air->student = $this->student;
    }


    /**
     * Add association between Iv and Shift
     *
     * @param Iv $iv
     */
    public function addIv(Iv $iv)
    {
        $this->ivs->add($iv);
        $iv->shift = $this;
        $iv->student = $this->student;
    }


    /**
     * Add association between Narrative and Shift
     *
     * @param Narrative $narrative
     */
    public function addNarrative(Narrative $narrative)
    {
        $this->narratives->add($narrative);
        $narrative->shift = $this;
        $narrative->student = $this->student;
    }


    /**
     * Add association between Vital and Shift
     *
     * @param Vital $vital
     */
    public function addVital(Vital $vital)
    {
        $this->vitals->add($vital);
        $vital->shift = $this;
        $vital->student = $this->student;
    }


    /**
     * Add association between Lab Assessment/skill and Shift
     *
     * @param LabSkill $labSkill
     */
    public function addLabSkill(LabSkill $labSkill)
    {
        $this->lab_skills->add($labSkill);
        $labSkill->shift = $this;
        $labSkill->student = $this->student;
    }


    /**
     * Add association between a practice item and this shift
     *
     * @param PracticeItem
     *
     * @return ShiftLegacy
     */
    public function addPracticeItem(PracticeItem $item)
    {
        $this->practice_items->add($item);
        $item->shift = $this;
        $item->student = $this->student;

        return $this;
    }


    /**
     * Remove an association between a practice item and this shift
     *
     * @param PracticeItem $item
     *
     * @return $this
     */
    public function removePracticeItem(PracticeItem $item)
    {
        if ($item->eval_session) {
            $item->eval_session->delete(false);
        }

        $this->practice_items->removeElement($item);
        $item->delete(false);

        return $this;
    }


    /**
     * Record a history event
     *
     * @param integer $changeType the ID of the history event
     */
    public function recordHistory($changeType)
    {
        $history = new ShiftHistory();
        $history->shift = $this;
        $history->change = self::id_or_entity_helper($changeType, 'ShiftHistoryChange');

        $user = User::getLoggedInUser();

        if ($user->id) {
            $history->user = $user;
        }

        $this->histories->add($history);
    }


    /**
     * @PrePersist
     * @PreUpdate
     */
    public function calculateEndTime()
    {
        if ($this->start_datetime) {
            $datetime = clone($this->start_datetime);
            $datetime->add(new \DateInterval("PT" . (int)($this->hours * 3600) . "S"));
            $this->setEnd_datetime($datetime);
        }
    }


    /**
     * @PostLoad
     * @todo ask stape about this
     */
    public function convertShiftDates()
    {
        if (!$this->start_datetime && $this->start_date) {
            $datetime = clone($this->start_date);

            if (is_int($this->start_time)) {
                $time = (string) $this->start_time;
            } else {
                $time = $this->start_time;
            }

            $time = trim($time);
            $time = (int) substr($time, 0, 4);
            $time = str_pad($time, 4, '0', STR_PAD_LEFT);

            $hours = substr($time, 0, 2);
            $minutes = substr($time, 2, 2);

            $datetime->setTime($hours, $minutes());
            $this->start_datetime = $datetime;
            $this->save();
        }
    }

    /**
     * @param integer $viewingUserId (optional)
     * @return mixed integer with number of comments, viewable by userid
     * if no user id, it doesn't check user's view permissions
     * OR 'hidden' if user has no permission to ADD comments and no viewable comments
     */
    public function hasComments($viewingUserId = null)
    {
        $comments = Comment::getUserViewableComments('shifts', $this->id, $viewingUserId);
        return count($comments);
    }


    /**
     * is this a quick added shift
     * @return boolean
     */
    public function isQuickAdd()
    {
        return $this->event_id < 0;
    }


    /**
     * Was this shift created by a student?
     * @return boolean
     */
    public function isStudentCreated()
    {
        return ($this->creator->role->name == 'student') ? true : false;
    }


    /**
     * Get a single listing of all of the associated skills for this shift.
     *
     * @return Array containing the associated shifts.
     */
    public function getAssociatedSkills($studentID)
    {
        // These need to be returned in the correct order...
        // Start with the Airways...
        $mainList = array('active' => array(), 'inactive' => array());

        $this->skillsMerge($mainList, 'Airway', $studentID);
        $this->skillsMerge($mainList, 'Iv', $studentID);
        $this->skillsMerge($mainList, 'CardiacIntervention', $studentID);
        $this->skillsMerge($mainList, 'OtherIntervention', $studentID);
        $this->skillsMerge($mainList, 'LabSkill', $studentID);

        // Sort the main list now...
        usort($mainList['active'], array(get_class($this), 'sortSkillList'));
        usort($mainList['inactive'], array(get_class($this), 'sortSkillList'));

        /*
        foreach($mainList as $ml){
            echo get_class($ml) . " -> " . $ml->id . "\n";
        }
        */


        return $mainList;
    }


    /**
     * Function to be used as a callback to a call to usort.  Sorts an array
     * of skills based on their stored orders.
     *
     * @param object $a
     * @param object $b
     *
     * @return int 0 if the elements are equal, -1 if A comes before B, and 1 if B
     * comes before A.
     */
    public static function sortSkillList($a, $b)
    {
        if ($a->skill_order == $b->skill_order) {
            return 0;
        }

        return ($a->skill_order < $b->skill_order ? -1 : 1);
    }


    /**
     * This is just a helper function to fetch and merge in skills of various
     * types to a main list.
     *
     * @param array $mainList The full listing of all skills of all types
     * @param String $entityName Name of the entity to merge into the listing
     * (should extend from the base Skill Entity).
     * @param int $studentID  ID of the student that performed the skills.
     */
    private function skillsMerge(&$mainList, $entityName, $studentID)
    {
        // Assuming the mainList is ordered...  Do a quick pass over the merger
        // list...
        $fullEntityName = "Fisdap\\Entity\\" . $entityName;

        $dql = "SELECT e FROM $fullEntityName e WHERE e.student = :studentID AND e.shift = :shiftID ORDER BY e.created ASC";

        $query = EntityUtils::getEntityManager()->createQuery($dql);
        $query->setParameter('studentID', $studentID);//2);
        $query->setParameter('shiftID', $this->id);//1483606);

        $result = $query->getResult();

        //Filter out skills that are attached to the patient
        foreach ($result as $id => $skill) {
            if ($skill->patient->id) {
                unset($result[$id]);
            }
        }
        $result = array_values($result);

        $programId = User::getLoggedInUser()->getProgramId();

        if ($entityName == 'CardiacIntervention') {
            $procedureSkillEntityName = '\Fisdap\Entity\ProgramCardiacProcedure';
        } elseif ($entityName == 'OtherIntervention') {
            $procedureSkillEntityName = '\Fisdap\Entity\ProgramOtherProcedure';
        } elseif ($entityName == 'LabSkill') {
            $procedureSkillEntityName = '\Fisdap\Entity\ProgramLabAssessment';
        } else {
            $procedureSkillEntityName = '\Fisdap\Entity\Program' . $entityName . "Procedure";
        }

        foreach ($result as $merge) {
            // If the skill is no longer included in the program, add it to a
            // different list so it can still be tracked.
            if ($procedureSkillEntityName::programIncludesProcedure($programId, $merge->procedure->id)) {
                $mainList['active'][] = $merge;
            } else {
                $mainList['inactive'][] = $merge;
            }
        }
    }


    /**
     * This function deletes the skills, as specified in $deleteData, from
     * this shift.
     *
     * @internal Array $deleteData Array containing data to delete in the following
     * format: {SkillType}_{TypeID}_{SkillID}.  For example, if we see the value
     * AirwayProcedure_5_20, we know that we need to delete from the Airway
     * table the record with the ID of 20.  The "5" id represents what the
     * specific Airway skill is, and has no real bearing on what we're deleting.
     */
    public function deleteQuickSkills()
    {
        $rawMergeList = array_merge($this->airways->toArray(), $this->ivs->toArray(), $this->cardiac_interventions->toArray(), $this->other_interventions->toArray(), $this->lab_skills->toArray());

        // Take out the ones that are no longer in the settings page...
        $mergeList = $this->getAssociatedSkills($this->student->id);

        $this->deleteGroup($mergeList['active']);

        $this->airways = new ArrayCollection();
        $this->ivs = new ArrayCollection();
        $this->cardiac_interventions = new ArrayCollection();
        $this->other_interventions = new ArrayCollection();
        $this->lab_skills = new ArrayCollection();
    }


    /**
     * Get all the verified runs tied to this shift
     *
     * @return array an array of run entities
     */
    public function getVerifiedRuns()
    {
        $verifiedRuns = array();

        foreach ($this->runs as $run) {
            if ($run->verification->verified) {
                $verifiedRuns[] = $run;
            }
        }

        return $verifiedRuns;
    }

    /**
     * Set the verification for this patient
     * @param Verification $ver
     */
    public function setVerification(Verification $ver = null)
    {
        if ($ver !== null) {
            $ver->shift = $this;
            $ver->student = $this->student;
        }

        $this->verification = $ver;

        if (is_null($ver) && !is_null($this->verification)) {
            $this->verification->delete();
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
     * Get the number of verified runs tied to this shift
     *
     * @return integer
     */
    public function getNumVerifiedRuns()
    {
        return $this->getEntityRepository()->getVerifiedRunCount($this->id);
    }


    /**
     * @return mixed
     */
    public function getNumRuns()
    {
        return $this->getEntityRepository()->getRunCount($this->id);
    }


    /**
     * Get triggers for critical thinking emails
     * @return array containing all skills that qualify for critical thinking
     */
    public function getCriticalThinkingTriggers()
    {
        $triggers = array(
            'Birth' => 0,
            'Delivery of Electrical Therapy' => false,
            'Cardiac Arrest' => false,
            'Respiratory Arrest' => false,
            'Administration of Sodium Bicarbonate' => false,
            'Endotracheal Intubation' => false,
            'Cricothyrotomy' => false,
            'Chest Decompression' => false,
            'Pericardiocentesis' => false,
        );

        $birthIds = array(23, 36, 37);
        $cardiacProcIds = array(2, 3, 4);
        $intubationIds = array(5, 6, 10);

        //Find any Cardiac Arrest or Birth impressions
        foreach ($this->patients as $patient) {
            if ($patient->primary_impression->id == 4 || $patient->secondary_impression->id == 4) {
                $triggers['Cardiac Arrest'] = true;
            }

            if ((in_array($patient->primary_impression->id, $birthIds) || in_array($patient->secondary_impression->id, $birthIds)) && $this->type == 'field') {
                $triggers['Birth'] = true;
            }
        }

        //Find any Electrical Therapy cardiac interventions
        foreach ($this->cardiac_interventions as $cardiac) {
            if (in_array($cardiac->procedure->id, $cardiacProcIds)) {
                $triggers['Delivery of Electrical Therapy'] = true;
                break;
            }
        }

        //Find any meds that match
        foreach ($this->meds as $med) {
            if ($med->medication->id == 19) {
                $triggers['Administration of Sodium Bicarbonate'] = true;
            }

            if ($med->route->id == 9) {
                $triggers['Respiratory Arrest'] = true;
            }
        }

        //Find any airways that match
        foreach ($this->airways as $airway) {
            if ($airway->procedure->id == 12) {
                $triggers['Respiratory Arrest'] = true;
            }

            if (in_array($airway->procedure->id, $intubationIds)) {
                $triggers['Endotracheal Intubation'] = true;
            }

            if ($airway->procedure->id == 15) {
                $triggers['Cricothyrotomy'] = true;
            }
        }

        //Find any other interventions that match
        foreach ($this->other_interventions as $other) {
            if ($other->procedure->id == 25) {
                $triggers['Chest Decompression'] = true;
            }

            if ($other->procedure->id == 7) {
                $triggers['Pericardiocentesis'] = true;
            }
        }

        return $triggers;
    }


    /**
     * @todo refactor out of this class...violates SRP ~bgetsug
     */
    protected function sendCriticalThinkingEmail()
    {
        $allTriggers = $this->getCriticalThinkingTriggers();

        $triggers = array();
        foreach ($allTriggers as $name => $triggered) {
            if ($triggered) {
                $triggers[] = $name;
            }
        }

        //If at least one of the triggers was true, send the email
        if (count($triggers) > 0) {
            $emailAddresses = $this->student->getInstructorEmails();
            $emailAddresses[] = $this->student->email;


            $subject = "Interesting experience, " . $this->student->user->first_name
                . " " . $this->student->user->last_name . ", "
                . $this->start_datetime->format("Y-m-d") . " "
                . $this->site->name;

            foreach ($emailAddresses as $address) {
                $mail = new \Fisdap_TemplateMailer();

                $mail->addTo($address)
                    ->setSubject($subject)
                    ->setReplyTo($this->student->email)
                    ->setViewParam('shift', $this)
                    ->setViewParam('triggers', $triggers)
                    ->sendHtmlTemplate('critical-thinking.phtml');
            }
        }
    }


    /**
     * @todo refactor out of this class...violates SRP ~bgetsug
     */
    protected function sendNarratives()
    {
        //If at least one narrative exists, prepare to send it.
        if (count($this->narratives) > 0) {

            // make sure we have some actual text to send
            $empty_narrative = true;
            foreach ($this->narratives as $narrative) {
                if (!$narrative->isBlank()) {
                    $empty_narrative = false;
                }
            }

            if ($empty_narrative) {
                return;
            }

            $subject = "Narrative, " . $this->student->user->first_name
                . " " . $this->student->user->last_name . ", "
                . $this->start_datetime->format("Y-m-d") . " "
                . $this->site->name;

            $emailAddresses = $this->student->getInstructorEmails();
            $emailAddresses[] = $this->student->email;

            foreach ($emailAddresses as $address) {
                $mail = new \Fisdap_TemplateMailer();
                $mail->addTo($address)
                    ->setReplyTo($this->student->email)
                    ->setSubject($subject)
                    ->setViewParam('shift', $this)
                    ->sendTextTemplate('narratives.phtml');
            }
        }
    }


    /**
     * Is this shift in the future?
     *
     * @return boolean true if the shifts starts after today, false if it starts
     * today or earlier.
     */
    public function isFuture()
    {
        $today = new \DateTime();

        if ($this->start_datetime->format("Y-m-d") <= $today->format("Y-m-d")) {
            return false;
        }

        return true;
    }


    /**
     * Determines if the given shift is editable
     * Here are the conditions that must be met
     * 1). User has view permissions, look $this->isViewable()
     * 2). If instructor in the same program as student with edit permissions
     * 3). If student shift must be unlocked
     *
     * @param User $user
     * @return boolean
     */
    public function isEditable($user = null)
    {
        $user = User::getUser($user);
        if (!$user) {
            return false;
        }

        $allowed = $this->isViewable($user);

        if ($user->getCurrentRoleName() == "instructor") {
            //Make sure the instructor has permission this particular type of Skills Tracker data
            $allowed = $allowed && $user->getCurrentRoleData()->hasPermission("Edit " . ucfirst($this->type) . " Data");
        } else {
            //Shift must be unlocked
            $allowed = $allowed && !$this->locked;
        }

        return $allowed;
    }


    /**
     * Determines if the given shift is editable
     * Here are the conditions that must be met
     * 1). User must be an instructor in the same program as student with edit permissions
     * 2). User must be a student and shift must be unlocked
     *
     * @param User $user
     * @return boolean
     */
    public function isShiftScheduleEditable($user = null)
    {
        $user = User::getUser($user);
        if (!$user) {
            return false;
        }

        $allowed = $this->isViewable($user);

        $roleName = $user->getCurrentRoleName();

        if ($roleName == 'instructor') {
            // Instructor Schedules permissions

            // Permissions have shifts as 'Field', 'Lab', 'Clinic'
            $permissionShiftType = ($this->type == 'clinical') ? 'Clinic' : ucwords($this->type);
            $allowed = $allowed && $user->getCurrentRoleData()->hasPermission('Edit ' . $permissionShiftType . ' Schedules');
        } elseif ($roleName == 'student') {
            //Shift must be unlocked
            $allowed = $allowed && !$this->locked;
        } else {
            $allowed = false;
        }

        return $allowed;
    }


    /**
     *  Shift if viewable if:
     *      - belongs to student viewing it
     *      - viewing instructor is in the same program as the student shift belongs to
     *
     * @param mixed $user, default: null=currently logged in user
     *
     * @return boolean Is user allowed to view this shift
     */
    public function isViewable($user = null)
    {
        // can data be viewed by currently logged in user (null)
        // won't work without following line
        $a = $this->student->id;
        return $this->student->dataCanBeViewedBy($user);
        //return \Fisdap\Entity\User::canViewData($this->student->user, $user);
    }


    /**
     * Get the number of skills tied only to this shift
     */
    public function getSkillCount()
    {
        $skills = EntityUtils::getRepository('ShiftLegacy')->getSkillsByShift($this->id, array('shiftOnly' => true));

        return count($skills);
    }

    /**
     * @param $type string
     */
    public function setType($type)
    {
        $this->type = $type;
    }


    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }


    /**
     * Get the name of the type of run
     */
    public function getRunType()
    {
        switch ($this->type) {
            case "field":
                return "Run";
            case "clinical":
                return "Assessment";
            case "lab":
                return "Scenario";
        }
    }


    /**
     * @param $shiftId
     * @param $user
     *
     * @return mixed
     */
    public static function canEditData($shiftId, $user)
    {
        $shift = EntityUtils::getEntity('ShiftLegacy', $shiftId);
        return $shift->isEditable($user);
    }


    /**
     * Delete empty runs
     */
    public function deleteEmptyRuns()
    {
        foreach ($this->runs as $run) {
            if (count($run->patients) == 0) {
                $this->removeRun($run);
                $run->delete();
            }
        }
    }


    /**
     * @return bool
     */
    public function isValid()
    {
        $valid = true;
        $invalidPatients = array();

        $program = ProgramLegacy::getCurrentProgram();

        if ($program->program_settings->require_shift_evals) {
            $required_evals = EntityUtils::getRepository('ProgramRequiredShiftEvaluations')->getByProgram($program->id, $this->type);

            foreach ($required_evals as $required_eval) {
                $this->missingEvals[] = $required_eval->getEvalDef()->eval_title;
            }

            foreach ($this->eval_sessions as $shift_eval) {
                if (in_array($shift_eval->eval_def->eval_title, $this->missingEvals)) {
                    $deleteKey = array_search($shift_eval->eval_def->eval_title, $this->missingEvals);
                    unset($this->missingEvals[$deleteKey]);
                }
            }

            $this->missingEvals = array_unique($this->missingEvals);

            if (count($this->missingEvals) > 0) {
                $valid = false;
            }
        }

        foreach ($this->patients as $patient) {
            if (!$patient->isValid()) {
                $invalidPatients[] = $patient;
                $valid = $valid && false;
            }
        }

        $this->invalidPatients = $invalidPatients;
        return $valid;
    }


    /**
     * @return array
     */
    public function getInvalidPatients()
    {
        return $this->invalidPatients;
    }


    /**
     * Get an array of all the skills that we're added to this shift through
     * quick add
     *
     * @return array of Skills
     */
    public function getQuickAddedSkills()
    {
        if ($this->type == 'field') {
            return array();
        }

        $shiftSkills = $this->getEntityRepository()->getSkillsByShift($this->id, array('shiftOnly' => true));
        return $shiftSkills;
    }


    /**
     * @param $shiftIds
     *
     * @return array
     * @throws \Zend_Exception
     */
    public static function getPatientsSQL($shiftIds)
    {
        //$query = "SELECT p.*, Type, Hours FROM fisdap2_patients p, ShiftData sd WHERE p.shift_id = sd.Shift_id AND p.shift_id = " . $shiftId;
        $query = "SELECT p.*, sd.Type, sd.Hours
                  FROM fisdap2_patients p
                  INNER JOIN ShiftData sd ON p.shift_id = sd.Shift_id
                  WHERE sd.Shift_id IN (" . implode(', ', $shiftIds) . ")";

        $rawResults = \Zend_Registry::get('db')->query($query)->fetchAll();
        $patients = array();
        foreach ($rawResults as $key => $patient) {
            $patients[$patient['shift_id']][$patient['id']] = $patient;
            unset($rawResults[$key]);
        }

        return $patients;
    }


    /**
     * @return array
     */
    public function getHookIds()
    {
        if ($this->type == 'field') {
            return array('113');
        } elseif ($this->type == 'clinical') {
            return array('114');
        } elseif ($this->type == 'lab') {
            return array('115');
        }

        return array();
    }


    /**
     * Make a new shift that has the same date/hours/location of this shift but for another student
     *
     * @param StudentLegacy $student
     *
     * @return ShiftLegacy
     */
    public function copyShiftForLabPartner(StudentLegacy $student)
    {
        $shift = EntityUtils::getEntity("ShiftLegacy");
        $shift->set_start_datetime($this->start_datetime);
        $shift->hours = $this->hours;
        $shift->type = $this->type;
        $shift->site = $this->site;
        $shift->base = $this->base;
        $shift->student = $student;
        $shift->creator = $student->user_context;

        return $shift;
    }

    /**
     * Get the string we use for the date and time in titles
     */
    public function getTitleDateTime()
    {
        return $this->start_datetime->format('M j, Y, Hi');
    }


    /**
     * @return string
     */
    public function getLocation()
    {
        try {
            $sitename = $this->site->name;
        } catch (EntityNotFoundException $e) {
            return "site unknown";
        }

        try {
            $basename = $this->base->name;
        } catch (EntityNotFoundException $e) {
            return $sitename . ": base unknown";
        }

        return $sitename . ": " . $basename;
    }


    /**
     * @return string
     */
    public function getZoomViewTitle()
    {
        return $this->start_datetime->format("Hi") . " (" . $this->getDurationText() . ") " . $this->getLocation();
    }


    /**
     * @return string
     */
    public function getDetailViewDate()
    {
        return $this->getTitleDateTime() . " (" . $this->getDurationText() . ")";
    }


    /**
     * @return string
     */
    public function getDurationText()
    {
        $title = $this->hours . "hr";
        if ($this->hours > 1) {
            $title .= "s";
        }
        return $title;
    }


    /**
     * returns the event entity for a shift
     * @return bool|EntityBaseClass
     */
    public function getParentEvent()
    {
        $entity = false;

        if ($this->event_id) {
            $entity = EntityUtils::getEntity("EventLegacy", $this->event_id);
        }

        return $entity;
    }


    /**
     * these are JUST from the event
     * @return string
     */
    public function getInstructorList()
    {
        $return_text = "";
        $instructors = array();

        // it came from an event
        $event = $this->getParentEvent();
        if ($event) {
            $instructor_slot = $event->getSlotByType('instructor');

            if ($instructor_slot) {
                if ($instructor_slot->assignments) {
                    foreach ($instructor_slot->assignments as $assignment) {
                        $user = $assignment->user_context->user;
                        $instructors[] = $user->first_name . " " . $user->last_name;
                    }
                }
            }
        }

        $instructor_count = count($instructors);
        if ($instructor_count > 0) {
            $return_text .= "Instructor";
            $return_text .= ($instructor_count != 1) ? "s" : "";
            $return_text .= ": ";
            $return_text .= implode(", ", $instructors);
        }

        return $return_text;
    }


    /**
     * these are JUST from the event
     * @return string
     */
    public function getPreceptorList()
    {
        $return_text = "";
        $preceptors = array();

        // it came from an event
        $event = $this->getParentEvent();
        if ($event) {
            if ($event->preceptor_associations) {
                foreach ($event->preceptor_associations as $preceptor_assoc) {
                    $preceptors[] = $preceptor_assoc->preceptor->first_name . " " . $preceptor_assoc->preceptor->last_name;
                }
            }
        }

        $preceptor_count = count($preceptors);
        if ($preceptor_count > 0) {
            $return_text .= "Preceptor";
            $return_text .= ($preceptor_count != 1) ? "s" : "";
            $return_text .= ": ";
            $return_text .= implode(", ", $preceptors);
        }

        return $return_text;
    }


    /**
     * @return string
     */
    public function getPreceptorText()
    {
        $preceptors = array();
        if (count($this->preceptor_associations) > 0) {
            $title = (count($this->preceptor_associations) == 1) ? "Preceptor: " : "Preceptors: ";
            foreach ($this->preceptor_associations as $pa) {
                $preceptors[] = $pa->preceptor->first_name . " " . $pa->preceptor->last_name;
            }
        }
        return $title . implode(", ", $preceptors);
    }


    /**
     * @return mixed
     */
    public function hasData()
    {
        return $this->getEntityRepository()->hasData($this->id);
    }


    /**
     * Remove association between Event/Assignment and Shift - BATMAN!!
     */
    public function removeFromEvent()
    {
        $assignment = $this->slot_assignment;
        $assignment->shift = null;
        $this->slot_assignment = null;
        $assignment->remove();
        $this->event_id = -1;
        $this->save();
    }


    /**
     * Before this shift is deleted, if it's a scheduler shift, call the remove method on the slot assignment to achieve two things:
     * 1). Record event history that this shift has been deleted
     * 2). Email event email list about the removal
     *
     * @param boolean $flush should doctrine changes be flushed
     * @return void
     */
    public function delete($flush = true)
    {
        if (!$this->isQuickAdd()) {
            //Don't flush yet, that'll happen later
            $this->slot_assignment->remove(false);
            $this->slot_assignment = null;
        }

        parent::delete($flush);
    }


    private function getMeds()
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->isNull('patient'));
        return $this->meds->matching($criteria);
    }

    private function getCardiacInterventions()
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->isNull('patient'));
        return $this->cardiac_interventions->matching($criteria);
    }

    private function getOtherInterventions()
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->isNull('patient'));
        return $this->other_interventions->matching($criteria);
    }

    private function getAirways()
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->isNull('patient'));
        return $this->airways->matching($criteria);
    }

    private function getIvs()
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->isNull('patient'));
        return $this->ivs->matching($criteria);
    }

    private function getVitals()
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->isNull('patient'));
        return $this->vitals->matching($criteria);
    }

    /**
     * Set the entry time
     *
     * Need a "camel snaked" setter for use with Alice
     *
     * @param \DateTime $entry_time
     */
    public function setEntry_time(\DateTime $entry_time)
    {
        $this->entry_time = $entry_time;
    }

    private function collectionToObjectArray($collection)
    {
        $rtv = array();

        foreach ($collection as $intervention) {
            array_push($rtv, $intervention->toArray());
        }

        return $rtv;
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'student' => $this->student,
            'site' => $this->site,
            'base' => $this->base,
            'start_date' => $this->start_date,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'hours' => $this->hours,
            'entry_time' => $this->entry_time,
            'locked' => $this->locked,
            'type' => $this->type,
            'event_id' => $this->event_id,
            'trade' => $this->trade,
            'trade_status' => $this->trade_status,
            'audited' => $this->audited,
            'tardy' => $this->tardy,
            'first_locked' => $this->first_locked,
            'completed_from' => $this->completed_from,
            'pda_shift_id' => $this->pda_shift_id,
            'late' => $this->late,
            'attendence' => $this->getAttendance(),
            'attendence_comments' => $this->attendence_comments,
            'patients' => $this->patients,
            'attachments' => $this->getAttachments(),
            'soft_deleted' => $this->soft_deleted,
            'start_datetime' => $this->start_datetime,
            'end_datetime' => $this->end_datetime,
            'created' => $this->created,
            'updated' => $this->updated,
            'medications' => $this->collectionToObjectArray($this->getMeds()),
            'otherInterventions' => $this->collectionToObjectArray($this->getOtherInterventions()),
            'cardiacInterventions' => $this->collectionToObjectArray($this->getCardiacInterventions()),
            'airways' => $this->collectionToObjectArray($this->getAirways()),
            'ivs' => $this->collectionToObjectArray($this->getIvs()),
            'vitals' => $this->collectionToObjectArray($this->getVitals()),
        ];
    }
}
