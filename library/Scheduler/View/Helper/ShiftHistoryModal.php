<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                                           *
*        Copyright (C) 1996-2013.  This is an unpublished work of           *
*                         Headwaters Software, Inc.                         *
*                            ALL RIGHTS RESERVED                            *
*        This program is a trade secret of Headwaters Software, Inc. and    *
*        it is not to be copied, distributed, reproduced, published, or     *
*        adapted without prior authorization of Headwaters Software, Inc.   *
*                                                                           *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

/**
 * This helper will display a modal with a shift history
 */

/**
 * @package Scheduler
 */
class Scheduler_View_Helper_ShiftHistoryModal extends Zend_View_Helper_Abstract 
{
	
	/**
	 * @var string the html to be rendered
	 */
	protected $_html;
	
	// will create an empty modal
	public function shiftHistoryModal($addScripts = false)
	{
		// set up our modal
		$this->view->headScript()->appendFile("/js/library/Scheduler/View/Helper/shift-history-modal.js");
			
		$this->_html =  "<div id='history-modal'>";
		$this->_html .= 	"<div id='history-modal-content'></div>";
		$this->_html .= "</div>";
		
		return $this->_html;
	}
	
	// generates the content for the modal
	public function generateShiftHistory($id, $quick_add = false)
	{
		$user = \Fisdap\Entity\User::getLoggedInUser();

        // if this is a quick-add skill, we're looking at a shift id
        if ($quick_add) {
            $shift = \Fisdap\EntityUtils::getEntity("ShiftLegacy", $id);
            $type = $shift->type;
            $location = $shift->getLocation();
            $title = $shift->getTitleDateTime();
            $creator = $shift->creator->user->getName();
        } else {
            // otherwise, we're dealing with an event
            $event = \Fisdap\EntityUtils::getEntity("EventLegacy", $id);
            $is_shared = (count($event->event_shares) > 0) ? true : false;
            $see_student_names = true;
            $type = $event->type;
            $location = $event->getLocation();
            $title = $event->getTitleDateTime();

            if ($is_shared) {
                $see_student_names = $user->getProgram()->seesSharedStudents($event->site->id);
            }
        }

		$returnContent  = "<img id='site-icon' class='icon' src='/images/icons/".$type."SiteIconColor.png'>";
		$returnContent .= "<h4 class='table-label ".$type."'>";
		$returnContent .= 	$location;
		$returnContent .= "</h4>";
		$returnContent .= "<h4 class='header' style='margin: 0 0 5px 30px;position:relative;top:-5px'>".
                            $title.
						  "</h4>";
							
		// make the table
		$returnContent .= "<table class='history-table-header fisdap-table'>";
		$returnContent .= 	"<thead>";
		$returnContent .= 		"<tr>";
		$returnContent .= 			"<th class='left-column'>Date</th>";
		$returnContent .= 			"<th>Action</th>";
		$returnContent .= 		"</tr>";
		$returnContent .= 	"</thead>";
		$returnContent .= "</table>";
		
		$returnContent .= "<div class='history-table-body'>";
		$returnContent .= "<table class='fisdap-table'>";
		$returnContent .= 	"<tbody>";

        // if this is a quick-add shift, we only care about the creation
        if ($quick_add) {
            $returnContent .= "<tr><td class='left-column'>" . $shift->created->format('M j, Y, H:i') . "</td>";
            $returnContent .= "<td>" . $creator . " created the shift.</td></tr>";
        } else {
            // if it's an event, show all relevant actions
            foreach ($event->getRelevantActions() as $action) {
                $initiator = $action->initiator;
                $show_record = true;
                if ($action->recipient) {
                    $show_record = ($action->recipient->role->name == 'student') ? true : false;
                }

                if ($show_record) {

                    if ($initiator->user->id == $user->id) {
                        $initiator_description = "You";
                    } else {
                        $initiator_description = $initiator->user->getFullName();
                        $initiator_description .= ($is_shared) ? " from " . $initiator->getProgram()->abbreviation : "";
                    }

                    $returnContent .= "<tr>";
                    $returnContent .= "<td class='left-column'>" . $action->time->format('M j, Y, H:i') . "</td>";
                    $returnContent .= "<td>" .

                        $initiator_description . " " .
                        $action->action_type->description;

                    if ($action->recipient) {
                        $recipient = $action->recipient;

                        // can our current logged in user see student names?
                        if ($see_student_names || $recipient->getProgram()->getId() == $user->getProgramId()) {
                            $recipient_description = $recipient->user->getFullName();
                        } else {
                            // assume it is a student since we aren't showing instructors
                            $recipient_description = " a student from " . $recipient->getProgram()->abbreviation;
                        }

                        $returnContent .= " " . $recipient_description;

                        // add some more language if this is a removal
                        if ($action->action_type->id == 4) {
                            $returnContent .= " from the shift";
                        }
                    }

                    $returnContent .= ".</td>";
                    $returnContent .= "</tr>";
                }
            }
        }
		
		$returnContent .= 	"</tbody>";
		$returnContent .= "</table>";
		$returnContent .= "</div>";
		
		$returnContent .= "<div class='small gray-button'>";
		$returnContent .= "<button id='historyCloseButton'>Ok</button>";
		$returnContent .= "</div>";
		
		return $returnContent;
	}

}
