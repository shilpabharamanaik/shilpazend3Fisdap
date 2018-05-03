<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                                           *
*        Copyright (C) 1996-2014.  This is an unpublished work of           *
*                         Headwaters Software, Inc.                         *
*                            ALL RIGHTS RESERVED                            *
*        This program is a trade secret of Headwaters Software, Inc. and    *
*        it is not to be copied, distributed, reproduced, published, or     *
*        adapted without prior authorization of Headwaters Software, Inc.   *
*                                                                           *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

/**
 * This is a custom validator for limiting the date range to a given number of days
 */

/**
 * @package    Fisdap
 * @subpackage Validators
 */
class Fisdap_Validate_DateRangeLimit extends Zend_Validate_Abstract
{
	const TOO_MANY = 'tooManyDays';
    const TOO_FEW = 'tooFewDays';
	const MISSING_START = 'missingStart';
	const MISSING_END = 'missingEnd';
	
    protected $minDays = 0;
    protected $maxDays = NULL;
	
	protected $_messageTemplates = array(
        self::TOO_MANY => "Date range spans too many days.",
        self::TOO_FEW => "Date range spans too few days.",
		self::MISSING_START => "Please choose a start date.",
		self::MISSING_END => "Please choose an end date.",
    );
	
    public function __construct($minDays = 0, $maxDays = null)
	{
		$this->minDays = $minDays;
		$this->maxDays = $maxDays;
		
		$this->_messageTemplates[self::TOO_MANY] = "Date range cannot be longer than " . $this->maxDays . " days.";
		$this->_messageTemplates[self::TOO_FEW] = "Date range must be at least " . $this->minDays . " days.";
	}
	
    public function isValid($value)
    {
        $isValid = true;
        $this->_setValue($value);
		
		//Do we have a start date to compare?
		if (!$value['startDate']) {
			$this->_error(self::MISSING_START);
			$isValid = false;
		}
		
		//Do we have an end date to compare?
		if (!$value['endDate']) {
			$this->_error(self::MISSING_END);
			$isValid = false;
		}
		
		//If we've already hit an error, there's no point in validating further
		if ($isValid === false) {
			return $isValid;
		}
		
		//Now to check if the two dates fall within the given date limit
		$startDate = strtotime($value['startDate']);
		$endDate = strtotime($value['endDate']);
		$days = (($endDate - $startDate)/86400) + 1;
		
		if ($this->maxDays && $days > $this->maxDays) {
			$this->_error(self::TOO_MANY);
			$isValid = false;
		}
		
		if ($days < $this->minDays) {
			$this->_error(self::TOO_FEW);
			$isValid = false;
		}

        return $isValid;
    }

}