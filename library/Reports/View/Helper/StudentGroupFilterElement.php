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
 * Description of StudentFilterElement
 *
 * @author astevenson
 */
class Reports_View_Helper_StudentGroupFilterElement extends Zend_View_Helper_FormElement
{

	public function studentGroupFilterElement($name, $value = null, $attribs = null)
	{
		// set defaults, if availaable
		$defaults = array();
		$defaults['year'] = (isset($value['year'])) ? $value['year'] : 'all';
		$defaults['month'] = (isset($value['month'])) ? $value['month'] : 'all';
		$defaults['classyear'] = (isset($value['classyear'])) ? $value['classyear'] : 'all';
		$defaults['class'] = (isset($value['class'])) ? $value['class'] : 'all';
		if (is_array($value['type'])) {
			$defaults['type']['emt'] = (in_array('EMT', $value['type'])) ? 'EMT' :  null;
			$defaults['type']['aemt'] = (in_array('Advanced EMT', $value['type'])) ? 'Advanced EMT' :  null;
			$defaults['type']['pmed'] = (in_array('Paramedic', $value['type'])) ? 'Paramedic' :  null;
		} else {
			$defaults['type'] = array('emt' => 'EMT', 'aemt' => 'Advanced EMT', 'pmed' => 'Paramedic');
		}
		//$defaults['student'] = (isset($value['student'])) ? $value['student'] : null;
		$defaults['selected_students'] = (isset($value['selected_students'])) ? $value['selected_students'] : null;
		
		$yearOptions = array('all' => 'All Years');
		if ($defaults['year'] != 'all') { $yearOptions[$defaults['year']] = $defaults['year']; }
		$monthOptions = array(
			'all' => 'All Months',
			1 => 'Jan',
			2 => 'Feb',
			3 => 'Mar',
			4 => 'Apr',
			5 => 'May',
			6 => 'June',
			7 => 'July',
			8 => 'Aug',
			9 => 'Sep',
			10 => 'Oct',
			11 => 'Nov',
			12 => 'Dec',
		);
		
		$classSectionYearOptions = array('all' => 'All'); //'All Years');
		if ($defaults['classyear'] != 'all') { $classSectionYearOptions[$defaults['classyear']] = $defaults['classyear']; }

		$classSectionOptions = array('all' => 'All'); //'All Sections');
		if ($defaults['class'] != 'all') { $classSectionOptions[$defaults['class']] = $defaults['class']; }
		
		$studentOptions = explode(',', $defaults['selected_students']);
		
		// allow a disabled class to be shown if the form requests it
		$disabledClass = '';
		if ($this->view->form->userGroupFilterCheckboxes == FALSE) {
			$disabledClass = 'disabled';
		}
		
		$html = "
			<div class='report-block'>
				<h3 class='section-header'>Choose your students</h3>
				
				<div class='grid_8 report-body'>
				
				<div id='report-block-student-filter' class='grid_12'>
				  
					<div id='student-filter-group-heading'>Filter students by:</div>
					
					<div class='grid_5'>Graduating:<br />"
						. $this->view->formSelect($name . "[month]", $defaults['month'], array(), $monthOptions)
						. $this->view->formSelect($name . "[year]", $defaults['year'], array(), $yearOptions) . "
					</div>
					
					<div class='grid_7'>In student group:<br />"
						. $this->view->formSelect($name . "[classyear]", $defaults['classyear'], array(), $classSectionYearOptions)
						. $this->view->formSelect($name . "[class]", $defaults['class'], array(), $classSectionOptions) . "</div>

					<div class='clear'></div>
					<br />

					<div class='clear'></div>
					<div class='grid_3'>" . $this->view->formCheckbox($name . "[type][]", $defaults['type']['emt'], array('id' => 'student-type-emt'), array("EMT")) . " EMT-B </div>
					<div class='grid_3'>" . $this->view->formCheckbox($name . "[type][]", $defaults['type']['aemt'], array('id' => 'student-type-aemt'), array("Advanced EMT")) . " EMT-I </div>
					<div class='grid_3'>" . $this->view->formCheckbox($name . "[type][]", $defaults['type']['pmed'], array('id' => 'student-type-pmed'), array("Paramedic")) . " Paramedic </div>
				</div>
				  
				<div class='grid_12 student-filter-results-wrapper'>
				    Students<div id='student-filter-status'></div><div id='student-filter-controls' class='" . $disabledClass . "'><label>Select:</label> <a href='#' class='control-all'>All</a> <a href='#' class='control-none'>None</a></div><div id='student-filter-results' class='" . $disabledClass . "'>";
		foreach($studentOptions as $key => $student) {
			$checked = FALSE;
			if (is_array($defaults['student']) && in_array($key, $defaults['student'])) {
				$checked = TRUE;
			}
			$html .= '<div class="student-filter-results-student"> ' . $this->view->formCheckbox($name . "[student][]", $key, array('class' => 'student_checkbox')) . ' ' . $student .'</div>';
		}
		$html .= "	</div>";
		$html .= $this->view->formHidden($name . '[selected_students]', $defaults['selected_students'], array('id' => 'selected-students'));
		$html .= "
				<div class='clear'></div>
				</div>
			</div>
			</div>
		";

		return $html;
	}

}
