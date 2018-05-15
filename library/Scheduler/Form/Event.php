<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                                           *
*        Copyright (C) 1996-2011.  This is an unpublished work of           *
*                         Headwaters Software, Inc.                         *
*                            ALL RIGHTS RESERVED                            *
*        This program is a trade secret of Headwaters Software, Inc. and    *
*        it is not to be copied, distributed, reproduced, published, or     *
*        adapted without prior authorization of Headwaters Software, Inc.   *
*                                                                           *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * /


/**
 * Form for creating/editing an event!
 * @package Scheduler
 * @author hammer:)
 */
class Scheduler_Form_Event extends Fisdap_Form_Base
{
    /**
     * @var \Fisdap\Entity\User
     */
    public $user;
    
    /**
    * @var Doctrine\ORM\EntityManager
    */
    protected $em;
   
    /**
     * @var array
     */
    public $request_types;
    
    /**
     * @var \Fisdap\Entity\EventLegacy
     */
    public $event;

    /**
     * @var string
     */
    public $type;
    
    /**
     * @var array
     */
    public $selectedDaysArray;
    
    /**
     * @var array
     */
    public $echoedDaysArray;
    
    /**
     * @var view helper!
     */
    public $pickCalHtml;

    /**
     * @var array
     * This will be used to keep track of some series data when we try to extend an existing series
     * Only used when Editing multiple events that have a repeating series
     */
    public $series_data;

    public $multiple_events;
    public $windows;
    public $assignModal;
    public $is_admin;
    public $student_conflicts;
    public $all_future_events_in_series;
    public $program;
    public $profession_cert_levels;
    public $using_session_events;
    public $show_limited_interface_msg;
    public $event_session_id;

    /**
     * @param null $type
     * @param null $event_id
     * @param null $multiple_events
     * @param bool $using_session_events
     * @param null $event_session_id
     * @param null $options
     */
    public function __construct($type, $event_id = null, $multiple_events = null, $using_session_events = false, $event_session_id = null, $options = null)
    {
        $this->user = \Fisdap\Entity\User::getLoggedInUser();
        $this->program = \Fisdap\EntityUtils::getEntity('ProgramLegacy', $this->user->getProgramId());
        $this->event = \Fisdap\EntityUtils::getEntity('EventLegacy', $event_id);
        $this->multiple_events = $multiple_events;
        $this->student_conflicts = false;
        $this->em = \Fisdap\EntityUtils::getEntityManager();
        $this->type = $type;
        $this->profession_cert_levels = \Fisdap\EntityUtils::getEntity('CertificationLevel')->getAll($this->program->profession->id);
        $this->show_limited_interface_msg = false;
        $this->using_session_events = $using_session_events;
        $this->event_session_id = $event_session_id;
        
        if ($this->event) {
            $this->type = $this->event->type;
            
            $this->is_admin = false;
            $program = $this->program;
            
            if ($this->event->program->id == $program->id) {
                $this->is_admin = true;
            } else {
                // check out the site and find out if this user is an admin
                if ($program->isAdmin($this->event->site->id)) {
                    $this->is_admin = true;
                } elseif ($program->sharesSite($this->event->site->id)) {
                    // they are a "bob", can edit the second tab
                    $this->is_admin = false;
                    if (count($this->multiple_events) > 0) {
                        $this->all_future_events_in_series = $this->editingAllFutureEventsInSeries(count($this->multiple_events), $this->event->series);
                    } else {
                        $this->all_future_events_in_series = true;
                    }
                } else {
                    // this user has no business being here, kick 'em out
                    return false;
                }
            }
        }
        
        $this->pickCalHtml = "";
        $this->windows = array();
        $this->request_types = \Fisdap\Entity\RequestType::getAll();




        parent::__construct($options);
    }
    
