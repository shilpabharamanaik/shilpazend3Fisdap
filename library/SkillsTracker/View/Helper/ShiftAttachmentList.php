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
 * This helper will display a list of the user's attachments for a particular shift
 */

/**
 * @package SkillsTracker
 */
class SkillsTracker_View_Helper_ShiftAttachmentList extends Zend_View_Helper_Abstract
{
    /**
     * @var string the html to be rendered
     */
    protected $_html;

    /**
     * @param array $runs array of arrays containing each attachment to be
     * rendered in a view
     *
     * @return string the attachments list rendered as an html table
     */
    public function shiftAttachmentList($shift, $attachments, $instructorView, $attachmentsRemaining, $canEdit = true)
    {
        $this->view->headLink()->appendStylesheet("/css/library/SkillsTracker/View/Helper/attachment-list.css");
        $this->view->headScript()->appendFile("/js/library/SkillsTracker/View/Helper/attachment-list.js");

        $class = ($canEdit) ? "" : "read-only";

        $this->_html = "<div id='attachment-table' class='attachment-table-container $class' data-shiftid='$shift->id' data-shifttype='$shift->type'><a name='attachments'></a>";

        $this->_html .= "<div class='table-header'>";
        $this->_html .= "<h2 class='section-header with-button'>Attachments</h2>";
        if ($attachmentsRemaining <= 0) {
            $msg = ($instructorView) ? $shift->student->user->getName() . " has hit the maximum number of shift attachments." : "You've hit the maximum number of attachments you can add to your shifts.";
            $this->_html .= "<div class='notice'>$msg</div>";
        }
        if ($canEdit && $attachmentsRemaining > 0) {
            $this->_html .= "<a id='add-attachment' class='$shift->type' href='#' title='add an attachment' alt='+ Attachment'>+ Attachment</a>";
        }
        $this->_html .= "</div>";

        // add the null state msg in case we need it
        $nullMsg = ($canEdit && $attachmentsRemaining > 0) ?
            "Attach files that are relevant to your shift, such as ECGs, signatures, or an image of your paper PCR." :
            "It doesn't look like any files have been attached to this shift yet.";
        $this->_html .= "<div id='no-attachments'>$nullMsg</div>";

        // if there are attachments, add the table
        $this->_html .= '<table class="fisdap-table my-shift-table"><tbody>';
        // if there are attachments, add the rows
        if ($attachments) {
            $this->_html .= $this->view->partialLoop('shiftAttachmentRow.phtml', $attachments);
        }
        $this->_html .= '</tbody></table>';

        $this->_html .= "</div>";

        // add the modal for viewing/creating/editing attachments
        $this->_html .= $this->view->attachmentModal();

        return $this->_html;
    }
}