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
 * This helper will display a list of the user's runs for a particular shift
 */

/**
 * @package SkillsTracker
 */
class SkillsTracker_View_Helper_RunList extends Zend_View_Helper_Abstract
{
    /**
     * @var string the html to be rendered
     */
    protected $_html;

    /**
     * @param array $runs array of arrays containing each run to be
     * rendered in a view
     *
     * @return string the run list rendered as an html table
     */
    public function runList($shift, $runs, $isInstructor = false, $type)
    {
        $this->_html = "<div id='patient-table'><div class='table-header'>";
        $this->_html .= "<h2 class='section-header with-button'>Patients</h2>";
        $this->_html .= "<a id='add-patient' class='$type' href='/skills-tracker/shifts/create-run/shiftId/" . $shift->id . "' title='add a patient' alt='+ Patient'>+ Patient</a>";
        $this->_html .= "</div>";

        // if there are runs, add the table
        if ($runs) {
            $this->_html .= $this->view->formHidden('shiftId', $shift->id);
            $this->_html .= '<table id="run-table" class="fisdap-table my-shift-table">
			<colgroup>
				<col />
				<col />
				<col />
				<col />
				<col />
				<col />
				<col />
			</colgroup>
			
	
			<thead class="' . $shift->type . '">
				<tr>';

            if (\Fisdap\Entity\User::getLoggedInUser()->getCurrentRoleData()->program->program_settings->allow_signoff_on_patient) {
                $this->_html .= '<th scope="col"></th>';
            }
            $this->_html .= '
					<th scope="col"></th>
					<th id="run-list-messages" scope="col"></th>
					<th scope="col">Interventions</th>
					<th scope="col">Team Lead</th>
		';

            if (\Fisdap\Entity\User::getLoggedInUser()->getCurrentRoleData()->program->program_settings->allow_signoff_on_patient) {
                $this->_html .= '<th scope="col" align="center">Preceptor<br/>sign off</th>';
            }

            $this->_html .= '
					<th scope="col"></th>
				</tr>
			</thead>
			<tbody>';

            if ($isInstructor) {
                $this->_html .= $this->view->partialLoop('runCellsInstructor.phtml', $runs);
            } else {
                $this->_html .= $this->view->partialLoop('runCells.phtml', $runs);
            }

            $this->_html .= '</tbody></table>';
        } else {
            $this->_html .= "<p>Document your patient contacts including signs and symptoms, impressions and skills.</p>";
        }
        $this->_html .= "</div>";

        return $this->_html;
    }
}
