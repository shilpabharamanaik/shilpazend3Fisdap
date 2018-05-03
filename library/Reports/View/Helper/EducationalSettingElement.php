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
class Reports_View_Helper_EducationalSettingElement extends Zend_View_Helper_FormElement
{

	public function educationalSettingElement($name, $value = null, $attribs = null)
	{
		$html = "
			<div class='report-block'>
				<h3 class='section-header'>Choose your educational setting</h3>
				<div class='grid_8 report-body'>
					Only include information from... <br />
					<div class='clear'></div>
					<div class='grid_3'>" . $this->view->formCheckbox($name . "[shifttype][]", null, array('checked' => true, 'id' => 'shift-type-field'), array("field")) . " Field Shifts</div>
					<div class='grid_3'>" . $this->view->formCheckbox($name . "[shifttype][]", null, array('checked' => true, 'id' => 'shift-type-clinical'), array("clinical")) . " Clinical Shifts</div>
					<div class='grid_3'>" . $this->view->formCheckbox($name . "[shifttype][]", null, array('checked' => true, 'id' => 'shift-type-lab'), array("lab")) . " Lab Shifts</div>
					
					<div class='clear'></div>
				</div>
			</div>
		";
		
		return $html;
	}

}