    /**
     * init method that adds all the elements to the form
     */
    public function init()
    {
        parent::init();
        $this->addFiles();
        
        $program = \Fisdap\EntityUtils::getEntity('ProgramLegacy', $this->user->getProgramId());
        
        if ($this->event) {
            if ($this->is_admin) {
                $form_id = "admin-edit-event-form";
            } else {
                $form_id = "non-admin-edit-event-form";
                $form_class = ($this->all_future_events_in_series) ? "" : "not-all-future-events-in-series";
            }
        } else {
            $form_id = "event-form";
        }
        
        $this->setAttrib('id', $form_id);
        $this->setAttrib('data-eventId', $this->event->id);
        $this->setAttrib('class', "event-form " . $form_class);
        $this->assignModal = new Scheduler_Form_AssignModal();
        $shift_type = new Zend_Form_Element_Hidden("shift_type");
        $shift_type->setValue($this->type);
        
        
        $window_ids = array();
        if ($this->event) {
            foreach ($this->event->getSlotByType('student')->windows as $window) {
                if ($window->program->id == $program->id) {
                    $window_ids[$window->id] = $window->id;
                }
            }
        }
        
        // set up the elements for window_ids - just informative for javascript
        $exisiting_windows = new Zend_Form_Element_Select('existing_windows');
        $exisiting_windows->setRegisterInArrayValidator(false);
        $exisiting_windows->setMultiOptions($window_ids);
        $exisiting_windows->setAttribs(array("multiple" => "multiple"));
        $exisiting_windows->setValue($window_ids);
        
        
        $site_change_warning = new Zend_Form_Element_Hidden('site_change_warning');
        $site_change_warning->setValue(0);

        if ($this->event) {
            if ($this->event->isShared()) {
                $site_change_warning->setValue(1);
            }
        }
        
        // Now create the individual elements
        $customName = new Zend_Form_Element_Text("custom_name");
        $customName->setLabel("Custom shift name:")
                   ->setRequired(false)
                   ->addValidator("StringLength", true, array('max' => '128'))
                   ->addErrorMessage('Please enter custom name that is less than 128 characters long.')
                   ->setAttribs(array("class" => "fancy-input", "maxlength" => "128"));
                   
        if ($this->event) {
            $customName->setValue($this->event->name);
        }
        
        $siteOptions = \Fisdap\EntityUtils::getRepository('SiteLegacy')->getFormOptionsByProgram($this->user->getProgramId(), $this->type, null, null, true);
        $actualOptions = array();
        foreach ($siteOptions as $id => $name) {
            if (count(\Fisdap\EntityUtils::getRepository('BaseLegacy')->getFormOptionsByProgram($this->user->getProgramId(), true, null, $id)) > 0) {
                $actualOptions[$id] = $name;
            }
        }
        
        //if we have an event where the current user is an admin but not the creator, disable sites
        if ($this->event && $this->is_admin && $this->program->id != $this->event->program->id) {
            $siteAttribs = array("disabled" => "disabled");
        } else {
            $siteAttribs = array();
        }
        
        $site = $this->createChosen('site', ucfirst($this->type) . " site: <span class='description'>(required)</span>", "401px", " ", $actualOptions, false, $siteAttribs);
        
        if ($this->event) {
            $site->setValue($this->event->site->id);
            $options_site_id = $this->event->site->id;
        } else {
            $options_site_id = key($actualOptions);
        }
        
        $baseOptions = \Fisdap\EntityUtils::getRepository('BaseLegacy')->getFormOptionsByProgram($this->user->getProgramId(), true, null, $options_site_id);
        $baseLabel = ($this->type == "clinical") ? "Department: <span class='description'>(required)</span>" : "Base: <span class='description'>(required)</span>";
        $base = $this->createChosen('base', $baseLabel, "225px", " ", $baseOptions, false);
        $base->setRegisterInArrayValidator(false);
        
        if ($this->event) {
            $base->setValue($this->event->base->id);
        }

        $this->selectedDaysArray = array();

        if ($this->event) {
            if ($this->multiple_events) {
                $dates = \Fisdap\EntityUtils::getRepository('EventLegacy')->getStartDates($this->multiple_events);
                foreach ($dates as $selected_day) {
                    $this->selectedDaysArray[] = $selected_day['start_datetime']->format("Y-m-d");
                }
            } else {
                $event_date = $this->event->start_datetime->format("Y-m-d");
                $this->selectedDaysArray = array($event_date => $event_date);
            }

            // if we're editing a single event, provide a date picker to change the date of the shift
            if (count($this->multiple_events) == 1 || !($this->multiple_events)) {
                $singleShiftDate = new Zend_Form_Element_Text("single_shift_date");
                $singleShiftDate->setValue($this->event->start_datetime->format("m/d/Y"));
                $singleShiftDate->setAttribs(array("class" => "selectDate fancy-input"));
                $this->addElement($singleShiftDate);
            } else {
                // we're editing multiple events, provide a date picker to change the last date of a series
                // ------------ extending a series ------------
                // This series MUST be repeating, and we MUST have data to use
                if ($this->event->series->repeating) {
                    $event_dates = \Fisdap\EntityUtils::getRepository('EventLegacy')->getDatesByIds($this->multiple_events);

                    $last_event_date = $event_dates[count($event_dates)-1]->format("m/d/Y");
                    $first_event_date = $event_dates[0]->format("M j, Y");
                    $frequency_type = $this->event->series->repeat_frequency_type->name;

                    $this->series_data['last_event_date'] = $last_event_date;
                    $this->series_data['first_event_date'] = $first_event_date;
                    $this->series_data['frequency'] = $this->event->series->repeat_frequency;
                    $this->series_data['frequency_type'] = ($frequency_type) ? $frequency_type : "day";

                    $newSeriesEndDate = new Zend_Form_Element_Text("extend_series_date");
                    $newSeriesEndDate->setValue($last_event_date);
                    $newSeriesEndDate->setAttribs(array("class" => "selectDate fancy-input", "data-firsteventdate" => $event_dates[0]->format("m/d/Y")));
                    $this->addElement($newSeriesEndDate);

                    $adding_dates = new Zend_Form_Element_Hidden("adding_dates");
                    $this->addElement($adding_dates);
                }
            }
        }
        
        $selectedDays = new Zend_Form_Element_Select("selected_days");
        $selectedDays->setMultiOptions($this->selectedDaysArray);
        $selectedDays->setAttribs(array("multiple" => "multiple"));
        $selectedDays->setRegisterInArrayValidator(false);
        
        if ($this->event) {
            $selectedDays->setValue($this->event->start_datetime->format("Y-m-d"));
        }
        
        $this->echoedDaysArray = array();
        $echoedDays = new Zend_Form_Element_Select("echoed_days");
        $echoedDays->setMultiOptions($this->echoedDaysArray);
        $echoedDays->setAttribs(array("multiple" => "multiple"));
        $echoedDays->setRegisterInArrayValidator(false);



        
        $today = new DateTime();
        $startDate = new Zend_Form_Element_Hidden("start_date");
        $startDate->setValue($today->format("Y-m-j"));

        $eventSessionId = new Zend_Form_Element_Hidden('edit_event_session_id');
        $eventSessionId->setValue($this->event_session_id);

        $startTime = new Zend_Form_Element_Text("start_time");
        $startTime->setLabel('Start time: <span class="description">(required)</span>')
                ->addValidator("Digits", true)
                ->addValidator("Date", true, array("format"=>"Hi"))
                ->addValidator("Between", true, array('min' => '0000', 'max' => '2359'))
                ->setRequired(true)
                ->addErrorMessage('Please enter a valid start time in 24 hour format.')
                ->setAttribs(array("class" => "fancy-input"));
                
        if ($this->event) {
            $startTime->setValue($this->event->start_datetime->format("Hi"));
        }
                
        $duration = new Zend_Form_Element_Text("duration");
        $duration->setLabel('Duration: <span class="description">(required)</span>')
                 ->addValidator("Float", true)
                 ->addValidator("Between", true, array('min' => '0.01', 'max' => '120.00'))
                 ->setRequired(true)
                 ->addErrorMessage('Please enter a valid duration.')
                 ->setAttribs(array("class" => "fancy-input"));
            
        if ($this->event) {
            $duration->setValue($this->event->duration);
        }
        
        $repeat = new Zend_Form_Element_Checkbox('repeat');
        $repeat->setRequired(false);
        
        $drop = new Zend_Form_Element_Checkbox('drop_previously_shared_students');
        $drop->setRequired(false);
        
        $share = new Zend_Form_Element_Checkbox('share_flag');
        $share->setRequired(false);
        $share->setValue(1);
        
        $frequencyOptions = array();
        for ($i = 1; $i < 25; $i++) {
            $frequencyOptions[$i] = $i;
        }
        
        $repeatFrequency = $this->createChosen('repeat_frequency', '', "60px", " ", $frequencyOptions, false);
        
        $repeatFrequencyTypeOptions = \Fisdap\Entity\FrequencyType::getFormOptions();
        $repeatFrequencyType = new Zend_Form_Element_Radio("repeat_frequency_type_radios");
        $repeatFrequencyType->setMultiOptions($repeatFrequencyTypeOptions);
        
        $repeatUntil = new Zend_Form_Element_Text("repeat_until");
        $sixWeeksOut = new DateTime('+6 weeks');
        $repeatUntil->setValue($sixWeeksOut->format("m/d/Y"));
        $repeatUntil->setAttribs(array("class" => "selectDate"));
        
        $slots = new Zend_Form_Element_Text("slots");
        $slots->setLabel("# Students who can go: <span class='description'>(required)</span>")
                   ->setRequired(true)
                   ->addValidator("Digits", true)
                   ->addValidator("Between", true, array('min' => '0', 'max' => '5000'))
                   ->addErrorMessage('Please enter the number of students who can attend this shift.')
                   ->setAttribs(array("class" => "fancy-input"));
                   
        if ($this->event) {
            $slots->setValue($this->event->getSlotByType("student")->count);
        }
                   
        $customName->addDecorator('Label', array('escape'=>false));
        $site->addDecorator('Label', array('escape'=>false));
        $base->addDecorator('Label', array('escape'=>false));
        $startTime->addDecorator('Label', array('escape'=>false));
        $duration->addDecorator('Label', array('escape'=>false));
        $slots->addDecorator('Label', array('escape'=>false));
        
        $submitButton = new Fisdap_Form_Element_SaveButton('save');
        $submitButton->setLabel("Create shifts");
        
        $notes = new Zend_Form_Element_Textarea("notes");
        $notes->setLabel("Notes: <span class='description'>(200 characters max)</span>")
                ->setRequired(false)
                ->addValidator("StringLength", true, array('max' => '200'))
                ->addErrorMessage('Please enter notes that are less than 200 characters long.')
            ->addDecorator('Label', array('escape'=>false))
              ->setAttribs(array("class" => "fancy-input", 'maxlength' => "200"));
              
        if ($this->event) {
            $notes->setValue($this->event->notes);
        }

        $email_list = new Zend_Form_Element_Textarea("email_list");
        $email_list->setLabel("Email list: <span class='description'>(comma separated)</span>")
                   ->setAttribs(array("class" => "fancy-input"));
                   
        $email_list->addDecorator('Label', array('escape'=>false));
        
        if ($this->event) {
            if ($this->event->email_list) {
                $email_list->setValue(implode(", ", $this->event->email_list));
            }
        }
        
        $default_preceptors = array();
        $preceptorOptions = \Fisdap\EntityUtils::getRepository('PreceptorLegacy')->getPreceptorFormOptions($this->user->getProgramId(), $options_site_id);
        
        if ($this->event) {
            if (count($this->event->preceptor_associations) > 0) {
                foreach ($this->event->preceptor_associations as $pa) {
                    // get every preceptor thats currently on the shift, regardless of program association
                    $preceptorOptions[$pa->preceptor->id] = $pa->preceptor->first_name . " " . $pa->preceptor->last_name;
                    $default_preceptors[] = $pa->preceptor->id;
                }
            }
        }
        
        $preceptor = $this->createChosen('preceptors', "Preceptors", "265px", " ", $preceptorOptions, true);
        $preceptor->setRegisterInArrayValidator(false);
        
        if ($this->event && $this->is_admin) {
            $preceptor->setValue($default_preceptors);
        }
        
        $default_instructors = array();
        $instructorOptions = \Fisdap\EntityUtils::getRepository('ProgramLegacy')->getInstructorFormOptions($this->user->getProgramId());
        
        if ($this->event) {
            if (count($this->event->getSlotByType("instructor")->assignments) > 0) {
                foreach ($this->event->getSlotByType("instructor")->assignments as $assignment) {
                    $instructorOptions[$assignment->user_context->getRoleData()->id] = $assignment->user_context->user->first_name . " " . $assignment->user_context->user->last_name;
                    $default_instructors[] = $assignment->user_context->getRoleData()->id;
                }
            }
        }
        
        $instructor = $this->createChosen('instructors', "Instructors", "265px", " ", $instructorOptions, true);
        $instructor->setRegisterInArrayValidator(false);
        if ($this->event && $this->is_admin) {
            $instructor->setValue($default_instructors);
        }
        
        $sharingPrograms = new Zend_Form_Element_Select("sharing_programs");
        $sharingPrograms->setAttribs(array("multiple" => "multiple"));
        $sharingPrograms->setRegisterInArrayValidator(false);
        
        $default_students = array();
        $student_options = array();
        $this->student_conflicts = false;
        
        if ($this->event) {
            if (count($this->multiple_events) > 1) {
                $assignments = \Fisdap\EntityUtils::getRepository('EventLegacy')->getStudentAssignments($this->event->series->id);
                
                $count = 0;
                foreach ($assignments as $assignment) {
                    if ($assignments[$count+1]) {
                        $conflicts = array_diff($assignments[$count], $assignments[$count+1]);
                    }
                    
                    $count++;
                    
                    if ($conflicts) {
                        $this->student_conflicts = true;
                        break;
                    }
                }
            }
        }
        
        if (!$this->student_conflicts && $this->event) {
            $see_student_names = $program->seesSharedStudents($this->event->site->id);
            if (count($this->event->getSlotByType("student")->assignments) > 0) {
                foreach ($this->event->getSlotByType("student")->assignments as $assignment) {
                    $student_name = ($see_student_names || $assignment->user_context->program->id == $program->id) ? $assignment->user_context->user->first_name . " " . $assignment->user_context->user->last_name : "Student from " . $assignment->user_context->program->name;
                    $student_name .= ($assignment->user_context->program->id == $program->id) ? "" : "*";
                    $student_options[$assignment->user_context->getRoleData()->id] = $student_name;
                    $default_students[] = $assignment->user_context->getRoleData()->id;
                }
            }
        }
        
        if (!$this->student_conflicts) {
            $assigned_students = new Zend_Form_Element_Select("assigned_students");
            $assigned_students->setAttribs(array("multiple" => "multiple"));
            $assigned_students->setMultiOptions($student_options);
            $assigned_students->setRegisterInArrayValidator(false);
            $assigned_students->setValue($default_students);
        }
        
        $cert_options = \Fisdap\Entity\CertificationLevel::getAllFormOptions();
        $certs = \Fisdap\Entity\CertificationLevel::getAll();
        
        // default values for cert levels
        $default_certs = array();
        if ($this->event) {
            foreach ($certs as $cert) {
                if ($this->event->cert_levels & $cert->bit_value) {
                    $default_certs[] = $cert->id;
                }
            }
        }
        
        if (count($default_certs) == count($certs)) {
            $default_certs = array();
        }
        
        //foreach($cert_options as $val => $label){$default_certs[] = $val;}
        $event_cert_levels = $this->createChosen('sharing_cert_levels', "Students who can attend this shift:", "320px", "All certification levels...", $cert_options, true);
        $event_cert_levels->setValue($default_certs);
        $event_cert_levels->setRegisterInArrayValidator(false);
        
        $signup = new Zend_Form_Element_Hidden("students_can_sign_up");
        // check out the scheduler 'breaker' setting for this shift type
        // if the type is null for whatever strange reason, have a fall back so this doesn't throw an error
        if (!$this->type) {
            $this->type = "lab";
        }
        $signup_default = ($program->program_settings->{'student_pick_' . $this->type}) ? 1 : 0;
        
        $signup->setValue($signup_default);
        $this->addElements(array($signup));
        //$this->setDefaults(array("students_can_sign_up" => 1));
        
        // shift change permissions
        foreach ($this->request_types as $request_type) {
            // add the permission elements for each type of shift change
            $ability = new Zend_Form_Element_Hidden($this->type."_".$request_type->name);
            $permission = new Zend_Form_Element_Hidden($this->type."_".$request_type->name."_permission");
            $this->addElements(array($ability, $permission));
            
            // set the defaults
            if ($this->event) {
                $this->setDefaults(array(
                    $this->type.'_'.$request_type->name => ($this->event->student_can_switch & $request_type->bit_value ? 1 : 0),
                    $this->type.'_'.$request_type->name.'_permission' => ($this->event->switch_needs_permission & $request_type->bit_value ? 1 : 0)
                ));
            } else {
                $this->setDefaults(array(
                    $this->type.'_'.$request_type->name => ($program->program_settings->{'student_switch_'.$this->type} & $request_type->bit_value ? 1 : 0),
                    $this->type.'_'.$request_type->name.'_permission' => ($program->program_settings->{'switch_'.$this->type.'_needs_permission'} & $request_type->bit_value ? 1 : 0)
                ));
            }
        }
        
        // Add elements
        $this->addElements(array(
            $site,$base,$customName,$startTime,$duration,$repeat,$repeatFrequency,$repeatUntil,$slots,$submitButton,$echoedDays,$selectedDays,$startDate,$preceptor,$instructor,$notes,
            $email_list,$repeatFrequencyType,$sharingPrograms,$event_cert_levels,$assigned_students,$shift_type,$exisiting_windows,$drop,$share,$site_change_warning,$eventSessionId,
        ));
        
        $this->setDefaults(array(
            'constraint_types' => 2
        ));
        
        //Set the decorators for the form
        $this->setDecorators(array(
            'FormErrors',
            'PrepareElements',
            array('ViewScript', array('viewScript' => "forms/event-form.phtml")),
            'Form'
        ));
    }

