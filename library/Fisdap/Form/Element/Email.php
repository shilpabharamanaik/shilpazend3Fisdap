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
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

/**
 * Custom Zend_Form_Element_Text for collecting email address
 * All this does is add a whitespace filter and email address validator
 */

/**
 * @package Fisdap
 */
class Fisdap_Form_Element_Email extends Zend_Form_Element_Text
{
    public function init()
    {
        $this->setLabel('Email')
             ->addFilter("StringTrim")
             ->addValidator('EmailAddress', false, array('mx' => true));
    }
}
