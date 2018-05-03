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
 * Form for creating the calendar display's fancy filters
 * @package Scheduler
 * @author hammer:)
 */
class Scheduler_Form_CalDisplayFilters extends Fisdap_Form_Base
{
	/**
	 * @var \Fisdap\Entity\User
	 */
	public $user;

	/*
	 *
	 *
	 */
	public $isStudent;

	public $duplicated_site_names;
	public $site_data;

	/*
	 *
	 */
	public $canViewFullCal;

	/**
	 * @var array
	 */
	public $filters;

	/**
	 * @param $options mixed additional Zend_Form options
	 */
	public function __construct($filters = null, $options = null)
	{
		$this->filters = $filters;
		$this->user = \Fisdap\Entity\User::getLoggedInUser();
		parent::__construct($options);
	}

	/**
	 * init method that adds all the elements to the form
	 */
	public function init()
	{
		parent::init();

		$this->setAttrib('id', 'cal-display-filters');

		if($this->user->getCurrentRoleName() == 'student'){
			$this->canViewFullCal = $this->user->getCurrentUserContext()->program->program_settings->student_view_full_calendar;
			$this->isStudent = true;
		}

		// add files for chosen
		//$this->addJsFile("/js/jquery.chosen.js");
		//$this->addCssFile("/css/jquery.chosen.css");

		// add files for slider checkboxes
		$this->addJsFile("/js/jquery.sliderCheckbox.js");
		$this->addCssFile("/css/jquery.sliderCheckbox.css");

		// add files for this form
		$this->addJsFile("/js/library/Scheduler/Form/cal-display-filters.js");
		$this->addCssFile("/css/library/Scheduler/Form/cal-display-filters.css");

		// add files for fancy filters
		$this->addJsFile("/js/jquery.fancyFilters.js");
		$this->addCssFile("/css/jquery.fancyFilters.css");


		if($this->isStudent){
			// they're a Christian! this user cannot see other studnet's schedulers.
			// give 'em a different view script all together
			if($this->canViewFullCal){
				$vs = "forms/cal-display-filters-student.phtml";
				$this->addCssFile("/css/library/Scheduler/Form/cal-display-filters-student.css");
				$chosenWidth = "405px";
				$availShiftsLabel = "Show shifts available for sign up.";
				$chosenShiftsLabel = "Show shifts chosen by/assigned to:";

			}
			else {
				$vs = "forms/cal-display-filters-student-limited.phtml";
				$this->addCssFile("/css/library/Scheduler/Form/cal-display-filters-student-limited.css");
				$chosenWidth = "405px";
				$availShiftsLabel = "Show available shifts";
				$chosenShiftsLabel = "Show chosen shifts";
			}
		}
		else {
			$vs = "forms/cal-display-filters.phtml";
			$chosenWidth = "268px";
			$availShiftsLabel = "Show shifts available to:";
			$chosenShiftsLabel = "Show shifts chosen by/assigned to:";
		}
		
		// Now create the individual elements
		
		// sites/bases/preceptors for first col
		$siteOptions = $this->getSiteOptions();
		$site = $this->createChosen('sites_filters', "Sites", $chosenWidth, " ", $siteOptions);
		
		// since hte chosens we are using for these filters are quite complicated, we can't allow duplicate names
		$baseOptions = \Fisdap\EntityUtils::getRepository('BaseLegacy')->getFormOptionsByProgram($this->user->getProgramId());
		$base_options_to_use = array();
		
		foreach($baseOptions as $site_id => $bases){
			if($this->duplicated_site_names){
				foreach($this->duplicated_site_names as $old_site_name => $new_site_name){
					if($old_site_name == $this->site_data[$site_id]){
						$base_options_to_use[$new_site_name] = $bases;
					}
					else {
						$base_options_to_use[$this->site_data[$site_id]] = $bases;
					}
				}
			}
			else {
				$base_options_to_use[$this->site_data[$site_id]] = $bases;
			}
		}
		
		$base = $this->createChosen('bases_filters', "Bases/Departments", $chosenWidth, "All bases...", $base_options_to_use);
		
		$preceptorOptions = \Fisdap\EntityUtils::getRepository('PreceptorLegacy')->getPreceptorFormOptions($this->user->getProgramId());
		$preceptor = $this->createChosen('preceptors_filters', "Preceptors", $chosenWidth, "All preceptors...", $preceptorOptions);
		
		// slider/cert level/groups for second col
		$available = new Zend_Form_Element_Checkbox('available_filters');
		$available->setValue(1);
		
		$programProfession = $this->user->getCurrentProgram()->profession->id;


        // Is this program a "hospital" ?
        // If it is, use ALL fisdap certification levels.
        $program_repo = \Fisdap\EntityUtils::getRepository('ProgramLegacy');
        $cert_level_repo = \Fisdap\EntityUtils::getRepository('CertificationLevel');
        $is_hospital = $this->user->getCurrentProgram()->isHospital();

        $certOptions = ($is_hospital) ? \Fisdap\Entity\CertificationLevel::getAllFormOptions() : $cert_level_repo->getFormOptions($programProfession);

		$availCert = $this->createChosen('available_cert_filters', "Certification levels", $chosenWidth, "All certification levels...", $certOptions);
		
		//$groupStateOptions = array("1" => "Active", "0" => "Inactive");
		//$availGroupsState = $this->createChosen('available_groups_state_filters', "Student groups", $chosenWidth, "", $groupStateOptions, false);
		
		$groupOptions = \Fisdap\EntityUtils::getRepository('ClassSectionLegacy')->getFormOptions($this->user->getProgramId(), null);
		$availGroup = $this->createChosen('available_group_filters', "Student groups", $chosenWidth, "All student groups...", $groupOptions);
		
		$signUpNow = new Zend_Form_Element_Checkbox('available_open_window_filters');
		$signUpNow->setLabel("Hide shifts that are invisible to students.")
				  ->setValue(0);
				  
		// slider/cert level/groups/grad date/students for third col
		$chosen = new Zend_Form_Element_Checkbox('chosen_filters');
		$chosen->setValue(1);
		
		$chosenCert = $this->createChosen('chosen_cert_filters', "Certification levels", $chosenWidth, "All certification levels...", $certOptions);
		//$chosenGroupsState = $this->createChosen('chosen_groups_state_filters', "Student groups", $chosenWidth, "", $groupStateOptions, false);
		$chosenGroup = $this->createChosen('chosen_group_filters', "Student groups", $chosenWidth, "All student groups...", $groupOptions);
		$grad = new Fisdap_Form_Element_GraduationDate('grad_filters');
                
                if (\Zend_Auth::getInstance()->hasIdentity()) {
                    $program = \Fisdap\Entity\ProgramLegacy::getCurrentProgram();
                    $years = $program->get_possible_graduation_years(false);
                    if (count($years) > 0) {
                        $start = reset($years);                    
                    } else {
                        $start = date("Y") - 5;                    
                    }
                    $grad->setYearRange($start,max($years));
                }
				
		$studentOptions = $program_repo->getCompleteStudentFormOptions($this->user->getProgramId(), true, true, true, true);
		$students = $this->createChosen('students_filters', "Students", $chosenWidth, "All students...", $studentOptions);
		
		// Add elements
		$this->addElements(array(
			$site,
			$base,
			$preceptor,
			$available,
			$availCert,
			$availGroupsState,
			$availGroup,
			$signUpNow,
			$chosen,
			$chosenCert,
			$chosenGroupsState,
			$grad,
			$chosenGroup,
			$students
		));
		$available->removeDecorator('Label');
		$chosen->removeDecorator('Label');

		if ($this->filters) {
			$filters = $this->filters;

			$this->setDefault("sites_filters", $filters['sites']);


			//Figure out the total possible bases, if different from saved filters, populate
			$numBases = \Fisdap\EntityUtils::getRepository("BaseLegacy")->getBaseCount($filters['sites'], $this->user->getProgramId());
			if ($numBases != count($filters['bases'])) {
				$this->setDefault("bases_filters", $filters['bases']);
			}

			//Figure out the total possible students, if different from saved filters, populate the form
			$userFilters = array(
				"certificationLevels" => is_array($filters['certs']) ? $filters['certs'] : array(),
				"graduationMonth" => $filters['gradMonth'],
				"graduationYear" => $filters['gradYear'],
				"section" => $filters['section'],
			);
			$numStudents = count(\Fisdap\EntityUtils::getRepository("User")->getAllStudentsByProgram($this->user->getProgramId(), $userFilters, false, true));
			if ($numStudents != count($filters['chosen_students'])) {
				$this->setDefault("students_filters", $filters['chosen_students']);
			}


			$this->setDefaults(array(
				"preceptors_filters" => $filters['preceptors'],
				"available_filters" => $filters['show_avail'],
				"available_cert_filters" => $filters['avail_certs'],
				"available_group_filters" => $filters['avail_groups'],
				"available_open_window_filters" => $filters['avail_open_window'],
				"chosen_filters" => $filters['show_chosen'],
				"chosen_cert_filters" => $filters['certs'],
				"chosen_group_filters" => $filters['groups'],
				"grad_filters" => array("month" => $filters['gradMonth'], "year" => $filters['gradYear']),
			));
		} else if($this->isStudent && $this->canViewFullCal) {
			$this->setDefaults(array(
			'students_filters' => $this->user->getCurrentUserContext()->id));
		}

		//Set the decorators for the form
		$this->setDecorators(array(
			'FormErrors',
			'PrepareElements',
			array('ViewScript', array('viewScript' => $vs)),
			'Form'
		));
	}

