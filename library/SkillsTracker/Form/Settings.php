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
 * Preceptor Signoff Form
 */

/**
 * @package    SkillsTracker
 * @subpackage Forms
 */
class SkillsTracker_Form_Settings extends Fisdap_Form_Base
{
    // These track and store the various elements for the different available
    // types.
    public $airwayElements = array();
    public $ivElements = array();
    public $cardiacElements = array();
    public $medElements = array();
    public $otherElements = array();
    public $labElements = array();
    public $groups = array();
    public $groupsToDisplay;
    public $formElements = array();
    public $practiceCategoryTable;
    public $isStaff = false;
    public $usingClinicalPractice = false;
    public $hasNewPracticeCategory;
    public $hasNewPPCP = true;
    public $subjectTypes = array();
    private $ppcpSkillsheets = array(607,653,631,656,657,658,659,644,645,646,647,648,649,650,651,608,609,616,624,625,626,628,629,630,632,675,677,634,635,636,637,652,655,654,627);

    /**
     * @param $options mixed additional Zend_Form options
     */
    public function __construct($practiceCategoryTable = null, $options = null)
    {
        $this->practiceCategoryTable =  new SkillsTracker_View_Helper_PracticeCategoryTable();
        $this->isStaff = \Fisdap\Entity\User::getLoggedInUser()->isStaff();
        $program = \Fisdap\Entity\ProgramLegacy::getCurrentProgram();
        $this->hasNewPracticeCategory = count(\Fisdap\EntityUtils::getRepository("PracticeCategory")->findBy(array("program" => $program, "name" => "History Taking and Physical Examination")));
        $definitions = \Fisdap\EntityUtils::getRepository('PracticeDefinition')->getProgramDefinitions($program);
        foreach ($definitions as $definition) {
            if (in_array($definition->skillsheet->id, $this->ppcpSkillsheets)) {
                $this->hasNewPPCP = false;
                break;
            }
        }
        $this->usingClinicalPractice = $program->program_settings->practice_skills_clinical;

        parent::__construct($options);
    }

