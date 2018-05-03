<?php namespace Fisdap\Ascend\Greatplains\Models\Transformers;

use Fisdap\Ascend\Greatplains\Contracts\Customer;
use Fisdap\Ascend\Greatplains\Contracts\Support\Arrayable;
use Fisdap\Ascend\Greatplains\Contracts\Support\JsonSerializable;
use Fisdap\Ascend\Greatplains\Contracts\Models\Transformers\CustomerTransformer as CustomerTransformerInterface;

/**
 * Class CustomerTransformer
 *
 * Transform a customer entity to necessary format to save to web service or potential database
 *
 * @package Fisdap\Ascend\Greatplains\Models\Transformers
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class CustomerTransformer implements JsonSerializable, CustomerTransformerInterface, Arrayable
{
    /**
     * @var Customer
     */
    protected $customer;

    /**
     * The json response from the api
     *
     * @var string
     */
    private $response;

    /**
     * Get location where data gets stored
     *
     * @return string
     */
    public function getPersistentLocation()
    {
        return "api/customers";
    }

    /**
     * Get the persistent data to save to persistence layer
     *
     * @return string
     */
    public function getPersistentData()
    {
        return $this->toJson();
    }

    /**
     * Set the customer entity
     *
     * @param Customer $customer
     * @return CustomerTransformer
     */
    public function setCustomer(Customer $customer)
    {
        $this->customer = $customer;
        return $this;
    }

    /**
     * Get the customer entity
     *
     * @return Customer
     */
    public function getEntity()
    {
        return $this->customer;
    }

    /**
     * Get data as array
     *
     * @return array
     */
    public function toArray()
    {
        return [
            Customer::ID_FIELD                 => $this->getEntity()->getId(),
            Customer::NAME_FIELD               => $this->getEntity()->getName(),
            Customer::ADDRESSES_FIELD          => $this->getEntity()->getAddresses()->toArray()
        ];
    }

    /**
     * Return json representation of object
     *
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }

    /**
     * Set the json response from api into the transformer class
     *
     * @param $data
     * @return mixed
     */
    public function setResponse($data)
    {
        $this->response = $data;
        return $this;
    }
}
