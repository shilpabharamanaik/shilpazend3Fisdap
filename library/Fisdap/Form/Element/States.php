<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
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
 * Custom Zend_Form_Element_Select for displaying a list of US states
 */

/**
 * @package Fisdap
 */
class Fisdap_Form_Element_States extends Zend_Form_Element_Select
{
    protected static $_states = array(
        "USA" => array(
            array('Alabama', "AL"),
            array('Alaska', "AK"),
            array('Arizona', "AZ"),
            array('Arkansas', "AR"),
            array('California', "CA"),
            array('Colorado', "CO"),
            array('Connecticut', "CT"),
            array('Delaware', "DE"),
            array('District Of Columbia', "DC"),
            array('Florida', "FL"),
            array('Georgia', "GA"),
            array('Hawaii', "HI"),
            array('Idaho', "ID"),
            array('Illinois', "IL"),
            array('Indiana', "IN"),
            array('Iowa', "IA"),
            array('Kansas', "KS"),
            array('Kentucky', "KY"),
            array('Louisiana', "LA"),
            array('Maine', "ME"),
            array('Maryland', "MD"),
            array('Massachusetts', "MA"),
            array('Michigan', "MI"),
            array('Minnesota', "MN"),
            array('Mississippi', "MS"),
            array('Missouri', "MO"),
            array('Montana', "MT"),
            array('Nebraska', "NE"),
            array('Nevada', "NV"),
            array('New Hampshire', "NH"),
            array('New Jersey', "NJ"),
            array('New Mexico', "NM"),
            array('New York', "NY"),
            array('North Carolina', "NC"),
            array('North Dakota', "ND"),
            array('Ohio', "OH"),
            array('Oklahoma', "OK"),
            array('Oregon', "OR"),
            array('Pennsylvania', "PA"),
            array('Rhode Island', "RI"),
            array('South Carolina', "SC"),
            array('South Dakota', "SD"),
            array('Tennessee', "TN"),
            array('Texas', "TX"),
            array('Utah', "UT"),
            array('Vermont', "VT"),
            array('Virginia', "VA"),
            array('Washington', "WA"),
            array('West Virginia', "WV"),
            array('Wisconsin', "WI"),
            array('Wyoming', "WY"),
        ),
        "CAN" => array(
            array('Alberta','AB'),
            array('British Columbia','BC'),
            array('Manitoba','MB'),
            array('New Brunswick','NB'),
            array('Newfoundland and Labrador','NF'),
            array('Northwest Territories','NT'),
            array('Nova Scotia','NS'),
            array('Nunavut','NU'),
            array('Ontario','ON'),
            array('Prince Edward Island','PE'),
            array('Quebec','PQ'),
            array('Saskatchewan','SK'),
            array('Yukon','YT')
        ),
        "ZAF" => array(
            array("Eastern Cape", "Eastern Cape"),
            array("Free State", "Free State"),
            array("Gauteng", "Gauteng"),
            array("KwaZulu-Natal", "KwaZulu-Natal"),
            array("Limpopo", "Limpopo"),
            array("Mpumalanga", "Mpumalanga"),
            array("North West", "North West"),
            array("Northern Cape", "Northern Cape"),
            array("Western Cape", "Western Cape"),
        ),
        "AUS" => array(
            array("Northern Territory", "NT"),
            array("Australian Capital Territory", "ACT"),
            array("Tasmania", "TAS"),
            array("South Australia", "SA"),
            array("Western Australia", "WA"),
            array("Queensland", "QLD"),
            array("Victoria", "VIC"),
            array("New South Wales", "NSW")
        )
    );

    protected $fullNames = false;
    protected $nullOption = true;
    protected $countryCode = "USA";

    public function init()
    {
        $this->setCountry($this->countryCode);
        $this->setRegisterInArrayValidator(false);
        $this->setAllowEmpty(false);
    }

    /**
     * Change the state values to match the given country
     * @param string $countryCode the abbreviation of the country
     * @return \Fisdap_Form_Element_States
     */
    public function setCountry($countryCode = "USA")
    {
        $this->countryCode = $countryCode;

        $this->clearMultiOptions();
        $states = self::$_states[$this->countryCode];

        if (!empty($states)) {
            if ($this->nullOption) {
                $this->addMultiOption("", "--");
            }

            foreach ($states as $state) {
                if ($this->fullNames) {
                    $this->addMultiOption($state[0], $state[0]);
                } else {
                    $this->addMultiOption($state[1], $state[0]);
                }
            }
        }

        return $this;
    }

    /**
     * Use the full name of the state rather than 2 char abbreviation
     * @return \Fisdap_Form_Element_States
     */
    public function useFullNames()
    {
        $this->fullNames = true;
        $this->setCountry($this->countryCode);

        return $this;
    }
    
    /**
     * Determines whether or not to add a null option at the top of the list
     * @return \Fisdap_Form_Element_States
     */
    public function setNullOption($useNull)
    {
        $this->nullOption = $useNull;

        return $this;
    }

    /**
     * Get the abbreviation for a state given the full name
     *
     * @param string $fullName the full name of the state
     * @return string the abbreviation for the state
     */
    public static function getAbbreviation($fullName, $countryCode = "USA")
    {
        foreach (self::$_states[$countryCode] as $state) {
            if ($state[0] == $fullName) {
                return $state[1];
            }
        }
        return null;
    }

    /**
     * Get the full name for a state given the abbreviation
     *
     * @param string $abbreviation the abbreviation for the state
     * @return string the full name of the state
     */
    public static function getFullName($abbreviation, $countryCode = "USA")
    {
        foreach (self::$_states[$countryCode] as $state) {
            if ($state[1] == $abbreviation) {
                return $state[0];
            }
        }
        return null;
    }

    /**
     * Determine if a given country has states in Fisdap
     * @param string $countryCode
     * @return boolean
     */
    public static function hasStates($countryCode)
    {
        return array_key_exists($countryCode, self::$_states);
    }
}
