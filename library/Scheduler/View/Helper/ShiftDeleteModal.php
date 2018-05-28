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
 * This helper will display a modal do delete a student-created shift
 */

/**
 * @package Scheduler
 */
class Scheduler_View_Helper_ShiftDeleteModal extends Zend_View_Helper_Abstract 
{
	
	/**
	 * @var string the html to be rendered
	 */
	protected $_html;
	
	// will create an empty modal
	public function shiftDeleteModal()
	{
		// set up our modal
		$this->view->headScript()->appendFile("/js/library/Scheduler/View/Helper/shift-delete-modal.js");
		//$this->view->headLink()->appendStylesheet("/css/library/Scheduler/View/Helper/shift-delete-modal.css");
		
		$this->_html =  "<div id='shiftDeleteDialog'>";
		$this->_html .= 	"<div id='shift-delete-modal-content'></div>";
		$this->_html .= "</div>";
		
		return $this->_html;
	}
	
	// generates the content for the modal
	public function generateShiftDelete($shift_id)
	{
		$user = \Fisdap\Entity\User::getLoggedInUser();
		$shift = \Fisdap\EntityUtils::getEntity("ShiftLegacy", $shift_id);
		
		// no shift?
		if ($shift->id < 1) {
			$returnContent = "<div>
						Oops! We cannot find the requested shift.
					</div>
					<div class='delete-buttons'>
						<div id='cancelButtonWrapper' class='small gray-button'>
							<a href='#' id='no-shift-btn'>Ok</a>
						</div>
					</div>";
			return $returnContent;
		}
		
		$returnContent = "<div id='main-delete-content'>
					<div>
						You have chosen to delete this shift:
					</div>
					<div id='shift-div'>	
						<img id='site-icon' class='icon' src='/images/icons/".$shift->type."SiteIconColor.png'>
						<h4 class='site-desc ".$shift->type."'>".$shift->getDetailViewDate()."</h4>
						<h4 class='header' style='margin: 0 0 5px 30px'>".$shift->getLocation()."</h4>
					</div>";
				
		$returnContent .= "</div>
				<div class='delete-buttons'>
					<div id='cancelButtonWrapper' class='small gray-button'>
						<a href='#' id='delete-cancel-btn'>Cancel</a>
					</div>
					<div id='deleteButtonWrapper' class='small green-buttons'>
						<a href='#' id='delete-btn' data-shiftid=".$shift->id.">Confirm</a>
					</div>
				</div>";		

		return $returnContent;
	}

}
