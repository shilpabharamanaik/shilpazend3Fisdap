<?php namespace Fisdap\Ascend\Greatplains\Contracts;

use Fisdap\Ascend\Greatplains\Contracts\Models\Entity;

/**
 * interface Customer
 *
 * Represent an individual customer model from Greatplains
 *
 * @package Fisdap\Ascend\Greatplains\Contracts
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
interface Customer extends Entity
{
    const ID_FIELD = 'Id';
    const NAME_FIELD = 'Name';
    const ADDRESSES_FIELD = 'Addresses';

    /**
     * Get the customer id
     *
     * @return string|null
     */
    public function getId();

    /**
     * Get customers name
     *
     * @return string|null
     */
    public function getName();

    /**
     * Get address collection
     *
     * @return AddressCollection
     */
    public function getAddresses();
}
