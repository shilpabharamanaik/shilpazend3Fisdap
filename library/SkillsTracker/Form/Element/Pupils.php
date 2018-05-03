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
 * Custom Pupils Prompt
 */

/**
 * Class creating a composite pupils element
 *
 * @package SkillsTracker
 */
class SkillsTracker_Form_Element_Pupils extends Zend_Form_Element_Xhtml
{
    /**
     * @var boolean
     */
    protected $_equal;
    
    /**
     * @var boolean
     */
    protected $_round;
    
    /**
     * @var boolean
     */
    protected $_reactive;
    
    /**
     * @var string the view helper that will render this composite element
     */
    public $helper = "pupilsElement";
    
    /**
     * Set the value of this form element
     *
     * @param array the pupil values to set
     * @return SkillsTracker_Form_Element_Pupils the form element
     */
    public function setValue($value)
    {
        $this->_equal = $value['equal'];
        $this->_round = $value['round'];
        $this->_reactive = $value['reactive'];
        return $this;
    }
    
    /**
     * returns the value of this object
     * @return array the age in years and months
     */
    public function getValue()
    {
        $values = array(
            'equal' => $this->_equal,
            'round' => $this->_round,
            'reactive' => $this->_reactive,
        );
        
        return $values;
    }
}