    private function createChosen($elementName, $label, $width, $placeholderText, $options, $multi = "multiple", $additionalAttribs = array())
    {
        $chosen = new Zend_Form_Element_Select($elementName);
        $chosen->setMultiOptions($options)
             ->setLabel($label)
             ->setAttribs(array("class" => "chzn-select",
                                "data-placeholder" => $placeholderText,
                                "style" => "width:" . $width,
                                "multiple" => $multi,
                                "tabindex" => count($options)));
        
        foreach ($additionalAttribs as $key => $value) {
            $chosen->setAttrib($key, $value);
        }
        
        return $chosen;
    }
    
    /**
     * Process the submitted POST values and do whatever you need to do
     *
     * @param array $post the POSTed values from the user
     * @param array $session_data contains data from windows
     * @return mixed either the values or the form w/errors
     */
    public function process($post, $window_data)
    {
        \Fisdap\EntityUtils::getEntityManager()->getConnection()->exec("SET SESSION wait_timeout = 600");
        \Zend_Registry::get('db')->query("SET SESSION wait_timeout = 600");

        $values = $post;
        
        // create a new event
        if (!$this->event) {
            $days = ($values['echoed_days']) ? array_merge($values['selected_days'], $values['echoed_days']) : $values['selected_days'];
            if (is_null($days)) {
                return false;
            }

            $this->processNew($values, $days, $window_data);
        } else {
            $new_events = array();
            $edit_data = $this->processEdit($values, $window_data);
            $new_series = $edit_data['series'];
            $email_data = $edit_data['edit_email_data'];

            // do we have any new events? (happens when we extend a series)
            if ($values['adding_dates']) {
                // update the window data array so processNew can create "new" windows (instead of trying to udpate existing ones)
                $new_window_data = array("new" => $window_data['existing']);
                $new_events = $this->processNew($values, explode(",", $values['adding_dates']), $new_window_data, $new_series);
            }

            // Send the edit email. We had to wait until now so we could use our new event entities
            $mail = $email_data['mail'];
            $events = $email_data['events'];
            $retained_users = $email_data['retained_users'];
            $existing_emails = $email_data['existing_emails'];

            $mail->setViewParam("added_events", $new_events);
            $mail->setViewParam("events", $events);

            $email_count = 0;
            $subject = (count($events) == 1) ? "Shift edited" : "Shifts edited";
            $mail->setSubject($subject);
            // Check to see if we should be sending email notifications to students for this program
            if ($this->program->program_setting->send_scheduler_student_notifications) {
                foreach ($retained_users as $user_context) {
                    $user_email = $user_context->user->email;
                    // keep track of the existing folks so we don't re-add their email in the next step
                    $existing_emails[] = $user_email;
                    $mail->addTo($user_email);
                    $email_count++;
                }
            }
            $email_list = $events[0]->getInstructorEmails();
            foreach ($email_list as $email) {
                if (!in_array($email, $existing_emails)) {
                    $mail->addTo($email);
                    $email_count++;
                }
            }
            if ($email_count > 0) {
                $mail->sendHtmlTemplate('shift-edit.phtml');
            }
        }
        
        return true;
    }

