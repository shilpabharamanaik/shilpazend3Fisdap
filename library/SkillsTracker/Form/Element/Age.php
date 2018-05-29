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
 * Custom Age Prompt
 */

/**
 * Class creating a composite age element
 *
 * @package SkillsTracker
 */
class SkillsTracker_Form_Element_Age extends Zend_Form_Element_Xhtml
{
    /**
     * @var int graduation month
     */
    protected $_years;
    
    /**
     * @var int graduation year
     */
    protected $_months;
    
    /**
     * @var string the view helper that will render this composite element
     */
    public $helper = "ageElement";
    
    /**
     * Set the value of this form element
     *
     * @param array the year and month values to set
     * @return SkillsTracker_Form_Element_Age the form element
     */
    public function setValue($value)
    {
        while($value['months'] - 12 >= 0){
            $value['months'] = $value['months'] - 12;
            $value['years'] = $value['years'] + 1;       
        }
        $this->_months = $value['months'];
        $this->_years = $value['years'];
        return $this;

    }
    
    /**
     * returns the value of this object
     * @return array the age in years and months
     */
    public function getValue()
    {
        $values = array(
            'months' => $this->_months,
            'years' => $this->_years,
        );
        
        return $values;
    }
}
