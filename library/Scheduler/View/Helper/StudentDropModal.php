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
 * This helper will display a modal so an instructor can drop a student from a shift
 */
use Fisdap\Entity\User;
use Fisdap\EntityUtils;

/**
 * @package Scheduler
 */
class Scheduler_View_Helper_StudentDropModal extends Zend_View_Helper_Abstract
{
    
    /**
     * @var string the html to be rendered
     */
    protected $_html;
    
    // will create an empty modal
    public function studentDropModal()
    {
        
        // set up our modal
        $this->view->headScript()->appendFile("/js/library/Scheduler/View/Helper/student-drop-modal.js");
        //$this->view->headLink()->appendStylesheet("/css/library/Scheduler/View/Helper/student-drop-modal.css");
        
        $this->_html =  "<div id='studentDropDialog'>";
        $this->_html .= 	"<div id='drop-modal-content'></div>";
        $this->_html .= "</div>";
        
        return $this->_html;
    }
    
    // generates the content for the modal
    public function generateStudentDrop($assignment_id)
    {
        $user = User::getLoggedInUser();
        $assignment = EntityUtils::getEntity("SlotAssignment", $assignment_id);
        $different_program = ($assignment->user_context->program->id != $user->getCurrentUserContext()->program->id) ? true : false;
        if ($user->getCurrentUserContext()->program->seesSharedStudents($assignment->slot->event->site->id) || !$different_program) {
            $fullname = $assignment->user_context->user->getName();
            $firstname = $assignment->user_context->user->first_name;
        } else {
            $fullname = "a student from ".$assignment->user_context->program->name;
            $firstname = "the student";
        }
        
        $returnContent = "<div id='main-drop-content'>
					<div>
						You have chosen to drop $fullname from this shift:
					</div>
					<div id='shift-div'>	
						<img id='site-icon' class='icon' src='/images/icons/".$assignment->slot->event->type."SiteIconColor.png'>
						<h4 class='site-desc ".$assignment->slot->event->type."'>".$assignment->slot->event->getDetailViewDate()."</h4>
						<h4 class='header' style='margin: 0 0 5px 30px'>".$assignment->slot->event->getLocation()."</h4>
					</div>";
        if ($assignment->shift->hasData()) {
            if ($different_program) {
                $returnContent .= "<div class='notice'>
						This shift has Skills Tracker data. You do not have permission to ".
                        "drop $firstname from this shift.
					</div>
				</div>
				<div class='drop-buttons'>
					<div id='cancelButtonWrapper' class='small gray-button'>
						<a href='#' id='cancel-btn'>Ok</a>
					</div>
				</div>";

                return $returnContent;
            }
            
            $returnContent .= "<div class='notice'>
						This shift has Skills Tracker data. If you drop $firstname from this shift, the data will be lost.
					</div>";
        }
    
        $returnContent .= "</div>
				<div class='drop-buttons'>
					<div id='cancelButtonWrapper' class='small gray-button'>
						<a href='#' id='cancel-btn'>Cancel</a>
					</div>
					<div id='saveButtonWrapper' class='small green-buttons'>
						<a href='#' id='do-drop-btn' data-assignmentid=".$assignment_id.">Confirm</a>
					</div>
				</div>";

        return $returnContent;
    }
}
