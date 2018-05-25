<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                                           *
*        Copyright (C) 1996-2013.  This is an unpublished work of           *
*                         Headwaters Software, Inc.                         *
*                            ALL RIGHTS RESERVED                            *
*        This program is a trade secret of Headwaters Software, Inc. and    *
*        it is not to be copied, distributed, reproduced, published, or     *
*        adapted without prior authorization of Headwaters Software, Inc.   *
*                                                                           *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

/**
 * @package    SkillsTracker
 * @subpackage Forms
 */
class Scheduler_Form_Settings extends Fisdap_Form_Base
{	
	public $isStaff = false;
	
	/**
	 * @var \Fisdap\Entity\ProgramLegacy
	 */
	private $program;
	
	/**
	 * @var \Fisdap\Entity\ProgramSettings
	 */
	private $program_settings;
	
	/**
	 * @var array
	 */
	public $request_types;
	
	/**
	 * @var array
	 */
	public $site_types;

    /**
     * @var Scheduler_Form_WindowSubForm
     */
    public $field_window_sub_form;

    /**
     * @var Scheduler_Form_WindowSubForm
     */
    public $lab_window_sub_form;

    /**
     * @var Scheduler_Form_WindowSubForm
     */
    public $clinical_window_sub_form;
	
	/**
	 * @param $options mixed additional Zend_Form options
	 */
	public function __construct($options = null)
	{
		$this->isStaff = \Fisdap\Entity\User::getLoggedInUser()->isStaff();
		
		$program = \Fisdap\EntityUtils::getEntity('ProgramLegacy', \Fisdap\Entity\User::getLoggedInUser()->getProgramId());
		$this->program = $program;
		$this->program_settings = $program->program_settings;
		
		$this->request_types = \Fisdap\Entity\RequestType::getAll();

        $this->field_window_sub_form = new Scheduler_Form_WindowSubForm("field", $this->program_settings->default_field_window->id, true);
        $this->lab_window_sub_form = new Scheduler_Form_WindowSubForm("lab", $this->program_settings->default_lab_window->id, true);
        $this->clinical_window_sub_form = new Scheduler_Form_WindowSubForm("clinical", $this->program_settings->default_clinical_window->id, true);

        $site_types = \Fisdap\Entity\SiteType::getFormOptions();
		ksort($site_types);
		$this->site_types = $site_types;
		
		parent::__construct($options);
	}
	
	public function init()
	{
		parent::init();

		$this->addJsFile("/js/library/Scheduler/Form/settings.js");
		$this->addJsFile("/js/jquery.sliderCheckbox.js");
		$this->addCssFile("/css/library/Scheduler/Form/settings.css");
		$this->addCssFile("/css/jquery.sliderCheckbox.css");

		// add files for permissions flippys
		$this->addJsFile("/js/jquery.flippy.js");
		$this->addCssFile("/css/jquery.flippy.css");
		// IE8 and lower need this for flippy to work
		$this->addJsFile("/js/excanvas.js");
		
		// add a section of prompts for each site type
		foreach ($this->site_types as $type) {
			$this->addSection($type);
		}
		
		// Students
		$studentView = new Zend_Form_Element_Hidden('student_view_full_calendar');
		$this->addElements(array($studentView));

        //Send student notifications
        $sendStudentNotifications = new Zend_Form_Element_Checkbox("sendStudentNotifications");
        $this->addElement($sendStudentNotifications);

		// Some misc buttonage...
		$cancel = new Zend_Form_Element_Button('Cancel');
		$cancel->setOptions(array('id' => 'settings-cancel'));
		
		$save = new Zend_Form_Element_Submit('Save');
		$save->setOptions(array('id' => 'settings-save'));
		
		$this->addElements(array($cancel, $save));
		
		// Reset all of the view helpers to just output the element and no
		// chromage.
		$this->setElementDecorators(array('ViewHelper'), null, false);
		
		//Set the decorators for this form
		$this->setDecorators(array(
			'PrepareElements',
			array('ViewScript', array('viewScript' => "settings/settingsForm.phtml")),
			'Form',
		));
		
		// Set up the defaults for the form...
		$this->setDefaults(array(
			'student_view_full_calendar' => $this->program_settings->student_view_full_calendar,
			'sendStudentNotifications' => $this->program_settings->send_scheduler_student_notifications,
		));
		foreach ($this->site_types as $type) {
			$this->setSectionDefaults($type);
		}



    }
	
	private function addSection($type) {
		$create = new Zend_Form_Element_Hidden($type."_create");
		$this->addElements(array($create));
		
		$pick = new Zend_Form_Element_Hidden($type."_pick");
		$this->addElements(array($pick));
		
		foreach ($this->request_types as $request_type) {
			$ability = new Zend_Form_Element_Hidden($type."_".$request_type->name);
			$permission = new Zend_Form_Element_Hidden($type."_".$request_type->name."_permission");
			$this->addElements(array($ability, $permission));
		}
	}
	
