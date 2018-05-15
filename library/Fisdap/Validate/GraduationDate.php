<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                                           *
*        Copyright (C) 1996-2011.  This is an unpublished work of           *
*                         Headwaters Software, Inc.                         *
*                            ALL RIGHTS RESERVED                            *
*        This program is a trade secret of Headwaters Software, Inc. and    *
*        it is not to be copied, distributed, reproduced, published, or     *
*        adapted without prior authorization of Headwaters Software, Inc.   *
*                                                                           *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

/**
 * This is a custom validator for graduation date
 */

/**
 * @package    Fisdap
 * @subpackage Validators
 */
class Fisdap_Validate_GraduationDate extends Zend_Validate_Abstract
{
    const MISSING_MONTH = 'missingMonth';
    const MISSING_YEAR = 'missingYear';
    const IN_THE_PAST = 'pastDate';
    
    protected $_required = true;
    protected $futureOnly;
    
    protected $_messageTemplates = array(
        self::MISSING_MONTH => "Please enter a month.",
        self::MISSING_YEAR => "Please enter a year.",
        self::IN_THE_PAST => "Please enter a date that is in the future.",
    );
    
    public function __construct($required = true, $futureOnly = true)
    {
        $this->setRequired($required);
        $this->futureOnly = $futureOnly;
    }
    
    public function isValid($value)
    {
        $isValid = true;
        $this->_setValue($value);
        $today = new DateTime();
        
        

        if (is_array($value)) {
            //return true if the field isn't required and both values are empty
            if (!$this->_required && $value['month'] == 0 && $value['year'] == 0) {
                return $isValid;
            }

            if ($value['month'] <= 0) {
                $this->_error(self::MISSING_MONTH);
                $isValid = false;
            }
            
            if ($value['year'] <= 0) {
                $this->_error(self::MISSING_YEAR);
                $isValid = false;
            }
            
            if ($this->futureOnly) {
                if ($isValid) {
                    if ($value['month'] == 12) {
                        $month = 1;
                        $year = $value['year'] + 1;
                    } else {
                        $month = $value['month']+1;
                        $year = $value['year'];
                    }
                    
                    $gradDate = new DateTime($month . "/01/" . $year);
                    if ($gradDate < $today) {
                        $this->_error(self::IN_THE_PAST);
                        $isValid = false;
                    }
                }
            }
        }

        return $isValid;
    }
    
    /**
     * Set whether this validator requires values to be set
     *
     * @param boolean $value is this field required
     * @return \Fisdap_Validate_GraduationDate
     */
    public function setRequired($value = true)
    {
        $this->_required = $value;
        return $this;
    }
    
    /**
     * Return the required field
     *
     * @return boolean
     */
    public function getRequired()
    {
        return $this->_required;
    }
}
