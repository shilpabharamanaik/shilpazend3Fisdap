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
 * Patient Care Form
 */

/**
 * @package    SkillsTracker
 * @subpackage Forms
 */
class SkillsTracker_Form_PatientCare extends Fisdap_Form_Base
{
	/**
	 * @var \Fisdap\Entity\Patient
	 */
	public $patient;
	
	/**
	 * @var \Fisdap\Entity\Run
	 */
	public $run;

	/**
	 * @var boolean
	 *
	 * Is this a new patient?
	 */
	public $new;
	
	/**
	 * @var array decorators for individual elements
	 */
    public static $elementDecorators = array(
        'ViewHelper',
		array(array('break' => 'HtmlTag'), array('tag' => 'br', 'openOnly' => true, 'placement' => 'PREPEND')),
        array('Label', array('escape' => false)),
        array(array('prompt' => 'HtmlTag'), array('tag'=>'div', 'class'=>'form-prompt')),
    );

	/**
	 * @var array decorators for the airway management input
	 */
	public static $airwayDecorators = array(
		'ErrorHighlight',
		'ViewHelper',
		array(array('break' => 'HtmlTag'), array('tag' => 'br', 'openOnly' => true, 'placement' => 'PREPEND')),
		array('Label', array('escape' => false)),
		array(array('prompt' => 'HtmlTag'), array('tag'=>'div', 'class'=>'form-prompt airway_management_options_wrapper')),
	);


	/**
	 * @var array decorators
	 */
	public static $orientationDecorators = array(
		'ViewHelper',
		array('Label', array('placement' => 'PREAPPEND')),
	);
	
	/**
	 * @var array decorators for select objects
	 */
	public static $selectDecorators = array(
		'ViewHelper',
		array(array('break' => 'HtmlTag'), array('tag' => 'br', 'openOnly' => true, 'placement' => 'PREPEND')),
        array('Label', array('escape' => false)),
        array(array('prompt' => 'HtmlTag'), array('tag'=>'div', 'class'=>'form-prompt select-prompt')),
	);
	
	/**
	 * @var array decorators for checkbox elements
	 */
	public static $checkboxDecorators = array(
		'ViewHelper',
		array('Label', array('placement' => 'APPEND', 'escape' => false)),
		array(array('prompt' => 'HtmlTag'), array('tag'=>'div', 'class'=>'form-prompt')),
	);
	
	/**
	 * @var array decorators for buttons
	 */
	public static $buttonDecorators = array(
		'ViewHelper',
	);
	
	/**
	 * @var array decorators for hidden elements
	 */
	public static $hiddenElementDecorators = array(
		'ViewHelper',
	);
	
	/**
	 * @param int $patientId the id of the shift to edit
	 * @param $options mixed additional Zend_Form options
	 */
	public function __construct($patientId = null, $runId = null, $new = false, $options = null)
	{
		$this->patient = \Fisdap\EntityUtils::getEntity('Patient', $patientId);
		$this->run = \Fisdap\EntityUtils::getEntity('Run', $runId);
		$this->new = $new;
		
		parent::__construct($options);
	}
	
