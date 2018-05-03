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
 * Compliance Notification Form
 */

/**
 * @package    Scheduler
 * @subpackage Forms
 */
class Scheduler_Form_NotificationSubForm extends Fisdap_Form_Base
{
    /**
	 * @var \Fisdap\Entity\ProgramLegacy
	 */
	private $program;
	
	/**
	 * @var \Fisdap\Entity\RequirementNotification
	 */
	private $notification_settings;
	
	/**
	 * @var array
	 */
	public $warning_ids;
	
	/**
	 * @var boolean
	 */
	private $new_req;
    
	/**
     * @var array decorators for slider checkboxes
     */
	public static $checkboxDecorators = array(
		'ViewHelper',
		'Errors',
		array('HtmlTag', array('tag' => 'div', 'class' => 'slider')),
		array('Label', array('tag' => 'div', 'openOnly' => true, 'placement' => 'append', 'class' => 'slider-label', 'escape'=>false)),
		
	);
	
	/**
     * @var array decorators for text boxes
     */
	public static $textDecorators = array(
		'ViewHelper',
		'Errors',
		array('HtmlTag', array('tag' => 'div', 'class' => 'textbox')),
	);
	
	/**
     * @var array decorators for hidden elements
     */
    public static $hiddenDecorators = array(
        'ViewHelper',
    );
	
    public function __construct($programId = null, $requirement_id = null, $new_req = false)
    {
		if ($programId) {
			$program = \Fisdap\EntityUtils::getEntity('ProgramLegacy', $programId);
		} else {
			$program = \Fisdap\EntityUtils::getEntity('ProgramLegacy', \Fisdap\Entity\User::getLoggedInUser()->getProgramId());
		}
		$this->program = $program;
		
		// figure out if this is requirement-specific form, or to use the program defaults
		if ($requirement_id) {
			$this->notification_settings = \Fisdap\EntityUtils::getRepository("Requirement")->getNotificationSettings($this->program->id, $requirement_id);
		} else {
			$this->notification_settings = \Fisdap\EntityUtils::getRepository("Requirement")->getNotificationDefaultsByProgram($this->program->id);
		}
		
		// if no notification settings exist, make a new one
		if (!$this->notification_settings) {
			$new_notification = \Fisdap\EntityUtils::getEntity('RequirementNotification');
			$new_notification->set_requirement($requirement_id);
			$new_notification->set_program($program->id);
			$new_notification->save();
			$this->notification_settings = $new_notification;
			
		}
		
		$this->new_req = $new_req;
		
        parent::__construct();
    }


    public function init()
    {
		$this->addJsFile("/js/library/Scheduler/Form/notification-sub-form.js");
		$this->addCssFile("/css/library/Scheduler/Form/notification-sub-form.css");
		
		// on assign checkbox
		$onAssign = new Zend_Form_Element_Checkbox('on_assign');
		$onAssign->setLabel("Send a notification when a requirement is assigned.")
				 ->setAttribs(array("class" => "notification-sliders"));
		
		// warning elements 
		$warnings = $this->notification_settings->warnings;
		$warning_ids = array();
		$added_warnings = 0;
		if (count($warnings) > 0) {
			foreach ($warnings as $warning) {
				// if this is a new requirement, we're working off the default settings and so
				// we need to make new copies of the warnings
				if ($this->new_req) {
					$added_warnings++;
					$warning_ids[] = $id = "new".$added_warnings;
					$switch = $warning->send_warning_notification;
					$offset = $warning->warning_offset_value;
					$values = array("switch" => $switch, "offset" => $offset);
					$this->addWarningElement($id, $values);
				} else {
					$this->addWarningElement($warning);
					$warning_ids[] = $warning->id;
					$added_warnings = 0;
				}
			}
		} else {
				$warning_ids[] = $id = "new1";
				$this->addWarningElement($id);
				$added_warnings = 1;
		}
		$this->warning_ids = $warning_ids;
		
		// keep track of the warnings being added/deleted
		$addedWarnings = new Zend_Form_Element_Hidden("added_warnings");
		$deletedWarnings = new Zend_Form_Element_Hidden("deleted_warnings");
		
		// on non-compliant checkbox
		$onNoncompliant = new Zend_Form_Element_Checkbox('on_noncompliant');
		$onNoncompliant->setLabel("Send a non-compliant notification on the day the assignment is due or expires.")
					   ->setAttribs(array("class" => "notification-sliders"));
		
		$this->addElements(array($onAssign, $addedWarnings, $deletedWarnings, $onNoncompliant));
		
		// set element decorators
		$this->setElementDecorators(self::$checkboxDecorators, array('on_assign', 'on_noncompliant'));
		$this->setElementDecorators(self::$hiddenDecorators, array('added_warnings', 'deleted_warnings'));
		
		//Set the decorators for this form
		$this->setDecorators(array(
			'PrepareElements',
			array('ViewScript', array('viewScript' => "compliance/notification-sub-form.phtml")),
			'Form',
		));

		// Set up the defaults for the form...
		$this->setDefaults(array(
			'on_assign' => $this->notification_settings->send_assignment_notification,
			'on_noncompliant' => $this->notification_settings->send_non_compliant_assignment,
			'added_warnings' => $added_warnings
		));
    }
    
