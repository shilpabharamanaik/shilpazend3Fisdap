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
 * Custom decorator for adding some additional description to a label
 */


/**
 * @package Fisdap
 */
class Fisdap_Form_Decorator_LabelDescription extends Zend_Form_Decorator_Label
{
    public function getLabel()
    {
        $label = parent::getLabel();
        $element = parent::getElement();
        $desc = $element->getDescription();
        
        if ($desc) {
            $label .= "&nbsp;<span class='form-desc'>" . $desc . "</span>";
        }
        

        return $label;
    }
}