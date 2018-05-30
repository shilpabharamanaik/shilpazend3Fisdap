<?php

class Fisdap_Validate_MultipleEmails extends Zend_Validate_Abstract
{
    const INVALID = 'invalid';

    protected $_messageTemplates = array(
        self::INVALID => "'%value%' does not contain a valid email address."
    );

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function isValid($value)
    {
        //Swap semicolons for commas
        $value = str_replace(";", ",", $value);

        //See if any commas exist, which would indicate multiple emails
        if (strpos($value, ",") !== false) {
            $emails = explode(',', $value);
        } else {
            $emails = array($value);
        }

        //Instantiate an email validator to check all the given emails
        $emailValidator = new \Zend_Validate_EmailAddress();

        foreach ($emails as $email) {
            if (!$emailValidator->isValid($email)) {
                $this->_error(self::INVALID);
                return false;
            }
        }
        return true;
    }
}
