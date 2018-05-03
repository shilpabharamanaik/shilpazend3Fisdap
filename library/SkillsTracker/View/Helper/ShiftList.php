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
 * This helper will display a list of the user's shifts
 */

/**
 * @package SkillsTracker
 */
class SkillsTracker_View_Helper_ShiftList extends Zend_View_Helper_Abstract
{
    /**
     * @var string the html to be rendered
     */
    protected $_html;

    /**
     * @param array $shifts array of arrays containing each shift to be
     * rendered in a view
     *
     * @param Fisdap\Api\Client\Students\Gateway\StudentsGateway $studentsGateway a gateway to the api client to get student shifts
     *
     * @param array $messages an array of messages to be put into the shift list
     * header
     *
     * @return string the shift list rendered as an html table
     */
    public function shiftList($studentId, $studentsGateway, $filter = null, $isInstructor = false, $messages = array())
    {

        if (!$filter) {
            $filter = array("type" => array(),
                "attendance" => array(),
                "date" => "all");
        }

        $this->messages = $messages;
        $this->isInstructor = $isInstructor;
        $this->student = \Fisdap\EntityUtils::getEntity('StudentLegacy', $studentId);

        if (!$this->student || !$this->student->user_context->getPrimarySerialNumber()) {
            $this->_html = "<div id='data-not-found-error' class='error'>Data not found.</div>";
            return $this->_html;
        }

        // Check to see if the student has skills tracker permissions- if they
        // do not, AND this is an instructor, show an error/warning message.
        if (!$this->student->user_context->getPrimarySerialNumber()->hasSkillsTracker() && $isInstructor) {
            return "<div>".$this->student->user->getFullName() . " is not using the Skills Tracker. " .
            "<a href='/account/orders/upgrade'>Upgrade</a> " . $this->student->user->first_name . "'s account if you ".
            "want to begin tracking skills and patient care.</div>";
        }

        $shifts = $this->getShifts($studentId, $filter);
        $shiftCount = count($shifts);

        // if we have shifts we care about, use the api client to get the attachment info
        if ($shiftCount) {
            $attachmentsAdded = $this->addAttachmentData($studentId, $studentsGateway, $shifts);
        }

        if (!$shiftCount && ($filter['type'] != 'all' || $filter['attendance'] != 'all')) {
            // no shifts using current filter, now check if any other shifts present?
            $anyShifts = $this->getShifts($studentId, array());

            if (count($anyShifts)) {
                $anyShiftsPresent = true;
            }
        }

        if ($shiftCount || $anyShiftsPresent) {

            if (!$attachmentsAdded) {
                $this->_html .= '<div class="notice">An error has occurred. We could not retrieve attachment data.</div>';
            }

            $this->_html .= '<div class="grid_10">';
            $this->_html .= $this->view->formHidden('isInstructor', $isInstructor);
            $this->_html .= $this->view->formHidden('studentId', $studentId);


            // if there are shifts in this filter
            if ($shiftCount) {
                $this->showShiftsTable($shifts, $isInstructor);

                $this->_html .= '<div id="shift-list-summary-container">';
                $this->_html .= $this->view->shiftListSummary($shifts, $this->student);
                $this->_html .= '</div>';
            }

            if (!$shiftCount) {
                $studentName = $this->student->first_name . " " . $this->student->last_name;

                $studentYou = ($isInstructor) ? $studentName . " doesn't" : "you don't";
                $message = "Either " . $studentYou . " have any shifts or they have been filtered out of this view.";

                $this->_html .= "<br/> &nbsp; " . $message;
            }


            $this->_html .= '</div>';

            $this->_html .= '<div class="grid_2">';


            //Construct quick links array
            $quickLinks = array();
            if ($this->isInstructor) {
                $portfolio_link = "/portfolio/index/about/studentId/" . $this->student->id;
            } else {
                $portfolio_link = "/portfolio/index/about";
            }
            $quickLinks['View Portfolio'] = $portfolio_link;
            if ($this->student->user_context->getPrimarySerialNumber()->hasScheduler()) {
                $quickLinks['Pick shifts'] = '/scheduler';
            }
            $quickLinks['Goals'] = '/reports/goals';
            $quickLinks['Video tutorial'] = 'http://www.youtube.com/watch?v=fo2xAqPM-mU';

            $this->_html .= $this->view->quickLinksHelper($quickLinks);
            $this->_html .= '</div>';

        } else {
            $this->_html .= '<div class="grid_12">';
            $this->_html .= $this->view->youtubeVideo("fo2xAqPM-mU", "shiftListBlankState.phtml");
            $this->_html .= '</div>';
            $this->_html .= '<div class="clear"></div>';
            //$this->_html .= $this->view->addShiftWidget();
        }

        return $this->_html;
    }

