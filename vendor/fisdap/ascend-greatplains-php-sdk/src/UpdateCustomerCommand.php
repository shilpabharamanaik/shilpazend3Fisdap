<?php namespace Fisdap\Ascend\Greatplains;

use Fisdap\Ascend\Greatplains\Contracts\Factories\CreateCustomerBuilder;
use Fisdap\Ascend\Greatplains\Contracts\Models\Transformers\CustomerTransformer;
use Fisdap\Ascend\Greatplains\Contracts\Repositories\CustomerRepository;
use Fisdap\Ascend\Greatplains\Contracts\Customer;

/**
 * Class UpdateCustomerCommand
 *
 * Update an existing customer
 *
 * @package Fisdap\Ascend\Greatplains
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class UpdateCustomerCommand
{
    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @var CustomerTransformer
     */
    private $customerTransformer;

    /**
     * UpdateCustomerCommand constructor.
     *
     * @param CustomerRepository $customerRepository
     * @param CustomerTransformer $customerTransformer
     */
    public function __construct(CustomerRepository $customerRepository, CustomerTransformer $customerTransformer)
    {
        $this->customerRepository = $customerRepository;
        $this->customerTransformer = $customerTransformer;
    }

    /**
     * Update a customer entity to the data abstraction layer
     *
     * @param CreateCustomerBuilder $createCustomerBuilder
     * @return Customer
     */
    public function handle(CreateCustomerBuilder $createCustomerBuilder)
    {
        $customer = $createCustomerBuilder->buildCustomerEntity();

        $transformer = $this->customerRepository->update($this->customerTransformer->setCustomer($customer));

        return $customer;
    }
}
