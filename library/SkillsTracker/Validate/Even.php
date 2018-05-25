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
 * This is a custom validator for an even number
 */

/**
 * @package    SkillsTracker
 * @subpackage Validators
 */
class SkillsTracker_Validate_Even extends Zend_Validate_Abstract
{
    const NOT_EVEN = 'notEven';
    
    protected $_messageTemplates = array(
        self::NOT_EVEN => "'%value%' is not an even number.",
    );
    
    public function isValid($value)
    {
        $isValid = true;
        $this->_setValue($value);
        
        if ($value % 2 != 0 ) {
            $this->_error(self::NOT_EVEN);
            $isValid = false;
        }
        
        return $isValid;
    }
}