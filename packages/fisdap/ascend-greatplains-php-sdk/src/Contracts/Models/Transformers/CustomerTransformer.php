<?php namespace Fisdap\Ascend\Greatplains\Contracts\Models\Transformers;

use Fisdap\Ascend\Greatplains\Contracts\Customer;

/**
 * Interface CustomerTransformer
 *
 * Customer transformer to transform data to use in storage layer
 *
 * @package Fisdap\Ascend\Greatplains\Contracts\Models\Transformers
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
interface CustomerTransformer extends PersistentEntityTransformer, EntityTransformer
{
    /**
     * Set the customer entity
     *
     * @param Customer $customer
     * @return CustomerTransformer
     */
    public function setCustomer(Customer $customer);
}
