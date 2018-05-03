<?php namespace Fisdap\Ascend\Greatplains\Models;

use Fisdap\Ascend\Greatplains\Contracts\AddressCollection;
use Fisdap\Ascend\Greatplains\Contracts\Customer as CustomerInterface;

/**
 * Class Customer
 *
 * Represent an individual customer model from Greatplains
 *
 * @package Fisdap\Ascend\Greatplains\Models
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class Customer implements CustomerInterface
{
    /**
     * Customers id
     *
     * @var string
     */
    protected $id;

    /**
     * Customers name
     *
     * @var string
     */
    protected $name;

    /**
     * Address collection
     *
     * @var AddressCollection
     */
    protected $addresses;

    /**
     * Customer constructor.
     * @param $id
     * @param $name
     * @param AddressCollection $addressCollection
     */
    public function __construct(
        $id,
        $name,
        AddressCollection $addressCollection
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->addresses = $addressCollection;
    }

    /**
     * Get the customer id
     *
     * @return string|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get customers name
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get address collection
     *
     * @return AddressCollection
     */
    public function getAddresses()
    {
        return $this->addresses;
    }
}