    public function init()
    {
        parent::init();

        $this->addJsFile("/js/library/SkillsTracker/Form/settings.js");
        $this->addJsFile("/js/jquery.chosen.js");
        $this->addJsFile("/js/jquery.sliderCheckbox.js");
        $this->addCssFile("/css/library/SkillsTracker/Form/settings.css");
        $this->addCssFile("/css/jquery.chosen.css");
        $this->addCssFile("/css/jquery.sliderCheckbox.css");

        $program = \Fisdap\EntityUtils::getEntity('ProgramLegacy', \Fisdap\Entity\User::getLoggedInUser()->getProgramId());

        // Schedules block

        $scheduleAllowLabShifts = new Zend_Form_Element_Checkbox('can_students_create_lab');
        $scheduleAllowClinicalShifts = new Zend_Form_Element_Checkbox('can_students_create_clinical');
        $scheduleAllowFieldShifts = new Zend_Form_Element_Checkbox('can_students_create_field');

        $scheduleAbsent = new Zend_Form_Element_Checkbox('absent');
        $scheduleAbsentWithPermission = new Zend_Form_Element_Checkbox('absent_with_permission');
        $scheduleTardy = new Zend_Form_Element_Checkbox('tardy');

        $this->addElements(array($scheduleAllowLabShifts, $scheduleAllowClinicalShifts, $scheduleAllowFieldShifts, $scheduleAbsent, $scheduleAbsentWithPermission, $scheduleTardy));

        // Emails block
        $emailWarnWhenLateToggle = new Zend_Form_Element_Checkbox('send_late_shift_emails');
        $lockShiftsWhenLateToggle = new Zend_Form_Element_Checkbox('autolock_late_shifts');


        $emailWarnWhenLateLabValue = new Zend_Form_Element_Text('late_lab_deadline');
        $emailWarnWhenLateLabValue->setOptions(array('size' => 5, 'maxlength' => 5));

        $emailWarnWhenLateClinicalValue = new Zend_Form_Element_Text('late_clinical_deadline');
        $emailWarnWhenLateClinicalValue->setOptions(array('size' => 5, 'maxlength' => 5));

        $emailWarnWhenLateFieldValue = new Zend_Form_Element_Text('late_field_deadline');
        $emailWarnWhenLateFieldValue->setOptions(array('size' => 5, 'maxlength' => 5));

        $emailCriticalThinkingPrompts = new Zend_Form_Element_Checkbox('send_critical_thinking_emails');

        $this->addElements(array($emailWarnWhenLateToggle, $lockShiftsWhenLateToggle, $emailWarnWhenLateLabValue, $emailWarnWhenLateClinicalValue, $emailWarnWhenLateFieldValue, $emailCriticalThinkingPrompts));

        // Documentation block
        $allowStudentNarrative = new Zend_Form_Element_Checkbox('include_narrative');
        $allowQuickAddClinical = new Zend_Form_Element_Checkbox('quick_add_clinical');
        $requireEvals = new Zend_Form_Element_Checkbox('require_evals');
        $this->addElements(array($allowStudentNarrative, $allowQuickAddClinical, $requireEvals));

        //Staff only settings
        $practiceSkillsField = new Zend_Form_Element_Checkbox('practice_skills_field');
        $practiceSkillsClinical = new Zend_Form_Element_Checkbox('practice_skills_clinical');

        $this->addElements(array($practiceSkillsField, $practiceSkillsClinical));

        // lab practice block
        $skills = \Fisdap\EntityUtils::getRepository('PracticeSkill')->getAllFormOptions(true);
        $skills['Airway']['0airway_management'] = "Airway Management";
        ksort($skills);
        ksort($skills['Airway']);

        $rawGroups = \Fisdap\EntityUtils::getRepository('CertificationLevel')->getAllCertificationLevelInfo(\Fisdap\Entity\ProgramLegacy::getCurrentProgram()->profession->id);
        $count = 0;
        foreach ($rawGroups as $group) {
            $group['categories'] = \Fisdap\EntityUtils::getRepository('PracticeCategory')->getAllByProgram($program->id, $group['id']);
            $this->groups[] = $group;
        }
        ksort($this->groups);

        foreach ($this->groups as $goalGroup) {
            foreach ($goalGroup['categories'] as $practiceCat) {
                $categoryName = new Zend_Form_Element_Text('category' . $practiceCat->id . '_cat_name');

                foreach ($practiceCat->practice_definitions as $practiceDef) {
                    // active checkbox
                    $active = new Zend_Form_Element_Checkbox('category' . $practiceCat->id . '_definition' . $practiceDef->id . '_active');
                    $active->setAttribs(array("class" => "slider-checkbox"));

                    // name checkbox
                    $defName = new Zend_Form_Element_Text('category' . $practiceCat->id . '_definition' . $practiceDef->id . '_name');
                    $defName->setAttribs(array("class" => 'def-name-input'));

                    // peer goal textbox
                    $peerGoal = new Zend_Form_Element_Text('category' . $practiceCat->id . '_definition' . $practiceDef->id . '_peer');
                    $peerGoal->setOptions(array('maxlength' => 5));

                    // instructor goal textbox
                    $instructorGoal = new Zend_Form_Element_Text('category' . $practiceCat->id . '_definition' . $practiceDef->id . '_instructor');
                    $instructorGoal->setOptions(array('maxlength' => 5));

                    // eureka window textbox
                    $eurekaWindow = new Zend_Form_Element_Text('category' . $practiceCat->id . '_definition' . $practiceDef->id . '_eureka_window');
                    $eurekaWindow->setOptions(array('maxlength' => 5));

                    // eureka goal textbox
                    $eurekaGoal = new Zend_Form_Element_Text('category' . $practiceCat->id . '_definition' . $practiceDef->id . '_eureka_goal');
                    $eurekaGoal->setOptions(array('maxlength' => 5));

                    $skillSelect = new Zend_Form_Element_Select('category' . $practiceCat->id . '_definition' . $practiceDef->id . '_practice_skills');
                    $skillSelect->setMultiOptions($skills)
                        ->setAttribs(array("class" => "chzn-select",
                            "data-placeholder" => "Practice Skills",
                            "style" => "width:300px",
                            "multiple" => "multiple",
                            "tabindex" => count($skills)));
                    $practice_skills = $practiceDef->getPracticeSkillIds();
                    if ($practiceDef->airway_management_credit === true) {
                        $practice_skills[] = '0airway_management';
                    }
                    $skillSelect->setValue($practice_skills);


                    // add 'em
                    $this->addElements(array($active, $defName, $peerGoal, $instructorGoal, $eurekaWindow, $eurekaGoal, $skillSelect));

                    $this->setDefaults(array(
                        'category' . $practiceCat->id . '_definition' . $practiceDef->id . '_active' => $practiceDef->active,
                        'category' . $practiceCat->id . '_definition' . $practiceDef->id . '_name' => $practiceDef->name,
                        'category' . $practiceCat->id . '_definition' . $practiceDef->id . '_peer' => $practiceDef->peer_goal,
                        'category' . $practiceCat->id . '_definition' . $practiceDef->id . '_instructor' => $practiceDef->instructor_goal,
                        'category' . $practiceCat->id . '_definition' . $practiceDef->id . '_eureka_window' => $practiceDef->eureka_window,
                        'category' . $practiceCat->id . '_definition' . $practiceDef->id . '_eureka_goal' => $practiceDef->eureka_goal
                    ));

                    // add them to our array of elements - the view helper will need this later
                    $this->formElements[$practiceCat->id][$practiceDef->id] = array('active' => $active,
                        'defName' => $defName,
                        'peerGoal' => $peerGoal,
                        'instructorGoal' => $instructorGoal,
                        'eurekaWindow' => $eurekaWindow,
                        'eurekaGoal' => $eurekaGoal,
                        'skillSelect' => $skillSelect);
                }

                $this->addElements(array($categoryName));

                $this->setDefaults(array('category' . $practiceCat->id . '_cat_name' => $practiceCat->name));
            }
        }



        // Sign off and Auditing block
        $educatorsCanAudit = new Zend_Form_Element_Checkbox('allow_educator_shift_audit');
        $educatorsCanSignoff = new Zend_Form_Element_Checkbox('disable_educator_signoff');
        $signOffLocation = new Zend_Form_Element_Radio('signoff_location');
        $signOffLocation->setMultiOptions(array(
            'patient' => 'Patient',
            'shift' => 'Shift'))
            ->setSeparator('<br>');
        $signOffPatientsRunToggleSignature = new Zend_Form_Element_Checkbox('allow_educator_signoff_signature');
        $signOffPatientsRunToggleLogin = new Zend_Form_Element_Checkbox('allow_educator_signoff_login');
        //$signOffPatientsRunToggleEmail = new Zend_Form_Element_Checkbox('allow_educator_signoff_email');
        $allowEducatorEvaluations = new Zend_Form_Element_Checkbox('allow_educator_evaluations');
        $signOffPatientsRunToggleAttachment = new Zend_Form_Element_Checkbox('allow_educator_signoff_attachment');

        $this->addElements(array($educatorsCanSignoff, $signOffPatientsRunToggleSignature,
            $signOffPatientsRunToggleLogin,
            //$signOffPatientsRunToggleEmail,
            $educatorsCanAudit,
            $allowEducatorEvaluations,
            $signOffPatientsRunToggleAttachment,
            $signOffLocation));

        // My Fisdap Goals Widget settings
        $includeLabInMygoals = new Zend_Form_Element_Checkbox('include_lab_in_mygoals');
        $includeClinicalInMygoals = new Zend_Form_Element_Checkbox('include_clinical_in_mygoals');
        $includeFieldInMygoals = new Zend_Form_Element_Checkbox('include_field_in_mygoals');
        $this->addElements(array($includeLabInMygoals, $includeClinicalInMygoals, $includeFieldInMygoals));

        // figure out all the subject types we use and make a check box for each
        $this->subjectTypes = \Fisdap\Entity\Subject::getSelectOptions();
        $this->addCheckboxGroup($this->subjectTypes, "subject_type", $program->program_settings->subject_types_in_mygoals);

        // Add in the timezone settings.
        $timezoneElement = new Zend_Form_Element_Select('program_timezone');
        $timezoneElement->setMultiOptions(\Fisdap\Entity\Timezone::getFormOptions());
        $this->addElement($timezoneElement);

        // Some misc buttonage...
        $cancel = new Zend_Form_Element_Button('Cancel');
        $cancel->setOptions(array('id' => 'settings-cancel'));

        $save = new Zend_Form_Element_Submit('Save');
        $save->setOptions(array('id' => 'settings-save'));

        $this->addElements(array($cancel, $save));

        // Reset all of the view helpers to just output the element and no
        // chromage.
        $this->setElementDecorators(array('ViewHelper'), null, false);

        // add all the procedure checkboxes for the customization tab
        $this->airwayElements = $this->createProcedureElements('AirwayProcedure');
        $this->cardiacElements = $this->createProcedureElements('CardiacProcedure');
        $this->ivElements = $this->createProcedureElements('IvProcedure');
        $this->medElements = $this->createProcedureElements('MedType');
        $this->otherElements = $this->createProcedureElements('OtherProcedure');
        $this->labElements = $this->createProcedureElements('LabAssessment');

        //Set the decorators for this form
        $this->setDecorators(array(
            'PrepareElements',
            array('ViewScript', array('viewScript' => "settings/settingsForm.phtml")),
            'Form',
        ));


        // Set up the defaults for the form...
        $this->setDefaults(array(
            'can_students_create_lab' => $program->can_students_create_lab,
            'can_students_create_clinical' => $program->can_students_create_clinical,
            'can_students_create_field' => $program->can_students_create_field,

            'absent' => $program->program_settings->allow_absent,
            'tardy' => $program->program_settings->allow_tardy,
            'absent_with_permission' => $program->allow_absent_with_permission,

            'send_late_shift_emails' => $program->send_late_shift_emails,
            'autolock_late_shifts' => $program->program_settings->autolock_late_shifts,
            'late_lab_deadline' => $program->late_lab_deadline,
            'late_clinical_deadline' => $program->late_clinical_deadline,
            'late_field_deadline' => $program->late_field_deadline,

            'send_critical_thinking_emails' => $program->send_critical_thinking_emails,

            'include_narrative' => $program->include_narrative,
            'quick_add_clinical' => $program->program_settings->quick_add_clinical,
            'require_evals' => $program->program_settings->require_shift_evals,


            'allow_educator_signoff_signature' => $program->program_settings->allow_educator_signoff_signature,
            'allow_educator_signoff_login' => $program->program_settings->allow_educator_signoff_login,
            'allow_educator_signoff_email' => $program->program_settings->allow_educator_signoff_email,
            'allow_educator_signoff_attachment' => $program->program_settings->allow_educator_signoff_attachment,
            'allow_educator_shift_audit' => $program->program_settings->allow_educator_shift_audit,

            'allow_educator_evaluations' => $program->program_settings->allow_educator_evaluations,

            'program_timezone' => $program->program_settings->timezone->id,

            'include_lab_in_mygoals' => $program->program_settings->include_lab_in_mygoals,
            'include_clinical_in_mygoals' => $program->program_settings->include_clinical_in_mygoals,
            'include_field_in_mygoals' => $program->program_settings->include_field_in_mygoals,

            'practice_skills_field' => $program->program_settings->practice_skills_field,
            'practice_skills_clinical' => $program->program_settings->practice_skills_clinical,
        ));

        if ($program->program_settings->allow_signoff_on_patient) {
            $this->setDefault('signoff_location', 'patient');
        } elseif ($program->program_settings->allow_signoff_on_shift) {
            $this->setDefault('signoff_location', 'shift');
        } else {
            $this->setDefault('disable_educator_signoff', true);
        }
    }

