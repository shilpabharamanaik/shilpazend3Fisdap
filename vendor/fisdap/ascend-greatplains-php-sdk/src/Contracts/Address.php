<?php namespace Fisdap\Ascend\Greatplains\Contracts;

use Fisdap\Ascend\Greatplains\Contracts\Support\Arrayable;

/**
 * Interface Address
 *
 * Represent an address object
 *
 * @package Fisdap\Ascend\Greatplains\Contracts
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
interface Address extends Arrayable, HasPhoneNumbers, HasInternetAddresses
{
    const ID_FIELD = 'Id';
    const LINE_1_FIELD = 'Line1';
    const LINE_2_FIELD = 'Line2';
    const LINE_3_FIELD = 'Line3';
    const CITY_FIELD = 'City';
    const STATE_FIELD = 'State';
    const POSTAL_CODE_FIELD = 'PostalCode';
    const COUNTRY_REGION_FIELD = 'CountryRegion';
    const CONTACT_PERSON_FIELD = 'ContactPerson';

    /**
     * Get ID
     *
     * @return string
     */
    public function getId();

    /**
     * Get Line 1
     *
     * @return string
     */
    public function getLine1();

    /**
     * Get Line 2
     *
     * @return string
     */
    public function getLine2();

    /**
     * Get Line 3
     *
     * @return string
     */
    public function getLine3();

    /**
     * Get city
     *
     * @return string
     */
    public function getCity();

    /**
     * Get state
     *
     * @return string
     */
    public function getState();

    /**
     * Get postal code
     *
     * @return string
     */
    public function getPostalCode();

    /**
     * Get country region
     *
     * @return string
     */
    public function getCountryRegion();

    /**
     * Get contact person
     *
     * @return string
     */
    public function getContactPerson();
}
