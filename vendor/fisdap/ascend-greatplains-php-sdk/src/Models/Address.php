<?php namespace Fisdap\Ascend\Greatplains\Models;

use Fisdap\Ascend\Greatplains\Contracts\Address as AddressInterface;
use Fisdap\Ascend\Greatplains\Contracts\InternetAddressCollection;

/**
 * Class Address
 *
 * Object representing an individual address in great plains
 *
 * @package Fisdap\Ascend\Greatplains\Models
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class Address implements AddressInterface
{
    /**
     * Id of the address
     *
     * @var string
     */
    protected $id;

    /**
     * Line 1 of address
     *
     * @var string
     */
    protected $line1;

    /**
     * Line 2 of address
     *
     * @var string
     */
    protected $line2;

    /**
     * Line 3 of address
     *
     * @var string
     */
    protected $line3;

    /**
     * City
     *
     * @var string
     */
    protected $city;

    /**
     * State
     *
     * @var string
     */
    protected $state;

    /**
     * Postal code
     *
     * @var string
     */
    protected $postalCode;

    /**
     * Country Region
     *
     * @var string
     */
    protected $countryRegion;

    /**
     * Internet Addresses
     *
     * @var InternetAddressCollection
     */
    protected $internetAddressCollection;

    /**
     * Contact Person
     *
     * @var string
     */
    protected $contactPerson;

    /**
     * First phone number
     *
     * @var Phone|null
     */
    protected $phone1;

    /**
     * Second phone number
     *
     * @var Phone|null
     */
    protected $phone2;

    /**
     * Third phone number
     *
     * @var Phone|null
     */
    protected $phone3;

    /**
     * Fax number
     *
     * @var Phone|null
     */
    protected $fax;

    /**
     * Properties representing an address
     *
     * @param string $id
     * @param string $line1
     * @param string $line2
     * @param string $line3
     * @param string $city
     * @param string $state
     * @param string $postalCode
     * @param string $countryRegion
     * @param InternetAddressCollection $internetAddressCollection
     * @param string $contactPerson
     * @param Phone|null $phone1
     * @param Phone|null $phone2
     * @param Phone|null $phone3
     * @param Phone|null $fax
     */
    public function __construct(
        $id,
        $line1,
        $line2,
        $line3,
        $city,
        $state,
        $postalCode,
        $countryRegion,
        InternetAddressCollection $internetAddressCollection,
        $contactPerson,
        $phone1 = null,
        $phone2 = null,
        $phone3 = null,
        $fax = null
    ) {
        $this->id = $id;
        $this->line1 = $line1;
        $this->line2 = $line2;
        $this->line3 = $line3;
        $this->city = $city;
        $this->state = $state;
        $this->postalCode = $postalCode;
        $this->countryRegion = $countryRegion;
        $this->internetAddressCollection = $internetAddressCollection;
        $this->contactPerson = $contactPerson;
        $this->phone1 = $phone1;
        $this->phone2 = $phone2;
        $this->phone3 = $phone3;
        $this->fax = $fax;
    }

    /**
     * Get ID
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get Line 1
     *
     * @return string
     */
    public function getLine1()
    {
        return $this->line1;
    }

    /**
     * Get Line 2
     *
     * @return string
     */
    public function getLine2()
    {
        return $this->line2;
    }

    /**
     * Get Line 3
     *
     * @return string
     */
    public function getLine3()
    {
        return $this->line3;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Get state
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Get postal code
     *
     * @return string
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * Get country region
     *
     * @return string
     */
    public function getCountryRegion()
    {
        return $this->countryRegion;
    }

    /**
     * Get internet address collection
     *
     * @return InternetAddressCollection
     */
    public function getInternetAddressCollection()
    {
        return $this->internetAddressCollection;
    }

    /**
     * Get contact person
     *
     * @return string
     */
    public function getContactPerson()
    {
        return $this->contactPerson;
    }

    /**
     * Get first phone number
     *
     * @return Phone|null
     */
    public function getPhone1()
    {
        return $this->phone1;
    }

    /**
     * Get second phone number
     *
     * @return Phone|null
     */
    public function getPhone2()
    {
        return $this->phone2;
    }

    /**
     * Get third phone number
     *
     * @return Phone|null
     */
    public function getPhone3()
    {
        return $this->phone3;
    }

    /**
     * Get fax number
     *
     * @return Phone|null
     */
    public function getFax()
    {
        return $this->fax;
    }

    /**
     * Get address object as array
     *
     * @return array
     */
    public function toArray()
    {
        $address = [
            self::ID_FIELD             => $this->getId(),
            self::LINE_1_FIELD         => $this->getLine1(),
            self::LINE_2_FIELD         => $this->getLine2(),
            self::LINE_3_FIELD         => $this->getLine3(),
            self::CITY_FIELD           => $this->getCity(),
            self::STATE_FIELD          => $this->getState(),
            self::POSTAL_CODE_FIELD    => $this->getPostalCode(),
            self::COUNTRY_REGION_FIELD => $this->getCountryRegion(),
            self::CONTACT_PERSON_FIELD => $this->getContactPerson()
        ];

        $address[self::INTERNET_ADDRESSES_FIELD] = (!empty($this->getInternetAddressCollection())) ? $this->getInternetAddressCollection()->toArray() : null;
        $address[self::PHONE_1_FIELD] = ($this->getPhone1()) ? $this->getPhone1()->toArray() : null;
        $address[self::PHONE_2_FIELD] = ($this->getPhone2()) ? $this->getPhone2()->toArray() : null;
        $address[self::PHONE_3_FIELD] = ($this->getPhone3()) ? $this->getPhone3()->toArray() : null;
        $address[self::FAX_FIELD] = ($this->getFax()) ? $this->getFax()->toArray() : null;

        return $address;
    }
}
