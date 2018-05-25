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
class Scheduler_View_Helper_WhichEventsInSeriesModal extends Zend_View_Helper_Abstract 
{
	
	/**
	 * @var string the html to be rendered
	 */
	protected $_html;
	
	// will create an empty modal
	public function whichEventsModal($view)
	{
		// set up our modal
		$view->headScript()->appendFile("/js/library/Scheduler/View/Helper/which-events-modal.js");
		//$view->headLink()->appendStylesheet("/css/library/Scheduler/View/Helper/which-events-modal.css");
		
		$this->_html =  "<div id='whichEventsDialog'>";
		$this->_html .= 	"<div id='which-events-modal-content'></div>";
		$this->_html .= "</div>";
		
		return $this->_html;
	}
	
	// generates the content for the modal
	public function generateWhichEvents($series_id, $event_id, $event_action, $view)
	{
		$user = \Fisdap\Entity\User::getLoggedInUser();
		$event = \Fisdap\EntityUtils::getEntity("EventLegacy", $event_id);
		
		$this->_html .= $view->partial('which-events-modal.phtml', array("event" => $event, "series_id" => $series_id, "event_action" => $event_action));
		return $this->_html;
	}

}
