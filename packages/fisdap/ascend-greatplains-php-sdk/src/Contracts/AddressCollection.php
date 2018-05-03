<?php namespace Fisdap\Ascend\Greatplains\Contracts;

use Fisdap\Ascend\Greatplains\Contracts\Support\Arrayable;

/**
 * Interface AddressCollection
 *
 * Represent a collection of addresses
 *
 * @package Fisdap\Ascend\Greatplains\Contracts
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
interface AddressCollection extends Arrayable
{
    /**
     * Append a new address to the collection
     *
     * @param Address $address
     * @return AddressCollection
     */
    public function append(Address $address);
}
