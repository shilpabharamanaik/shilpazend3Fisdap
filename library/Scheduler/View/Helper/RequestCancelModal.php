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
 * This helper will display a modal to confirm a request cancel
 */

/**
 * @package Scheduler
 */
class Scheduler_View_Helper_RequestCancelModal extends Zend_View_Helper_Abstract
{
    
    /**
     * @var string the html to be rendered
     */
    protected $_html;
    
    // will create an empty modal
    public function requestCancelModal($addScripts = false)
    {
        // set up our modal
        $this->_html =  "<div id='requestCancelDialog'>";
        $this->_html .= "</div>";
        
        return $this->_html;
    }
    
    // generates the content for the modal
    public function generateRequestCancel($request_id, $request_type)
    {
        $user = \Fisdap\Entity\User::getLoggedInUser();
        $request = \Fisdap\EntityUtils::getEntity("ShiftRequest", $request_id);
        
        $returnContent = "<div id='cancel-modal-content'>
					<div id='shift-div'>	
						<img id='site-icon' class='icon' src='/images/icons/".$request->event->type."SiteIconColor.png'>
						<h4 class='site-desc ".$request->event->type."'>".$request->event->getDetailViewDate()."</h4>
						<h4 class='header' style='margin: 0 0 5px 30px'>".$request->event->getLocation()."</h4>
					</div>";
        if ($request_type == "offer") {
            $swap = $request->getCurrentSwap();
            $returnContent .= "<div>
						Are you sure you want to cancel the following swap offer for the shift above?
					</div>
					<div id='shift-div'>	
						<img id='site-icon' class='icon' src='/images/icons/".$swap->offer->slot->event->type."SiteIconColor.png'>
						<h4 class='site-desc ".$swap->offer->slot->event->type."'>".$swap->offer->slot->event->getDetailViewDate()."</h4>
						<h4 class='header' style='margin: 0 0 5px 30px'>".$swap->offer->slot->event->getLocation()."</h4>
					</div>";
        } else {
            $returnContent .= "<div>
						Are you sure you want to cancel the $request_type for this shift?
					</div>";
        }
        $returnContent .= "</div>
				<div class='cancel-buttons'>
					<div id='noButtonWrapper' class='small gray-button'>
						<a href='#' id='no-cancel-btn'>No</a>
					</div>
					<div id='yesButtonWrapper' class='small green-buttons'>
						<a href='#' id='yes-cancel-btn' data-requestid=".$request->id.">Yes</a>
					</div>
				</div>";

        return $returnContent;
    }
}
