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
 * Entity class for Legacy Students.
 *
 * @Entity(repositoryClass="Fisdap\Data\Student\DoctrineStudentLegacyRepository")
 * @Table(name="StudentData")
 * @HasLifecycleCallbacks
 */
class StudentLegacy extends RoleData
{
    const STUDENT_MAILING_LIST_ID = 2;

    /**
     * @Id
     * @Column(name="Student_id", type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @codeCoverageIgnore
     * @deprecated
     * @Column(name="Program", type="string", nullable=true)
     */
    protected $program_abbreviation;

    /**
     * @codeCoverageIgnore
     * @deprecated in favor of the user_context->program relationship
     * @var ProgramLegacy
     * @ManyToOne(targetEntity="ProgramLegacy")
     * @JoinColumn(name="Program_id", referencedColumnName="Program_id")
     */
    protected $program;

    /**
     * @codeCoverageIgnore
     * @deprecated
     * @Column(name="Mentor_id", type="integer", nullable=true)
     */
    protected $mentor_id;

    /**
     * @codeCoverageIgnore
     * @deprecated
     * @Column(name="Box_Number", type="integer", nullable=true)
     */
    protected $box_number;

    /**
     * @codeCoverageIgnore
     * @deprecated
     * @Column(name="Address", type="string", nullable=true)
     */
    protected $address;

    /**
     * @deprecated
     * @Column(name="City", type="string", nullable=true)
     */
    protected $city;

    /**
     * @codeCoverageIgnore
     * @deprecated
     * @Column(name="Country", type="string", nullable=true)
     */
    protected $country;

    /**
     * @codeCoverageIgnore
     * @deprecated
     * @Column(name="State", type="string", nullable=true)
     */
    protected $state;

    /**
     * @codeCoverageIgnore
     * @deprecated
     * @Column(name="ZipCode", type="string", nullable=true)
     */
    protected $zip;

    /**
     * @codeCoverageIgnore
     * @deprecated
     * @Column(name="HomePhone", type="string", nullable=true)
     */
    protected $home_phone;

    /**
     * @codeCoverageIgnore
     * @deprecated
     * @Column(name="WorkPhone", type="string", nullable=true)
     */
    protected $work_phone;

    /**
     * @deprecated
     * @Column(name="EmailAddress", type="string", nullable=true)
     */
    protected $email;

    /**
     * @codeCoverageIgnore
     * @deprecated
     * @Column(name="Pager", type="string", nullable=true)
     */
    protected $pager;

    /**
     * @codeCoverageIgnore
     * @deprecated
     * @Column(name="BirthDate", type="string", nullable=true)
     */
    protected $birth_date;

    /**
     * @codeCoverageIgnore
     * @deprecated
     * @Column(name="Gender", type="string", nullable=true)
     */
    protected $gender;

    /**
     * @codeCoverageIgnore
     * @deprecated
     * @Column(name="Ethnicity", type="string", nullable=true)
     */
    protected $ethnicity;

    /**
     * @codeCoverageIgnore
     * @deprecated
     * @Column(name="Class_Year", type="string", nullable=true)
     */
    protected $graduation_year = "1996";

    /**
     * @codeCoverageIgnore
     * @deprecated
     * @Column(name="EMT_Year", type="string", nullable=true)
     */
    protected $emt_graduation_year;

    /**
     * @codeCoverageIgnore
     * @deprecated
     * @Column(name="ClassMonth", type="integer", nullable=false)
     */
    protected $graduation_month;

    /**
     * @Column(name="research_consent", type="boolean", nullable=true)
     */
    protected $research_consent;

    /**
     * @codeCoverageIgnore
     * @deprecated
     * @Column(name="GoodDataFlag", type="integer", nullable=false)
     */
    protected $good_data_flag = 0;

    /**
     * @Column(name="ClassAssigned", type="boolean", nullable=false)
     */
    protected $class_assigned = 0;

    /**
     * @Column(name="MaxFieldShifts", type="integer", nullable=true)
     */
    protected $field_shift_limit = -1;

    /**
     * @Column(name="MaxClinicShifts", type="integer", nullable=true)
     */
    protected $clinical_shift_limit = -1;

    /**
     * @Column(name="MaxLabShifts", type="integer", nullable=true)
     */
    protected $lab_shift_limit = -1;

