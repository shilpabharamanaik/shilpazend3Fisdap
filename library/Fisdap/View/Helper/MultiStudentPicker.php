<?php

/* * **************************************************************************
 *
 *         Copyright (C) 1996-2011.  This is an unpublished work of
 *                          Headwaters Software, Inc.
 *                             ALL RIGHTS RESERVED
 *         This program is a trade secret of Headwaters Software, Inc.
 *         and it is not to be copied, distributed, reproduced, published,
 *         or adapted without prior authorization
 *         of Headwaters Software, Inc.
 *
 * ************************************************************************** */

/**
 * Description of StudentPicker
 *
 * @author astevenson
 */
class Fisdap_View_Helper_MultiStudentPicker extends Zend_View_Helper_Abstract
{
    
    /**
     * This helper is used to generate a chunk of HTML and JS that will allow someone
     * to pick a group of students from a program.
     *
     * @param string $providedOptions Array containing various options for the form.
     * 		See $this->getOptions() for more detail.
     * 	String containing the URL to post to when the selected
     *		students are submitted
     * @param string $sourceLink String containing the URL to use to fetch a list of
     * 		(optionally filtered) students.
     *		Defaults to the ajax controller action for getting list of ID/names
     * @return type String containing the HTML for the multi-student selector.
     */
    public function multiStudentPicker($providedOptions=array())
    {
        $options = $this->getOptions($providedOptions);
        
        $this->view->headScript()->appendFile("/js/library/Fisdap/View/Helper/multiStudentPicker.js");
        $this->view->headLink()->appendStylesheet("/css/library/Fisdap/View/Helper/multiStudentPicker.css");
        
        $user = \Fisdap\Entity\User::getLoggedInUser();
        
        $students = \Fisdap\EntityUtils::getRepository('User')->getAllStudentsByProgram($user->getProgramId());
        
        // Set up the class sections and years here, and return them for use
        // in the quicksearch
        $classSectionRepository = \Fisdap\EntityUtils::getRepository('ClassSectionLegacy');
        
        $classSectionYears = $classSectionRepository->getUniqueYears($user->getProgramId());
        $classSections = $classSectionRepository->getNamesByProgram($user->getProgramId());
        
        // Build up a JS array of the selected students id's...
        if (is_array($options['selectedStudents'])) {
            $selectedStudentIDsString = '[' . implode(', ', $options['selectedStudents']) . ']';
        } else {
            $selectedStudentIDsString = '[]';
        }
        
        $statusOptions = array(
            1 => "Active",
            4 => "Left Program",
            2 => "Graduated",
        );
        
        $isAjax = ($options['isAjaxPost'])?'true':'false';
        $canViewStudentNames = ($options['canViewStudentNames'])?'true':'false';
        
        // Hidden element to contain any additionalQueryArgs, which should be an array
        if (is_array($options['additionalQueryArgs'])) {
            $additionalQueryArgsElement = '<input type="hidden" name="msp-additionalQueryArgs" value="' . htmlentities(json_encode($options['additionalQueryArgs'])) . '" />';
        }
        
        // see if the session is remembering some settings for you
        if ($options['useSessionSettings']) {
            $session = new \Zend_Session_Namespace("MultiStudentPicker");
            $selectedCertifications = $session->selectedCertifications;
            $selectedStatus = $session->selectedStatus;
            $defaultMonth = $session->selectedGradMonth;
            $defaultYear = $session->selectedGradYear;
            $selectedSectionYear = $session->selectedSectionYear;
            $selectedSection = $session->selectedSection;
        } else {
            $selectedCertifications = array();
            $selectedStatus = array(1);
            $defaultMonth = 0;
            $defaultYear = 0;
            $selectedSectionYear = null;
            $selectedSection = null;
        }
        
        $gradElements = $this->getGraduationDateElements($defaultMonth, $defaultYear);
        
        $html = <<<HTML
			<script>
				var msp_ajaxSource = '{$options['sourceLink']}';
				var msp_selectedStudents = {$selectedStudentIDsString};
				var msp_targetFormId = '{$options['targetFormId']}';
				var msp_isAjaxPost = {$isAjax};
				var msp_ajaxPostURL = '{$options['ajaxPostURL']}';
				var msp_ajaxResultsContainer = '{$options['ajaxResultsContainer']}';
				var msp_postLoadCallback = {$options['postLoadCallback']};
				var msp_useSessionSettings = '{$options['useSessionSettings']}';
				var msp_canViewStudentNames = {$canViewStudentNames};
				var msp_blockUi = {$options['blockUi']};
			</script>
			
			<div id='msp_container' class='dark-accordion'>
				<div id='msp_filter_menu'>
					<div id='msp_filters'>
						<h3 class='bottomRoundedCorners'><button id='filters-title'><div id='filters-title-icon'><img id='plus' src='/images/icons/plus_Gray.png'></div><span id='filters-title-text'>Filters</span></button></h3>
						<div id='msp_filter_form'>
							<div class='grid_3 certLevelWrapper'>
								<div class='msp_section_title'>Certification Level: </div>
								{$this->view->formMultiCheckbox("certificationLevels", $selectedCertifications, array(), \Fisdap\Entity\CertificationLevel::getFormOptions(false, false, "description", $user->getCurrentProgram()->profession->id))}
							</div>
							
							<div class='grid_3 graduatingWrapper'>
								{$gradElements}
								
								<div class='grid_12' style='margin-top:.5em'>
									<div class='msp_section_title'>Graduation Status:</div>
									{$this->view->formMultiCheckbox("graduationStatus", $selectedStatus, array(), $statusOptions)}
								</div>
								
							</div>
							
							<div class='grid_4'>
								<div class='grid_12'>
									<div class='grid_6'>
										<div>Group Year:</div> 
									</div>
									
									<div class='grid_6'>
										{$this->view->formSelect('sectionYear', $selectedSectionYear, array("onchange" => "msp_updateClassSections()"), $classSectionYears)}
									</div>
								</div>
								
								<div class='grid_12' style='margin-top:.3em'>
									<div class='grid_6'>
										<div>Section:</div> 
									</div>
									
									<div class='grid_6'>
										{$this->view->formSelect('section', $selectedSection, array(), $classSections)}
									</div>
								</div>
							</div>
							
							<div class='clear'></div>

						</div>
						
							<div class='clear'></div>

					</div>
					
					<div class='clear'></div>

				</div>
				
				<div class='clear'></div>
				
				<div id='student-list-throbber'><img src='/images/throbber_small.gif'></div>
				<div class='msp_student_list_div'>
					<div id='msp_student_list_container'>
						<table id='msp_student_list' class='fisdap-table'>
						</table>
					</div>
				</div>
			</div>
			{$additionalQueryArgsElement}
HTML;
                        
        return $html;
    }
    
