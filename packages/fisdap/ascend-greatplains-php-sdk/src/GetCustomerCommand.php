<?php namespace Fisdap\Ascend\Greatplains;

use Fisdap\Ascend\Greatplains\Contracts\Models\Transformers\FindCustomerFetcher;
use Fisdap\Ascend\Greatplains\Contracts\Repositories\CustomerRepository;
use Fisdap\Ascend\Greatplains\Contracts\Customer;

/**
 * Class GetCustomerCommand
 *
 * Find a customer
 *
 * @package Fisdap\Ascend\Greatplains
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class GetCustomerCommand
{
    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @var FindCustomerFetcher
     */
    private $findCustomerFetcher;

    /**
     * GetCustomer constructor
     *
     * @param CustomerRepository $customerRepository
     * @param FindCustomerFetcher $findCustomerFetcher
     */
    public function __construct(CustomerRepository $customerRepository, FindCustomerFetcher $findCustomerFetcher)
    {
        $this->customerRepository = $customerRepository;
        $this->findCustomerFetcher = $findCustomerFetcher;
    }

    /**
     * Fetch a customer from Greatplains
     *
     * @param string $id
     * @return Customer
     */
    public function handle($id)
    {
        /**
         * @var $entityFetcher FindCustomerFetcher
         */
        $entityFetcher = $this->customerRepository->getOneByEntityFetcher($this->findCustomerFetcher->setId($id));
        return $entityFetcher->getCustomer();
    }
}
