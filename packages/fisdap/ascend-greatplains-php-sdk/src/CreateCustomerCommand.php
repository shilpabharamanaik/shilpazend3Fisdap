<?php namespace Fisdap\Ascend\Greatplains;

use Fisdap\Ascend\Greatplains\Contracts\Models\Transformers\CustomerTransformer;
use Fisdap\Ascend\Greatplains\Contracts\Repositories\CustomerRepository;
use Fisdap\Ascend\Greatplains\Contracts\Factories\CreateCustomerBuilder;
use Fisdap\Ascend\Greatplains\Contracts\Customer;

/**
 * Class CreateCustomerCommand
 *
 * Create and return a new customer object
 *
 * @package Fisdap\Ascend\Greatplains
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class CreateCustomerCommand
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
     * CreateCustomer constructor
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
     * Create a new customer and save entity to data abstraction layer
     *
     * @param CreateCustomerBuilder $createCustomerBuilder
     * @return Customer
     */
    public function handle(CreateCustomerBuilder $createCustomerBuilder)
    {
        $customer = $createCustomerBuilder->buildCustomerEntity();

        $customerTransformer = $this->customerRepository->store($this->customerTransformer->setCustomer($customer));

        return $customer;
    }
}