	public function init()
	{
		parent::init();
		
		$this->setAttrib('id', 'patientCareForm');
		$this->addJsFile("/js/library/SkillsTracker/Form/patient-care.js?123");
		
		$subject = new SkillsTracker_Form_Element_Subject('subject');
		$subject->setLabel("Subject:");

		$teamLead = new Zend_Form_Element_Checkbox('teamLead');
		$teamLead->setLabel("I was the Team Leader." . self::NSC_DIAMOND);
		
		$teamSize = new Zend_Form_Element_Text('teamSize');
		$teamSize->setLabel("Team size:")
				 ->setAttrib('size', '4');
		//xdebug_break();
		
		
		$preceptor = new SkillsTracker_Form_Element_Preceptor('preceptor');
		// preceptor is required for field shifts
		if ($this->run->shift->type == "field"){
			$preceptor->setLabel(self::REQUIRED_SYMBOL . "Preceptor:");
		} else {
			$preceptor->setLabel("Preceptor:");
		}
		$preceptorOptions = \Fisdap\Entity\PreceptorLegacy::getSortedPreceptorSelect($this->run);
		$preceptor->setMultiOptions($preceptorOptions);
		
		$responseMode = new Zend_Form_Element_Radio('responseMode');
		$responseMode->setLabel("Response Mode to Scene:")
					 ->setMultiOptions(\Fisdap\Entity\ResponseMode::getFormOptions());
		
		$interview = new Zend_Form_Element_Checkbox("interview");
		$interview->setLabel("I performed the patient interview");
		
		$exam = new Zend_Form_Element_Checkbox("exam");
		$exam->setLabel("I performed the patient exam");
		
		$airway_success = new Zend_Form_Element_Checkbox("airway_success");
		$airway_success->setLabel("The patient required airway management");

		$airway_management_options = new Zend_Form_Element_Radio("airway_management_options");
		$airway_management_options->setMultiOptions(array(
			"0" => "but I did not manage the patient's airway.",
			"1" => "and I successfully managed the patient's airway.",
			"2" => "but I did not successfully manage the patient's airway."
		));

		$age = new SkillsTracker_Form_Element_Age("age");
		$age->setLabel(self::REQUIRED_SYMBOL . "Age:" . self::NSC_DIAMOND)
			->setAttrib('size', '4');

		$gender = new Zend_Form_Element_Radio("gender");
		$gender->setLabel(self::REQUIRED_SYMBOL . "Gender:")
			   ->setMultiOptions(\Fisdap\Entity\Gender::getFormOptions())
			   ->setSeparator(" ");

		$ethnicity = new Zend_Form_Element_Select("ethnicity");
		$ethnicity->setLabel("Ethnicity:")
					 ->setMultiOptions(\Fisdap\Entity\Ethnicity::getFormOptions(true));

		$alert = new Zend_Form_Element_Radio("alert");
		$alert->setLabel("Patient Alert and Oriented?")
			  ->setMultiOptions(\Fisdap\Entity\MentalAlertness::getFormOptions(false, false));

		$orientation = new Zend_Form_Element_MultiCheckbox("orientation");
		$orientation->setLabel("Oriented to:")
					->setMultiOptions(\Fisdap\Entity\MentalOrientation::getFormOptions(false, false))
			        ->setSeparator(" ");

		/*
		$response = new Zend_Form_Element_MultiCheckbox("response");
		$response->setLabel("Responds to:")
				 ->setMultiOptions(\Fisdap\Entity\MentalResponse::getFormOptions());
		*/

		$complaints = new Zend_Form_Element_MultiCheckbox("complaints");
		$complaints->setLabel("Complaints:")
				   ->setMultiOptions(\Fisdap\Entity\Complaint::getFormOptions())
				   ->setAttrib('helper', 'multiCheckboxList')
				   ->setAttrib('numColumns', 2)
				   ->setSeparator('');

		$primary = new Zend_Form_Element_Select("primary");
		$primary->setLabel(self::REQUIRED_SYMBOL . "Primary Impression:" . self::NSC_DIAMOND)
					 ->setMultiOptions(\Fisdap\Entity\Impression::getFormOptions(true));

		$secondary = new Zend_Form_Element_Select("secondary");
		$secondary->setLabel("Secondary Impression:" . self::NSC_DIAMOND)
				  ->setMultiOptions(\Fisdap\Entity\Impression::getFormOptions(true));

		$witness = new Zend_Form_Element_Radio("witness");
		$witness->setLabel("Arrest witness by:")
					 ->setMultiOptions(\Fisdap\Entity\Witness::getFormOptions());

		$pulseReturn = new Zend_Form_Element_Radio("pulseReturn");
		$pulseReturn->setLabel("Return of Pulse:")
					 ->setMultiOptions(\Fisdap\Entity\PulseReturn::getFormOptions());

		$mechanism = new Zend_Form_Element_MultiCheckbox("mechanism");
		$mechanism->setLabel("Mechanism of Injury:")
				  ->setMultiOptions(\Fisdap\Entity\Mechanism::getFormOptions());

		$cause = new Zend_Form_Element_Select("cause");
		$cause->setLabel("Cause of Injury:")
			  ->setMultiOptions(\Fisdap\Entity\Cause::getFormOptions(true));

		$intent = new Zend_Form_Element_Radio("intent");
		$intent->setLabel("Intent of Injury:")
			   ->setMultiOptions(\Fisdap\Entity\Intent::getFormOptions(true));

		$criticality = new Zend_Form_Element_Radio("criticality");
		$criticality->setLabel("Patient Criticality:")
					->setMultiOptions(\Fisdap\Entity\PatientCriticality::getFormOptions(false, false));

		$disposition = new Zend_Form_Element_Select("disposition");
		$disposition->setLabel("Patient Disposition:")
					->setMultiOptions(\Fisdap\Entity\PatientDisposition::getFormOptions(true));

		$transport = new Zend_Form_Element_Select("transport");
		$transport->setLabel("Transport Mode From Scene:")
				  ->setMultiOptions(\Fisdap\Entity\ResponseMode::getFormOptions(true));

		$save = new Fisdap_Form_Element_SaveButton('save');

		$cancel = new Fisdap_Form_Element_CancelButton('cancel');

		$hiddenId = new Zend_Form_Element_Hidden('hiddenPatientId');
		$runId = new Zend_Form_Element_Hidden('runId');
		$formName = new Zend_Form_Element_Hidden('formName');
		$formName->setValue('PatientCare');

		$this->addElements(array(
			$subject,
			$teamLead,
			$teamSize,
			$preceptor,
			$responseMode,
			$interview,
			$exam,
			$airway_success,
			$airway_management_options,
			$age,
			$gender,
			$ethnicity,
			$alert,
			$orientation,
		//	$response,
			$complaints,
			$primary,
			$secondary,
			$witness,
			$pulseReturn,
			$mechanism,
			$cause,
			$intent,
			$criticality,
			$disposition,
			$transport,
			$hiddenId,
			$save,
			$cancel,
			$formName,
			$runId,
		));

		$this->setElementDecorators(self::$elementDecorators, array('hiddenPatientId', 'runId', 'interview', 'exam', 'airway_success', 'teamLead', 'save', 'cancel', 'formName'), false);
		$this->setElementDecorators(self::$checkboxDecorators, array('teamLead', 'interview', 'exam', 'airway_success'), true);
		$this->setElementDecorators(self::$hiddenElementDecorators, array('hiddenPatientId', 'save', 'cancel', 'formName', 'runId'), true);
		$this->setElementDecorators(self::$selectDecorators, array('responseMode', 'criticality', 'complaints', 'alert'), true);
		$this->setElementDecorators(self::$gridElementDecorators, array('preceptor', 'teamSize', 'age', 'gender', 'ethnicity', 'subject'), true);
		$this->setElementDecorators(self::$airwayDecorators, array('airway_management_options'), true);
		$this->setElementDecorators(self::$orientationDecorators, array('orientation'), true);

		$this->setDecorators(array(
			'PrepareElements',
			array('ViewScript', array('viewScript' => "patientCareForm.phtml")),
			'Form',
		));

		// EDITING AN EXISTING PATIENT
		if ($this->patient->id) {
			$am_repo = \Fisdap\EntityUtils::getRepository('AirwayManagement');
			$airway_management = \Fisdap\EntityUtils::getEntity('AirwayManagement', $am_repo->getIdByPatient($this->patient->id));
			$airway_success_val = ($airway_management->id) ? 1 : 0;

			$airway_management_options_val = 0;

			if($airway_management->id){
				if($airway_management->performed_by === true){
					$airway_management_options_val = ($airway_management->success) ? "1" : "2";
				} else {
					$airway_management_options_val = ($airway_management->performed_by === false) ? "0" : null;
				}
			}

			$this->setDefaults(array(
				'teamLead' => $this->patient->team_lead,
				'teamSize' => $this->patient->team_size,
				'preceptor' => $this->patient->preceptor->id,
				'interview' => $this->patient->interview,
				'exam' => $this->patient->exam,
				'airway_success' => $airway_success_val,
				'airway_management_options' => $airway_management_options_val,
				'age' => array('years' => $this->patient->age, 'months' => $this->patient->months),
				'gender' => $this->patient->gender->id,
				'ethnicity' => $this->patient->ethnicity->id,
				'primary' => $this->patient->primary_impression->id,
				'secondary' => $this->patient->secondary_impression->id,
				'complaints' => $this->patient->getComplaintIds(),
				'responseMode' => $this->patient->response_mode->id,
				'criticality' => $this->patient->patient_criticality->id,
				'disposition' => $this->patient->patient_disposition->id,
				'transport' => $this->patient->transport_mode->id,
				'alert' => $this->patient->msa_alertness->id,
				'orientation' => $this->patient->msa_orientations,
				'response' => $this->patient->msa_responses,
				'mechanism' => $this->patient->mechanisms,
				'cause' => $this->patient->cause->id,
				'intent' => $this->patient->intent->id,
				'witness' => $this->patient->witness->id,
				'pulseReturn' => $this->patient->pulse_return->id,
				'hiddenPatientId' => $this->patient->id,
                'subject' => $this->patient->subject->id,
				'runId' => $this->run->id,
			));
		} else {
			// ADDING A NEW PATIENT
			// note: this will never actually happen because of the way we get to this page; there should always
			// already be a pre-existing patient associated with the run
			$this->setDefaults(array(
				'responseMode' => 1,
				'gender' => 1,
				'alert' => 2,
				'criticality' => 1,
				'intent' => 1,
				'witness' => 1,
				'pulseReturn' => 1,
				'cause' => 0,
				'ethnicity' => 6,
				'primary' => 0,
				'secondary' => 0,
				'intent' => 0,
				'disposition' => 1,
				'responseMode' => 3,
				'runId' => $this->run->id,
			));
		}

		// if this is a new patient, set the preceptor (if it's unset
		if ($this->new && $this->patient->preceptor->id < 1) {
			if (!is_null($this->run->shift->event_id) && $this->run->shift->event_id != -1){
				//get preceptor for this event and set default preceptor value to be that preceptor
				$event = \Fisdap\EntityUtils::getEntity('EventLegacy', $this->run->shift->event_id);
                if ($event) {
                    $preceptor = $event->getFirstPreceptor();
                    $this->setDefaults(array('preceptor' => $preceptor->id));
                }
			}
		}
	}