    private function createProcedureElements($procedureType)
    {
        // Get a full listing of all available procedures...
        $fullProcName = "\Fisdap\Entity\\" . $procedureType;
        $allProcs = $fullProcName::getFormOptions(false, true, false);

        $programId = \Fisdap\Entity\User::getLoggedInUser()->getProgramId();

        $procTypeEntityName = "\Fisdap\Entity\Program" . $procedureType;

        $finalProcArray = array();

        foreach ($allProcs as $procId => $procName) {
            $checkboxName = 'procedure_' . $procedureType . '_' . $procId;
            $procElement = new Zend_Form_Element_Checkbox($checkboxName);

            $procElement->setDecorators(array('ViewHelper'));

            $this->addElement($procElement);

            // Set the default value here.
            $isChecked = $procTypeEntityName::programIncludesProcedure($programId, $procId);

            $this->setDefaults(array($checkboxName => $isChecked));

            $finalProcArray[] = $procElement . " " . $procName;
        }

        return $finalProcArray;
    }

    public function process($data)
    {
        $program = \Fisdap\EntityUtils::getEntity('ProgramLegacy', \Fisdap\Entity\User::getLoggedInUser()->getProgramId());

        // Map the data fields to the ProgramLegacy entity...
        $program->can_students_create_lab = (boolean) $data['can_students_create_lab'];
        $program->can_students_create_clinical = (boolean) $data['can_students_create_clinical'];
        $program->can_students_create_field = (boolean) $data['can_students_create_field'];

        $program->program_settings->allow_tardy = (boolean) $data['tardy'];
        $program->program_settings->allow_absent = (boolean) $data['absent'];
        $program->allow_absent_with_permission = (boolean) $data['absent_with_permission'];


        $program->send_late_shift_emails = (boolean) $data['send_late_shift_emails'];
        if ($program->send_late_shift_emails) {
            $program->program_settings->autolock_late_shifts = (boolean) $data['autolock_late_shifts'];
        } else {
            $program->program_settings->autolock_late_shifts = false;
        }

        $program->late_lab_deadline = (int) $data['late_lab_deadline'];
        $program->late_clinical_deadline = (int) $data['late_clinical_deadline'];
        $program->late_field_deadline = (int) $data['late_field_deadline'];

        $program->send_critical_thinking_emails = (boolean) $data['send_critical_thinking_emails'];

        $program->include_narrative = (boolean) $data['include_narrative'];

        $program->program_settings->require_shift_evals = (boolean) $data['require_evals'];

        if ($this->element->isStaff || !$this->element->usingClinicalPractice) {
            $program->program_settings->quick_add_clinical = (boolean)$data['quick_add_clinical'];
        }

        $program->program_settings->allow_educator_signoff_signature = (boolean) $data['allow_educator_signoff_signature'];
        $program->program_settings->allow_educator_signoff_login = (boolean) $data['allow_educator_signoff_login'];
        $program->program_settings->allow_educator_signoff_attachment = (boolean) $data['allow_educator_signoff_attachment'];
        //$program->program_settings->allow_educator_signoff_email = (boolean) $data['allow_educator_signoff_email'];

        $program->program_settings->allow_educator_shift_audit = (boolean) $data['allow_educator_shift_audit'];

        $program->program_settings->allow_educator_evaluations = (boolean) $data['allow_educator_evaluations'];

        //This is ghetto, I know...I'm sorry
        if ($data['signoff_location'] == 'shift') {
            $program->program_settings->allow_signoff_on_patient = false;
            $program->program_settings->allow_signoff_on_shift = true;
        } elseif ($data['signoff_location'] == 'patient') {
            $program->program_settings->allow_signoff_on_patient = true;
            $program->program_settings->allow_signoff_on_shift = false;
        } else {
            $program->program_settings->allow_signoff_on_patient = false;
            $program->program_settings->allow_signoff_on_shift = false;
        }

        $program->program_settings->include_lab_in_mygoals = (boolean) $data['include_lab_in_mygoals'];
        $program->program_settings->include_clinical_in_mygoals = (boolean) $data['include_clinical_in_mygoals'];
        $program->program_settings->include_field_in_mygoals = (boolean) $data['include_field_in_mygoals'];

        $st_to_use = $this->getCheckboxGroupResults($data, $this->subjectTypes, "subject_type");
        $program->program_settings->subject_types_in_mygoals = $st_to_use;

        if ($this->isStaff) {
            $program->program_settings->practice_skills_field = $data['practice_skills_field'];
            $program->program_settings->practice_skills_clinical = $data['practice_skills_clinical'];
        }

        $this->saveIncludedProcedures($data);


        // save the lab skills stuff!
        foreach ($this->groups as $goalGroup) {
            foreach ($goalGroup['categories'] as $practiceCat) {
                // save each category name!
                if ($data['category' . $practiceCat->id . '_cat_name']) {
                    $practiceCat->name = $data['category' . $practiceCat->id . '_cat_name'];
                } else {
                    // for IE, quick fix because IE can't seem to handle normal things
                    $practiceCat->name = $practiceCat->name;
                }
                $practiceCat->save(false);

                // save each practice definition
                foreach ($practiceCat->practice_definitions as $practiceDef) {
                    if (isset($data['category' . $practiceCat->id . '_definition' . $practiceDef->id . '_name'])) {
                        $practiceDef->name = $data['category' . $practiceCat->id . '_definition' . $practiceDef->id . '_name'];
                        $practiceDef->active = $data['category' . $practiceCat->id . '_definition' . $practiceDef->id . '_active'];
                        $practiceDef->peer_goal = $data['category' . $practiceCat->id . '_definition' . $practiceDef->id . '_peer'];
                        $practiceDef->instructor_goal = $data['category' . $practiceCat->id . '_definition' . $practiceDef->id . '_instructor'];
                        $practiceDef->eureka_window = $data['category' . $practiceCat->id . '_definition' . $practiceDef->id . '_eureka_window'];
                        $practiceDef->eureka_goal = $data['category' . $practiceCat->id . '_definition' . $practiceDef->id . '_eureka_goal'];

                        $selected_skills = $data['category' . $practiceCat->id . '_definition' . $practiceDef->id . '_practice_skills'];

                        // remove airway_management from this list, since it isn't really a skill
                        if ($selected_skills) {
                            if (in_array("0airway_management", $selected_skills)) {
                                $selected_skills_without_am = array_diff($selected_skills, array('0airway_management'));
                                // give this LPD airway_management credit
                                $practiceDef->airway_management_credit = true;
                            } else {
                                // make this LPD false for airway_management credit
                                $selected_skills_without_am = $selected_skills;
                                $practiceDef->airway_management_credit = false;
                            }
                        } else {
                            // make this LPD false for airway_management credit
                            $selected_skills_without_am = $selected_skills;
                            $practiceDef->airway_management_credit = false;
                        }

                        if (count($selected_skills_without_am) == 0) {
                            $practiceDef->practice_skills->clear();
                        } else {
                            $practiceDef->setPracticeSkillIds($selected_skills_without_am);
                        }

                        $practiceDef->save(false);
                    }
                }
            }
        }

        $program->save();
    }

