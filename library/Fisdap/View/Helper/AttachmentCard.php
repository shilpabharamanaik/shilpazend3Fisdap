<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                                           *
*        Copyright (C) 1996-2014.  This is an unpublished work of           *
*                         Headwaters Software, Inc.                         *
*                            ALL RIGHTS RESERVED                            *
*        This program is a trade secret of Headwaters Software, Inc. and    *
*        it is not to be copied, distributed, reproduced, published, or     *
*        adapted without prior authorization of Headwaters Software, Inc.   *
*                                                                           *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

/**
 * This file contains a view helper to render an attachment card
 * which generally displays a thumbnail with some metadata about
 * the card.
 *
 * It relies upon the view attachments modal so you must pass in
 * some parameters which the modal requires as well.
 */

/**
 * @package Fisdap
 *
 * @return string html
 */
class Fisdap_View_Helper_AttachmentCard extends Zend_View_Helper_Abstract
{
    protected $_html;

    public function attachmentCard($shiftAttachment, $shiftId, $shiftType)
    {
        $this->view->headScript()->appendFile("/js/library/Fisdap/View/Helper/attachment-card.js");
        $this->view->headLink()->appendStylesheet("/css/library/Fisdap/View/Helper/attachment-card.css");

        $cardIdAttribute = $shiftAttachment->id ? ' attachment-id="'. $shiftAttachment->id .'" ' : '';

        // figure out what image to show
        $attachmentService = new \Fisdap\Service\AttachmentService();
        $shiftAttachmentPreview = $attachmentService->getPreview($shiftAttachment);
        if ($shiftAttachmentPreview['type'] == "image") {
            $imageSrc = ($shiftAttachment->variationUrls->medium) ? $shiftAttachment->variationUrls->medium : urldecode($shiftAttachment->tempUrl);
        } else {
            $imageSrc = $shiftAttachmentPreview['src'];
        }
        $shiftAttachment->preview = array('src' => $imageSrc);

        // figure out the attachment info
        $attachment_info_display_helper = new Fisdap_View_Helper_AttachmentInfo();
        $attachmentInfo = $attachment_info_display_helper->attachmentInfo($shiftAttachment, $shiftType);

        $this->_html = '<div class="attachment-card" data-shift-id="'. $shiftId .'" data-shift-type="' . $shiftType . '" ' . $cardIdAttribute . '>';

        $this->_html .= $attachment_info_display_helper->attachmentDownloadPreviewLink($shiftAttachment, $shiftId, $shiftAttachmentPreview['class']);
        $this->_html .= '<div class="attachment-card-contents" title="view attachment">' . $attachmentInfo . '</div>';

        $this->_html .= '<div class="clear"></div>';
        $this->_html .= '</div>';
        return $this->_html;
    }
}