    private function getGraduationDateElements($defaultMonth, $defaultYear)
    {
        $years = array("-1" => "Year");
        $months = Util_FisdapDate::get_month_prompt_names();
        
        if (Zend_Auth::getInstance()->hasIdentity()) {
            $program = \Fisdap\Entity\ProgramLegacy::getCurrentProgram();
            $years = $program->get_possible_graduation_years();
        }
        
        //get data from values
        $month = isset($value['month']) ? $value['month'] : $defaultMonth;
        $year = isset($value['year']) ? $value['year'] : $defaultYear;
        
        $html .= $this->view->formLabel("", "Graduating:", array());
        $html .= "<br>";
        $html .= $this->view->formSelect("graduationMonth", $month, array(), $months);
        $html .= $this->view->formSelect("graduationYear", $year, array(), $years);
        
        return $html;
    }
    
    /**
     * This function takes the provided options and augments them with the set of default
     * options.  Dig through the switch statement to get a commented list of available options.
     *
     * @param unknown_type $providedOptions Array containing the options provided by the
     * instantiator.  Defaults will be added to the array if they are not defined.
     */
    private function getOptions($providedOptions=null)
    {
        // Get a bit of info about the user accessing this list.  If they are an instructor without permission
        // to view student names on reports, pass back a var here.
        $user = \Fisdap\Entity\User::getLoggedInUser();
        $showNames = true;
        
        $defaultOptions = array(
            'sourceLink' => '/ajax/get-filtered-student-list', // Source that can provide a filtered list of students- see that action implementation for details
            'canViewStudentNames' => $showNames,
            'targetFormId' => '', // ID of the form element to append the selected student IDs to
            'selectedStudents' => array(), // This should be an array of student IDs to show preselected
            'isAjaxPost' => false, // This can be used with the ajaxPostURL field to do an ajax post of the form (specified by targetFormId) as opposed to a regular post.
            'ajaxPostURL' => '', // The URL to post the form to on 'submission'
            'ajaxResultsContainer' => '',
            'additionalQueryArgs' => array(), // An optional ONE-DIMENSIONAL array of additional query arguments. These are submitted to the sourceLink in an AJAX request that retrieves the list of students. Can be useful if variable parameters are necessary to retrieve the proper studetn list based on context
            'postLoadCallback' => 'function(){}', // An optional callback that gets fired when the MSP fully initializes with a student list for the first time.  String should contain a valid javascript function, or the name of an existing function in your viewscript.
            'useSessionSettings' => false, // remember filter settings across the whole website, if you use this, remember to set the session variables when you process the form
            'blockUi' => 0 // block UI while request is processing
        );
        
        return array_merge($defaultOptions, $providedOptions);
    }
}
