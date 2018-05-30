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
 * This helper will display a list of the user's runs for a particular shift with less detail for lock shift modal
 */

/**
 * @package SkillsTracker
 */
class SkillsTracker_View_Helper_ShortRunList extends Zend_View_Helper_Abstract
{
    /**
     * @var string the html to be rendered
     */
    protected $_html;

    /**
     * @param array $runs array of arrays containing each run to be rendered in a view
     *
     * @return string the run list rendered as an html table
     */
    
    public function shortRunList($shift, $runs = null)
    {
        //If no runs are given, find the runs for the shift.
        if ($runs == null) {
            $runstemp = \Fisdap\EntityUtils::getRepository('Run')->findByShift($shift->id);
            $runPartials = array();
            foreach ($runstemp as $run) {
                $runPartials[] = array('run' => $run, 'patients' => \Fisdap\EntityUtils::getRepository('Patient')->getPatientsByRun($run->id));
            }
            $runs = $runPartials;
        }

        // if there are still no runs, return the null state message
        if (count($runs) < 1) {
            $this->_html = "There are no patients documented for this shift.";
            return $this->_html;
        }
        
        $this->_html .= $this->view->formHidden('shiftId', $shift->id);
        $this->_html .= '
            <table id="short-run-table" class="fisdap-table">
			    <thead class="' . $shift->type . '">
				    <tr>
				        <th scope="col" align="center">'. $this->view->element->unlockAllRuns . '</th>
				        <th scope="col"></th>
				        <th id="run-list-messages" scope="col"></th>
				        <th scope="col" align="center">sign off</th>
				    </tr>
			    </thead>

			    <tbody>';

        $this->_html .= $this->view->partialLoop('runCellsLockShift.phtml', $runs);

        $this->_html .= '
                </tbody>
            </table>';

        return $this->_html;
    }
}