    public function processNew($values, $days, $window_data, $existing_series = null)
    {
        $program = $this->program;
        $current_role = $this->user->getCurrentUserContext();

        $instructor_user_contexts = [];
        // create an array of user role entities for instructors to be assigned
        if ($values['instructors']) {
            foreach ($values['instructors'] as $instructor_id) {
                $instructor_user_contexts[] = \Fisdap\EntityUtils::getEntity('InstructorLegacy', $instructor_id)->user_context;
            }
        }

        $is_sharing = false;

        if ($program->isAdmin($values['site']) && $values['share_flag']) {
            // must be add 1 since it will eventually include this program
            if ((count($values['sharing_programs'])+1) > 0) {
                $is_sharing = true;
                $receiving_programs = array();
                $values['sharing_programs'][] = $this->user->getProgramId();
                foreach ($values['sharing_programs'] as $program_id) {
                    $receiving_programs[] = \Fisdap\EntityUtils::getEntity('ProgramLegacy', $program_id);
                }
            }
        }

        $cert_level_config = $this->getEventCertConfig($values['sharing_cert_levels'], $is_sharing);

        // create a series if there is more than one shift
        if (count($days) > 1 || $existing_series) {
            $send_individual_emails = false;

            if ($existing_series) {
                $series = $existing_series;
            } else {
                $series = \Fisdap\EntityUtils::getEntity('EventSeries');
                if (count($values['echoed_days']) > 0) {
                    $series->repeating = $values['repeat'];
                    $series->repeat_frequency = $values['repeat_frequency'];
                    $series->set_frequency_type($values['repeat_frequency_type_radios']);
                    $series->set_repeat_start_date($days[0]);
                    $series->set_repeat_end_date($values['repeat_until']);
                } else {
                    $series->repeating = 0;
                }
                $series->save();
            }
        } else {
            $send_individual_emails = true;
        }

        // make an event for each selected day
        $events = array();
        $batch_size = 250;
        $counter = 0;
        foreach ($days as $event_start) {
            $event = \Fisdap\EntityUtils::getEntity('EventLegacy');

            $event->set_site($values['site']);
            $event->set_base($values['base']);
            $event->name = $values['custom_name'];

            $start = new DateTime($event_start . " " . $values['start_time']);
            $event->start_datetime = $start;

            $event->duration = $values['duration'];
            $event->cert_levels = $cert_level_config;

            // convert hours to minutes
            $minutes = intval($values['duration'] * 60);
            $end = date("Y-m-d H:i:s", strtotime('+' . $minutes . ' minutes', $start->format("U")));
            $event->end_datetime = $end;

            $event->type = $this->type;
            $event->program = $program;
            $event->notes = $values['notes'];
            $event->email_list = explode(",", $values['email_list']);

            $this->savePermissions($values, $event);

            // log the action in event history
            $action = \Fisdap\EntityUtils::getEntity("EventAction");
            $action->set_type(1);
            $action->initiator = $current_role;
            $event->addAction($action);

            $studentSlot = \Fisdap\EntityUtils::getEntity('Slot');
            $studentSlot->slot_type = 1;
            $studentSlot->count = $values['slots'];
            $event->addSlot($studentSlot);

            if ($is_sharing) {
                // share this event with everyone
                foreach ($receiving_programs as $receiving_program) {
                    $event->share($receiving_program, false);
                }
            }

            // create an instructor slot if we have instructors
            if (count($values['instructors']) > 0) {
                $instructorSlot = \Fisdap\EntityUtils::getEntity('Slot');
                $instructorSlot->slot_type = 2;
                $instructorSlot->count = count($values['instructors']);
                $event->addSlot($instructorSlot);

                // now assign the instructors
                foreach ($instructor_user_contexts as $instructor_ur) {
                    $event->assign($instructor_ur, $send_individual_emails, false);
                }
            }

            // now do preceptors
            if (count($values['preceptors']) > 0) {
                foreach ($values['preceptors'] as $preceptor_id) {
                    $event->addPreceptor($preceptor_id);
                }
            }

            if ($series) {
                $series->addEvent($event);
            }



            $this->saveWindowData($window_data, $event, $values['students_can_sign_up']);

            $event->save(false);

            $counter++;
            if ($counter >= $batch_size) {
                $this->em->flush();
                $counter = 0;
            }

            $events[] = $event;
        } // individual event loop

        // final flush
        $this->em->flush();

        // now that we have event ids, do we have any students to assign?
        // make sure we do just an extra bit of validation and only assign the number of slots availabe
        // though technically this should always be fine, we'll just be sure to avoid corrupt data
        // don't flush until the end, though
        $flush = false;
        $counter = 0;
        $batch_size = 50;
        if (count($values['assigned_students']) > 0) {
            $student_user_contexts = array();
            foreach ($values['assigned_students'] as $student_id) {
                $student_user_contexts[] = \Fisdap\EntityUtils::getEntity("StudentLegacy", $student_id)->user_context;
            }

            foreach ($events as $event) {
                foreach ($student_user_contexts as $student_ur) {
                    $event->assign($student_ur, $send_individual_emails, $flush);
                }

                $counter++;
                if ($counter >= $batch_size) {
                    $this->em->flush();
                    $counter = 0;
                }
            }
        }
        $this->em->flush();
        $this->em->clear();

        // if we created multiple events, send the bulk email
        if (!$send_individual_emails) {
            @usort($events, array("self", "sortByDate"));
            $mail = new \Fisdap_TemplateMailer();
            $assignees = array('students' => array(), 'instructors' => array(), 'preceptors' => array(),
                'count' => 0);

            // send the main email to all assignees
            $mail->setSubject("Shifts assigned")
                ->setViewParam("events", $events);

            // add any assigned instructors
            if ($values['instructors']) {
                foreach ($values['instructors'] as $instructor_id) {
                    // get the user role
                    $user_context = \Fisdap\EntityUtils::getEntity('InstructorLegacy', $instructor_id)->user_context;
                    $mail->addTo($user_context->user->email);
                    $assignees['instructors'][] = $user_context->user->getName();
                    $assignees['count']++;
                }
            }

            // Check to see if we should be sending email notifications to students for this program
            if ($this->program->program_setting->send_scheduler_student_notifications) {
                // add any assigned students
                if ($values['assigned_students']) {
                    foreach ($values['assigned_students'] as $student_id) {
                        // get the user role
                        $user_context = \Fisdap\EntityUtils::getEntity('StudentLegacy', $student_id)->user_context;
                        $mail->addTo($user_context->user->email);
                        $assignees['students'][] = $user_context->user->getName();
                        $assignees['count']++;
                    }
                }
            }

            if ($assignees['count'] > 0) {
                $mail->sendHtmlTemplate('bulk-shift-assignment.phtml');
            }

            $mail->clearRecipients();

            // now send it to the email list
            if ($assignees['count'] > 0) {
                $email_list = $events[0]->getInstructorEmails();
                foreach ($email_list as $email) {
                    $mail->addTo($email);
                }
                if (count($email_list) > 0) {
                    $mail->setViewParam("assignees", $assignees)
                        ->sendHtmlTemplate("bulk-shift-assignment-others.phtml");
                }
            }
        }

        return $events;
    }
    
