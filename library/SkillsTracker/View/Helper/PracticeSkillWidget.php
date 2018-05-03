<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                                           *
*        Copyright (C) 1996-2011.  This is an unpublished work of           *
*                         Headwaters Software, Inc.                         *
*                            ALL RIGHTS RESERVED                            *
*        This program is a trade secret of Headwaters Software, Inc. and    *
*        it is not to be copied, distributed, reproduced, published, or     *
*        adapted witdout prior autdorization of Headwaters Software, Inc.   *
*                                                                           *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

/**
 * This view helper will render the lab practice widget
 */

/**
 * @package SkillsTracker
 */
class SkillsTracker_View_Helper_PracticeSkillWidget extends Zend_View_Helper_Abstract 
{
	/**
	 * @var string tde html to be rendered
	 */
	protected $_html;
	
	public function practiceSkillWidget($shift, $partnerShifts)
	{
		$type = $shift->type;
		$isInstructor = \Fisdap\Entity\User::getLoggedInUser()->isInstructor();
		
		$this->_html = "
            <div class='grid_12 island withTopMargin'>
                <div id='lab-practice-table'>
                    <div class='table-header'>
			            <h2 class='section-header with-button'>" . ucfirst($type) . " Practice</h2>
				        <a href='#' class='$type' id='add-lab-partner" . ($isInstructor ? "-instructor" : null) . "' data-shiftid=" .  $shift->id . "' alt='+ Partner' title='add a partner'>Add Partner</a>
                    </div>
			        <div id='lab-practice-container'>"
				    . $this->view->practiceSkillTable($shift, true, false) .
			        "</div>

			        <div class='clear'></div>
		
			        <div class='lab-partner-student-picker content-div'>
				        <h2 class='page-sub-title'>Assign students for $type practice items</h2>" .
				    ($isInstructor ? $this->view->multiStudentPicker(array(
				    	'targetFormId' => 'blah',
				    	'sourceLink' => '/skills-tracker/shifts/get-lab-partner-students',
				    	'additionalQueryArgs' => array("shiftId" => $shift->id),
				    	'canViewStudentNames' => true)) : null) .
				        "<div class='extra-small blue-button'>
				        	<a href='#' id='add-lab-partners-instructor'>Ok</a>
                        </div>
			        </div>
                </div>
			</div>";
			
		foreach ($partnerShifts as $partnerShift) {
            // make sure we have a shift
            if ($partnerShift) {
                $this->_html .= "<div class='grid_12 island'>" . $this->view->practiceSkillTable($partnerShift) . "</div>";
            }
		}
		
		return $this->_html;
	}
}