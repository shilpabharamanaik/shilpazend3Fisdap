<?php namespace User\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * Entity for program settings
 *
 * @Entity(repositoryClass="Fisdap\Data\Program\Settings\DoctrineProgramSettingsRepository")
 * @Table(name="fisdap2_program_settings")
 */
class ProgramSettings extends EntityBaseClass
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @var ProgramLegacy
     * @OneToOne(targetEntity="ProgramLegacy", inversedBy="program_settings")
     * @JoinColumn(name="program_id", referencedColumnName="Program_id")
     */
    protected $program;

    /**
     * @var bool
     * @Column(type="boolean", nullable=true)
     */
    protected $allow_educator_signoff_signature = false;

    /**
     * @var bool
     * @Column(type="boolean", nullable=true)
     */
    protected $allow_educator_signoff_login = false;

    /**
     * @var bool
     * @Column(type="boolean", nullable=true)
     */
    protected $allow_educator_signoff_email = false;

    /**
     * @var bool
     * @Column(type="boolean", nullable=true)
     */
    protected $allow_educator_signoff_attachment = false;

    /**
     * @var bool
     * @Column(type="boolean", nullable=true)
     */
    protected $allow_educator_shift_audit = false;

    /**
     * @var bool
     * @Column(type="boolean", nullable=true)
     */
    protected $allow_educator_evaluations = false;

    /**
     * @var bool
     * @Column(type="boolean", nullable=true)
     */
    protected $allow_tardy = true;

    /**
     * @var bool
     * @Column(type="boolean", nullable=true)
     */
    protected $allow_absent = true;

    /**
     * @var Timezone
     * @ManyToOne(targetEntity="Timezone", inversedBy="programSettings")
     */
    protected $timezone;

    /**
     * @var bool
     * @Column(type="boolean", nullable=true)
     */
    protected $allow_signoff_on_patient = false;

    /**
     * @var bool
     * @Column(type="boolean", nullable=true)
     */
    protected $allow_signoff_on_shift = false;

    /**
     * @var bool
     * @Column(type="boolean", nullable=true)
     */
    protected $autolock_late_shifts = false;

    /**
     * @var bool
     * @Column(type="boolean", nullable=true)
     */
    protected $include_lab_in_mygoals = true;

    /**
     * @var bool
     * @Column(type="boolean", nullable=true)
     */
    protected $include_field_in_mygoals = true;

    /**
     * @var bool
     * @Column(type="boolean", nullable=true)
     */
    protected $include_clinical_in_mygoals = true;

    /**
     * @var string
     * @Column(type="array", nullable=true)
     */
    protected $subject_types_in_mygoals = array(1);

    /**
     * @var bool
     * @Column(type="boolean", nullable=true)
     */
    protected $practice_skills_field = false;

    /**
     * @var bool
     * @Column(type="boolean", nullable=true)
     */
    protected $practice_skills_clinical = false;

    /**
     * @var bool
     * @Column(type="boolean", nullable=true)
     */
    protected $quick_add_clinical = true;

    // SCHEDULER SETTINGS
    /**
     * @var bool
     * @Column(type="boolean", nullable=true)
     */
    protected $student_view_full_calendar = false;

    /**
     * @var bool
     * @Column(type="boolean", nullable=true)
     */
    protected $student_pick_field = false;

    /**
     * @var bool
     * @Column(type="boolean", nullable=true)
     */
    protected $student_pick_clinical = false;

    /**
     * @Column(type="boolean", nullable=true)
     */
    protected $student_pick_lab = false;

    /**
     * @var int
     * @Column(type="integer")
     */
    protected $student_switch_field = 0;

    /**
     * @var int
     * @Column(type="integer")
     */
    protected $switch_field_needs_permission = 0;

    /**
     * @var int
     * @Column(type="integer")
     */
    protected $student_switch_clinical = 0;

    /**
     * @var int
     * @Column(type="integer")
     */
    protected $switch_clinical_needs_permission = 0;

    /**
     * @var int
     * @Column(type="integer")
     */
    protected $student_switch_lab = 0;

    /**
     * @var int
     * @Column(type="integer")
     */
    protected $switch_lab_needs_permission = 0;

    /**
     * @var Window
     * @ManyToOne(targetEntity="Window")
     */
    protected $default_field_window;

    /**
     * @var Window
     * @ManyToOne(targetEntity="Window")
     */
    protected $default_lab_window;

    /**
     * @var Window
     * @ManyToOne(targetEntity="Window")
     */
    protected $default_clinical_window;

    /**
     * @var bool
     * @Column(type="boolean", nullable=true)
     */
    protected $send_scheduler_student_notifications = true;

    /**
     * @var bool
     * @Column(type="boolean", nullable=true)
     */
    protected $require_shift_evals;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @return boolean
     */
    public function isAllowEducatorSignoffSignature()
    {
        return $this->allow_educator_signoff_signature;
    }


    /**
     * @param boolean $allow_educator_signoff_signature
     */
    public function setAllowEducatorSignoffSignature($allow_educator_signoff_signature)
    {
        $this->allow_educator_signoff_signature = $allow_educator_signoff_signature;
    }


    /**
     * @return boolean
     */
    public function isAllowEducatorSignoffLogin()
    {
        return $this->allow_educator_signoff_login;
    }


    /**
     * @param boolean $allow_educator_signoff_login
     */
    public function setAllowEducatorSignoffLogin($allow_educator_signoff_login)
    {
        $this->allow_educator_signoff_login = $allow_educator_signoff_login;
    }


    /**
     * @return boolean
     */
    public function isAllowEducatorSignoffEmail()
    {
        return $this->allow_educator_signoff_email;
    }


    /**
     * @param boolean $allow_educator_signoff_email
     */
    public function setAllowEducatorSignoffEmail($allow_educator_signoff_email)
    {
        $this->allow_educator_signoff_email = $allow_educator_signoff_email;
    }


    /**
     * @return boolean
     */
    public function isAllowEducatorSignoffAttachment()
    {
        return $this->allow_educator_signoff_attachment;
    }


    /**
     * @param boolean $allow_educator_signoff_attachment
     */
    public function setAllowEducatorSignoffAttachment($allow_educator_signoff_attachment)
    {
        $this->allow_educator_signoff_attachment = $allow_educator_signoff_attachment;
    }


    /**
     * @return boolean
     */
    public function isAllowEducatorShiftAudit()
    {
        return $this->allow_educator_shift_audit;
    }


    /**
     * @param boolean $allow_educator_shift_audit
     */
    public function setAllowEducatorShiftAudit($allow_educator_shift_audit)
    {
        $this->allow_educator_shift_audit = $allow_educator_shift_audit;
    }


    /**
     * @return boolean
     */
    public function isAllowEducatorEvaluations()
    {
        return $this->allow_educator_evaluations;
    }


    /**
     * @param boolean $allow_educator_evaluations
     */
    public function setAllowEducatorEvaluations($allow_educator_evaluations)
    {
        $this->allow_educator_evaluations = $allow_educator_evaluations;
    }


    /**
     * @return boolean
     */
    public function isAllowTardy()
    {
        return $this->allow_tardy;
    }


    /**
     * @param boolean $allow_tardy
     */
    public function setAllowTardy($allow_tardy)
    {
        $this->allow_tardy = $allow_tardy;
    }


    /**
     * @return boolean
     */
    public function isAllowAbsent()
    {
        return $this->allow_absent;
    }


    /**
     * @param boolean $allow_absent
     */
    public function setAllowAbsent($allow_absent)
    {
        $this->allow_absent = $allow_absent;
    }


    /**
     * @return boolean
     */
    public function allowSignoffOnPatient()
    {
        return $this->allow_signoff_on_patient;
    }


    /**
     * @param boolean $allow_signoff_on_patient
     */
    public function setAllowSignoffOnPatient($allow_signoff_on_patient)
    {
        $this->allow_signoff_on_patient = $allow_signoff_on_patient;
    }


    /**
     * @return boolean
     */
    public function allowSignoffOnShift()
    {
        return $this->allow_signoff_on_shift;
    }


    /**
     * @param boolean $allow_signoff_on_shift
     */
    public function setAllowSignoffOnShift($allow_signoff_on_shift)
    {
        $this->allow_signoff_on_shift = $allow_signoff_on_shift;
    }


    /**
     * @return int
     */
    public function lateFieldDeadlineHours()
    {
        return $this->late_field_deadline_hours;
    }


    /**
     * @return int
     */
    public function lateClinicalDeadlineHours()
    {
        return $this->late_clinical_deadline_hours;
    }


    /**
     * @return int
     */
    public function lateLabDeadlineHours()
    {
        return $this->late_lab_deadline_hours;
    }


    /**
     * @return boolean
     */
    public function autolockLateShifts()
    {
        return $this->autolock_late_shifts;
    }


    /**
     * @param boolean $autolock_late_shifts
     */
    public function setAutolockLateShifts($autolock_late_shifts)
    {
        $this->autolock_late_shifts = $autolock_late_shifts;
    }


    /**
     * @return boolean
     */
    public function includeLabInMygoals()
    {
        return $this->include_lab_in_mygoals;
    }


    /**
     * @param boolean $include_lab_in_mygoals
     */
    public function setIncludeLabInMygoals($include_lab_in_mygoals)
    {
        $this->include_lab_in_mygoals = $include_lab_in_mygoals;
    }


    /**
     * @return boolean
     */
    public function includeFieldInMygoals()
    {
        return $this->include_field_in_mygoals;
    }


    /**
     * @param boolean $include_field_in_mygoals
     */
    public function setIncludeFieldInMygoals($include_field_in_mygoals)
    {
        $this->include_field_in_mygoals = $include_field_in_mygoals;
    }


    /**
     * @return boolean
     */
    public function includeClinicalInMygoals()
    {
        return $this->include_clinical_in_mygoals;
    }


    /**
     * @param boolean $include_clinical_in_mygoals
     */
    public function setIncludeClinicalInMygoals($include_clinical_in_mygoals)
    {
        $this->include_clinical_in_mygoals = $include_clinical_in_mygoals;
    }


    /**
     * @return string
     */
    public function getSubjectTypesInMygoals()
    {
        return $this->subject_types_in_mygoals;
    }


    /**
     * @param string $subject_types_in_mygoals
     */
    public function setSubjectTypesInMygoals($subject_types_in_mygoals)
    {
        $this->subject_types_in_mygoals = $subject_types_in_mygoals;
    }


    /**
     * @return boolean
     */
    public function hasPracticeSkillsField()
    {
        return $this->practice_skills_field;
    }


    /**
     * @param boolean $practice_skills_field
     */
    public function setPracticeSkillsField($practice_skills_field)
    {
        $this->practice_skills_field = $practice_skills_field;
    }


    /**
     * @return boolean
     */
    public function hasPracticeSkillsClinical()
    {
        return $this->practice_skills_clinical;
    }


    /**
     * @param boolean $practice_skills_clinical
     */
    public function setPracticeSkillsClinical($practice_skills_clinical)
    {
        $this->practice_skills_clinical = $practice_skills_clinical;
    }


    /**
     * @return boolean
     */
    public function hasQuickAddClinical()
    {
        return $this->quick_add_clinical;
    }


    /**
     * @param boolean $quick_add_clinical
     */
    public function setQuickAddClinical($quick_add_clinical)
    {
        $this->quick_add_clinical = $quick_add_clinical;
    }


    /**
     * @return boolean
     */
    public function studentCanViewFullCalendar()
    {
        return $this->student_view_full_calendar;
    }


    /**
     * @param boolean $student_view_full_calendar
     */
    public function setStudentViewFullCalendar($student_view_full_calendar)
    {
        $this->student_view_full_calendar = $student_view_full_calendar;
    }


    /**
     * @return boolean
     */
    public function studentCanPickField()
    {
        return $this->student_pick_field;
    }


    /**
     * @param boolean $student_pick_field
     */
    public function setStudentPickField($student_pick_field)
    {
        $this->student_pick_field = $student_pick_field;
    }


    /**
     * @return boolean
     */
    public function studentCanPickClinical()
    {
        return $this->student_pick_clinical;
    }


    /**
     * @param boolean $student_pick_clinical
     */
    public function setStudentPickClinical($student_pick_clinical)
    {
        $this->student_pick_clinical = $student_pick_clinical;
    }


    /**
     * @return mixed
     */
    public function studentCanPickLab()
    {
        return $this->student_pick_lab;
    }


    /**
     * @param mixed $student_pick_lab
     */
    public function setStudentPickLab($student_pick_lab)
    {
        $this->student_pick_lab = $student_pick_lab;
    }


    /**
     * @return integer
     */
    public function studentCanSwitchField()
    {
        return $this->student_switch_field;
    }


    /**
     * @param integer $student_switch_field
     */
    public function setStudentSwitchField($student_switch_field)
    {
        $this->student_switch_field = $student_switch_field;
    }


    /**
     * @return integer
     */
    public function switchFieldNeedsPermission()
    {
        return $this->switch_field_needs_permission;
    }


    /**
     * @param integer $switch_field_needs_permission
     */
    public function setSwitchFieldNeedsPermission($switch_field_needs_permission)
    {
        $this->switch_field_needs_permission = $switch_field_needs_permission;
    }


    /**
     * @return integer
     */
    public function studentCanSwitchClinical()
    {
        return $this->student_switch_clinical;
    }


    /**
     * @param integer $student_switch_clinical
     */
    public function setStudentSwitchClinical($student_switch_clinical)
    {
        $this->student_switch_clinical = $student_switch_clinical;
    }


    /**
     * @return integer
     */
    public function switchClinicalNeedsPermission()
    {
        return $this->switch_clinical_needs_permission;
    }


    /**
     * @param integer $switch_clinical_needs_permission
     */
    public function setSwitchClinicalNeedsPermission($switch_clinical_needs_permission)
    {
        $this->switch_clinical_needs_permission = $switch_clinical_needs_permission;
    }


    /**
     * @return integer
     */
    public function studentCanSwitchLab()
    {
        return $this->student_switch_lab;
    }


    /**
     * @param integer $student_switch_lab
     */
    public function setStudentSwitchLab($student_switch_lab)
    {
        $this->student_switch_lab = $student_switch_lab;
    }


    /**
     * @return integer
     */
    public function switchLabNeedsPermission()
    {
        return $this->switch_lab_needs_permission;
    }


    /**
     * @param integer $switch_lab_needs_permission
     */
    public function setSwitchLabNeedsPermission($switch_lab_needs_permission)
    {
        $this->switch_lab_needs_permission = $switch_lab_needs_permission;
    }


    /**
     * @return Window
     */
    public function getDefaultFieldWindow()
    {
        return $this->default_field_window;
    }


    /**
     * @param Window $default_field_window
     */
    public function setDefaultFieldWindow(Window $default_field_window)
    {
        $this->default_field_window = $default_field_window;
    }


    /**
     * @return Window
     */
    public function getDefaultLabWindow()
    {
        return $this->default_lab_window;
    }


    /**
     * @param Window $default_lab_window
     */
    public function setDefaultLabWindow(Window $default_lab_window)
    {
        $this->default_lab_window = $default_lab_window;
    }


    /**
     * @return Window
     */
    public function getDefaultClinicalWindow()
    {
        return $this->default_clinical_window;
    }


    /**
     * @param Window $default_clinical_window
     */
    public function setDefaultClinicalWindow(Window $default_clinical_window)
    {
        $this->default_clinical_window = $default_clinical_window;
    }

    /**
     * @return boolean
     */
    public function sendSchedulerStudentNotifications()
    {
        return $this->send_scheduler_student_notifications;
    }


    /**
     * @param boolean $send_scheduler_student_notifications
     */
    public function setSendSchedulerStudentNotifications($send_scheduler_student_notifications)
    {
        $this->send_scheduler_student_notifications = $send_scheduler_student_notifications;
    }


    /**
     * @param $type
     *
     * @return string
     */
    public function getChangeRequestDescription($type)
    {
        $description = "";
        $request_types = RequestType::getAll();

        foreach ($request_types as $request_type) {
            $can = ($this->{'student_switch_'.$type} & $request_type->bit_value ? 1 : 0);
            $perm = ($this->{'switch_'.$type.'_needs_permission'} & $request_type->bit_value ? 1 : 0);

            $description .= "Students";
            $description .= ($can) ? " can " : " cannot ";
            $description .= $request_type->name;
            $description .= ($perm) ? " with " : " without ";
            $description .= ($can) ? "permission. " : ". ";
        }

        return $description;
    }


    /**
     * Set the timezone for this program
     *
     * @param int|Timezone $value
     *
     * @return ProgramSettings
     * @codeCoverageIgnore
     * @deprecated
     */
    public function set_timezone($value)
    {
        $this->timezone = self::id_or_entity_helper($value, "Timezone");
        return $this;
    }


    /**
     * @param Timezone $timezone
     */
    public function setTimezone(Timezone $timezone)
    {
        $this->timezone = $timezone;
    }

    /*
    public function toArray()
    {
        return [
            "allowShiftAudit" => $this->isAllowEducatorShiftAudit(),
            "allowEvaluations" => $this->isAllowEducatorEvaluations(),
            "allowTardy" => $this->isAllowTardy(),
            "allowAbsent" => $this->isAllowAbsent(),
            "timezoneId" => ($this->timezone != null ? $this->timezone->getId() : null),
            "quickAddClinical" => $this->hasQuickAddClinical(),
            "signoff" => [
                "allowWithSignature" => $this->isAllowEducatorSignoffSignature(),
                "allowWithLogin" => $this->isAllowEducatorSignoffLogin(),
                "allowWithEmail" => $this->isAllowEducatorSignoffEmail(),
                "allowWithAttachment" => $this->isAllowEducatorSignoffAttachment(),
                "allowOnPatient" => $this->allowSignoffOnPatient(),
                "allowOnShift" => $this->allowSignoffOnShift()
            ],
            "myGoals" => [
                "includeLab" => $this->includeLabInMygoals(),
                "includeField" => $this->includeFieldInMygoals(),
                "includeClinical" => $this->includeClinicalInMygoals(),
                "subjectTypes" => $this->getSubjectTypesInMygoals()
            ],
            "practiceSkills" => [
                "field" => $this->hasPracticeSkillsField(),
                "clinical" => $this->hasPracticeSkillsClinical()
            ],
            "emailNotifications" => [
                "sendCriticalThinking" => $this->sendCriticalThinking(),
                "sendLateShift" => $this->sendLateShift(),
                "sendSchedulerStudentNotifications" => $this->sendSchedulerStudentNotifications()
            ],
            "shiftDeadlines" => [
                "lateFieldDeadlineHours" => $this->program->getLateFieldDeadline(),       // missing?
                "lateClinicalDeadlineHours" => null,    // missing?
                "lateLabDeadlineHours" => null,         // missing?
                "autolockLateShifts" => $this->autolockLateShifts()
            ],
            "studentPermissions" => [
                "pickLab" => $this->studentCanPickLab(),
                "createLab" => null,                    // missing?
                "pickClinical" => $this->studentCanPickClinical(),
                "createClinical" => null,               // missing?
                "pickField" => $this->studentCanPickField(),
                "createField" => null,                  // missing?
                "viewFullCalendar" => $this->studentCanViewFullCalendar(),
                "allowAbsentWithPermission" => null,    // missing?
                "includeNarrative" => null,             // missing?
                "switchField" => $this->studentCanSwitchField(),
                "switchFieldNeedsPermission" => $this->switchFieldNeedsPermission(),
                "switchClinical" => $this->studentCanSwitchClinical(),
                "switchClinicalNeedsPermission" => $this->switchClinicalNeedsPermission(),
                "switchLab" => $this->studentCanSwitchLab(),
                "switchLabNeedsPermission" => $this->switchLabNeedsPermission()
            ],
            "commerce" => [
                "orderPermissionId" => null,            // missing?
                "requiresPurchaseOrder" => null         // missing?
            ]
        ];
    }
    */
}
