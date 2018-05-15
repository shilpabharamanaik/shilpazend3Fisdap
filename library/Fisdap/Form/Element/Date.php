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
 * Custom Date Prompt
 */

/**
 * Class creating a composite date element
 *
 * @package Fisdap
 */
class Fisdap_Form_Element_Date extends Zend_Form_Element_Xhtml
{
    /**
     * @var integer day
     */
    protected $_day;
    
    /**
     * @var integer month
     */
    protected $_month;
    
    /**
     * @var integer year
     */
    protected $_year;
    
    /**
     * @var string the view helper that will render this composite element
     */
    public $helper = "dateElement";
    
    /**
     * Set the date of this form element
     *
     * @param mixed \DateTime | array the day/month/year values to set
     * @return Fisdap_Form_Element_Date the form element
     */
    public function setValue($value)
    {
        if ($value instanceof \DateTime) {
            $this->_day = $value->format('j');
            $this->_month = $value->format('n');
            $this->_year = $value->format('Y');
            return $this;
        }
        
        $this->_day = $value['day'];
        $this->_month = $value['month'];
        $this->_year = $value['year'];
        return $this;
    }
    
    /**
     * returns the value of this form element
     *
     * @return mixed array
     */
    public function getValue()
    {
        return array(
            'day' => $this->_day,
            'month' => $this->_month,
            'year' => $this->_year,
        );
    }
}
