<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                                           *
*        Copyright (C) 1996-2011.  This is an unpublished work of           *
*                         Headwaters Software, Inc.                         *
*                            ALL RIGHTS RESERVED                            *
*        This program is a trade secret of Headwaters Software, Inc. and    *
*        it is not to be copied, distributed, reproduced, published, or     *
*        adapted without prior authorization of Headwaters Software, Inc.   *
*                                                                           *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

/**
 * This produces a modal form for editing shifts
 */

/**
 * @package    SkillsTracker
 * @subpackage Forms
 */
class SkillsTracker_Form_Shift extends SkillsTracker_Form_Modal
{

    /**
     * @var \Fisdap\Entity\ShiftLegacy
     */
    public $shift;

    /**
     * @var \Fisdap\Entity\Student
     */
    public $student;

    /**
     * @var integer
     */
    private $studentId;

    /**
     * @var integer
     */
    private $programId;

    /**
     * @var mixed string | array
     */
    public $types;

    /**
     * @var \Fisdap\Entity\User
     */
    public $user;

    /**
     * @var \Fisdap\Entity\UserContext
     */
    public $currentContext;

    /**
     * @var boolean
     */
    public $has_scheduler;

    /**
     * @var boolean
     */
    public $show_attendence = false;

    /**
     * @var string
     */
    public $schedulerUrl;

    /**
     * @param integer $shiftId the id of the shift to edit
     * @param integer $studentId
     * @param integer $programId
     * @param mixed $types string | array of strings representing shift types
     * @param $options mixed additional Zend_Form options
     */
    public function __construct($shiftId = null, $studentId = null, $programId = null, $types = null, $options = null)
    {
        if (isset($shiftId) && $shiftId!='') {
            $shiftId = $shiftId;
            unset($_SESSION['shiftId']);
        } elseif (isset($_SESSION['shiftId']) && $_SESSION['shiftId']!='') {
            $shiftId = $_SESSION['shiftId'];
        }
        $this->user = \Fisdap\Entity\User::getLoggedInUser();
        $this->currentContext = $this->user->getCurrentUserContext();
        $this->shift = \Fisdap\EntityUtils::getEntity('ShiftLegacy', $shiftId);

        if ($studentId) {
            $this->studentId = $studentId;
        } elseif (!$this->currentContext->isInstructor()) {
            $this->studentId = $this->currentContext->getRoleData()->id;
        }

        $this->student = \Fisdap\EntityUtils::getEntity('StudentLegacy', $this->studentId);
        $this->programId = $programId;
        $this->types = $types;

        // if this is an existing, past and future shift and the student has skillstracker
        // add !$this->shift->isFuture() condition to get attendence only for past students
        if ($this->shift->id && $this->shift->student->user_context->getPrimarySerialNumber()->hasSkillsTracker()) {
            $this->show_attendence = true;
        }

        $siteType = (is_array($this->types)) ? $this->types[0] : $this->types;
        $this->schedulerUrl = ($siteType) ? "/scheduler/shift/new/type/$siteType" : "/scheduler";

        parent::__construct($options);
    }