    protected function getShifts($studentId, $filter)
    {
        $em = \Fisdap\EntityUtils::getEntityManager();

        $rawShifts = $em->getRepository('Fisdap\Entity\ShiftLegacy')->getShiftsByStudent($studentId, $filter);
        $shiftPartials = array();

        if ($this->isInstructor) {
            foreach ($rawShifts as $shift) {
                $shiftPartials[] = array('shift' => $shift, 'hasInstructorPermission' => $this->view->user->getCurrentRoleData()->hasPermission('Edit ' . ucfirst($shift['type']) . ' Data'));
            }
        } else {
            foreach ($rawShifts as $shift) {
                $shiftPartials[] = array('shift' => $shift);
            }
        }

        return $shiftPartials;
    }

    protected function showShiftsTable($shifts)
    {

        $this->_html .= '<table id="shift-table" ';

        if ($this->isInstructor) {
            $this->_html .= 'class="instructor"';
        }

        $this->_html .= '>
							<colgroup>
								<col id="summary-column" />
								<col />
								<col />
						';

        // If this is the instructors view, we need to put in an extra
        // column for "audited."
        if ($this->isInstructor) {
            $this->_html .= '<col />';
        }

        $this->_html .= '
								<col />
								<col />	
								<col />	
							</colgroup>


							<thead>
								<tr>';

        $this->_html .= '<th id="shift-list-messages" scope="col">' . implode(",", $this->messages) . '</th>
									<th scope="col">Patients</th>
									';
        if ($this->student->program->program_settings->allow_signoff_on_patient || $this->student->program->program_settings->allow_signoff_on_shift) {
            $this->_html .= '<th scope="col">Preceptor<br>Signoff</th>';
        }

        // If this is the instructors view, we need to put in an extra
        // column for "audited."
        if ($this->isInstructor && $this->student->program->program_settings->allow_educator_shift_audit) {
            $this->_html .= '<th scope="col">Audited</th>';
        }

        $this->_html .= '
									<th scope="col">Attendance</th>
									<th scope="col"></th>
								</tr>
							</thead>
							<tbody>';


        if ($this->isInstructor) {
            $this->_html .= $this->view->partialLoop('shiftCellsInstructor.phtml', $shifts);
        } else {
            $this->_html .= $this->view->partialLoop('shiftCells.phtml', $shifts);
        }

        $this->_html .= '</tbody></table>';
        //$this->_html .= '</div>';

    }

    /**
     * Given a list of shifts, add information about how many shift attachments each has
     *
     * @param $studentId
     * @param $studentsGateway
     * @param $shifts
     */
    private function addAttachmentData($studentId, $studentsGateway, &$shifts)
    {
        try {
            // use the api client to get the students shifts, keyed by id, with attachment info
            $studentShifts = $studentsGateway->getShifts($studentId, null, array("attachments"));
        } catch (\GuzzleHttp\Exception\ServerException $e) {
            return false;
        }

        // loop through the shifts we already grabbed and add attachment info
        foreach ($shifts as $key => $shift) {
            $shiftId = $shift['shift']['id'];
            $shifts[$key]['shift']['attachment_count'] = count($studentShifts[$shiftId]->attachmentIds);
        }
        return true;
    }
}
