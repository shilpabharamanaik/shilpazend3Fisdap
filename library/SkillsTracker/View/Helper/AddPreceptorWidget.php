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
 * This helper will wrap a form in a modal dialog
 */

/**
 * @package SkillsTracker
 */
class SkillsTracker_View_Helper_AddPreceptorWidget extends Zend_View_Helper_Abstract
{
    /**
     * @var string the html to be rendered
     */
    protected $_html;
    
    public function addPreceptorWidget($studentId, $siteId)
    {
        $this->view->headScript()->appendFile('/js/library/SkillsTracker/View/Helper/add-preceptor-widget.js');
        
        $form = new SkillsTracker_Form_AddPreceptor($studentId, $siteId);
        
        return "<div id='preceptorDialog'>" . $form . "</div>";
    }
}
