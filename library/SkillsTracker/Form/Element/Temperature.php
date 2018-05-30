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
 * Custom Pulse Prompt
 */

/**
 * Class creating a composite pulse element
 *
 * @package SkillsTracker
 */
class SkillsTracker_Form_Element_Temperature extends Zend_Form_Element_Xhtml
{
    /**
     * @var string the pulse rate
     */
    protected $_temperature;
    
    /**
     * @var \Fisdap\Entity\VitalPulseQuality
     */
    protected $_units;
    
    /**
     * @var string the view helper that will render this composite element
     */
    public $helper = "temperatureElement";
    
    /**
     * Set the value of this form element
     *
     * @param array the pulse quality and rate
     * @return SkillsTracker_Form_Element_Pulse the form element
     */
    public function setValue($value)
    {
        $this->_temperature = $value['temperature'];
        $this->_units = $value['units'];
        
        return $this;
    }
    
    /**
     * returns the value of this pulse
     * @return array the pulse rate and quality
     */
    public function getValue()
    {
        return array('temperature' => $this->_temperature, 'units' => $this->_units);
    }
}
