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
 * This is a custom validator for activation code - will also validate product codes
 */

/**
 * @package    Account
 * @subpackage Validators
 */
class Account_Validate_ActivationCode extends Zend_Validate_Abstract
{
    const INVALID_PRODUCT_CODE = 'invalidProductCode';
    const INVALID_SERIAL = 'invalidSerial';
    const SERIAL_NOT_FOUND = 'serialNotFound';
    const ACTIVATED_SERIAL = 'activatedSerial';
    const JBL_CODE = 'jblCode';
    
    
    protected $_messageTemplates = array(
        self::INVALID_PRODUCT_CODE => "'%value%' is not a valid product code.",
        self::INVALID_SERIAL => "'%value%' is not a valid activation code.",
        self::SERIAL_NOT_FOUND => "'%value%' is not a valid activation code.",
        self::ACTIVATED_SERIAL => "'%value%' has already been activated.",
        self::JBL_CODE => "It looks like you are trying to redeem a Jones & Bartlett Learning access code. Redeem your code at JBLearning.com.",
    );
    
    public function isValid($value)
    {
        $isValid = true;
        $this->_setValue($value);
        $pattern = '/^([0-9]{2})[-]?([A-Za-z0-9]{13})[-]?([A-Za-z0-9]{4})$/';

        //If the fist char is numeric, it's a serial number
        if (is_numeric($value[0])) {
            if (!\Fisdap\Entity\SerialNumberLegacy::isSerialFormat($value, $matches)) {
                $isValid = false;
                $this->_error(self::INVALID_SERIAL);
                return $isValid;
            }

            if (!$serial = \Fisdap\Entity\SerialNumberLegacy::getBySerialNumber($matches[1] . "-" . $matches[2] . "-" . $matches[3])) {
                //var_dump($serial);
                $isValid = false;
                $this->_error(self::SERIAL_NOT_FOUND);
                return $isValid;
            }
            
            if ($serial->isActive()) {
                $isValid = false;
                $this->_error(self::ACTIVATED_SERIAL);
            }
            
            return $isValid;
        }

        //Check for product code validity

        // if this is a legacy code, it is valid
        if (\Fisdap\Entity\ProductCode::isLegacyProductCode($value)) {
            return $isValid;
        }

        $product_code = \Fisdap\Entity\ProductCode::getByProductCode($value);
        // make sure we have a real product code
        if (!$product_code) {
            $isValid = false;
            if (strlen($value) == 10) {
                $this->_error(self::JBL_CODE);
            } else {
                $this->_error(self::INVALID_PRODUCT_CODE);
            }
            return $isValid;
        }

        // make sure the program associated with this product code can actually order accounts
        if (!$product_code->isValid()) {
            $isValid = false;
            $this->_error(self::INVALID_PRODUCT_CODE);
            return $isValid;
        }
        
        return $isValid;
    }
}
