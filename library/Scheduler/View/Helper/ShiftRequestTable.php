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
 * View Helper to display a table of shift requests
 */

/**
 * @package Scheduler
 */
class Scheduler_View_Helper_ShiftRequestTable extends Zend_View_Helper_Abstract
{
    protected $_html;

    public function shiftRequestTable($request_tables)
    {
        $this->view->headLink()->appendStylesheet("/css/library/Scheduler/View/Helper/shift-history-modal.css");

        $user = \Fisdap\Entity\User::getLoggedInUser();
        $found_first_respond_image = false;

        foreach ($request_tables as $table_type => $requests) {
            $requestPartials = array();
            foreach ($requests as $request) {
                $requestArray['id'] = $request->id;
                $requestArray['date'] = $request->sent->format("M j, Y H:i");
                $requestArray['request'] = ucfirst($request->request_type->name);
                $requestArray['person'] = $request->owner->user->getName();
                $requestArray['event_type'] = $request->event->type;
                $requestArray['event_date'] = $request->event->getDetailViewDate();
                $requestArray['event_location'] = $request->event->getLocation();
                $requestArray['addl-info'] = '';
                $requestArray['tools'] = '';

                //Add a link to shift history for instructors
                if ($user->isInstructor()) {
                    $requestArray['tools'] .= "<img class='open_history_modal' src='/images/icons/swap-history.png' data-eventid=" . $request->event->id . " title='view shift history'>";
                }

                if ($request->isPending()) {
                    $cancel_link = "<img class='small-icon cancel-request' src='/images/icons/delete.png' data-requestid=".$request->id;
                    if ($user->getCurrentUserContext()->id == $request->owner->id) {
                        $cancel_link .= " data-request-type=".$request->request_type->name." title='cancel ".$request->request_type->name."'>";
                        $requestArray['tools'] .= $cancel_link;
                    }
                    if ($request->request_type->name == 'swap' &&
                        $user->getCurrentUserContext()->id == $request->recipient->id &&
                        !($request->requiresAction($user->getCurrentUserContext()->id))) {
                        $cancel_link .= " data-request-type='offer' title='cancel offer'>";
                        $requestArray['tools'] .= $cancel_link;
                    }
                }

                $yes_id = 4;
                $yes_text = "Accept";

                if ($request->request_type->name == 'swap' && $user->getCurrentUserContext()->id == $request->recipient->id) {
                    $yes_text = "Make offer";
                    $yes_id = 1;
                }
                $buttons = "<span class='extra-small blue-button'>".
                    "<a href='#' class='request-response' data-requestid=".$request->id." data-stateid=$yes_id>$yes_text</a>".
                    "</span>".
                    "<span class='extra-small gray-button'>".
                    "<a href='#' class='request-response' data-requestid=".$request->id." data-stateid=5>Decline</a>".
                    "</span>";
                if ($user->getCurrentRoleName() != 'instructor') {
                    $requestArray['status'] = $request->requiresAction($user->getCurrentUserContext()->id) ? $buttons : $request->getStatus(true);
                } else {
                    $requestArray['status'] = $request->getStatus(true);
                }

                // if this is a cover or a swap, add a little extra information
                if ($request->request_type->name == 'cover') {
                    $requestArray['addl-info'] = "covered by ".$request->getRecipientName();

                } else if ($request->request_type->name == 'swap') {
                    $swap = $request->getCurrentSwap();
                    $requestArray['addl-info'] = "<div>swapping with ".$request->getRecipientName();
                    if ($swap) {
                        if ($swap->accepted->name == 'declined' || $swap->accepted->name == 'expired' || $swap->accepted->name == 'cancelled') {
                            $requestArray['addl-info'] .= " - offer ".$swap->accepted->name."</div>";
                        } else {
                            $requestArray['addl-info'] .= " for:</div>".
                                "<img id='site-icon' class='icon' src='/images/icons/".$swap->offer->slot->event->type."SiteIconColor.png'>".
                                "<h4 class='".$swap->offer->slot->event->type."'>".$swap->offer->slot->event->getDetailViewDate()."</h4>".
                                "<h4 class='header' style='margin: 0 0 5px 30px'>".$swap->offer->slot->event->getLocation()."</h4>";
                            $requestArray['addl-site-type'] = $swap->offer->slot->event->type;
                        }

                        if ($user->getCurrentRoleName() == 'student') {
                            $requestArray['tools'] .= "<img class='swap-history' src='/images/icons/swap-history.png' data-requestid=".$request->id." title='view swap history'>";
                        }
                    }

                }

                $requestPartials[] = array('request' => $requestArray, 'type' => $table_type);
            }

            // set up the header

            $header_text = ($table_type == "pending" || $table_type == "completed") ? "<span id='" . $table_type . "_requests_header'>" . ucfirst($table_type) . " requests</span>" : ucfirst($table_type) . " requests";
            $colspan = 5;

            $buttonset = new \Fisdap_Form_Element_jQueryUIButtonset("type");
            $buttonset->setOptions(array("all" => "All", "field" => "Field", "clinical" => "Clinical", "lab" => "Lab"))
                ->setUiSize("extra-small")
            ->setUiTheme("gray-buttons")
            ->setDecorators(array("ViewHelper"))
            ->setValue("all");

            if ($user->getCurrentRoleName() == 'instructor' && $table_type == 'pending') {
                $this->_html .= "<h3 id='$table_type-header' class='section-header'>". $header_text . "<div id='shift-type-filter'>" . $buttonset . "</div></h3><div class='clear'></div>";
                $this->_html .= $this->view->partial("shift-request-action-bar.phtml");
            } else {
                $this->_html .= "<h3 id='$table_type-header' class='section-header'>". $header_text . " (In last year)</h3><div class='clear'></div>";
            }
            $this->_html .= "<table id='$table_type' class='fisdap-table shift-request-table'>".
                "<thead>".
                "<tr>";

            if ($user->getCurrentRoleName() == 'instructor' && $table_type == 'pending') {
                $this->_html .= "<th></th>";
                $colspan++;
            }
            $this->_html .= "<th class='sent'>Sent</th>".
                "<th>Request</th>".
                "<th class='name'>By</th>".
                "<th class='name'>Shift</th>";
            if ($user->getCurrentRoleName() != 'instructor' || $table_type == 'completed') {
                $colspan++;
                $this->_html .= "<th class='status'>Status</th>";
            }
            $this->_html .= "<th></th>".
                "</tr>".
                "</thead>".
                "<tbody>";

            // print out the rows
            if ($requestPartials) {
                if ($user->getCurrentRoleName() == 'instructor') {
                    $this->_html .= $this->view->partialLoop("instructorShiftRequestCell.phtml", $requestPartials);
                } else {
                    $this->_html .= $this->view->partialLoop("studentShiftRequestCell.phtml", $requestPartials);
                }
            } else {
                $this->_html .= 	"<tr><td colspan=$colspan class='no-results'>It looks like there aren't any $table_type shift requests.</td></tr>";
            }

            $this->_html .= 	"</tbody>";
            $this->_html .= "</table>";
        }
        $this->_html .= $this->view->requestResponseModal;

        return $this->_html;
    }
}
