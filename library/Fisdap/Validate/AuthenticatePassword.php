<?php

class Fisdap_Validate_AuthenticatePassword extends Zend\Validator\AbstractValidator
{
    const NOT_AUTH = 'notAuth';
 
    protected $_messageTemplates = array(
        self::NOT_AUTH => "Incorrect password."
    );
 
    public function isValid($value)
    {
        $authenticated = \Fisdap\Entity\User::authenticate_password(Zend\Authentication::getInstance()->getIdentity(), $value);
        
        if ($authenticated) {
            return true;
        }
        
        $this->_error(self::NOT_AUTH);
        return false;
    }
}