	private function setSectionDefaults($type) {
		$get_create_method = "get_can_students_create_".$type;
		$this->setDefaults(array(
			$type.'_create' => $this->program->$get_create_method(),
			$type.'_pick' => $this->program_settings->{'student_pick_'.$type},
		));
		
		foreach ($this->request_types as $request_type) {
			$this->setDefaults(array(
				$type.'_'.$request_type->name => ($this->program_settings->{'student_switch_'.$type} & $request_type->bit_value ? 1 : 0),
				$type.'_'.$request_type->name.'_permission' => ($this->program_settings->{'switch_'.$type.'_needs_permission'} & $request_type->bit_value ? 1 : 0)
			));
		}
	}
	
	public function process($data)
	{		
		// Map the data fields to the program settings entity...
		$this->program_settings->student_view_full_calendar = (boolean) $data['student_view_full_calendar'];
		$this->program_settings->send_scheduler_student_notifications = (boolean) $data['sendStudentNotifications'];

		// process the prompts for each site type
		foreach ($this->site_types as $type) {
			$this->program_settings->{'student_pick_'.$type} = (boolean) $data[$type.'_pick'];
			
			$set_create_method = "set_can_students_create_".$type;
			$this->program->$set_create_method((boolean) $data[$type.'_create']);
			
			// count up the bitwise code for each request type
			$ability = 0;
			$permission = 0;
			foreach ($this->request_types as $request_type) {
				if ($data[$type.'_'.$request_type->name]) {
					$ability += $request_type->bit_value;
				}
				if ($data[$type.'_'.$request_type->name.'_permission']) {
					$permission += $request_type->bit_value;
				}
			}
		
			$this->program_settings->{'student_switch_'.$type} = $ability;
			$this->program_settings->{'switch_'.$type.'_needs_permission'} = $permission;


            // -------------------- default windows! --------------------

            // now lets save the default windows
            $form_id = "new_window_" . $type;
            $new_window = true;

            // is there already a default window for this program/type?
            if($this->program_settings->{'default_'.$type.'_window'}){
                $window = $this->program_settings->{'default_'.$type.'_window'};
                $new_window = false;
            }
            else {

                // Create a new default window
                $window = \Fisdap\EntityUtils::getEntity('Window');

                // Set the program to the current one
                $window->program = $this->program;

                // Flag it as a default window
                $window->default_window = true;

            }

            // is sign up for this shift on?
            if($data[$type.'_pick']){


                // -------------------- offsets --------------------

                // Save the offset type
                $window->set_offset_type_start($data['offset_type_start_' . $form_id]);
                $window->set_offset_type_end($data['offset_type_end_' . $form_id]);

                $window = $this->saveOffsetValues("start", $data, $window, $form_id);
                $window = $this->saveOffsetValues("end", $data, $window, $form_id);

                // -------------------- start/end dates --------------------

                $today = new DateTime();

                // Only save a start date if it's a static offset type
                if($data['offset_type_start_' . $form_id] == 1) {
                    $window->set_start_date($window->offset_value_start[0]);
                }
                else {
                    // give it todays date so everyone is happy
                    $window->set_start_date($today);
                }

                // Only save an end date if it's a static offset type
                if($data['offset_type_end_' . $form_id] == 1) {
                    $window->set_end_date($window->offset_value_end[0]);
                }
                else {
                    // give it todays date so everyone is happy
                    $window->set_end_date($today);
                }


                $window->active = 1;
                $window->save();

                if($new_window) {
                    $this->program_settings->{'default_' . $type . '_window'} = $window;
                }

            }
            else {
                // if there is an existing default window for this type, discard any changes, just deactivate it
                if(!$new_window) {
                    $window->active = 0;
                    $window->save();
                }
            }

		}
		
		$this->program->save();
	}

    public function saveOffsetValues($offset, $data, $window, $form_id)
    {
        $offset_type = $data['offset_type_' . $offset . '_' . $form_id];

        if($offset_type == 1) {
            // If the offset type is 1, we have a STATIC date
            $window->{'offset_value_' . $offset} = array($data['offset_value_' . $offset . '_static_' . $form_id]);

        }
        else if($offset_type == 2){
            // If the offset type is 2, we have an INTERVAL date
            $window->{'offset_value_' . $offset} = array($data['offset_value_' . $offset . '_interval_' . $form_id], $data['offset_value_' . $offset . '_interval_type_' . $form_id]);
        }
        else if($offset_type == 3){
            // If the offset type is 3, we have a MONTHLY date
            $window->{'offset_value_' . $offset} = array($data['offset_value_' . $offset . '_prevMonth_' . $form_id]);
        }
        else {
            // If the offset type is 4, we have a SHIFT CREATION date
            $window->{'offset_value_' . $offset} =  array(null);
        }

        return $window;

    }
	
}
