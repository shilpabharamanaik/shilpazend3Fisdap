<?php namespace Fisdap\Ascend\Greatplains\Collections;

use Fisdap\Ascend\Greatplains\Contracts\InternetAddress;
use Fisdap\Ascend\Greatplains\Contracts\InternetAddressCollection as InternetAddressCollectionInterface;

/**
 * Class InternetAddressCollection
 *
 * InternetAddress collection to store internet address objects
 *
 * @package Fisdap\Ascend\Greatplains\Collections
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class InternetAddressCollection implements InternetAddressCollectionInterface
{
    /**
     * @var InternetAddress[]
     */
    protected $internetAddresses = [];

    /**
     * Add a new internet address to the collection
     *
     * @param InternetAddress $internetAddress
     * @return $this
     */
    public function append(InternetAddress $internetAddress)
    {
        $this->internetAddresses[] = $internetAddress;
        return $this;
    }

    /**
     * Return internet addresses as array
     *
     * @return array
     */
    public function toArray()
    {
        $data = [];

        foreach ($this->internetAddresses as $internetAddress) {
            $data[$internetAddress->getType()] = $internetAddress->getValue();
        }

        return $data;
    }
}
