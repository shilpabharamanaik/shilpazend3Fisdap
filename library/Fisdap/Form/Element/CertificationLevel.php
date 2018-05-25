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
 * Custom Certification Level Prompt
 */

/**
 * Class creating a composite certification level element
 *
 * @package Fisdap
 */
class Fisdap_Form_Element_CertificationLevel extends Zend_Form_Element_MultiCheckbox
{
    public function init()
    {
        $options = \Fisdap\Entity\CertificationLevel::getFormOptions(false,false,"description");
        $this->setMultiOptions($options);
        $this->setLabel("Certification level");
    }
}
