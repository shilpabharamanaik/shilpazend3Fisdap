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
 * Custom Blood Pressure Prompt
 */

/**
 * Class creating a composite blood pressure element
 *
 * @package SkillsTracker
 */
class SkillsTracker_Form_Element_BloodPressure extends Zend_Form_Element_Xhtml
{
    /**
     * @var string the systolic bp
     */
    protected $_systolic;
    
    /**
     * @var string the diastolic bp
     */
    protected $_diastolic;
    
    /**
     * @var string the view helper that will render this composite element
     */
    public $helper = "bloodPressureElement";
    
    /**
     * Set the value of this form element
     *
     * @param array the two BP values
     * @return SkillsTracker_Form_Element_BloodPressure the form element
     */
    public function setValue($value)
    {
		$this->_systolic = $value['systolic'];
		$this->_diastolic = $value['diastolic'];
        
        return $this;
    }
    
    /**
     * returns the value of this blood pressure
     * @return int the ID of the subject
     */
    public function getValue()
    {      
        return array('systolic' => $this->_systolic, 'diastolic' => $this->_diastolic);
    }
}