    /**
     * @deprecated
     * @Column(name="CellPhone", type="string", nullable=false)
     */
    protected $cell_phone;

    /**
     * @codeCoverageIgnore
     * @deprecated
     * @Column(name="ContactPhone", type="string", nullable=false)
     */
    protected $contact_phone = "";

    /**
     * @codeCoverageIgnore
     * @deprecated
     * @Column(name="ContactName", type="string", nullable=false)
     */
    protected $contact_name = "";

    /**
     * @codeCoverageIgnore
     * @deprecated
     * @Column(name="ContactRelation", type="string", nullable=false)
     */
    protected $contact_relation = "";

    /**
     * @codeCoverageIgnore
     * @deprecated
     * @Column(name="TestingExpDate", type="date", nullable=false)
     */
    protected $testing_expiration_date;

    /**
     * @codeCoverageIgnore
     * @deprecated
     * @Column(name="DefaultGoalSet_id", type="integer", nullable=false)
     */
    protected $default_goal_set_id = -1;

    /**
     * @Column(type="boolean", nullable=true);
     */
    protected $good_data = null;

    /**
     * @ManyToOne(targetEntity="GraduationStatus", cascade={"persist"})
     */
    protected $graduation_status;

    /**
     * @OneToMany(targetEntity="ShiftLegacy", mappedBy="student")
     */
    protected $shifts;

    /**
     * @OneToMany(targetEntity="Patient", mappedBy="student")
     */
    protected $patients;

    /**
     * @OneToMany(targetEntity="Run", mappedBy="student")
     */
    protected $runs;

    /**
     * @var ArrayCollection|ClassSectionStudentLegacy[]
     * @OneToMany(targetEntity="ClassSectionStudentLegacy", mappedBy="student", cascade={"persist","remove"})
     */
    protected $classSectionStudent;

    /**
     * @OneToMany(targetEntity="StudentCourseCertifications", mappedBy="student")
     */
    protected $studentCourseCertifications;

    /**
     * @OneToMany(targetEntity="PortfolioOptions", mappedBy="student")
     */
    protected $portfolioOptions;

    /**
     * @OneToMany(targetEntity="PortfolioUploads", mappedBy="student")
     */
    protected $portfolioUploads;

    /**
     * @OneToMany(targetEntity="PortfolioDetails", mappedBy="student")
     */
    protected $portfolioDetails;


    /**
     * @OneToMany(targetEntity="Iv", mappedBy="student")
     */
    protected $ivs;

    /**
     * @OneToMany(targetEntity="Med", mappedBy="student")
     */
    protected $meds;

    /**
     * @OneToMany(targetEntity="OtherIntervention", mappedBy="student")
     */
    protected $other_interventions;

    /**
     * @OneToMany(targetEntity="CardiacIntervention", mappedBy="student")
     */
    protected $cardiac_interventions;

    /**
     * @OneToMany(targetEntity="Airway", mappedBy="student")
     */
    protected $airways;

    /**
     * @OneToOne(targetEntity="Narrative", mappedBy="student")
     */
    protected $narratives;

    /**
     * @OneToMany(targetEntity="Vital", mappedBy="student")
     */
    protected $vitals;


    /**
     * @todo move this to __construct()
     */
    public function init()
    {
        $this->shifts = new ArrayCollection();
        $this->patients = new ArrayCollection();
        $this->runs = new ArrayCollection();
        $this->classSectionStudent = new ArrayCollection();
        $this->studentCourseCertifications = new ArrayCollection();
        $this->portfolioOptions = new ArrayCollection();
        $this->portfolioUploads = new ArrayCollection();
        $this->portfolioDetails = new ArrayCollection();

        $this->testing_expiration_date = new \DateTime('0000-00-00');
        $this->graduation_month = date("n");
        $this->graduation_year = date("Y");

        //Set their graduation status to "In Progress" by default
        $this->set_graduation_status(1);
    }

    public function remove_groups($flush = false)
    {
        foreach ($this->classSectionStudent as $section) {
            $this->classSectionStudent->removeElement($section);
            $section->delete($flush);
        }
    }


    /**
     * @param $value
     *
     * @return $this
     *
     * @codeCoverageIgnore
     * @deprecated
     */
    public function set_first_name($value)
    {
        $this->first_name = $value;
        $this->user->first_name = $value;
        return $this;
    }


