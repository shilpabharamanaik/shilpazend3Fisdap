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
 * This is a custom validator for limiting the date range to the mm/dd/yyyy format
 */

/**
 * @package    Fisdap
 * @subpackage Validators
 */
class Fisdap_Validate_DateFormat extends Zend_Validate_Abstract
{
	const INVALID_START = 'invalidStart';
	const INVALID_END = 'invalidEnd';
	
	protected $_messageTemplates = array(
        self::INVALID_START => "Please enter a valid start date in mm/dd/yyyy format.",
		self::INVALID_END => "Please enter a valid end date in mm/dd/yyyy format.",
    );
	
    public function __construct()
	{
		
	}
	
    public function isValid($value)
    {
        $isValid = true;
        $this->_setValue($value);
		
		if ($value['startDate'] && !\Util_String::isValidDate($value['startDate'])) {
			$this->_error(self::INVALID_START);
			$isValid = false;
		}
		
		if ($value['endDate'] && !\Util_String::isValidDate($value['endDate'])) {
			$this->_error(self::INVALID_END);
			$isValid = false;
		}

        return $isValid;
    }

}