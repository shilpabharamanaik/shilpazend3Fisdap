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
class Reports_View_Helper_StudentFilterElement extends Zend_View_Helper_FormElement
{

	public function studentFilterElement($name, $value = null, $attribs = null)
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
			$defaults['type'] = array('emt' => null, 'aemt' => null, 'pmed' => null);
		}
		$defaults['student'] = (isset($value['student'])) ? $value['student'] : null;
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
		
		$studentOptions = array('all' => 'All');
		if ($defaults['student'] != 'all') { $studentOptions[$defaults['student']] = $defaults['student']; }
		

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
					
					<div class='grid_7'>In Student group:<br />"
						. $this->view->formSelect($name . "[classyear]", $defaults['classyear'], array(), $classSectionYearOptions)
						. $this->view->formSelect($name . "[class]", $defaults['class'], array(), $classSectionOptions) . "</div>

					<div class='clear'></div>
					<br />

					<div class='clear'></div>
					<div class='grid_3'>" . $this->view->formCheckbox($name . "[type][]", $defaults['type']['emt'], array('id' => 'student-type-emt'), array("EMT")) . " EMT-B </div>
					<div class='grid_3'>" . $this->view->formCheckbox($name . "[type][]", $defaults['type']['aemt'], array('id' => 'student-type-aemt'), array("Advanced EMT")) . " EMT-I </div>
					<div class='grid_3'>" . $this->view->formCheckbox($name . "[type][]", $defaults['type']['pmed'], array('id' => 'student-type-pmed'), array("Paramedic")) . " Paramedic </div>
				</div>
				  
				<div class='grid_12'>
					<br/>Students: " . $this->view->formSelect($name . "[student]", $defaults['student'], array('id' => 'student_select_box', 'style' => "width: 300px;"), $studentOptions) .
					$this->view->formHidden($name . '[selected_students]', $defaults['selected_students'], array('id' => 'selected-students')) . "
				</div>
				<div class='clear'></div>
					
				</div>
			</div>
		";

		return $html;
	}

}
