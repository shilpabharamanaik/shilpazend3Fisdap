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
 * This produces a modal form for creating a pdf for scheduler
 */

/**
 * @package    Scheduler
 * @subpackage Forms
 */
class Scheduler_Form_PdfExportModal extends Fisdap_Form_BaseJQuery
{
	/**
	 * @var \Fisdap\Entity\ScheduleEmail
	 */
	public $email;
	
	/**
	 * @var bool
	 */
	public $is_inst;

    /**
     * @var bool
     */
    public $is_staff;

    /**
     * @var  \Fisdap\Entity\ProgramLegacy
     */
    public $program;

	/**
     * @var array decorators for individual elements
     */
	public static $basicDecorators = array(
		'ViewHelper',
		array('Description', array('tag' => 'span', 'openOnly' => true, 'placement' => 'prepend', 'class' => 'description')),
		'Errors',
		array('Label', array('tag' => 'span', 'openOnly' => true, 'placement' => 'prepend', 'class' => 'section-header no-border', 'escape'=>false)),
	);
	
	/**
     * @var array decorators for hidden elements
     */
    public static $hiddenDecorators = array(
        'ViewHelper',
    );


	/**
	 *
	 * @param $options mixed additional Zend_Form options
	 */
	public function __construct($emailId = null, $options = null)
	{
		if ($emailId) {
			$this->email = \Fisdap\EntityUtils::getEntity('ScheduleEmail', $emailId);
		}
		
		$this->is_inst = \Fisdap\Entity\User::getLoggedInUser()->isInstructor();
        $this->is_staff = \Fisdap\Entity\User::getLoggedInUser()->isStaff();
		
		parent::__construct($options);
	}
	
	public function init()
	{
		parent::init();
		
		$programId = \Fisdap\Entity\User::getLoggedInUser()->getProgramId();
		
		$this->addJsFile("/js/library/Scheduler/Form/pdf-export-modal.js");
		$this->addJsFile("/js/jquery.sliderCheckbox.js");
		$this->addCssFile("/css/jquery.sliderCheckbox.css");
		
		// create form elements
		$viewType = new Zend_Form_Element_Hidden("view_type");
		$name = new Zend_Form_Element_Text('name');
		$name->setAttribs(array("class" => "fancy-input"));

		$type = new Zend_Form_Element_Hidden('pdf_export_type');
		
		$startDate = new Zend_Form_Element_Text("start_date");
		$startDate->setLabel("From:")
				  ->setAttribs(array("class" => "selectDate fancy-input"));
		$date = new DateTime();
		$startDate->setValue($date->format("m/d/Y"));
		
		$endDate = new Zend_Form_Element_Text("end_date");
		$endDate->setLabel("Through:")
				->setAttribs(array("class" => "selectDate fancy-input"));
		
		$legend = new Zend_Form_Element_Checkbox('legend_switch');
		$legend->setRequired(false);
		
		// email option elements
		$email_subject = new Zend_Form_Element_Text('email_subject');
		$email_subject->setLabel("Subject:");
		$email_subject->setAttribs(array("class" => "fancy-input"));

		
		$email_recipients = new Zend_Form_Element_Textarea('email_recipients');
		$email_recipients->setAttribs(array("class" => "fancy-input"));
		$email_recipients->setLabel("Recipients:")
					     ->setDescription("(comma separated)");
		
		$email_note = new Zend_Form_Element_Textarea('email_note');
		$email_note->setAttribs(array("class" => "fancy-input"));
		$email_note->setLabel("Message:")
				   ->setRequired(false)
                   ->addValidator("StringLength", true, array('max' => '500'))
                   ->addErrorMessage('Please enter a message that is less than 500 characters long.');
				   
		// recurring email options
		$frequencyOffset = new Zend_Form_Element_Select('email_frequency_offset');
		$frequencyOffsetOptions = array();
		for($i = 1; $i <= 6; $i++){
			$frequencyOffsetOptions[$i] = $i;
		}
		$frequencyOffset->setMultiOptions($frequencyOffsetOptions)
				  ->setAttribs(array("class" => "chzn-select",
									 "multiple" => false));

        // Staff only options
        if($this->is_inst && $this->is_staff){
            // Get an array of non-staff instructors who have "View Schedules" access
            $instructors = \Fisdap\EntityUtils::getRepository('ProgramLegacy')->getNonStaffInstructorsByPermission($programId, 128);

            // Iterate through the result array and format it for use in a chosen
            $instructorOptions = array(""=>"");
            foreach ($instructors as $instructor) {
                $instructorOptions[$instructor['instructor_id']] = $instructor['first_name'] . " " . $instructor['last_name'];
            }

            // Make the chosen
            $instructor = $this->createChosen('scheduler_export_instructor', 'Instructor (staff only):', "250px", "Choose an instructor", $instructorOptions, false);
            $instructor->setValue('');
        }

		// hidden elements
		$recurringType = new Zend_Form_Element_Hidden("pdf_recurring_type");
		$orientationType = new Zend_Form_Element_Hidden("pdf_orientation_type");
		$colorType = new Zend_Form_Element_Hidden("pdf_color_type");
		$frequencyOffsetType = new Zend_Form_Element_Hidden("pdf_email_frequency_offset_type");
		$email_template = new Zend_Form_Element_Hidden("email_template");
		$recurring_email_id = new Zend_Form_Element_Hidden("email_id");
		
		// Add elements
		$this->addElements(array($viewType, $name, $type, $startDate, $endDate, $recurringType, $orientationType, $colorType, $legend,
								 $email_subject, $email_recipients, $email_note, $email_template, $recurring_email_id,
								 $frequencyOffset, $frequencyOffsetType, $instructor));
		$this->setElementDecorators(self::$basicDecorators, array('name', 'start_date', 'end_date', 'legend_switch',
																  'email_subject', 'email_recipients', 'email_note',
																  'email_frequency_offset'));
		$name->removeDecorator('Label');
		$legend->removeDecorator('Label');
		$frequencyOffset->removeDecorator('Label');
		$this->setElementDecorators(self::$hiddenDecorators, array('pdf_export_type',
																   'pdf_recurring_type',
																   'pdf_orientation_type',
																   'pdf_color_type',
																   'view_type',
																   'email_template',
																   'pdf_email_frequency_offset_type',
																   "email_id",));
	
		// set defaults
		$this->setDefaults(array(
			'pdf_export_type' => 'pdf',
			'pdf_recurring_type' => 'month',
			'pdf_orientation_type' => 'portrait',
			'pdf_color_type' => 'color',
			'email_template' => 'email-schedule.phtml',
			'pdf_email_frequency_offset_type' => 'freq_day',
			"email_id" => $this->email->id,
        ));
		
		// if we're editing a recurring email, set some other defaults
		if ($this->email->id) {
			$this->setDefaults(array(
				'pdf_export_type' => 'recurring',
				'name' => $this->email->title,
				'email_subject' => $this->email->email_subject,
				'email_recipients' => $this->email->email_list,
				'email_note' => $this->email->email_note,
				'legend_switch' => $this->email->legend,
				'pdf_recurring_type' => $this->email->recurring_type,
				'pdf_orientation_type' => $this->email->orientation,
				'pdf_color_type' => $this->email->color ? 'color' : 'b-and-w',
				'pdf_email_frequency_offset_type' => "freq_".$this->email->offset_type,
				'email_frequency_offset' => $this->email->offset_number,
                'scheduler_export_instructor' => $this->email->instructor->id,
			));
		}
		
		$this->setDecorators(array(
			'PrepareElements',
            array('ViewScript', array('viewScript' => "pdfExportModal.phtml")),
			'Form',
			array('DialogContainer',
				array(
					'id'			=> 'pdfDialog',
					'class'			=> 'pdfDialog',
					'jQueryParams' 	=> array(
						'tabPosition' 	=> 'top',
						'modal' 		=> true,
						'autoOpen' 		=> false,
						'resizable' 	=> false,
						'width' 		=> 880,
						'title' 		=> 'Export view',
					),
				)
			),
		));
		
	}