	/**
	 * Validate the form, if valid, save something, if not, return the error msgs
	 *
	 * @param array $data the POSTed data
	 * @return mixed either boolean true, or an array of error messages
	 */
	public function process($data)
	{

		if ($this->isValid($data)) {
			$values = $this->getValues($data);

			if ($values['hiddenPatientId']) {
				$patient = \Fisdap\EntityUtils::getEntity('Patient', $values['hiddenPatientId']);
			} else {
				$patient = \Fisdap\EntityUtils::getEntity('Patient');
			}
			$patient->team_lead = $values['teamLead'];
			$patient->team_size = $values['teamSize'];
			$patient->preceptor = ($values['preceptor'] == 0) ? null : $values['preceptor'];
			$patient->interview = $values['interview'];
			$patient->exam = $values['exam'];
			$patient->age = $values['age']['years'];
			$patient->months = $values['age']['months'];
			$patient->gender = $values['gender'];
			$patient->ethnicity = $values['ethnicity'];
			$patient->primary_impression = $values['primary'];
			$patient->secondary_impression = $values['secondary'];
			$patient->setComplaintIds($values['complaints']);
			$patient->response_mode = $values['responseMode'];
			$patient->patient_criticality = $values['criticality'];
			$patient->patient_disposition = $values['disposition'];
			$patient->transport_mode = $values['transport'];

			$patient->msa_alertness = $values['alert'];
			if ($patient->msa_alertness->id == 1) {
				$patient->msa_orientations = $values['orientation'];
				$patient->msa_responses = $values['response'];
			} else {
				$patient->msa_orientations = null;
				$patient->msa_responses = null;
			}


			if (($patient->primary_impression && $patient->primary_impression->isTrauma()) || ($patient->secondary_impression && $patient->secondary_impression->isTrauma())) {
				$patient->mechanisms = $values['mechanism'];
				$patient->cause = $values['cause'];
				$patient->intent = $values['intent'];
			} else {
				$patient->mechanisms = null;
				$patient->cause = null;
				$patient->intent = null;
			}

			if (($patient->primary_impression && $patient->primary_impression->isArrest()) || ($patient->secondary_impression && $patient->secondary_impression->isArrest())) {
				$patient->witness = $values['witness'];
				$patient->pulse_return = $values['pulseReturn'];
			} else {
				$patient->witness = null;
				$patient->pulse_return = null;
			}

			$run = \Fisdap\EntityUtils::getEntity('Run', $values['runId']);

			if($run->shift->type == 'lab'){
				$subject = \Fisdap\EntityUtils::getEntity('Subject', $values['subject']);
				$patient->set_subject($subject);
			}

            $airway_result = $this->saveAirwayManagement($values, $patient, $run->shift->type);
			$patient->airway_success = $airway_result;

            //if we get NULL back from saveAirwayManagement, we need to delete the existing airway management tied to the patient.
            if(is_null($airway_result)){
                if($patient->airway_management) {
                    $patient->airway_management = $airway_result;
                }
            }

            $run->addPatient($patient);
			$run->save();

			return true;
		}

		return $this;
	}