	public function isValid($data)
    {
		$warnings = array();
		foreach ($data as $id => $value) {

			// validate the warning offsets
			if (substr($id, 0, 24) == "warning_frequency_offset") {
				// if this is a new element, add it to the form
				if (substr($id, 0, 28) == "warning_frequency_offset_new") {
					$newId = substr($id, 25);
					$switch = $data['warning_switch_'.$newId];
					$values = array("switch" => $switch, "offeset" => $value);
					$this->addWarningElement($newId, $values);
				}
				
				$element = $this->getElement($id);
				//Add a validator for checking against the other values on the form
				$duplicateValidator = new Fisdap_Validate_NotInArray($warnings);
				$duplicateValidator->setMessage("Two warnings cannot be sent on the same day. Please enter a different number.");
				$element->addValidator($duplicateValidator);
				
				// keep track of the offset values
				$warnings[] = $value;
			}
			
		}
		return parent::isValid($data);
	}
	
    public function process($data, $bulk = false)
    {	
		$notification = $this->notification_settings;
		if ($bulk) {
			$base_notification = \Fisdap\EntityUtils::getRepository("Requirement")->getNotificationDefaultsByProgram($this->program->id);
			// clear out the warnings, we'll be basing the new settings on the program defaults
			foreach ($notification->warnings as $existing_warning) {
				$existing_warning->delete();
			}
		} else {
			$base_notification = $notification;
		}
		
		$notification->send_assignment_notification = $data['on_assign'];
		$notification->send_non_compliant_assignment = $data['on_noncompliant'];

		foreach ($base_notification->warnings as $base_warning) {
			// make new warning for the bulk edits
			if ($bulk) {
				$warning = \Fisdap\EntityUtils::getEntity('RequirementNotificationWarning');
				$warning->set_notification($notification);
			} else {
				$warning = $base_warning;
			}
			$base_warning_id = $base_warning->id;

			$warning->send_warning_notification = (is_null($data['warning_switch_'.$base_warning_id])) ? 0 : $data['warning_switch_'.$base_warning_id];
			$warning->warning_offset_value = $data['warning_frequency_offset_'.$base_warning_id];
			$warning->save();
		}
		
		$added_warnings = $data['added_warnings'];
		for ($i=1;$i<=$added_warnings;$i++) {
			$new_warning = \Fisdap\EntityUtils::getEntity('RequirementNotificationWarning');
			$warning_id = "new".$i;
			
			$new_warning->set_notification($notification);
			$new_warning->send_warning_notification = ($data['warning_switch_'.$warning_id]) ? true : false;
			$new_warning->warning_offset_value = $data['warning_frequency_offset_'.$warning_id];
			$new_warning->save();
		}
		
		$notification->save();
	}
	
	private function addWarningElement($warning, $values = array()) {
		$id = (is_string($warning)) ? $warning : $warning->id;
		$warningSwitch = new Zend_Form_Element_Checkbox('warning_switch_'.$id);
		$warningSwitch->setAttribs(array("class" => "notification-sliders warning-switch"));
				
		$warningFrequencyOffset = new Zend_Form_Element_Text('warning_frequency_offset_'.$id);
		$warningFrequencyOffset->setAttribs(array("class" => "warning-frequency-offset fancy-input"))
							   ->setRequired(true)
							   ->addValidator('Digits', true, array('messages' => array('notDigits' => 'Please enter a whole number.')))
							   ->addValidator('Between', true, array('min' => 1, 'max' => 365, 'messages' => array('notBetween' => 'Please enter a number from 1 to 365.')));

		$this->addElements(array($warningSwitch, $warningFrequencyOffset));
		
		$this->setElementDecorators(self::$checkboxDecorators, array('warning_switch_'.$id));
		$this->setElementDecorators(self::$textDecorators, array('warning_frequency_offset_'.$id));
		
		$warningSwitch->removeDecorator("Label");
		$warningFrequencyOffset->removeDecorator("Label");
		
		//set defaults
		if (is_string($warning)) {
			if ($values) {
				$switch = $values['switch'];
				$offset = $values['offset'];
			} else {
				$switch = 0;
				$offset = 30;
			}
		} else {
			$switch = $warning->send_warning_notification;
			$offset = $warning->warning_offset_value;
		}
		
		$this->setDefaults(array(
			'warning_switch_'.$id => $switch,
			'warning_frequency_offset_'.$id => $offset
		));
	}
}