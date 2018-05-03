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
 * Custom Mutliselect Chosen Element
 */

/**
 * Class creating a jQuery multiselect chosen form element
 *
 * @package Fisdap
 * @author pwolpers
 */
class Fisdap_Form_Element_jQueryMultiselectChosen extends Zend_Form_Element_Multiselect
{
    public function init()
    {
        $this->getView()->jQuery()->addOnLoad("$('#{$this->getName()}').width(380).chosen();");
    }
}