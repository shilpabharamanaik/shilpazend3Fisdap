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
 * Custom Graduation Date Prompt
 */

/**
 * Class creating a composite graduation date element
 *
 * @package Fisdap
 */
class Fisdap_Form_Element_GraduationDate extends Zend_Form_Element_Xhtml
{
    /**
     * @var int graduation month
     */
    protected $_month;
    
    /**
     * @var int graduation year
     */
    protected $_year;
    
    /**
     * @var string the view helper that will render this composite element
     */
    public $helper = "graduationDateElement";
    
    
    /**
     * Set a default label and year range
     * both can be overwritten
     */
    public function init($label = "Graduating:")
    {
        $this->setLabel($label);
        $this->setYearRange();
        $this->setMonthRange();
    }
    
    /**
     * Set the date of this form element
     *
     * @param mixed DateTime | array the month/year values to set
     * @return Fisdap_Form_Element_GraduationDate the form element
     */
    public function setValue($value)
    {
        if ($value instanceof \DateTime) {
            $this->_month = $value->format('n');
            $this->_year = $value->format('Y');
            return $this;
        }
        
        if(isset($value['month']))
        {
            $this->_month = $value['month'];
        }
        
        if(isset($value['year']))
        {
            $this->_year = $value['year'];
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
        return array('month' => $this->_month, 'year' => $this->_year);
    }
    
    /**
     * Set the year range
     *
     * @param integer $start the starting year
     * @param integer $end the ending year
     * @return \Fisdap_Form_Element_GraduationDate
     */
    public function setYearRange($start = null, $end = null)
    {
        //Set the default start date if none is provided
        if (!$start) {
            //Use the earliest class year if the user is logged in, otherwise go 5 years back
            if (\Zend_Auth::getInstance()->hasIdentity()) {
                $program = \Fisdap\Entity\ProgramLegacy::getCurrentProgram();
                $years = $program->get_possible_graduation_years(false);
                if (count($years) > 0) {
                    $start = reset($years);                    
                } else {
                    $start = date("Y") - 5;                    
                }
            } else {
                $start = date("Y") - 5;
            }
        }
        
        //Set the default end date if none is provided
        if (!$end) {
            $end = $start + 5;
        }
        
        $options = array(0 => "Year");
        
        for($i = $start; $i <= $end; $i++) {
            $options[$i] = $i;
        }
        
        $this->setAttrib('yearAttribs', array('formOptions' => $options));
        return $this;
    }
    
    /**
     * Use a custom set of year options
     *
     * @param array $options array of key=>value pairs representing years
     * @return \Fisdap_Form_Element_GraduationDate
     */
    public function setYearOptions($options)
    {
        $this->setAttrib('yearAttribs', array('formOptions' => $options));
        return $this;
    }
    
    /**
     * Add a custom year option
     * This will NOT overwrite current options
     *
     * @param integer the year option to add
     */
    public function addYearOption($option)
    {
        $options = $this->getAttrib('yearAttribs');
        $options['formOptions'][$option] = $option;
        ksort($options['formOptions']);
        return $this->setYearOptions($options['formOptions']);
    }
    
    /**
     * Only Display existing graduation years
     *
     * @param array $options array of key=>value pairs representing years
     * @return \Fisdap_Form_Element_GraduationDate
     */
    public function useExistingGraduationYears()
    {
        if (\Zend_Auth::getInstance()->hasIdentity()) {
            $program = \Fisdap\Entity\ProgramLegacy::getCurrentProgram();
            $years = $program->get_possible_graduation_years();
            $this->setYearOptions($years);
        }
        
        return $this;
    }
    
    /**
     * Set the month range
     *
     * @param integer $start the starting month
     * @param integer $end the ending month
     * @return \Fisdap_Form_Element_GraduationDate
     */
    public function setMonthRange($start = 1, $end = 12)
    {
        $options = array(0 => "Month");
		
		$months = Util_FisdapDate::get_short_month_names();
		
		foreach ($months as $i=>$month) {
			//Skip this value if it's not in our month range
			if ($i+1 < $start || $i+1 > $end) {
				continue;
			}
			
			$value = $i+1;
			if ($value < 10) {
				$value = "0" . $value;
			}
			$options[$value] = $month;
		}

        $this->setAttrib('monthAttribs', array('formOptions' => $options));
        return $this;
    }
    
    /**
     * Use a custom set of month options
     *
     * @param array $options array of key=>value pairs representing months
     * @return \Fisdap_Form_Element_GraduationDate
     */
    public function setMonthOptions($options)
    {
        $this->setAttrib('monthAttribs', array('formOptions' => $options));
        return $this;
    }
}
