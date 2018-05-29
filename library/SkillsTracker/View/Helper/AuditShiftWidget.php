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
 * This helper will display a widget for auditing a shift
 */

/**
 * @package SkillsTracker
 */
class SkillsTracker_View_Helper_AuditShiftWidget extends Zend_View_Helper_Abstract 
{
    /**
	 * @var string the html to be rendered
	 */
	protected $_html;
    
    public function auditShiftWidget($shiftId)
    {
        $this->view->headScript()->appendFile("/js/library/SkillsTracker/View/Helper/audit-shift-widget.js");
        
        $shift = \Fisdap\EntityUtils::getEntity('ShiftLegacy', $shiftId);
        
        $this->_html .= "<div id='audit-shift-widget'>";
        $this->_html .= $this->view->formCheckbox('audit', 1, array(), array('1' => $shift->audited)) . $this->view->formLabel('audit', 'Audited');
        $this->_html .= $this->view->formHidden('shiftId', $shift->id);
        $this->_html .= $this->view->formHidden('locked', $shift->locked);
        $this->_html .= "</div>";
        
        //Add confirmation box widget
        $this->_html .= "<div id='lockedDialog'>This shift has not yet been locked. In order to audit it, please click 'lock' below.</div>";
        
        return $this->_html;
    }
}