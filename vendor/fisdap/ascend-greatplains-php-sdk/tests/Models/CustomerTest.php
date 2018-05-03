<?php namespace Fisdap\Ascend\Greatplains\Phpunit\Models;

use Fisdap\Ascend\Greatplains\Models\Customer;
use Fisdap\Ascend\Greatplains\Contracts\Customer as CustomerInterface;
use Fisdap\Ascend\Greatplains\Contracts\Models\Entity;
use Fisdap\Ascend\Greatplains\Contracts\AddressCollection;
use Fisdap\Ascend\Greatplains\Phpunit\TestCase;
use \Mockery as mockery;

/**
 * Class CustomerTest
 *
 * Tests for the customer model
 *
 * @package Fisdap\Ascend\Greatplains\Phpunit\Models
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class CustomerTest extends TestCase
{
    /**
     * @var Customer
     */
    protected $customer;

    /**
     * Get customer object
     *
     * @return Customer
     */
    protected function getCustomer()
    {
        if (!$this->customer) {
            $addressCollection = mockery::mock(AddressCollection::class);

            $this->customer = new Customer('id', 'jason', $addressCollection);
        }
        return $this->customer;
    }

    /**
     * Test customer model has correct contracts
     */
    public function testCustomerModelHasCorrectContracts()
    {
        $this->assertInstanceOf(CustomerInterface::class, $this->getCustomer());
        $this->assertInstanceOf(Entity::class, $this->getCustomer());
    }

    /**
     * Test customer has correct fields
     */
    public function testCustomerHasCorrectFields()
    {
        $this->assertStringMatchesFormat('Id', CustomerInterface::ID_FIELD);
        $this->assertStringMatchesFormat('Name', CustomerInterface::NAME_FIELD);
        $this->assertStringMatchesFormat('Addresses', CustomerInterface::ADDRESSES_FIELD);
    }

    /**
     * Test customer has correct getters
     */
    public function testCustomerHasCorrectGetters()
    {
        $this->assertStringMatchesFormat('id', $this->getCustomer()->getId());
        $this->assertStringMatchesFormat('jason', $this->getCustomer()->getName());
        $this->assertInstanceOf(AddressCollection::class, $this->getCustomer()->getAddresses());
    }
}
