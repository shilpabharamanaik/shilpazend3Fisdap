<?php namespace Fisdap\Ascend\Greatplains\Collections;

use Fisdap\Ascend\Greatplains\Contracts\Address;
use Fisdap\Ascend\Greatplains\Contracts\AddressCollection as AddressCollectionInterface;

/**
 * Class AddressCollection
 *
 * Object to represent a collection of addresses
 *
 * @package Fisdap\Ascend\Greatplains\Collections
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class AddressCollection implements AddressCollectionInterface
{
    /**
     * @var Address[]
     */
    protected $addresses = [];

    /**
     * Append a new address to the collection
     *
     * @param Address $address
     * @return $this
     */
    public function append(Address $address)
    {
        $this->addresses[] = $address;
        return $this;
    }

    /**
     * Return address collection as array
     *
     * @return array
     */
    public function toArray()
    {
        $data = [];

        foreach ($this->addresses as $address) {
            $data[] = $address->toArray();
        }

        return $data;
    }
}