    public function processEdit($values, $window_data)
    {
        $event_ids = ($this->multiple_events) ? $this->multiple_events : array($this->event->id);

        if ($this->event->series) {
            // these events are part of a series, we need to create a new one if we aren't editing the whole thing
            $existing_series = $this->event->series;
            $number_of_events_being_edited = count($event_ids);
            $number_of_events_in_series = count($existing_series->events);
            
            if ($number_of_events_being_edited != $number_of_events_in_series) {
                // do we have more than one event? do we need to create a new series?
                if (count($event_ids) > 1) {
                    // create a new series
                    $new_series = $this->createNewSeries($existing_series);
                } else {
                    $no_more_series = true;
                }
            }
            
            $all_future_events_in_series = $this->all_future_events_in_series;
        } else {
            $all_future_events_in_series = true;
        }
        
        $existing_instructor_assignments = $this->getExisitingAssignments('instructor');
        $new_instructor_user_contexts = $this->getUserContextsForAssignments('InstructorLegacy', $values['instructors']);
        
        if (!$this->student_conflicts) {
            $existing_student_assignments = $this->getExisitingAssignments('student');
            $new_student_user_contexts = $this->getUserContextsForAssignments('StudentLegacy', $values['assigned_students']);
        }
        
        // get program entities for sharing here, instead of each event
        $sharing_programs = array();
        if ($values['sharing_programs'] && $this->is_admin) {
            foreach ($values['sharing_programs'] as $program_id) {
                $sharing_programs[$program_id] = \Fisdap\EntityUtils::getEntity('ProgramLegacy', $program_id);
            }

            if (!$sharing_programs[$this->program->id]) {
                $sharing_programs[$this->program->id] = $this->program;
                $values['sharing_programs'][] = $this->program->id;
            }
        }

        // keep track of old data, so we can compare it to changes later
        $old_data = array();
        $new_data = array();

        $events = array();
        $event_id_array = array();
        $event_start_datetime = null;
        $event_end_datetime = null;
        $flush = false;
        $counter = 0;
        $batch_size = 250;

        foreach ($event_ids as $event_id) {
            $event = \Fisdap\EntityUtils::getEntity('EventLegacy', $event_id);
            $events[] = $event;

            if (!$old_data['base']) {
                // populate our old data array, we'll want to keep track of some things for comparison later
                $old_data['base'] = $event->base->name;
                $old_data['site'] = $event->site->name;
                $old_data['start_time'] = $event->start_datetime->format("Hi");
                $old_data['duration'] = "(" . $event->getDurationText() . ")";
                $old_data['start_date'] = $event->start_datetime->format('l, M j, Y');
                $old_data['notes'] = $event->notes;
                $old_data['preceptors'] = $event->getPreceptorText();
                $old_data['instructors'] = $event->getInstructorText();
                $old_data['slot_text'] = $event->getStudentSlotText(false, true);
            }

            // Super janky, but disabling chosen's suck, if we don't get a site value, assume it's the same as the old one.
            if (!isset($values['site'])) {
                $values['site'] = $event->site->id;
            }
            
            // log the action in event history
            $action = \Fisdap\EntityUtils::getEntity("EventAction");
            $action->set_type(5);
            $action->initiator = $this->user->getCurrentUserContext();
            $event->addAction($action);
            
            // only admins can affect attributes on tab 1 and 3, don't even touch these if the user is not a site admin/owner
            if ($this->is_admin) {
                //Store the existing event datetime to handle comparison later when determining if we need to update the shift as well.
                $stored_start_datetime = $event->start_datetime->format('Y-m-d H:i:s');
                $stored_end_datetime = $event->end_datetime->format('Y-m-d H:i:s');

                // The very first thing we need to do is see if the date of the shift changed (but only when we're editing a single shift)
                if (isset($values['single_shift_date'])) {
                    // Since it's only one shift, go ahead and flush
                    $new_start_date = new DateTime($values['single_shift_date'] . " " . $values['start_time']);
                    $event->start_datetime = $new_start_date;
                    $event->save();
                }

                $shiftUpdate = false;
                                
                $cert_level_config = $this->handleEditSharing($event, $values, $sharing_programs);
                
                $event->set_site($values['site']);
                $event->set_base($values['base']);
                $event->name = $values['custom_name'];
                $event->duration = $values['duration'];
                $event->cert_levels = $cert_level_config;
                                
                $event_id_array[] = $event_id;
                
                $start = new DateTime($event->start_datetime->format("Y-m-d") . " " . $values['start_time']);
                $event_start_datetime = $start->format('Y-m-d H:i:s');

                // convert hours to minutes
                $minutes = intval($values['duration'] * 60);
                $end = date("Y-m-d H:i:s", strtotime('+' . $minutes . ' minutes', $event->start_datetime->format("U")));
                $event_end_datetime = $end;

                // check to see if we need to update the datetimes on the shift
                if ($event->start_datetime->format('Y-m-d H:i:s') != $event_start_datetime || $stored_start_datetime != $event_start_datetime || $event->end_datetime->format('Y-m-d H:i:s') != $event_end_datetime || $stored_end_datetime != $event_end_datetime) {
                    $shiftUpdate = true;
                }

                $event->start_datetime = $start;
                $event->end_datetime = $end;
                $event->notes = $values['notes'];
                $event->email_list = explode(",", $values['email_list']);
                $event->getSlotByType("student")->count = $values['slots'];
                               
                if ($shiftUpdate) {
                    $datetimesql = "UPDATE ShiftData s ".
                    "SET s.start_datetime = '$event_start_datetime', ".
                    "s.end_datetime = '$event_end_datetime' ".
                    "WHERE s.Event_id = $event_id";

                    $conn = $this->em->getConnection();
                    $conn->query($datetimesql);
                }
                                
                if ($new_series) {
                    $new_series->addEvent($event);
                }
                
                // we have just a single event that we're editing, and we need to just remove it from its current series
                if ($no_more_series) {
                    $existing_series->events->removeElement($event);
                    $event->series = null;
                }
                
                // update the number of instructor slots available
                if (isset($values['instructors'])) {
                    $event->getSlotByType('instructor')->count = count($values['instructors']);
                }
                // now assign/drop the instructors
                $this->modifyAssignments($existing_instructor_assignments, $new_instructor_user_contexts, $event, "instructor");
                
                // now do preceptors
                if ($event->preceptor_associations) {
                    foreach ($event->preceptor_associations as $preceptor_assoc) {
                        $event->preceptor_associations->removeElement($preceptor_assoc);
                        $preceptor_assoc->delete($flush);
                    }
                }
                
                if ($values['preceptors']) {
                    foreach ($values['preceptors'] as $preceptor_id) {
                        $event->addPreceptor($preceptor_id);
                    }
                }
            }
            
            if (!$this->student_conflicts) {
                $this->modifyAssignments($existing_student_assignments, $new_student_user_contexts, $event, "student");
            }


            // if we are not an admin, we need to make sure we're editing all future shifts in this series
            if ($this->is_admin || $all_future_events_in_series) {
                //$this->saveWindows($window_data, $event->getSlotByType('student'), $values['students_can_sign_up'], $event);
                $this->saveWindowData($window_data, $event, $values['students_can_sign_up']);
                $this->savePermissions($values, $event);
            }
            
            $event->save(false);
            
            $counter++;
            if ($counter >= $batch_size) {
                $this->em->flush();
                $counter = 0;
            }
        }
        
        // Delete the series if there's only one event left in it
        if ($existing_series && $this->is_admin) {
            if (count($existing_series->events) == 1) {
                $existing_series->events->first()->series = null;
                $existing_series->delete(false);
            } elseif (count($existing_series->events) == 0) {
                $existing_series->delete(false);
            }
        }
        
        // final flush
        $this->em->flush();

        // populate our new data array.
        $sample_event = $events[0];
        $new_data['base'] = $sample_event->base->name;
        $new_data['site'] = $sample_event->site->name;
        $new_data['start_time'] = $sample_event->start_datetime->format("Hi");
        $new_data['duration'] = "(" . $sample_event->getDurationText() . ")";
        $new_data['start_date'] = $sample_event->start_datetime->format('l, M j, Y');
        $new_data['notes'] = $sample_event->notes;
        $new_data['preceptors'] = $sample_event->getPreceptorText();
        $new_data['instructors'] = $sample_event->getInstructorText();
        $new_data['slot_text'] = $sample_event->getStudentSlotText(false, true);

        
        // ok, now send the emails
        @usort($events, array("self", "sortByDate"));
        $mail = new \Fisdap_TemplateMailer();
        
        // figure out what happened to who
        $dropped_users = $this->compare_lists($existing_instructor_assignments, $new_instructor_user_contexts, 'difference');
        $added_users = $this->compare_lists($new_instructor_user_contexts, $existing_instructor_assignments, 'difference');
        $retained_users = $this->compare_lists($new_instructor_user_contexts, $existing_instructor_assignments, 'intersect');
        
        if (!$this->student_conflicts) {
            $dropped_users = array_merge($dropped_users, $this->compare_lists($existing_student_assignments, $new_student_user_contexts, 'difference'));
            $added_users = array_merge($added_users, $this->compare_lists($new_student_user_contexts, $existing_student_assignments, 'difference'));
            $retained_users = array_merge($retained_users, $this->compare_lists($new_student_user_contexts, $existing_student_assignments, 'intersect'));
        }
        
        // send an email to all DROPPED folks first
        $subject = (count($events) == 1) ? "Shift dropped" : "Shifts dropped";
        $mail->setSubject($subject)
             ->setViewParam("events", $events)
             ->setViewParam("shift_info", $this->getShiftInfoForEmail($old_data, $new_data, count($events)))
             ->setViewParam("urlRoot", Util_HandyServerUtils::getCurrentServerRoot());
        foreach ($dropped_users as $user_context) {
            $mail->addTo($user_context->user->email);
        }
        // Check to see if we should be sending email notifications for this program
        if ($this->program->program_setting->send_scheduler_student_notifications) {
            if (count($dropped_users) > 0) {
                $mail->sendHtmlTemplate('shift-drop-from-edit.phtml');
            }
        }
            
        $mail->clearRecipients()
             ->clearSubject();
        
        // now send an email to all ADDED folks
        $existing_emails = array();
        $subject = (count($events) == 1) ? "Shift added" : "Shifts added";
        $mail->setSubject($subject);
        foreach ($added_users as $user_context) {
            $user_email = $user_context->user->email;
            // keep track of the new folks so we don't send them an "edited" email, too
            $existing_emails[] = $user_email;
            $mail->addTo($user_email);
        }
        // Check to see if we should be sending email notifications for this program
        if ($this->program->program_setting->send_scheduler_student_notifications) {
            if (count($added_users) > 0) {
                $mail->sendHtmlTemplate('shift-add-from-edit.phtml');
            }
        }
            
        $mail->clearRecipients()
             ->clearSubject();
        
        // now build an array of data to eventually send an email to all RETAINED folks AND the email list
        $edit_email_data = array();
        $edit_email_data['mail'] = $mail;
        $edit_email_data['events'] = $events;
        $edit_email_data['retained_users'] = $retained_users;
        $edit_email_data['existing_emails'] = $existing_emails;


        // run update query to adjust ShiftData if fields relevent to shiftdata have been changed.
        $event_id_string = implode(",", $event_id_array);

        if (!is_null($event_id_array)) {
            $site_id = $values['site'];
            $base_id = $values['base'];
            $hours = $values['duration'];

            $sql = "UPDATE ShiftData s ".
                   "SET s.AmbServ_id = $site_id, ".
                   "s.StartBase_id = $base_id, ".
                   "s.Hours = $hours ".
                   "WHERE s.Event_id IN ($event_id_string)";

            $conn = $this->em->getConnection();
            $conn->query($sql);
        }

        // Finally, return the series (if one)
        // If we are trying to 'extend' the current series, we'll need to return the one we just
        // used so the new shifts can use it
        $return_val = array("series" => null, "edit_email_data" => $edit_email_data);

        if ($values['adding_dates']) {
            $return_val['series'] = ($new_series) ? $new_series : $this->event->series;
        }

        return $return_val;
    }
    
