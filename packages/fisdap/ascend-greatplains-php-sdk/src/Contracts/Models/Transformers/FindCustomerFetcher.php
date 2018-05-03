<?php namespace Fisdap\Ascend\Greatplains\Contracts\Models\Transformers;

use Fisdap\Ascend\Greatplains\Contracts\Customer;

/**
 * Interface FindCustomerFetcher
 *
 * Find a customer fetcher interface
 *
 * @package Fisdap\Ascend\Greatplains\Contracts\Models\Transformers
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
interface FindCustomerFetcher extends PersistentEntityFetcher
{
    /**
     * Set the customer id
     *
     * @param string $id
     * @return FindCustomerFetcher
     */
    public function setId($id);

    /**
     * Get the customer id
     *
     * @return string
     * @throws \Exception
     */
    public function getId();

    /**
     * Get the customer returned back from API
     *
     * @return Customer
     */
    public function getCustomer();
}