    /**
     * @param $value
     *
     * @return $this
     *
     * @codeCoverageIgnore
     * @deprecated
     */
    public function set_last_name($value)
    {
        $this->last_name = $value;
        $this->user->last_name = $value;
        return $this;
    }


    /**
     * @param $value
     *
     * @return $this
     *
     * @codeCoverageIgnore
     * @deprecated
     */
    public function set_email($value)
    {
        $this->email = $value;
        $this->user->email = $value;
        return $this;
    }


    /**
     * @param $value
     *
     * @return $this
     *
     * @codeCoverageIgnore
     * @deprecated
     */
    public function set_username($value)
    {
        $this->username = $value;
        $this->user->username = $value;
        return $this;
    }


    /**
     * @param $value
     *
     * @return $this
     * @throws \Exception
     *
     * @codeCoverageIgnore
     * @deprecated
     */
    public function set_program($value)
    {
        $this->program = self::id_or_entity_helper($value, "ProgramLegacy");
        if ($this->user_context) {
            $this->user_context->program = self::id_or_entity_helper($value, "ProgramLegacy");
        }
        $this->program_abbreviation = $this->program->abbreviation;
        return $this;
    }


    /**
     * Add this student to the Fisdap Student Mailing list
     *
     * @param mixed $list integer | \Fisdap\Entity\MailingList
     * @return \Fisdap\Entity\StudentLegacy
     */
    public function addToMailingList($list = self::STUDENT_MAILING_LIST_ID)
    {
        $this->user->addMailingList($list, $this->user_context);
        return $this;
    }

    /**
     * Remove this student from the Fisdap Student Mailing list
     *
     * @param mixed $list integer | \Fisdap\Entity\MailingList
     * @return \Fisdap\Entity\StudentLegacy
     */
    public function removeFromMailingList($list = self::STUDENT_MAILING_LIST_ID)
    {
        $this->user->removeMailingList($list);
        return $this;
    }

    /**
     * Is the student on the student mailing list
     *
     * @param mixed $list integer | \Fisdap\Entity\MailingList
     * @return boolean
     */
    public function onMailingList($list = self::STUDENT_MAILING_LIST_ID)
    {
        return $this->user->onMailingList($list);
    }


    /**
     * Get the graduation date of this student
     * @todo refactor this to the context and deprecate?
     * @codeCoverageIgnore
     * @deprecated get from UserContext::end_date
     * @return \DateTime
     */
    public function getGraduationDate()
    {
        return $this->user_context->end_date;
    }

    /**
     * Set the graduation date of this student
     *
     * @codeCoverageIgnore
     * @deprecated
     * @param \DateTime $date
     * @return \Fisdap\Entity\StudentLegacy
     */
    public function setGraduationDate(\DateTime $date)
    {
        if ($this->user_context) {
            $this->user_context->end_date = $date;
        }
        $this->graduation_month = $date->format('n');
        $this->graduation_year = $date->format('Y');

        return $this;
    }

    /**
     * Get the certification entity for this student
     *
     * @return \Fisdap\Entity\CertificationLevel
     */
    public function getCertification($formatted = "")
    {
        if ($formatted) {
            return $this->user_context->certification_level->name;
        }

        return $this->user_context->certification_level;
    }

    /**
     * Set the cerification for this student
     *
     * @param mixed $cert integer | \Fisdap\Entity\CertificationLevel
     * @return \Fisdap\Entity\StudentLegacy
     */
    public function setCertification($cert)
    {
        $cert = self::id_or_entity_helper($cert, 'CertificationLevel');

        $this->user_context->certification_level = $cert;
        $this->user->getSerialNumberForRole()->account_type = $cert->name;
        $this->user->getSerialNumberForRole()->set_certification_level($cert->id);

        return $this;
    }

    /**
     * Set the graduation status
     *
     * @param mixed $value integer | \Fisdap\Entity\GraduationStatus
     * @return \Fisdap\Entity\StudentLegacy
     */
    public function set_graduation_status($value)
    {
        $this->graduation_status = self::id_or_entity_helper($value, 'GraduationStatus');
        return $this;
    }

