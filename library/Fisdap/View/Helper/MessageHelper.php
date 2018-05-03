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
 * Helper to display success, error, and alert messages from the flash messenger
 */

/**
 * @package Fisdap
 */
class Zend_View_Helper_MessageHelper extends Zend_View_Helper_Abstract
{
    protected $_html = "";
    
    public function messageHelper($type = 'success')
    {
        $flashMessenger = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
        
        $msgs = $flashMessenger->getMessages();
        if (count($msgs)) {
            $this->_html = "<div class='$type grid_12'>";
            $this->_html .= implode("<br>", $msgs);
            $this->_html .= "</div>";
        }
        
        return $this->_html;
    }
}