	private function saveAirwayManagement($values, $patient, $shift_type)
	{
		$airway_success = null;
		$am_repo = \Fisdap\EntityUtils::getRepository('AirwayManagement');

		if($values['airway_success'] == 1){

			// The patient required airway management
			if($values['hiddenPatientId']){
				$existing_airway_management_id = $am_repo->getIdByPatient($patient->id);

				if($existing_airway_management_id){
					$airway_management = \Fisdap\EntityUtils::getEntity('AirwayManagement', $existing_airway_management_id);
				}
				else {
					$airway_management = \Fisdap\EntityUtils::getEntity('AirwayManagement');
				}
			}
			else {
				$airway_management = \Fisdap\EntityUtils::getEntity('AirwayManagement');
			}

			$airway_management->airway_management_source = \Fisdap\EntityUtils::getEntity('AirwayManagementSource', 2);
			$airway_management->patient = $patient;
			$airway_management->shift = $patient->shift;

			if($shift_type == 'lab'){
				$airway_management->subject = \Fisdap\EntityUtils::getEntity('Subject', $values['subject']);
			}
			else {
				$airway_management->subject = \Fisdap\EntityUtils::getEntity('Subject', 1);
			}


			if($values['airway_management_options'] == "0"){
				// but I did not manage the patient's airway
				$airway_management->performed_by = false;
				$airway_success = false;
			}
			else if($values['airway_management_options'] == "1"){
				// and I successfully managed the patient's airway
				$airway_management->performed_by = true;
				$airway_management->success = true;
				$airway_success = true;
			}
			else if($values['airway_management_options'] == "2"){
				// but I did not successfully manage the patient's airway
				$airway_management->performed_by = true;
				$airway_management->success = false;
				$airway_success = false;
			}
			else {
				// no radio buttons have been selected. we can really complete this record
				$airway_management->performed_by = null;
				$airway_management->success = null;
				$airway_success = false;
			}

			$airway_management->save();

		}
		else {

			// remove the airway_management if it exists
			// The patient required airway management
			if($values['hiddenPatientId']){
				$airway_management = \Fisdap\EntityUtils::getEntity('AirwayManagement', $am_repo->getIdByPatient($patient->id));
				$airway_management->delete();
				$airway_success = null;
			}

		}

		return $airway_success;
	}
}
