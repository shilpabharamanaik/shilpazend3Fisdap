<?php namespace Fisdap\Api\Users\Jobs\Models;

use Swagger\Annotations as SWG;

/**
 * Class Address
 *
 * @package Fisdap\Api\Users\Jobs\Models
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @codeCoverageIgnore
 *
 * @SWG\Definition(definition="UserAddress")
 */
class Address
{
    /**
     * @var string|null
     * @SWG\Property(type="string")
     */
    public $address = null;

    /**
     * @var string|null
     * @SWG\Property(type="string")
     */
    public $city = null;

    /**
     * @var string|null
     * @SWG\Property(type="string")
     */
    public $state = null;

    /**
     * @var string|null
     * @SWG\Property(type="string")
     */
    public $country = null;

    /**
     * @var string|null
     * @SWG\Property(type="string")
     */
    public $zip = null;


    /**
     * Address constructor.
     *
     * @param null|string $address
     * @param null|string $city
     * @param null|string $state
     * @param null|string $zip
     * @param null $country
     */
    public function __construct($address = null, $city = null, $state = null, $zip = null, $country = null)
    {
        $this->address = $address;
        $this->city = $city;
        $this->state = $state;
        $this->country = $country;
        $this->zip = $zip;
    }
}
