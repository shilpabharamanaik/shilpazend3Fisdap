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
 * This file contains a view helper to render an attachment modal
 */

/**
 * @package Fisdap
 *
 * @return string html
 */
class Fisdap_View_Helper_AttachmentModal extends Zend_View_Helper_Abstract
{
	protected $_html;
	
    public function attachmentModal() {
		$this->view->headScript()->appendFile("/js/library/Fisdap/View/Helper/attachment-modal.js");
        $this->view->headLink()->appendStylesheet("/css/library/Fisdap/View/Helper/view-attachment.css");

        $this->_html = "<div id='attachmentModal'>";

        $this->_html .= "<div id='attachment-modal-content'>";
        $this->_html .= "</div>";

        $this->_html .= "<div id='attachment-modal-buttons' class='modal-buttons'>";
        $this->_html .= "</div>";

        $this->_html .= "</div>";
		
		return $this->_html;
    }
}