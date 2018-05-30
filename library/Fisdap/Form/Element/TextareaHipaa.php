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
 * Custom Zend_Form_Element_Select for displaying a list of US states
 */

/**
 * @package Fisdap
 */
class Fisdap_Form_Element_TextareaHipaa extends Zend_Form_Element_Textarea
{
    public function init()
    {
        $this->setAttribs(array(
               "title" => "Do not enter patient identifying information that could violate HIPAA",
               "placeholder" => "Do not enter patient identifying information that could violate HIPAA",
            ));
    }
}
