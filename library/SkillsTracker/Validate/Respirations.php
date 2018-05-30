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
 * This is a custom validator for respirations
 */

/**
 * @package    SkillsTracker
 * @subpackage Validators
 */
class SkillsTracker_Validate_Respirations extends Zend_Validate_Abstract
{
    const INVALID_RESP = 'invalidRespirations';
    
    protected $_messageTemplates = array(
        self::INVALID_RESP => "Please only use numbers to record the respiratory rate."
    );
    
    public function isValid($value)
    {
        $this->_setValue($value);

        if ($value['rate'] && !is_numeric($value['rate'])) {
            $this->_error(self::INVALID_RESP);
            return false;
        }

        return true;
    }
}
