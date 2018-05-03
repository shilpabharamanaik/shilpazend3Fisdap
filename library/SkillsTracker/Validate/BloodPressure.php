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
 * This is a custom validator for blood pressure
 */

/**
 * @package    SkillsTracker
 * @subpackage Validators
 */
class SkillsTracker_Validate_BloodPressure extends Zend_Validate_Abstract
{
    const INVALID_SYSTOLIC = 'invalidSystolic';
    const INVALID_DIASTOLIC = 'invalidDiastolic';
    const MISSING_SYSTOLIC = 'missingSystolic';
    const MISSING_DIASTOLIC = 'missingDiastolic';
    
    
    protected $_messageTemplates = array(
        self::INVALID_SYSTOLIC => "Please only use numbers to record the systolic blood pressure.",
        self::INVALID_DIASTOLIC => "Please only use numbers or the letter \"p\" (palpation) to record the systolic blood pressure.",
        self::MISSING_SYSTOLIC => "Please enter a value for systolic blood pressure.",
        self::MISSING_DIASTOLIC => "Please enter a value for diastolic blood pressure.",
    );
    
    public function isValid($value)
    {
        $isValid = true;
        $this->_setValue($value);

        if ($value['systolic'] && !is_numeric($value['systolic'])) {
            $this->_error(self::INVALID_SYSTOLIC);
            $isValid = false;
        }
        
        if ($value['diastolic'] && !(is_numeric($value['diastolic']) || strtoupper($value['diastolic']) == "P")) {
            $this->_error(self::INVALID_DIASTOLIC);
            $isValid = false;
        }
        
        if ($value['systolic'] && $value['diastolic'] == "") {
            $this->_error(self::MISSING_DIASTOLIC);
            $isValid = false;
        }
        
        if ($value['systolic'] == "" && $value['diastolic']) {
            $this->_error(self::MISSING_SYSTOLIC);
            $isValid = false;
        }

        return $isValid;
    }
}