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
 * @author     Hammer :)
 * @package    Account
 * @subpackage Forms
 */
class Account_Form_Accreditation extends Fisdap_Form_Base
{
	public $site;
	public $accreditation_info;
	
	/**
	 * @param SiteLegacy $site the currrent site
	 * @param $options mixed additional Zend_Form options
	 */
	public function __construct($site, $options = null)
	{
		$this->site = $site;
		$this->accreditation_info = $this->site->getAccreditationInfoByProgram(\Fisdap\Entity\User::getLoggedInUser()->getProgramId());
		
		parent::__construct($options);
	}
	
	
	public function init()
	{
		parent::init();
		
		// for when we need to use this form elsewhere
		 if (!$this->_view) {
            $this->_view = $this->getView();
        }
		$this->_view->addScriptPath(APPLICATION_PATH . '/modules/account/views/scripts');

		
		$user = \Fisdap\Entity\User::getLoggedInUser();
		
		$this->addJsFile("/js/library/Account/Form/site-sub-forms/accreditation.js");
		$this->addCssFile("/css/library/Account/Form/site-sub-forms/accreditation.css");
		
		// ------------------ Create/add the elements --------------------
		
		// Save button
		$save = new Fisdap_Form_Element_SaveButton("save_accreditation");
		
		// CAO element
		$cao = new Zend_Form_Element_Text('accreditation_cao');
		$cao->setLabel('CAO:')
			 ->addValidator('stringLength', false, array(0, 255))
			 ->addErrorMessage("CAO cannot have more than 255 characters.")
			 ->setAttribs(array("class" => "fancy-input"));
		
		$trigger_phone_masking = ($user->getProgram()->country == "USA") ? "add-masking" : "";
		
		// Phone number element
		$phone = new Zend_Form_Element_Text('accreditation_phone');
		$phone->setLabel('Phone:');
		$phone->setAttribs(array("class" => "fancy-input " . $trigger_phone_masking, "autocomplete" => "off", "maxlength" => "255"));
		
		// Distance from program element
		$distance_from_program = new Zend_Form_Element_Text('accreditation_distance_from_program');
		$distance_from_program->setLabel('Distance from location of program:');
		$distance_from_program->addValidator('between', false, array('min' => 0, 'max' => 5000));
		$distance_from_program->addValidator('float');
		$distance_from_program->addErrorMessage("Distance from location of program must be a postive number less than 5,000.");
		$distance_from_program->setAttribs(array("class" => "fancy-input"));
		
		// preceptor training hours element
		$preceptor_training_hours = new Zend_Form_Element_Text('accreditation_preceptor_training_hours');
		$preceptor_training_hours->setLabel('For how many hours?');
		$preceptor_training_hours->addValidator('between', false, array('min' => 0, 'max' => 5000));
		$preceptor_training_hours->addErrorMessage("Preceptor training hours must be a postive number.");
		$preceptor_training_hours->addValidator('float');
		$preceptor_training_hours->setAttribs(array("class" => "fancy-input"));
		
		$this->addElements(array($cao, $phone, $distance_from_program, $preceptor_training_hours, $save));
		
		// All of the yes/no elements
		$this->createYesNoButtonset('accreditation_signed_agreement', "Is there a current signed agreement with this affiliate?", $this->accreditation_info->signed_agreement);
		$this->createYesNoButtonset('accreditation_written_policies', "Are there written policies as to what students may do in each area?", $this->accreditation_info->written_policies);
		$this->createYesNoButtonset('accreditation_formally_trained_preceptors', "Are the preceptors formally trained?", $this->accreditation_info->formally_trained_preceptors);
		
		if($this->site->type != "clinical"){
			$this->createYesNoButtonset('accreditation_online_medical_direction', "Is there online medical direction for this affiliate?", $this->accreditation_info->online_medical_direction);
			$this->createYesNoButtonset('accreditation_advanced_life_support', "Does this affiliate provide Advanced Life Support?", $this->accreditation_info->advanced_life_support);
			$this->createYesNoButtonset('accreditation_quality_improvement_program', "Is there a quality improvement program that reviews runs?", $this->accreditation_info->quality_improvement_program);
			
			// Distance from program element

			//Appendix F data fields

			$number_of_runs = new Zend_Form_Element_Text('accreditation_number_of_runs');
			$number_of_runs->setLabel('Number of runs per year:');
			$number_of_runs->setAttribs(array("class" => "fancy-input appendix-f"));
			$this->addElement($number_of_runs);

			$active_ems_units = new Zend_Form_Element_Text('accreditation_active_ems_units');
			$active_ems_units->setLabel('Number of active EMS units (excluding backups):');
			$active_ems_units->addValidator('between', false, array('min' => 0, 'max' => 5000));
			$active_ems_units->addValidator('float');
			$active_ems_units->addErrorMessage("Number of active EMS units must be a postive number less than 5,000.");
			$active_ems_units->setAttribs(array("class" => "fancy-input appendix-f"));
			$this->addElement($active_ems_units);

			$trauma_calls = new Zend_Form_Element_Text('accreditation_number_of_trauma_calls');
			$trauma_calls->setLabel("Number of trauma calls per year:");
			$trauma_calls->setAttribs(array("class" => "fancy-input appendix-f"));
			$this->addElement($trauma_calls);

			$critical_trauma_calls = new Zend_Form_Element_Text('accreditation_number_of_critical_trauma_calls');
			$critical_trauma_calls->setLabel("Number of critical trauma calls per year:");
			$critical_trauma_calls->setAttribs(array("class" => "fancy-input appendix-f"));
			$this->addElement($critical_trauma_calls);

			$pediatric_calls = new Zend_Form_Element_Text('accreditation_number_of_pediatric_calls');
			$pediatric_calls->setLabel('Number of pediatric calls per year:');
			$pediatric_calls->setAttribs(array("class" => "fancy-input appendix-f"));
			$this->addElement($pediatric_calls);

			$cardiac_arrest_calls = new Zend_Form_Element_Text("accreditation_number_of_cardiac_arrest_calls");
			$cardiac_arrest_calls->setLabel("Number of cardiac arrest calls per year:");
			$cardiac_arrest_calls->setAttribs(array("class" => "fancy-input appendix-f"));
			$this->addElement($cardiac_arrest_calls);

			$cardiac_calls = new Zend_Form_Element_Text("accreditation_number_of_cardiac_calls");
			$cardiac_calls->setLabel("Number of cardiac calls (less cardiac arrest) per year:");
			$cardiac_calls->setAttribs(array("class" => "fancy-input appendix-f"));
			$this->addElement($cardiac_calls);

		}
		
		$supervision_options = \Fisdap\EntityUtils::getRepository("SiteAccreditationInfo")->getStudentSupervisionTypeFormOptions(true, ($this->site->type == "clinical"));
		$this->createButtonset('accreditation_student_supervision_type', 'Who supervises the students?', $supervision_options, $this->accreditation_info->student_supervision_type->id);
		
		// Set some defaults!
		if($this->accreditation_info){
			$this->setDefaults(array('accreditation_cao' => $this->accreditation_info->cao,
									 'accreditation_phone' => $this->accreditation_info->phone,
									 'accreditation_distance_from_program' => $this->accreditation_info->distance_from_program,
									 'accreditation_preceptor_training_hours' => $this->accreditation_info->preceptor_training_hours,
									 'accreditation_active_ems_units' => $this->accreditation_info->active_ems_units,
					                 'accreditation_number_of_runs' => $this->accreditation_info->number_of_runs,
									 'accreditation_number_of_trauma_calls' => $this->accreditation_info->number_of_trauma_calls,
									 'accreditation_number_of_critical_trauma_calls' => $this->accreditation_info->number_of_critical_trauma_calls,
									 'accreditation_number_of_pediatric_calls' => $this->accreditation_info->number_of_pediatric_calls,
									 'accreditation_number_of_cardiac_arrest_calls' => $this->accreditation_info->number_of_cardiac_arrest_calls,
									 'accreditation_number_of_cardiac_calls' => $this->accreditation_info->number_of_cardiac_calls,));
		}
		
		// Set the decorators for the form
		$this->setDecorators(array(
			'FormErrors',
			'PrepareElements',
			array('ViewScript', array('viewScript' => '/forms/site-sub-forms/accreditation.phtml')),
			'Form'
		));
		
	}
	
