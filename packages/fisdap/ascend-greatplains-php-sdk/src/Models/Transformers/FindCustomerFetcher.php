<?php namespace Fisdap\Ascend\Greatplains\Models\Transformers;

use Fisdap\Ascend\Greatplains\Contracts\Models\Transformers\FindCustomerFetcher as FindCustomerFetcherInterface;
use Fisdap\Ascend\Greatplains\Factories\CreateCustomerBuilder;
use Fisdap\Ascend\Greatplains\Contracts\Customer;

/**
 * Class FindCustomerFetcher
 *
 * Class used to find get data necessary to find a customer
 *
 * @package Fisdap\Ascend\Greatplains\Models\Transformers
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class FindCustomerFetcher implements FindCustomerFetcherInterface
{
    /**
     * The customer id
     *
     * @var string
     */
    private $id;

    /**
     * The json response from the api
     *
     * @var string
     */
    private $response;

    /**
     * Set the customer id
     *
     * @param string $id
     * @return FindCustomerFetcher
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Get the customer id
     *
     * @return string
     * @throws \Exception
     */
    public function getId()
    {
        if (!$this->id) {
            throw new \Exception("Customer ID is required to fetch a customer");
        }
        return $this->id;
    }

    /**
     * Get persistent location for data
     *
     * @return mixed
     */
    public function getPersistentLocation()
    {
        return "api/customers/" . $this->getId();
    }

    /**
     * Set the json response from api into the fetcher class
     *
     * @param array $data
     * @return mixed
     */
    public function setResponse($data)
    {
        $this->response = $data;
        return $this;
    }

    /**
     * Get the customer returned back from API
     *
     * @return Customer
     */
    public function getCustomer()
    {
        $data = $this->response['Data'];

        $customerBuilder = new CreateCustomerBuilder(
            $data[Customer::ID_FIELD],
            $data[Customer::NAME_FIELD],
            $data[Customer::ADDRESSES_FIELD]
        );

        return $customerBuilder->buildCustomerEntity();
    }
}
