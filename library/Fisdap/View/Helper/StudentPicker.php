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
class Fisdap_View_Helper_StudentPicker extends Zend_View_Helper_Abstract
{
	
	/**
	 * This helper is used to generate a chunk of HTML and JS that will allow someone
	 * to pick a student from the current program.
	 * 
	 * @param integer $studentId ID of the student to show up pre-selected (I guess)...
	 * @param string $clickLink String containing the URL to link to when a user 
	 *		clicks on a name from the main search box dropdown options
	 * @param string $listBaseLink String containing the URL to use for the 
	 *		results that return when searching using the "Go" button.  If null, 
	 *		defaults to $clickLink.
	 * @param string $studentDropdownLink String containing the URL to redirect 
	 *		to when a user selects a student from the advanced search student 
	 *		dropdown.  If null, defaults to $clickLink.
	 * @return type String containing the HTML for the student selector.
	 */
	public function studentPicker($studentId, $clickLink, $listBaseLink=null)
	{
		if($listBaseLink == null){
			$listBaseLink = $clickLink;
		}
		
		$this->view->headScript()->appendFile("/js/library/Fisdap/View/Helper/studentPicker.js");
		$this->view->headLink()->appendStylesheet("/css/library/Fisdap/View/Helper/studentPicker.css");
		
		$user = \Fisdap\Entity\User::getLoggedInUser();

        $students = \Fisdap\EntityUtils::getRepository('User')->getAllStudentsByProgram($user->getProgramId());
        $studentOptions = array('Select one...');
        foreach ($students as $student) {
            $studentOptions[$student['id']] = $student['first_name'] . " " . $student['last_name'];
        }
		
		// Set up the class sections and years here, and return them for use 
		// in the quicksearch
		$classSectionRepository = \Fisdap\EntityUtils::getRepository('ClassSectionLegacy');

		$classSectionYears = $classSectionRepository->getUniqueYears($user->getProgramId());
		$classSections = $classSectionRepository->getNamesByProgram($user->getProgramId());

		$defaultMessage = 'Type a student\'s name, username, or email, etc.';
		
		$this->view->headScript()->appendFile("/js/jquery.fieldtag.js");
		
		$html = <<<HTML
			<div id="find-student-container" class="grid_12">
			{$this->view->autoComplete('studentSearch', '', array('select' => new Zend_Json_Expr('function( event, ui ) { location.href="' . $clickLink . '" + ui.item.id; }'), 'source' => '/ajax/usersearch/'), array('size' => 80, 'title' => $defaultMessage))}
			<script type="text/javascript">
				var listBaseURL = '{$listBaseLink}';
				
				$('#studentSearch').fieldtag();

				$('#studentSearch').autocomplete({
					// This is a complete and utter hack.  Using it to restyle the popup
					// that appears when you're searching for people.  
					open: function(event, ui){
						// Add a border to the autocomplete stuff...
						$('.ui-autocomplete').css('border', '1px solid black');

						$('.ui-autocomplete').find('a').each(function(index, el){
							$(el).css('font-size', '10pt');
						});
					}
				});
			</script>
			{$this->view->formButton('go-btn', 'Go', array('class' => 'small'))}


			<div style='margin-top: 5px; padding-top: 5px;'><a href="#" id="advanced-search-link" class='small-link'>Advanced Search<img id="arrow" style="width:1em;" src="/images/arrow_left.png"></a></div>
			<div style='clear: both'></div>
			<div id='advanced-search-container'>
				<form id='advanced-search-form'>
					<div class="filter-prompt">
						Graduating:<br>
						{$this->view->graduationDateElement('graduation')}
					</div>
					<div class="filter-prompt">
						In student group:<br>
						{$this->view->formSelect('sectionYear', null, array("onchange" => "updateClassSections()"), $classSectionYears)}
						{$this->view->formSelect('section', null, array(), $classSections)}
					</div>
					<div class="filter-prompt">
						Student:<br>
						{$this->view->formSelect('student-list', $studentId, array(), $studentOptions)}
					</div>
				</form>
			</div>
		</div>
		<div id="student-results-container" class="grid_12" style="display: none">
			<h1 id="student-list-title" class="dark-gray">Search Results</h1>
			Did you mean... ?
			<br />
			<div id="student-results" class="grid_12">
			</div>
		</div>
HTML;
			
		return $html;
	}
}
