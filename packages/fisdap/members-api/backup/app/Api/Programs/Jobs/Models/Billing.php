<?php namespace Fisdap\Api\Programs\Jobs\Models;

use Swagger\Annotations as SWG;


/**
 * Class Billing
 *
 * @package Fisdap\Api\Programs\Jobs\Models
 * @author  Ben Getsug <bgetsug@fisdap.net>
 *          
 * @SWG\Definition(definition="ProgramBilling", required={"email", "contactName", "address1", "city", "state", "zip"})
 */
final class Billing
{
    /**
     * @var string
     * @see ProgramLegacy::$billing_email
     * @SWG\Property(type="string")
     */
    public $email;

    /**
     * @var string|null
     * @see ProgramLegacy::$billing_contact
     * @SWG\Property(type="string")
     */
    public $contactName = null;

    /**
     * @var string
     * @see ProgramLegacy::$billing_address
     * @SWG\Property(type="string")
     */
    public $address1;

    /**
     * @var string|null
     * @see ProgramLegacy::$billing_address2
     * @SWG\Property(type="string")
     */
    public $address2 = null;

    /**
     * @var string|null
     * @see ProgramLegacy::$billing_address3
     * @SWG\Property(type="string")
     */
    public $address3 = null;

    /**
     * @var string
     * @see ProgramLegacy::$billing_city
     * @SWG\Property(type="string")
     */
    public $city;

    /**
     * @var string
     * @see ProgramLegacy::$billing_state
     * @SWG\Property(type="string")
     */
    public $state;

    /**
     * @var string
     * @see ProgramLegacy::$billing_zip
     * @SWG\Property(type="string")
     */
    public $zip;

    /**
     * @var string
     * @see ProgramLegacy::$billing_country
     * @SWG\Property(type="string")
     */
    public $country = "USA";
    
    /**
     * @var string|null
     * @see ProgramLegacy::$billing_phone
     * @SWG\Property(type="string")
     */
    public $phone = null;

    /**
     * @var string|null
     * @see ProgramLegacy::$billing_fax
     * @SWG\Property(type="string")
     */
    public $fax = null;
    
}