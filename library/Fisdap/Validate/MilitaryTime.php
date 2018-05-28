<?php

class Fisdap_Validate_MilitaryTime extends Zend_Validate_Abstract
{
    const INVALID = 'invalid';

    protected $_messageTemplates = array(
        self::INVALID => "'%value%' is not in 24 hour format."
    );

    public function isValid($value)
    {
        if (preg_match("/^(2[0-3]|[0-1][0-9]){1}([0-5][0-9]){1}$/", $value)) {
			return true;
		}


        $this->_error(self::INVALID);
        return false;
    }
}