    public function init()
    {
        parent::init();

        $this->addJsFile("/js/library/SkillsTracker/Form/edit-student-shift-modal.js");
        $this->addCssFile("/css/library/SkillsTracker/Form/edit-student-shift-modal.css");
        $this->addJsFile("/js/jquery.chosen.relative.js");
        $this->addCssFile("/css/jquery.chosen.css");


        // Limit the shift types to the types the student has permission to create.
        $sites_in_opt_groups = false;

        if ($this->shift->id) {
            $sites_in_opt_groups = true;

            if (!$this->currentContext->isInstructor()) {

                // Which tpyes can I create?
                $allowed_types = array();

                $program = $this->currentContext->getProgram();

                if ($program->can_students_create_lab) {
                    $allowed_types[] = "lab";
                }

                if ($program->can_students_create_field) {
                    $allowed_types[] = "field";
                }

                if ($program->can_students_create_clinical) {
                    $allowed_types[] = "clinical";
                }

                $this->types = $allowed_types;
            } else {
                // If it's an insrtuctor, give them all three types
                $this->types = array("lab", "clinical", "field");
            }
        }

        $site = new Fisdap_Form_Element_Sites('site', $this->types, null, $sites_in_opt_groups);


        // Finally, make sure the site for THIS shift is available.
        if ($this->shift->id) {
            if (!in_array($this->shift->type, $this->types)) {
                if (count($allowed_types) == 0) {
                    $current_options = array();
                } else {
                    $current_options = $site->getMultiOptions();
                }

                $current_options[ucfirst($this->shift->type)][$this->shift->site->id] = $this->shift->site->name;

                $site->setMultiOptions($current_options);
            }
        }

        if ($this->shift->id) {
            $site->setValue($this->shift->site->id);
        }

        $siteType = (is_array($this->types)) ? $this->types[0] : $this->types;

        if (is_array($this->types)) {
            $siteLabel = "Site:";
        } else {
            $siteLabel = ($siteType) ? ucfirst($siteType) . " site:" : "Site:";
        }

        $siteAttribs = $this->getClassAndStyleAttribs();
        $siteAttribs['class'] .= ' sites';

        $site->setLabel($siteLabel)
            ->setAttribs($siteAttribs);

        $baseErrorMsg = "Your instructor must first create a base/dept for this site before a shift can be created.";

        if ($this->currentContext->isInstructor()) {
            $baseErrorMsg = "You must first create a base/dept for this site before a shift can be created.";
        }

        $base = new Fisdap_Form_Element_Bases('base');
        $baseAttribs = $this->getClassAndStyleAttribs();
        $baseAttribs['class'] .= ' bases';
        $base->setLabel('Base/Department')
            ->setRequired(true)
            ->addErrorMessage($baseErrorMsg)
            ->setAttribs($baseAttribs);

        if ($this->shift->id) {
            $base->setMultiOptions(\Fisdap\Entity\BaseLegacy::getBases($this->shift->site->id, $this->shift->student->user_context->getProgram()->id));
        }

        $date = new ZendX_JQuery_Form_Element_DatePicker('date');
        $date->setLabel('Date:')
            ->setRequired(true)
            ->addValidator('Regex', false, array('pattern' => '/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/'))
            ->setDescription("(required)")
            ->addErrorMessage("Please enter a date with the format MM/DD/YYYY.");

        if ($this->shift->id && !$this->shift->isStudentCreated() && !$this->currentContext->isInstructor()) {
            $site->setAttrib('disabled', 'disabled')->clearValidators()->setRequired(false);
            $base->setAttrib('disabled', 'disabled')->clearValidators()->setRequired(false);
            $date->setAttrib('disabled', 'disabled')->clearValidators()->setRequired(false);
        }

        $time = new Zend_Form_Element_Text('time');
        $time->setLabel('Start Time:')
            ->setRequired(true)
            ->setAttrib('maxlength', '4')
            ->addFilter(new \Fisdap_Filter_MilitaryTime())
            ->addValidator(new \Fisdap_Validate_MilitaryTime())
            ->setDescription("(required, 0000-2359)")
            ->addErrorMessage("Tell us when your shift starts using military time.  For example, 3:10 PM would be entered as 1510.");


        // Get the current program
        $program = $this->currentContext->getProgram();

        $attendence = new Zend_Form_Element_Radio('attendence');

        if ($this->currentContext->isInstructor()) {
            $attendenceOptions = \Fisdap\Entity\ShiftAttendence::getFormOptionsInstructor();
        } else {
            $attendenceOptions = \Fisdap\Entity\ShiftAttendence::getFormOptionsByProgram($program->id);
        }

        // Slightly rename the options for this field
        foreach ($attendenceOptions as $id => $name) {
            $attendenceOptions[$id] = "I was " . strtolower($name);
        }

        $attendence->setMultiOptions($attendenceOptions);

        $attendenceComments = new Zend_Form_Element_Textarea('attendenceComments');
        $attendenceComments->setLabel('Comments about attendance')
            ->setAttribs(array('rows' => '5', 'cols' => '30'));

        $hours = new Zend_Form_Element_Text('hours');
        $hours->setLabel('Duration:')
            ->setRequired(true)
            ->addValidator('Float')
            ->addValidator('Between', false, array('min' => '0.0', 'max' => '120.0', 'inclusive' => true))
            ->setDescription('(required, numbers only)')
            ->addErrorMessage("Please check the length of your shift and enter a number no greater than 120 hours (example: \"3.5\" hours).");

        $shiftId = new Zend_Form_Element_Hidden('shiftId');
        $locked = new Zend_Form_Element_Hidden('locked');
        $hasSites = new Zend_Form_Element_Hidden('hasSites');

        // if this is a new shift but there are no sites available, set the hasSites flags to 0
        if (count(\Fisdap\Entity\SiteLegacy::getSites($program->id, $this->types)) == 0 && !$this->shift->id) {
            $hasSites->setValue('0');
        } else {
            $hasSites->setValue('1');
        }

        $studentId = new Zend_Form_Element_Hidden('studentId');
        $studentId->setAttrib('id', 'hidden-modal-student-id');

        if ($this->studentId) {
            $studentId->setValue($this->studentId);
            $student = \Fisdap\EntityUtils::getEntity('StudentLegacy', $this->studentId);
            $this->has_scheduler = $student->user_context->getPrimarySerialNumber()->hasScheduler();
        }

        $this->addElements(array($site, $base, $date, $time, $hours, $attendence, $attendenceComments, $shiftId, $locked, $studentId, $hasSites));

        //$this->setDecorators(self::$formDecorators);
        $this->setElementDecorators(self::$elementDecorators, array('save', 'cancel', 'attendence', 'date', 'shiftId'), false);
        $this->setElementDecorators(self::$checkboxDecorators, array('attendence'), true);
        $this->setElementDecorators(self::$formJQueryElements, array('date'), true);
        $this->setElementDecorators(self::$hiddenElementDecorators, array('shiftId', 'locked', 'studentId', 'hasSites'), true);

        $formName = "shiftDialog";

        // Switch name of Modal Dialog if we're creating a shift or editing one.
        $modalTitle = ($this->shift->id) ? "Edit shift" : "Create shift";

        // Set decorators
        $this->setDecorators(array(
            'PrepareElements',
            array('ViewScript', array('viewScript' => "shiftForm.phtml")),
            'Form',
            array('DialogContainer', array(
                'id' => $formName,
                'class' => $formName,
                'jQueryParams' => array(
                    'tabPosition' => 'top',
                    'modal' => true,
                    'autoOpen' => false,
                    'resizable' => false,
                    'draggable' => false,
                    'width' => 800,
                    'title' => $modalTitle
                ),
            )),
        ));

        // Set some defaults
        if ($this->shift->id) {
            $this->setDefaults(array(
                'base' => $this->shift->base->id,
                'date' => $this->shift->start_datetime->format("m/d/Y"),
                'time' => $this->shift->start_datetime->format("Hi"),
                'attendence' => $this->shift->attendence->id,
                'attendenceComments' => $this->shift->attendence_comments,
                'hours' => $this->shift->hours,
                'shiftId' => $this->shift->id,
                'locked' => $this->shift->locked,
                'studentId' => $this->shift->student->id,
            ));
        }
    }