	private function createYesNoButtonset($element_name, $label, $default_val = null)
	{
		$this->createButtonset($element_name, $label, array("1" => "Yes", "0" => "No"), $default_val, "accreditation-yes-no-buttonset");
	}
	
	private function createButtonset($element_name, $label, $options, $default_val = null, $classes = null)
	{
		$buttonset = new Zend_Form_Element_Radio($element_name);
		$buttonset->setMultiOptions($options);
		$buttonset->setLabel($label);
		$buttonset->setAttribs(array("class" => $classes));
		$buttonset->setRegisterInArrayValidator(false);
		
		$this->addElement($buttonset);
		
		if(!is_null($default_val)){
			$this->setDefaults(array($element_name => $default_val));
		}
	}
	
	public function process($post)
	{
		
		if ($this->isValid($post)) {
			
			$program = \Fisdap\Entity\User::getLoggedInUser()->getProgram();
			$accreditation_info = ($this->accreditation_info) ? $this->accreditation_info : new \Fisdap\Entity\SiteAccreditationInfo;
			
			$accreditation_info->program_site_association = $this->site->getAssociationByProgram($program->id);
			$accreditation_info->cao = $post['accreditation_cao'];
			$accreditation_info->phone = $post['accreditation_phone'];
			$accreditation_info->distance_from_program = $post['accreditation_distance_from_program'];
			$accreditation_info->signed_agreement = $post['accreditation_signed_agreement'];
			$accreditation_info->written_policies = $post['accreditation_written_policies'];
			$accreditation_info->formally_trained_preceptors = $post['accreditation_formally_trained_preceptors'];
			$accreditation_info->preceptor_training_hours = $post['accreditation_preceptor_training_hours'];
			$accreditation_info->student_supervision_type = \Fisdap\EntityUtils::getEntity("StudentSupervisionType", $post['accreditation_student_supervision_type']);
			
			if($this->site->type != "clinical"){
				$accreditation_info->online_medical_direction = $post['accreditation_online_medical_direction'];
				$accreditation_info->advanced_life_support = $post['accreditation_advanced_life_support'];
				$accreditation_info->quality_improvement_program = $post['accreditation_quality_improvement_program'];
				$accreditation_info->number_of_runs = $post['accreditation_number_of_runs'];
				$accreditation_info->active_ems_units = $post['accreditation_active_ems_units'];
				$accreditation_info->number_of_trauma_calls = $post['accreditation_number_of_trauma_calls'];
				$accreditation_info->number_of_critical_trauma_calls = $post['accreditation_number_of_critical_trauma_calls'];
				$accreditation_info->number_of_pediatric_calls = $post['accreditation_number_of_pediatric_calls'];
				$accreditation_info->number_of_cardiac_arrest_calls = $post['accreditation_number_of_cardiac_arrest_calls'];
				$accreditation_info->number_of_cardiac_calls = $post['accreditation_number_of_cardiac_calls'];
			}
			
			$accreditation_info->save();
			
			return array("success" => true);
		}
		else {
			return $this->getMessages();
		}
		
		return true;
	} // end process()
}
