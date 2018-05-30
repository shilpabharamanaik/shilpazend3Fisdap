<?php

class Fisdap_Validate_States extends Zend_Validate_Abstract
{
    const REQUIRED = 'required';

    protected $_countryElementName;

    protected $_messageTemplates = array(
        self::REQUIRED => "Please choose a state/provence."
    );

    public function __construct($countryElementName = "country")
    {
        $this->setCountryElementName($countryElementName);
    }

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if a value for the state exists OR a country that does does not have states is chosen
     *
     * @param  mixed $value
     * @return boolean
     */
    public function isValid($value, $context = null)
    {
        //$this->_setValue($value);

        //Do certain validation if we have a country form element
        if (array_key_exists($this->_countryElementName, $context)) {
            $hasStates = \Fisdap_Form_Element_States::hasStates($context[$this->_countryElementName]);

            //If the selected country has no state/provence values, return true no matter what
            if ($hasStates === false) {
                return true;
            }
        }

        //If we don't have a country, then the state must be required
        if (empty($value)) {
            $this->_error(self::REQUIRED);
            return false;
        }

        return true;
    }

    /**
     * Set the element name of the country form element so that
     * the validator knows where to find the country value
     *
     * @param string $name the form element ID
     * @return \Fisdap_Validate_States
     */
    public function setCountryElementName($name = "country")
    {
        $this->_countryElementName = $name;
        return $this;
    }
}