    /**
     * Validate the form, if valid, save the shift, if not, return the error msgs
     *
     * @param array $data the POSTed data
     * @return mixed either boolean true, or an array of error messages
     */
    public function process($data)
    {
        if ($this->isValid($data)) {
            $values = $this->getValues($data);

            if ($values['shiftId']) {
                $shift = \Fisdap\EntityUtils::getEntity('ShiftLegacy', $values['shiftId']);
            } else {
                $shift = \Fisdap\EntityUtils::getEntity('ShiftLegacy');
                $shift->set_creator($this->currentContext);
            }

            // Only save these values if the shift exists and it's a quick-added shift
            if (!$this->shift->id || $this->shift->isQuickAdd()) {
                $shift->site = \Fisdap\EntityUtils::getEntity('SiteLegacy', $values['site']);
                $shift->base = \Fisdap\EntityUtils::getEntity('BaseLegacy', $values['base']);
                $shift->type = $shift->site->type;
            } elseif ($this->currentContext->isInstructor()) {

                // Reset the event_id if site, base, or start date has changed

                if ($values['site'] != $shift->site->id || $values['base'] != $shift->base->id || $values['date'] != $shift->start_datetime->format('m/d/Y')) {

                    // we need to remove the slot assignment (but not delete the associated shift) by calling removeFromEvent to disconnect the shift from the event.
                    $shift->removeFromEvent();

                    // reset the creator of the shift.
                    $shift->set_creator($this->currentContext);
                }

                $shift->site = \Fisdap\EntityUtils::getEntity('SiteLegacy', $values['site']);
                $shift->base = \Fisdap\EntityUtils::getEntity('BaseLegacy', $values['base']);
                $shift->type = $shift->site->type;
            }


            $shift->start_datetime = \DateTime::createFromFormat('m/d/Y', $values['date']);
            $shift->start_time = $values['time'];
            $shift->calculateEndTime();

            $time = Util_FisdapTime::create_from_military_time($values['time']);

            /*Originally, we were setting the legacy field (start_time) to match the new field (start_datetime), but because of sync issues with scheduler
            * we've had to modify other areas of skills-tracker to look at the legacy field for start/stop times. As a result, we're now setting the
            * "new" field to match the legacy field.
            */
            $shift->start_datetime->setTime($time->get_hours(), $time->get_minutes());

            //$shift->start_datetime = clone($shift->start_datetime->setTime($time->get_hours(), $time->get_minutes()));
            $shift->hours = $values['hours'];

            // Only set the attendance and comments if the shift is being edited.
            if ($shift->id) {
                $oldAttendance = $shift->attendence->id;
                $newAttendance = $values['attendence'];
                $shift->attendence = $values['attendence'];
                $shift->attendence_comments = $values['attendenceComments'];

                // if the shift is being changed from attended/tardy to absent (with or without permission), lock it
                if ($oldAttendance <= 2 && $newAttendance >= 3) {
                    $shift->lockShift(true);
                }
            }

            if (is_null($shift->student)) {
                if ($this->studentId == null) {
                    $shift->student = $this->currentContext->getRoleData();
                } else {
                    $shift->student = \Fisdap\EntityUtils::getEntity('StudentLegacy', $this->studentId);
                }
            }

            $shift->save();
            return $shift->id;
        }

        return $this->getMessages();
    }

    /**
     * Returns class and style attributes for either standard or mobile interfaces
     *
     * @return array With class and style keys, or empty for mobile
     */
    public function getClassAndStyleAttribs()
    {
        return array(
            "class" => "chzn-select",
            "style" => "width:350px"
        );
    }
}