	private function createChosen($elementName, $label, $width, $placeholderText, $options, $multi = "multiple")
	{
		$chosen = new Zend_Form_Element_Select($elementName);
		$chosen->setMultiOptions($options)
			 ->setLabel($label)
			 ->setAttribs(array("class" => "chzn-select",
										   "data-placeholder" => $placeholderText,
										   "style" => "width:" . $width,
										   "multiple" => $multi,
										   "tabindex" => count($options)));
		return $chosen;
	}

	private function getSiteOptions()
	{
		$types = array("Clinical", "Field", "Lab");
		
		$siteOptions = \Fisdap\EntityUtils::getRepository('SiteLegacy')->getFormOptionsByProgram($this->user->getProgramId(), null, "name", "DESC", true);
		$site_names = array();
		$duplicate_sites = array();
		$actualOptions = array();
		
		foreach($siteOptions as $type_name => $site_type){
			foreach($site_type as $site_id => $site_name){
				if(count(\Fisdap\EntityUtils::getRepository('BaseLegacy')->getFormOptionsByProgram($this->user->getProgramId(), true, null, $site_id)) > 0){
					if(!$actualOptions[$type_name]){$actualOptions[$type_name] = array();}
					$site_names[] = $site_name;
					$actualOptions[$type_name][$site_id] = $site_name;
				}
			}
		}
		
		$siteOptions = $actualOptions;
		
		$sorted_options = array();
		
		foreach($types as $type){
			if (is_array($siteOptions[$type])) {
				$key = "0" . "-" . $type;
				$sorted_options[$type] = array();
				$sorted_options[$type][$key] = "All " . $type ." Sites";
				foreach($siteOptions[$type] as $id => $site){
					
					$site_count = 0;
					foreach($site_names as $site_name){
						if($site == $site_name){
							$site_count++;
						}
					}
					
					if($site_count > 1){
						$new_site_name =  $site . " (" . strtolower($type) . ")";
						$this->duplicated_site_names[$site] = $new_site_name;
						$site = $new_site_name;
					}
					
					$this->site_data[$id] = $site;
					$sorted_options[$type][$id] = $site;
				}
			}
			
		}
		
		return $sorted_options;
	}

	/**
	 * Process the submitted POST values and do whatever you need to do
	 *
	 * @param array $post the POSTed values from the user
	 * @return mixed either the values or the form w/errors
	 */
	public function process($post)
	{
		if ($this->isValid($post)) {
			$values = $this->getValues();
		}
		return false;
	}
}
