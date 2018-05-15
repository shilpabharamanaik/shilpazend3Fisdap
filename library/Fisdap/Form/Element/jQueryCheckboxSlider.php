<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                                           *
*        Copyright (C) 1996-2014.  This is an unpublished work of           *
*                         Headwaters Software, Inc.                         *
*                            ALL RIGHTS RESERVED                            *
*        This program is a trade secret of Headwaters Software, Inc. and    *
*        it is not to be copied, distributed, reproduced, published, or     *
*        adapted without prior authorization of Headwaters Software, Inc.   *
*                                                                           *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * /

/**
 * Custom checkbox slider Element
 */

/**
 * Class creating a jQuery checkbox slider form element
 *
 * @package Fisdap
 * @author pwolpers
 */
class Fisdap_Form_Element_jQueryCheckboxSlider extends Zend_Form_Element_Checkbox
{
    public function init()
    {
        $this->getView()->jQuery()->addOnLoad("$('#{$this->getName()}').sliderCheckbox({onText:'On', offText:'Off'});");
    }
}
