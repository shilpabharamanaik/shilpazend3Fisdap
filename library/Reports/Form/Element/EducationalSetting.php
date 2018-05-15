<?php

/****************************************************************************
*
*         Copyright (C) 1996-2011.  This is an unpublished work of
*                          Headwaters Software, Inc.
*                             ALL RIGHTS RESERVED
*         This program is a trade secret of Headwaters Software, Inc.
*         and it is not to be copied, distributed, reproduced, published,
*         or adapted without prior authorization
*         of Headwaters Software, Inc.
*
****************************************************************************/

/**
 * Description of StudentFilter
 *
 * @author astevenson
 */
class Reports_Form_Element_EducationalSetting extends Zend_Form_Element_Xhtml
{
    /**
     * @var string the view helper that will render this composite element
     */
    public $helper = "educationalSettingElement";
    
    public function init()
    {
        $this->getView()->headScript()->appendFile("/js/library/Reports/Form/Element/advanced-settings.js");
    }
    
    public function getValue()
    {
        if (!isset($this->_value['shifttype'])) {
            $value = $this->_value;
            $value['shifttype'] = array();
            $this->setValue($value);
        }
        return parent::getValue();
    }
}
