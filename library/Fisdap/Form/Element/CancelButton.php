<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * 
*                                                                           *
*        Copyright (C) 1996-2011.  This is an unpublished work of           *
*                         Headwaters Software, Inc.                         *
*                            ALL RIGHTS RESERVED                            *
*        This program is a trade secret of Headwaters Software, Inc. and    *
*        it is not to be copied, distributed, reproduced, published, or     *
*        adapted without prior authorization of Headwaters Software, Inc.   *
*                                                                           *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * /

/**
 * Custom Cancel Button
 */

/**
 * Class extending Zend_Form_Element_Button
 *
 * @package Fisdap
 */
class Fisdap_Form_Element_CancelButton extends Zend_Form_Element_Button
{
    public function init()
    {
        //Set Element properties
        $this->setLabel('Cancel');
        $this->setAttrib('class', 'cancel-button gray-button medium');
        
        //Add jquery click event to cancel button
        if (!$this->_view) {
            $this->_view = $this->getView();
        }
        $jquery = $this->_view->jQuery();
        
        //get current jQuery handler based on noConflict settings
        $jqHandler = ZendX_JQuery_View_Helper_JQuery::getJQueryHandler();
        
        $function = '(".cancel-button").button().click(function() '
                  . '{ alert("I\'m a cancel button."); }'
                  . ')';
        
        $jquery->addOnload($jqHandler . $function);
    }
}
