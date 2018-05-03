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
 * Custom Date Range Prompt
 */

/**
 * Class creating a composite date range element with start date and end date
 *
 * @package Fisdap
 * @author khanson
 */
class Fisdap_Form_Element_CertificationLevelChosen extends Zend_Form_Element_Multiselect
{
	public function init()
    {
        $options = \Fisdap\Entity\CertificationLevel::getFormOptions(false,false,"description");
        $this->setMultiOptions($options);
		$this->setValue(array_keys($options));
		$this->setAttrib("data-placeholder", "All certification levels...");
		$this->getView()->jQuery()->addOnLoad("$('#{$this->getName()}').width(380).chosen();");
        //$this->setLabel("Certification level");
    }
}