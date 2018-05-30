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
 * Custom Save (Submit) Button
 */

/**
 * Class extending Zend_Form_Element_Submit
 *
 * @package Fisdap
 */
class Fisdap_Form_Element_SaveButton extends Zend_Form_Element_Submit
{
    /**
     * Set the label and html attributes for a save button
     */
    public function init()
    {
        if (!$this->_view) {
            $this->_view = $this->getView();
        }

        $this->_view->jQuery()->addOnLoad("$('input[type=submit].green-buttons').button().css('padding', '3px 10px').parent().addClass('green-buttons');");

        //Set Element properties
        $this->setLabel('Save');
        $this->setAttrib('class', 'green-buttons');
    }
}
