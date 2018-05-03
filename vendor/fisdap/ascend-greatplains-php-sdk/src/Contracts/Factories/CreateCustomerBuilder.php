<?php namespace Fisdap\Ascend\Greatplains\Contracts\Factories;

use Fisdap\Ascend\Greatplains\Contracts\Customer;

/**
 * Interface CreateCustomerBuilder
 *
 * Create and return a customer entity
 *
 * @package Fisdap\Ascend\Greatplains\Contracts\Factories
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
interface CreateCustomerBuilder
{
    /**
     * Build and return a customer entity
     *
     * @return Customer
     */
    public function buildCustomerEntity();
}