    public function isValid($data)
    {
        // Staff only options
        if($this->is_inst && $this->is_staff) {
            // if this is a recurring pdf, make sure a NON-STAFF instructor has been picked
            $export_type = $data['pdf_export_type'];
            if ($export_type == "recurring") {
                $element = $this->getElement('scheduler_export_instructor');
                $element->setRequired(true)
                        ->addErrorMessage("Please select an instructor.");
            }
        }
        return parent::isValid($data);
    }

	/**
	 * Validate the form, if valid, send or perform the request, if not, return the error msgs
	 *
	 * @param array $data the POSTed data
	 * @return mixed either boolean true, or an array of error messages
	 */
	public function process($form_data)
	{
		if ($this->isValid($form_data)) {
			// do a little extra validation
			$values = $this->getValues($form_data);
			$errors = false;

            $export_type = $values['pdf_export_type'];
            $view_type = $values['view_type'];

			// if this is a day view, limit them to 30 days
			if ($view_type != 'list') {
				$start_date = new DateTime($values["start_date"]);
				$end_date = new DateTime($values["end_date"]);
				$diff = date_diff($start_date, $end_date);
				if ($view_type == 'day' && $diff->format('%a') > 90) {
					$msg = "Sorry, we can't show you more than 90 days at a time in day view.<br>Please switch views or limit your date range.";
					$errors = true;
				}
				if ($view_type == 'week' && ($diff->format('%a'))/7 > 52) {
					$msg = "Sorry, we can't show you more than one year at a time in week view.<br>Please switch views or limit your date range.";
					$errors = true;
				}
				if ($view_type == 'month' && ($diff->format('%a'))/365 > 5) {
					$msg = "Sorry, we can't show you more than 5 years at a time in month view.<br>Please switch views or limit your date range.";
					$errors = true;
				}
				if ($errors) {
					$this->getElement('pdf_export_type')->addError($msg);
					$this->markAsError();
				}
			}
			
			// if this is going to be an email, we need some recipients
			if ($export_type != 'pdf') {
				// strip out all extraneous commas and spaces from recipients
				$recipients  = Util_Array::getCleanArray($values['email_recipients']);
				
				if (count($recipients) < 1) {
					$this->getElement('email_recipients')->addError("Please enter a recipient.");
					$this->markAsError();
					$errors = true;
					return $this->getMessages();
				}
				
				// make sure these are valid email addresses
				foreach ($recipients as $recipient_email) {
					if (!filter_var($recipient_email, FILTER_VALIDATE_EMAIL)) {
						$this->getElement('email_recipients')->addError('"'.$recipient_email.'" is not a valid email address.');
						$this->markAsError();
						$errors = true;
					}
				}
			}
			
			if ($errors) { return $this->getMessages(); }
			
			return true;
		}

		return $this->getMessages();
	}

    /**
     * Create a Zend Select that is wrapped by some chosen options.
     * @param $elementName
     * @param $label
     * @param $width
     * @param $placeholderText
     * @param $options
     * @param string $multi
     * @param array $additionalAttribs
     * @return Zend_Form_Element_Select
     */
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

        foreach($additionalAttribs as $key => $value) {
            $chosen->setAttrib($key, $value);
        }

        return $chosen;
    }
}