    public function getUserContextsForAssignments($roleDataEntityName, $ids)
    {
        $userContexts = [];

        if ($ids) {
            foreach ($ids as $roleDataId) {
                $userContexts[] = \Fisdap\EntityUtils::getEntity($roleDataEntityName, $roleDataId)->user_context;
            }
        }

        return $userContexts;
    }
    
    public function getExisitingAssignments($type)
    {
        $assignments = array();
        if ($this->event->getSlotByType($type)->assignments) {
            foreach ($this->event->getSlotByType($type)->assignments as $assignment) {
                $assignments[] = array('entity' => $assignment, 'userContextId' => $assignment->user_context->id);
            }
        }
        
        return $assignments;
    }
    
    public function editingAllFutureEventsInSeries($number_of_events_being_edited, $existing_series)
    {
        // now we need to find out if we are editing all future events in the series
        $today = new DateTime();
        $shift_count = 0;
        if ($existing_series) {
            foreach ($existing_series->events as $event) {
                if ($event->start_datetime > $today) {
                    $shift_count++;
                }
            }
        } else {
            $shift_count = 1;
        }
        
        $this->show_limited_interface_msg = ($number_of_events_being_edited == $shift_count) ? false : true;
        return ($number_of_events_being_edited == $shift_count) ? true : false;
    }
    
    public function createNewSeries($existing_series)
    {
        $new_series = \Fisdap\EntityUtils::getEntity('EventSeries');
        if ($existing_series->repeating) {
            $new_series->repeating = 1;
            $new_series->repeat_frequency = $existing_series->repeat_frequency;
            $new_series->set_frequency_type($existing_series->repeat_frequency_type);
            $new_series->set_repeat_start_date($existing_series->repeat_start_date);
            $new_series->set_repeat_end_date($existing_series->repeat_end_date);
        } else {
            $new_series->repeating = 0;
        }
        
        $new_series->save();
        return $new_series;
    }
    
    public function handleEditSharing($event, $values, $sharing_programs)
    {
        $current_program = $this->program;
        $current_recieving_programs = \Fisdap\EntityUtils::getRepository('EventLegacy')->getReceivingPrograms($event->id);
        $cert_level_config = $this->getEventCertConfig($values['sharing_cert_levels'], $values['share_flag']);
                
        if ($current_program->isAdmin($values['site']) && $values['share_flag']) {
            // if this site is different than it used to be, remove all sharing relationships and drop students
            if ($values['site'] != $event->site->id) {
                $this->removeAllSharing($current_recieving_programs, $event, true, false);
            }
            
            if ($sharing_programs) {
                foreach ($sharing_programs as $program_id => $program) {
                    if (!in_array($program_id, $current_recieving_programs)) {
                        // the user has added this program, add this program/event to eventShares
                        $event->share($program, false);
                    }
                }
                
                // now what is the different between the sharing programs and the current recieving programs? who do we need to remove?
                // let the user decide if their students are being dropped
                $no_longer_sharing = array_diff($current_recieving_programs, $values['sharing_programs']);
                
                if ($no_longer_sharing) {
                    foreach ($no_longer_sharing as $program_id) {
                        if ($program_id != $event->program->id) {
                            $event_share = $event->getEventShareByProgram($program_id);
                            if ($event_share) {
                                $event_share->removeShare($drop_students, $values['drop_previously_shared_students']);
                            }
                        }
                    }
                }
            } else {
                // we aren't choosing to share with anyone, are there any recieving programs who need to be removed?
                $this->removeAllSharing($current_recieving_programs, $event, $values['drop_previously_shared_students']);
            }
        } else {
            // they have turned off sharing, remove all sharing relationships
            $this->removeAllSharing($current_recieving_programs, $event, $values['drop_previously_shared_students']);
            
            // if they have sharing turned on, but have somehow switch the site or something, still remove all relationships, but
            // include a record in 'eventShares' for this program so it is a shareable event
            if ($values['share_flag']) {
                $event->share($event->program, false);
            }
        }
        
        return $cert_level_config;
    }
    
    public function removeAllSharing($current_recieving_programs, $event, $drop_students, $drop_students_from_all_programs = true)
    {
        if ($current_recieving_programs) {
            foreach ($current_recieving_programs as $program_id) {
                $event_share = $event->getEventShareByProgram($program_id);
                if ($event_share) {
                    // are we just dropping students that dont belong to the current user?
                    $curr_program_id = (!$drop_students_from_all_programs) ? $this->user->getProgramId() : null;
                    $event_share->removeShare($drop_students, false, $curr_program_id);
                }
            }
        }
    }
    
    public function getEventCertConfig($sharing_cert_levels, $sharing)
    {
        $all_config = \Fisdap\Entity\CertificationLevel::getConfiguration();
        $cert_level_config = 0;
        
        if ($sharing_cert_levels && $sharing) {
            foreach ($sharing_cert_levels as $cert_id) {
                $cert = \Fisdap\EntityUtils::getEntity('CertificationLevel', $cert_id);
                $cert_level_config = $cert_level_config + $cert->bit_value;
            }
        } else {
            $cert_level_config = $all_config;
        }
        
        return $cert_level_config;
    }
    
    public function savePermissions($values, $event)
    {
        // count up the bitwise code for each request type
        $ability = 0;
        $permission = 0;
        
        foreach ($this->request_types as $request_type) {
            if ($values[$this->type.'_'.$request_type->name]) {
                $ability += $request_type->bit_value;
            }
            if ($values[$this->type.'_'.$request_type->name.'_permission']) {
                $permission += $request_type->bit_value;
            }
        }
        
        if ($this->is_admin || !$this->event) {
            $event->student_can_switch = $ability;
            $event->switch_needs_permission = $permission;
        } else {
            if (!$event->getPreferencesForProgram($this->user->getProgramId())) {
                $shared_pref = \Fisdap\EntityUtils::getEntity('SharedEventPreferenceLegacy');
                $shared_pref->program = \Fisdap\EntityUtils::getEntity('ProgramLegacy', $this->user->getProgramId());
                
                $event->addSharedPreferences($shared_pref);
            } else {
                $shared_pref = $event->getPreferencesForProgram($this->user->getProgramId());
            }
            
            $shared_pref->student_can_switch = $ability;
            $shared_pref->switch_needs_permission = $permission;
            $shared_pref->save();
            
            $event->save(false);
        }
    }
    
    public function saveWindowData($window_data, $event, $sign_up)
    {
        $current_program_id = $this->program->id;
        $count = 0;
        
        // step 1: get student slot for this event
        $slot = $event->getSlotByType('student');
        
        // step 2: build exisiting event windows array (happens on edit)
        $exisiting_event_windows = array();
        if ($event->getSlotByType('student')->windows) {
            foreach ($event->getSlotByType('student')->windows as $window) {
                if ($window->program->id == $current_program_id) {
                    $exisiting_event_windows[] = $window;
                }
            }
        }
        // step 3: update existing windows with data from $window_data['existing']
        if ($window_data['existing']) {
            foreach ($window_data['existing'] as $temp_window_data) {
                $windowTemp = $exisiting_event_windows[$count];
                $windowTemp = $this->saveIndividualWindow($windowTemp, $temp_window_data, $sign_up, $event);
                $windowTemp->save(false);
                $count++;
            }
        }
        
        // step 4: create new windows with data from $window_data['new']
        if ($window_data['new']) {
            foreach ($window_data['new'] as $temp_window_data) {
                $windowTemp = \Fisdap\EntityUtils::getEntity('Window');
                $windowTemp = $this->saveIndividualWindow($windowTemp, $temp_window_data, $sign_up, $event);
                $slot->addWindow($windowTemp);
            }
        }
    }
    
