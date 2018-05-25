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
class Reports_View_Helper_AdvancedSettingsElement extends Zend_View_Helper_FormElement
{

	public function advancedSettingsElement($name, $value = null, $attribs = null)
	{
		$html = "
			<div class='report-block'>
				<div id='advanced-settings-toggle'>
					<a class='small-link' href='#'>
						<img id='toggle-indicator-on' src='/images/arrow_right.png' width='15' height='15'/><img id='toggle-indicator-off' src='/images/arrow_down.png' width='15' height='15'/>
						<span id='toggle-header'>Advanced settings</span>
					</a>
				</div>
				"
				//<div class='report-header clickable'><span id='toggle-header'>Advanced settings</span><span id='advanced-settings-toggle'>
				//	<img id='toggle-indicator-on' src='/images/arrow_left.png' width='15' height='15'/><img id='toggle-indicator-off' src='/images/arrow_down.png' width='15' height='15'/></span></div>
				."
					
				<div class='grid_8 report-body' id='advanced-settings'>
					Include all data from: <br />
					Shifts from: " . $this->view->formText($name . '[startdate]', '', array('id' => 'advanced-startdate')) . " to " . $this->view->formText($name . '[enddate]', '', array('id' => 'advanced-enddate')) . "
					<br />	<br />	
					Patient Type(s):<br />
					<div class='grid_4'>Humans</div>
					<div class='grid_4'>Animals</div>
					<div class='grid_4'>Manikins</div>
					<div class='grid_4'>" . $this->view->formCheckbox($name . "[patient-type][]", null, array('checked' => true, 'id' => 'patient-type-human-live'), array("live humans")) . " Live</div>
					<div class='grid_4'>" . $this->view->formCheckbox($name . "[patient-type][]", null, array('id' => 'patient-type-animal-live'), array("live animals")) . " Live</div>					
					<div class='grid_4'>" . $this->view->formCheckbox($name . "[patient-type][]", null, array('id' => 'patient-type-sim-manikin'), array("manikin sims")) . " Simulator</div>
					<div class='grid_4'>" . $this->view->formCheckbox($name . "[patient-type][]", null, array('id' => 'patient-type-human-dead'), array("dead humans")) . " Cadaver</div>
					<div class='grid_4'>" . $this->view->formCheckbox($name . "[patient-type][]", null, array('id' => 'patient-type-animal-dead'), array("dead animals")) . " Cadaver</div>
					<div class='grid_4'>" . $this->view->formCheckbox($name . "[patient-type][]", null, array('id' => 'patient-type-sim-other'), array("other sims")) . " Other</div>
					<br /><br />
					Audited or All: <br />
					<div class='grid_4'>" . $this->view->formRadio($name . "[audited-or-all]", '', array('id' => 'advanced-audited-or-all'), array('1' => 'Audited','0' => 'All')) . "</div>
				</div>
			</div>
		";
					// In case Louise wants it back the old way
					//<div class='grid_4'>" . $this->view->formCheckbox($name . "[patient-type][]", null, array('checked' => true, 'id' => 'patient-type-human-live'), array("live humans")) . " Live Humans</div>
					//<div class='grid_4'>" . $this->view->formCheckbox($name . "[patient-type][]", null, array('id' => 'patient-type-animal-live'), array("live animals")) . " Live Animals</div>					
					//<div class='grid_4'>" . $this->view->formCheckbox($name . "[patient-type][]", null, array('id' => 'patient-type-sim-manikin'), array("manikin sims")) . " Manikin Sims</div>
					//<div class='grid_4'>" . $this->view->formCheckbox($name . "[patient-type][]", null, array('id' => 'patient-type-human-dead'), array("dead humans")) . " Dead Humans</div>
					//<div class='grid_4'>" . $this->view->formCheckbox($name . "[patient-type][]", null, array('id' => 'patient-type-animal-dead'), array("dead animals")) . " Dead Animals</div>
					//<div class='grid_4'>" . $this->view->formCheckbox($name . "[patient-type][]", null, array('id' => 'patient-type-sim-other'), array("other sims")) . " Other Sims</div>
		return $html;
	}

}

				//
				//<div><a class='small-link' href='#'>
				//	<span id='toggle-header'>Advanced settings</span><span id='advanced-settings-toggle'>
				//	<img id='toggle-indicator-on' src='/images/arrow_left.png' width='15' height='15'/><img id='toggle-indicator-off' src='/images/arrow_down.png' width='15' height='15'/></span>
				//	</a></div>
