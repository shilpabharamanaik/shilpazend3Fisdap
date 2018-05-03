<?php namespace Fisdap\Api\Programs\Jobs\Models;

use Swagger\Annotations as SWG;


/**
 * Class Address
 *
 * @package Fisdap\Api\Programs\Jobs\Models
 * @author  Ben Getsug <bgetsug@fisdap.net>
 *          
 * @SWG\Definition(definition="ProgramAddress", required={"address1", "city", "state", "zip"})
 */
final class Address
{
    /**
     * @var string|null
     * @see ProgramLegacy::$address
     * @SWG\Property(type="string")
     */
    public $address1;

    /**
     * @var string|null
     * @SWG\Property(type="string")
     */
    public $address2 = null;

    /**
     * @var string|null
     * @SWG\Property(type="string")
     */
    public $address3 = null;

    /**
     * @var string
     * @SWG\Property(type="string")
     */
    public $city;

    /**
     * @var string
     * @SWG\Property(type="string")
     */
    public $state;

    /**
     * @var string
     * @SWG\Property(type="string")
     */
    public $zip;

    /**
     * @var string
     * @SWG\Property(type="string")
     */
    public $country = 'USA';
}