    public function saveIndividualWindow($window, $data, $sign_up, $event)
    {
        // each window we edit HERE belongs to the current logged in user
        // shared windows are handled in a differnet place
        $window->program = $this->program;
        
        // calculate the start/end dates based on window values and this event's start datetime
        $window->set_start_date($this->calculateOffsetDate($data['offset_type_start'], $data['offset_value_start'], $event->start_datetime));
        $window->set_end_date($this->calculateOffsetDate($data['offset_type_end'], $data['offset_value_end'], $event->start_datetime));
        
        // save this data for future edits
        $window->set_offset_type_start($data['offset_type_start']);
        $window->set_offset_type_end($data['offset_type_end']);
        $window->offset_value_start = $data['offset_value_start'];
        $window->offset_value_end = $data['offset_value_end'];
        
        // this window can only be active if the 'sign up' flippy was set to true
        $window->active = ($sign_up) ? $data['active'] : 0;
        
        // now deal with constraints
        // if we're editing an exisiting window, delete all old constraints/values for simplicity
        $window->clearConstraints(false);
            
        // certification level constraints
        $this->addCertificationLevelConstraints($data, $window, $event);

        // student group constraints
        if ($data['group_constraint']) {
            $window->addConstraintsFromArray(1, $data['group_constraint']);
        }
        
        return $window;
    }
    
    public function addCertificationLevelConstraints($data, $window, $event)
    {
        $window_constraint_data = $this->getDetailedWindowConstraintData($data, $event);
        $create_cert_constraints = $window_constraint_data['create_cert_constraints'];
        $constraint_vals = $window_constraint_data['constraint_vals'];
            
        if ($create_cert_constraints) {
            $window->addConstraintsFromArray(2, $constraint_vals);
        }
    }
    
    public function getDetailedWindowConstraintData($data, $event)
    {
        // Create certification level constraint/values if:
        //	-- We have some in the data AND the # of constraints != the # of certification levels in this program's profession
        //  -- OR there is a cert level bit configutation on the event that does not include all certificaiton levels
        $create_cert_constraints = false;
        $constraint_vals = array();
        
        if ($data['cert_constraint'] && count($data['cert_constraint']) != count($this->profession_cert_levels)) {
            $create_cert_constraints = true;
            $constraint_vals = $data['cert_constraint'];
        } else {
            $high_level_cert_data = $event->openToAllCertsInProfession($this->program->profession->id);
            if (!$high_level_cert_data['open']) {
                $create_cert_constraints = true;
                $constraint_vals = $high_level_cert_data['included_certs'];
            }
        }
        
        return array("create_cert_constraints" => $create_cert_constraints, "constraint_vals" => $constraint_vals);
    }
    
    public function saveWindows($window_data, $studentSlot, $students_can_sign_up, $event)
    {
        $window_count = 0;
        $exisiting_windows = array();
        if ($event->getSlotByType('student')->windows) {
            foreach ($event->getSlotByType('student')->windows as $window) {
                if ($window->program->id == $this->user->getProgramId()) {
                    $exisiting_windows[] = $window;
                }
            }
        }
        
        // as a fall back, if we haven't found window session data make sure these events still get a standard window
        if (!$window_data && count($exisiting_windows) == 0) {
            $window_data = array();
            $temp_id = rand(10, 10000);
            $temp_id = "tempid" . $temp_id;
            $window_data[$temp_id] = array();
            
            // use our handy dandy create window function
            $entity_data = \Fisdap\EntityUtils::getEntity('Slot')->getDefaultWindowArray($this->user);
            $window_data[$temp_id]['entity_data'] = $entity_data;
        }
        
        foreach ($window_data as $temp_id => $window_data) {
            $window_vals = $window_data['entity_data'];
            $window = \Fisdap\EntityUtils::getEntity('Window', $temp_id);
            $new_window = false;
            $create_window = false;
            
            if ($window) {
                // well we have a template but we need to get THIS event's window for this user's program
                if ($exisiting_windows[$window_count]) {
                    $window = $exisiting_windows[$window_count];
                } else {
                    $create_window = true;
                }
            } else {
                $create_window = true;
            }
            
            if ($create_window) {
                // create window
                $new_window = true;
                $window = \Fisdap\EntityUtils::getEntity('Window');
            }
            
            $window->program = $this->user->getCurrentRoleData()->program;
            $window->set_start_date($this->calculateOffsetDate($window_vals['offset_type_start']['id'], $window_vals['offset_value_start'], $event->start_datetime));
            $window->set_end_date($this->calculateOffsetDate($window_vals['offset_type_end']['id'], $window_vals['offset_value_end'], $event->start_datetime));
            $window->set_offset_type_start($window_vals['offset_type_start']['id']);
            $window->set_offset_type_end($window_vals['offset_type_end']['id']);
            $window->offset_value_start = $window_vals['offset_value_start'];
            $window->offset_value_end = $window_vals['offset_value_end'];
            $window->active = ($students_can_sign_up) ? $window_vals['active'] : 0;
            
            // start with certification levels
            // is there one set in the session for this window?
            
            // remove all exisitng constraints and cnostraint values if we have an existing window
            if (!$new_window) {
                foreach ($window->constraints as $constraint) {
                    foreach ($constraint->values as $constraint_val) {
                        $constraint->values->removeElement($constraint_val);
                        $constraint_val->delete(false);
                    }

                    // now remove the constraint itself
                    $window->constraints->removeElement($constraint);
                    $constraint->delete(false);
                }
            }
            
            // All becuase we want to be clever about how much info we're saving in the db....
            // Create certification level constraints if:
            //	-- There's some saved in the session AND
            //  -- The number of constrains is not equal to the number of certification levels in this program
            //  -- OR there is a cert level bit configutation on the event that does not include all certificaiton levels
            $create_cert_constraints = false;
            $constraint_vals = array();
            
            if ($window_vals['cert_constraint'] && count($window_vals['cert_constraint']['values']) != count($this->profession_cert_levels)) {
                $create_cert_constraints = true;
                $constraint_vals = $window_vals['cert_constraint']['values'];
            } else {
                $high_level_cert_data = $event->openToAllCertsInProfession($this->user->getCurrentProgram()->profession->id);
                if (!$high_level_cert_data['open']) {
                    $create_cert_constraints = true;
                    $constraint_vals = $high_level_cert_data['included_certs'];
                }
            }
            
            if ($create_cert_constraints) {
                $levelConstraint = \Fisdap\EntityUtils::getEntity('WindowConstraint');
                $levelConstraint->set_constraint_type(2);
                
                foreach ($constraint_vals as $id => $description) {
                    $constraintValue = \Fisdap\EntityUtils::getEntity('WindowConstraintValue');
                    if (is_array($description)) {
                        $constraintValue->value = $description['value'];
                        $constraintValue->description = \Fisdap\EntityUtils::getEntity('CertificationLevel', $description['value'])->description;
                    } else {
                        $constraintValue->value = $id;
                        $constraintValue->description = $description;
                    }
                    
                    $levelConstraint->addValue($constraintValue);
                }
                
                $window->addConstraint($levelConstraint);
            }
            
            if ($window_vals['group_constraint']) {
                $levelConstraint = \Fisdap\EntityUtils::getEntity('WindowConstraint');
                $levelConstraint->set_constraint_type(1);
                
                foreach ($window_vals['group_constraint']['values'] as $constraint_values) {
                    $constraintValue = \Fisdap\EntityUtils::getEntity('WindowConstraintValue');
                    $constraintValue->value = $constraint_values['value'];
                    $constraintValue->description = \Fisdap\EntityUtils::getEntity('ClassSectionLegacy', $constraint_values['value'])->name;
                    $levelConstraint->addValue($constraintValue);
                }
                
                $window->addConstraint($levelConstraint);
            }
            
            if ($new_window) {
                $studentSlot->addWindow($window);
            } else {
                $window->save(false);
            }
        }
    }
    