    private function savePracticeDefintions($data)
    {
    }

    /**
     * This function parses through the data saved and saves down any
     * procedure related data to its appropriate entities.
     *
     * @param type Array POST data from the settings form.
     */
    private function saveIncludedProcedures($data)
    {
        $programId = \Fisdap\Entity\User::getLoggedInUser()->getProgramId();

        $newProcedures = array();

        foreach ($data as $fieldName => $value) {
            $splitName = explode("_", $fieldName);

            if ($splitName[0] == 'procedure' && count($splitName) == 3) {
                $entBaseName = "Program" . $splitName[1];

                $entName = "\\Fisdap\\Entity\\" . $entBaseName;

                $procEnt = \Fisdap\Entity\ProgramProcedure::getProgramProcedureEntity($entName, $entName::$procedureTypeVarName, $programId, $splitName[2]);

                $procEnt->included = $value;

                $procEnt->save(false);

                $newProcedures[$entBaseName][] = $procEnt->id;
            }
        }

        // Now that all of the new procedures are collated together (and hopefully
        // up to date), associate them with the program entity...
        $program = \Fisdap\EntityUtils::getEntity('ProgramLegacy', $programId);

        foreach ($newProcedures as $procedureType => $allowedProcedures) {
            switch ($procedureType) {
                case "ProgramAirwayProcedure":
                    $programProcedureProperty = "airway_procedures";
                    break;
                case "ProgramCardiacProcedure":
                    $programProcedureProperty = "cardiac_procedures";
                    break;
                case "ProgramIvProcedure":
                    $programProcedureProperty = "iv_procedures";
                    break;
                case "ProgramMedType":
                    $programProcedureProperty = "med_types";
                    break;
                case "ProgramOtherProcedure":
                    $programProcedureProperty = "other_procedures";
                    break;
                case "ProgramLabAssessment":
                    $programProcedureProperty = 'lab_assessments';
                    break;

                    $program->$programProcedureProperty->clear();

                    foreach ($allowedProcedures as $procedure) {
                        $program->$programProcedureProperty->add($procedure);
                    }
            }
        }
    }
}