    /**
     * Has this student hit the limit for a given type of shift
     * @param string $type either field, clinical or lab
     *
     * @return bool
     */
    public function atLimit($type)
    {
        if (is_null($type)) {
            return false;
        }

        // no limit
        if ($this->{$type . "_shift_limit"} < 0) {
            return false;
        }

        $shifts = EntityUtils::getRepository('ShiftLegacy')->getShiftIdsByStudent($this, array('shiftType' => $type));
        if (count($shifts) >= $this->{$type . "_shift_limit"}) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get a student description suitable for use in the student searcher, and possibly other places.
     * @todo move this to refactor into a more suitable presentation layer function
     *
     * @return string
     */
    public function getLongName()
    {
        $cert = $this->getCertification()->abbreviation ? $this->getCertification()->abbreviation : "N/A";
        $name = $this->user->last_name . ", " . $this->user->first_name . " - " . $cert . ": " . $this->getGraduationDate()->format("m/Y");

        return $name;
    }

    /**
     * Get all instructors that should know about this student.
     * Find all the students groups this student is a members of and check if
     * those groups limit emails to only instructors in that group. If there are no
     * limitations, get all instructors in the program
     */
    public function getInstructors()
    {
        $instructors = array();
        $includeAllInstructors = true;

        //Get any instructors for this students class sections
        // who have the appropriate flags set...
        foreach ($this->classSectionStudent as $css) {
            if ($css->section->generate_emails) {
                $includeAllInstructors = false;

                foreach ($css->section->section_instructor_associations as $sia) {
                    if ($sia->instructor->email_event_flag) {
                        $instructors[] = $sia->instructor;
                    }
                }
            }
        }

        // If the student belongs to no class sections that limit emails, get all instructors
        if ($includeAllInstructors === true) {
            return EntityUtils::getRepository("InstructorLegacy")->findBy(array('program' => $this->program->id, 'email_event_flag' => true));
        }

        return $instructors;
    }

    /**
     * Gets all instructors related to this student and return their email addresses
     */
    public function getInstructorEmails()
    {
        $emails = array();
        $instructors = $this->getInstructors();

        foreach ($instructors as $instructor) {
            $emails[] = $instructor->email;
        }

        return $emails;
    }

    /**
     * @return \Fisdap\Entity\GoalSet
     */
    public function getGoalSet()
    {
        switch ($this->getCertification(true)) {
            case "emt-b":
                $certLevel = "emt_b";
                break;
            case "aemt":
                $certLevel = "emt_i";
                break;
            case "paramedic":
                $certLevel = "paramedic";
                break;
        }
        $options = array('program' => $this->program->id, 'account_type' => $certLevel, 'default_goalset' => 1);
        $defaultGoalset = EntityUtils::getRepository('GoalSet')->findOneBy($options);

        if (is_null($defaultGoalset)) {
            return EntityUtils::getEntity("GoalSet", 1);
        }

        return $defaultGoalset;
    }

    /**
     *  This may be for future use, here for compatibility with
     *  User::canViewData()
     *  Checks if other user can view this User's data.)
     *  Could handle anonymous uses too, but not currently
     *
     * @return bool
     */
    public function dataCanBeViewedBy($viewingUser = null)
    {
        if (is_null($viewingUser)) {
            $viewingUser = User::getLoggedInUser();
        }
        if (is_null($viewingUser)) {
            return false;
        }

        $viewingContext = $viewingUser->getCurrentUserContext();
        $viewingRole = $viewingContext->getRole()->getName();

        // student viewing, must be same student
        if ($viewingRole == 'student') {
            return ($viewingContext->id == $this->user_context->id);
        }

        if ($viewingRole == 'instructor') {
            if (!$viewingContext->hasPermission("View All Data")) {
                return false;
            }
            return ($viewingContext->getProgram()->id == $this->program->id);
        }

        return false;
    }

    /**
     * Determine whether student is considered graduated. This includes if their
     * graduation date has passed, or if they've been marked as graduated by an
     * instructor.
     *
     * @return boolean
     */
    public function isGraduated()
    {
        $graduationDate = new \DateTime($this->getGraduationDate()->format("Y-m-t"));
        $graduationDate->setTime(23, 59, 59);
        $today = new \DateTime("now");

        $graduationFlag = $this->graduation_status->id;

        if ($graduationFlag == 2 || $graduationFlag == 3 || $graduationFlag == 4 || $today > $graduationDate) {
            return true;
        }

        return false;
    }

    /**
     * Activate a serial number and tie it to this student
     *
     * @param \Fisdap\Entity\SerialNumberLegacy
     * @return \Fisdap\Entity\StudentLegacy
     */
    public function activateSerialNumber(SerialNumberLegacy $serial)
    {
        $serial->student_id = $this->id;
        parent::activateSerialNumber($serial);

        return $this;
    }

    /**
     * get the serial number for this student
     *
     * @return \Fisdap\Entity\SerialNumber
     */
    public function getSerialNumber()
    {
        foreach ($this->user->serial_numbers as $serial_number) {
            if ($serial_number->student_id == $this->id) {
                return $serial_number;
            }
        }

        return false;
    }

    public static function getRelevantInstructorIds($studentId)
    {
        $db = \Zend_Registry::get('db');

        $query = "SELECT
					Instructor_id
				FROM
					SectStudents ss,
					ClassSections cs,
					SectInstructors si
				WHERE
					Student_id = $studentId
				AND
					ss.Section_id = Sect_id
				AND
					si.Section_id = ss.Section_id
				AND
					GenEmails = 1";


        $result = $db->fetchCol($query);

        if (count($result) > 0) {
            return $result;
        } else {
            $query = "SELECT Instructor_id FROM StudentData sd, InstructorData id WHERE Student_id = $studentId AND Program_id = ProgramId";

            return $db->fetchCol($query);
        }
    }

    public static function getStudentsWithData($studentIds)
    {
        $repo = EntityUtils::getRepository(get_called_class());
        return $repo->getStudentsWithAllData($studentIds);
        //return $repo->getStudentWithNoData($studentId);
    }

    public static function getShiftsSQL($student_id, $dataReqs = null)
    {
        $query = "SELECT s.*, b.BaseName as base_name, a.AmbServName as site_name FROM ShiftData s INNER JOIN AmbulanceServices a ON a.AmbServ_id = s.AmbServ_id INNER JOIN AmbServ_Bases b on b.Base_id = s.StartBase_id WHERE Student_id = " . $student_id;
        if ($dataReqs) {
            $shiftSites = "'" . implode("','", $dataReqs->shiftSites) . "'";

            $query .= " AND (s.Type IN ($shiftSites) OR a.AmbServ_id IN ($shiftSites))";
            if ($dataReqs->startDate) {
                $query .= " AND s.start_datetime >= '" . $dataReqs->startDate->format("Y-m-d H:i:s") . "'";
            }
            if ($dataReqs->endDate) {
                $query .= " AND s.start_datetime <= '" . $dataReqs->endDate->setTime(23, 59, 59)->format("Y-m-d H:i:s") . "'";
            }
            if ($dataReqs->auditedOrAll) {
                $query .= " AND s.Audited = " . $dataReqs->auditedOrAll;
            }
        }

        return \Zend_Registry::get('db')->query($query)->fetchAll();
    }


    /**
     * Used to retrieve student ids for the Benchmark Goals Report
     * @param string $start_date lower bound of date constraint
     * @param string $end_date upper bound of date constraint
     * @return array
     */
    public function getBenchmarkStudents($start_date, $end_date)
    {
        $query = "select StudentData.Student_id from StudentData, fisdap2_user_roles, SerialNumbers where StudentData.user_role_id = fisdap2_user_roles.id and StudentData.Student_id = SerialNumbers.Student_id and StudentData.good_data = 1 and StudentData.research_consent = 1 and fisdap2_user_roles.certification_level_id = 3 and SerialNumbers.configuration & 1 and fisdap2_user_roles.end_date >= '" . $start_date . "' and fisdap2_user_roles.end_date <= '" . $end_date . "' limit 1000";

        return \Zend_Registry::get('db')->query($query)->fetchAll();
    }

    // gets an array of the
    public function getClassmates()
    {
        $filters = array('graduationYear' => $this->graduation_year,
            'graduationMonth' => $this->graduation_month,
            'certificationLevels' => array($this->getCertification()->id)
        );

        $classmates = EntityUtils::getRepository('User')->getAllStudentsByProgram($this->program->id, $filters);

        $filtered_classmates = array();

        foreach ($classmates as $classmate) {
            // add this student to the list if it's not the user
            if ($classmate['id'] != $this->id) {
                $filtered_classmates[] = $classmate;
            }
        }

        return $filtered_classmates;
    }
}
