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
class Reports_View_Helper_OutputSettingsElement extends Zend_View_Helper_FormElement
{

	public function outputSettingsElement($name, $value = null, $attribs = null)
	{
		$outputTypes = array('html' => 'Web page', 'pdf' => 'PDF (for printing or archiving)', 'csv' => 'Tab-delimited (for use in Excel)');
		
		$html = "
			<div class='report-block'>
				<div class='report-header'>Output</div>
				<div class='grid_8 report-body'>
					" . $this->view->formRadio($name . "[output]", 'html', array(), $outputTypes) . "
				</div>
			</div>
		";

		return $html;
	}

}