    public function modifyAssignments($exisiting_assignments, $new_user_contexts, $event, $type)
    {
        $already_assigned = array();
        
        if ($exisiting_assignments) {
            foreach ($exisiting_assignments as $exisiting_assignment) {
                $on_list = false;
                
                if ($new_user_contexts) {
                    foreach ($new_user_contexts as $ur) {
                        $ur_ids[] = $ur->id;
                        
                        if ($exisiting_assignment['userContextId'] == $ur->id) {
                            $on_list = true;
                            $already_assigned[] = $ur->id;
                        }
                    }
                }
                
                if (!$on_list) {
                    $event->removeUser($exisiting_assignment['userContextId'], false, false);
                }
            }
        } else {
            if ($type == "instructor" && count($new_user_contexts) > 0) {
                // create an instructor slot
                if (!$event->getSlotByType('instructor')) {
                    $instructorSlot = \Fisdap\EntityUtils::getEntity('Slot');
                    $instructorSlot->slot_type = 2;
                    $event->addSlot($instructorSlot);
                }
                $event->getSlotByType('instructor')->count = count($new_user_contexts);
            }
        }
        
        if ($ur_ids) {
            $ur_ids = array_unique($ur_ids);
        }
        
        // now compare the $already_assigned to what's on our $ur_ids list
        // anything left on the $instructor list needs to be added to the shift
        if ($ur_ids) {
            $new_assignments = array_diff($ur_ids, $already_assigned);
            if ($new_assignments) {
                foreach ($new_assignments as $userContextId) {
                    $event->assign(\Fisdap\EntityUtils::getEntity("UserContext", $userContextId), false, false);
                }
            }
        } else {
            $new_assignments = $new_user_contexts;
            if ($new_assignments) {
                foreach ($new_assignments as $user_context) {
                    $event->assign($user_context, false, false);
                }
            }
        }
    }
    
    public function calculateOffsetDate($type_id, $offset_value, $event_start)
    {
        if ($type_id == 1) {
            // static!
            $date = $offset_value[0];
        } elseif ($type_id == 2) {
            // interval!
            $date = date("Y-m-d", strtotime("-" . $offset_value[0] . " " . $offset_value[1], strtotime($event_start->format("Y-m-d"))));
        } elseif ($type_id == 3) {
            // previous month!
            $month_before = new DateTime($event_start->format('Y-m-01') . ' -1 month');
            $date = new DateTime($month_before->format("m/" . $offset_value[0] . "/Y"));
        }
        return $date;
    }
    
    private function addFiles()
    {
        // add files for chosen
        $this->addJsFile("/js/jquery.chosen.relative.js");
        $this->addCssFile("/css/jquery.chosen.css");

        // add files for this form
        $this->addJsFile("/js/library/Scheduler/Form/event-form.js");
        $this->addCssFile("/css/library/Scheduler/Form/event-form.css");
        
        // add files for slider checkboxes
        $this->addJsFile("/js/jquery.sliderCheckbox.js");
        $this->addCssFile("/css/jquery.sliderCheckbox.css");
        
        // add files for the calendar to pick dates
        $this->addJsFile("/js/library/Scheduler/View/Helper/event-form-pick-cal.js");
        $this->addCssFile("/css/library/Scheduler/View/Helper/event-form-pick-cal.css");
        
        // add files for permissions flippys
        $this->addJsFile("/js/jquery.flippy.js");
        $this->addCssFile("/css/jquery.flippy.css");
        
        // IE8 and lower need this for flippy to work
        $this->addJsFile("/js/excanvas.js");

        // Add JS for "momentJS" to help with date stuff
        $this->addJsFile("/js/moment.min.js");
        $this->addJsFile("/js/jquery.scrollTo-1.4.2-min.js");
    }
    
    public static function sortByDate($a, $b)
    {
        return ($a->start_datetime->format('U') < $b->start_datetime->format('U') ? -1 : 1);
    }
    
    // compares two lists to see which user roles are on the first list compared to the second
    // RETURNS THE USER ROLE ENTITY
    public function compare_lists($assignment_list_a, $assignment_list_b, $mode)
    {
        $intersect = array();
        $difference = array();
    
        foreach ($assignment_list_a as $a) {
            $on_list_b = false;
            
            // figure out the A user role id, which depends on the formatting of the list
            if (is_array($a)) {
                $id_a = $a['userContextId'];
                $ur_entity_a = \Fisdap\EntityUtils::getEntity('UserContext', $id_a);
            } else {
                $id_a = $a->id;
                $ur_entity_a = $a;
            }

            foreach ($assignment_list_b as $b) {
                // figure out the B user role id, which depends on the formatting of the list
                if (is_array($b)) {
                    $id_b = $b['userContextId'];
                } else {
                    $id_b = $b->id;
                }
            
                if ($id_a == $id_b) {
                    // IT'S ON THE LIST
                    $on_list_b = true;
                }
            }
            
            if ($on_list_b) {
                $intersect[] = $ur_entity_a;
            } else {
                $difference[] = $ur_entity_a;
            }
        }
                
        return ${$mode};
    }

    public function getShiftInfoForEmail($old_data, $new_data, $event_count)
    {
        $html = "";
        $highlight_color = "rgba(245, 156, 25, 0.3)";


        if ($this->type == "field") {
            $highlight_color = "rgba(13, 124, 154, 0.3)";
        } elseif ($this->type == "clinical") {
            $highlight_color = "rgba(106, 173, 10, 0.3)";
        }

        $highlight_span_start = "<span style='background-color:" . $highlight_color . ";'>";

        if ($old_data != $new_data) {
            $html .= "<p>From:<br />";

            $html .= $old_data['site'] . ": " . $old_data['base'] . "<br />";
            $html .= $old_data['start_time'] . " " . $old_data['duration'] . "<br />";

            if ($event_count == 1) {
                $html .= $old_data['start_date'] . "<br />";
                $html .= ($old_data['preceptors']) ? $old_data['preceptors'] . "<br />" : "";
                $html .= ($old_data['instructors']) ? $old_data['instructors'] . "<br />" : "";
                $html .= $old_data['slot_text'];
            }

            $html .= ($old_data['notes']) ? "<br />Notes: " . $old_data['notes'] . "<br />" : "";

            $html .= "</p><p style='margin-top:0.5em;'>To:<br />";
        } else {
            $html .= "<p>";
        }

        $html .= ($new_data['site'] == $old_data['site']) ? "" : $highlight_span_start;
        $html .= $new_data['site'];
        $html .= ($new_data['site'] == $old_data['site']) ? "" : "</span>";

        $html .= ": ";

        $html .= ($new_data['base'] == $old_data['base']) ? "" : $highlight_span_start;
        $html .= $new_data['base'];
        $html .= ($new_data['base'] == $old_data['base']) ? "" : "</span>";

        $html .= "<br />";

        $html .= ($new_data['start_time'] == $old_data['start_time']) ? "" : $highlight_span_start;
        $html .= $new_data['start_time'];
        $html .= ($new_data['start_time'] == $old_data['start_time']) ? "" : "</span>";

        $html .= " ";

        $html .= ($new_data['duration'] == $old_data['duration']) ? "" : $highlight_span_start;
        $html .= $new_data['duration'];
        $html .= ($new_data['duration'] == $old_data['duration']) ? "" : "</span>";

        $html .= "<br />";

        if ($event_count == 1) {
            $html .= ($new_data['start_date'] == $old_data['start_date']) ? "" : $highlight_span_start;
            $html .= $new_data['start_date'];
            $html .= ($new_data['start_date'] == $old_data['start_date']) ? "" : "</span>";

            $html .= "<br />";
        }

        if (strlen($new_data['preceptors'])) {
            $html .= ($new_data['preceptors'] == $old_data['preceptors']) ? "" : $highlight_span_start;
            $html .= $new_data['preceptors'];
            $html .= ($new_data['preceptors'] == $old_data['preceptors']) ? "" : "</span>";

            $html .= "<br />";
        }

        if (strlen($new_data['instructors'])) {
            $html .= ($new_data['instructors'] == $old_data['instructors']) ? "" : $highlight_span_start;
            $html .= $new_data['instructors'];
            $html .= ($new_data['instructors'] == $old_data['instructors']) ? "" : "</span>";

            $html .= "<br />";
        }

        if ($event_count == 1) {
            $html .= ($new_data['slot_text'] == $old_data['slot_text']) ? "" : $highlight_span_start;
            $html .= $new_data['slot_text'];
            $html .= ($new_data['slot_text'] == $old_data['slot_text']) ? "" : "</span>";

            $html .= "<br />";
        }


        if ($new_data['notes']) {
            $html .= ($new_data['notes'] == $old_data['notes']) ? "" : $highlight_span_start;
            $html .= ($new_data['notes'] == $old_data['notes']) ? "" : $highlight_span_start;
            $html .= $new_data['notes'];
            $html .= ($new_data['notes'] == $old_data['notes']) ? "" : "</span>";
        }


        $html .= "</p>";
        return $html;
    }
}
