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
 * This helper will display a modal with a swap history
 */

/**
 * @package Scheduler
 */
class Scheduler_View_Helper_SwapHistoryModal extends Zend_View_Helper_Abstract
{
    
    /**
     * @var string the html to be rendered
     */
    protected $_html;
    
    // will create an empty modal
    public function swapHistoryModal($addScripts = false)
    {
        // set up our modal
        $this->view->headScript()->appendFile("/js/library/Scheduler/View/Helper/swap-history-modal.js");
        $this->view->headLink()->appendStylesheet("/css/library/Scheduler/View/Helper/swap-history-modal.css");
            
        $this->_html =  "<div id='history-modal'>";
        $this->_html .= 	"<div id='history-modal-content'></div>";
        $this->_html .= "</div>";
        
        return $this->_html;
    }
    
    // generates the content for the modal
    public function generateSwapHistory($request_id)
    {
        $user = \Fisdap\Entity\User::getLoggedInUser();
        $request = \Fisdap\EntityUtils::getEntity("ShiftRequest", $request_id);
        $event = $request->event;
        
        $returnContent = "<img id='site-icon' class='icon' src='/images/icons/".$event->type."SiteIconColor.png'>";
        $returnContent .= "<h4 class='table-label ".$event->type."'>";
        $returnContent .= 	$event->getLocation();
        $returnContent .= "</h4>";
        $returnContent .= "<h4 class='header' style='margin: 0 0 5px 30px;position:relative;top:-5px'>".
                    $event->getTitleDateTime().
                  "</h4>";
        
        // make the table
        $returnContent .= "<table class='history-table-header fisdap-table'>";
        $returnContent .= 	"<thead>";
        $returnContent .= 		"<tr>";
        $returnContent .= 			"<th class='left-column'>Sent</th>";
        $returnContent .= 			"<th>Offer</th>";
        $returnContent .= 			"<th></th>";
        $returnContent .= 		"</tr>";
        $returnContent .= 	"</thead>";
        $returnContent .= "</table>";
        
        $returnContent .= "<div class='history-table-body'>";
        $returnContent .= "<table class='fisdap-table'>";
        $returnContent .= 	"<tbody>";
        foreach ($request->swaps as $swap) {
            $returnContent .= 	"<tr>";
            $returnContent .= 		"<td class='left-column'>".$swap->sent->format('M j, Y, H:i')."</td>";
            $returnContent .= 		"<td style='border-left-style:hidden;border-right-style:hidden'>".
                                "<h4 class='table-label ".$swap->offer->slot->event->type."'>".
                                    $swap->offer->slot->event->getLocation().
                                "</h4>".
                                "<h4 class='header' style='margin: 0 0 5px 30px;position:relative;top:-5px'>".
                                    $swap->offer->slot->event->getTitleDateTime().
                                "</h4>";
            $returnContent .= 		"</td>".
                            "<td style='border-left-style:hidden;border-right-style:hidden'>";
            if ($swap->accepted->name == 'unset') {
                $returnContent .= 		"<img class='icon' src='/images/icons/pending.png'>pending";
            }
            if ($swap->accepted->name == 'accepted') {
                $returnContent .= 		"<img class='icon' src='/images/icons/approved.png'>".$swap->accepted->name;
            }
            if ($swap->accepted->name == 'declined') {
                $returnContent .= 		"<img class='icon' src='/images/icons/denied.png'>".$swap->accepted->name;
            }
            $returnContent .= 		"</td>".
                        "</tr>";
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
