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
class Fisdap_Form_Element_DateRange extends Zend_Form_Element_Xhtml
{
	/**
     * @var string start date
     */
    protected $_startDate;
    
    /**
     * @var string end date
     */
    protected $_endDate;
    
    /**
     * @var string the view helper that will render this composite element
     */
    public $helper = "dateRangeElement";
    
    /**
     * Set a default label and year range
     * both can be overwritten
     */
    public function init($label = "Date range:")
    {
        $this->setLabel($label);
		$this->setDefaultStart("today");
		$this->setDefaultEnd("+1 month");
		$this->addValidator(new \Fisdap_Validate_DateFormat());
    }
    
    /**
     * Set the start and end dates of this form element
     *
     * @param array the startDate/endDate values to set
     * @return Fisdap_Form_Element_DateRange the form element
     */
    public function setValue($value)
    {
        if(isset($value['startDate']))
        {
            if ($value['startDate'] instanceof \DateTime) {
                $this->_startDate = $value['startDate']->format("m/d/Y");
            } else {
                $this->_startDate = $value['startDate'];
            }
        }
        
        if(isset($value['endDate']))
        {
            if ($value['endDate'] instanceof \DateTime) {
                $this->_endDate = $value['endDate']->format("m/d/Y");
            } else {
                $this->_endDate = $value['endDate'];
            }
        }
        
        return $this;
    }
    
    
    /**
     * returns the value of this form element
     * 
     * @return mixed array
     */
    public function getValue()
    {
        return array('startDate' => $this->_startDate, 'endDate' => $this->_endDate);
    }
	
	/**
     * Set the default start date
     * @param string $defaultStart this param will be passed into new \Datetime if not null
     */
    public function setDefaultStart($defaultStart = null)
    {
        $this->setAttrib('defaultStart', $defaultStart);
        return $this;
    }
	
	/**
     * Set the default end date
     * @param string $defaultEnd this param will be passed into new \Datetime if not null
     */
    public function setDefaultEnd($defaultEnd = null)
    {
        $this->setAttrib('defaultEnd', $defaultEnd);
        return $this;
    }
    
}