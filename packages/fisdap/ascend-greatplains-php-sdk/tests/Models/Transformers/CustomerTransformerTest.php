<?php namespace Fisdap\Ascend\Greatplains\Phpunit\Models\Transformers;

use Fisdap\Ascend\Greatplains\Contracts\AddressCollection as AddressCollectionInterface;
use Fisdap\Ascend\Greatplains\Contracts\InternetAddressCollection as InternetAddressCollectionInterface;
use Fisdap\Ascend\Greatplains\Models\Transformers\CustomerTransformer;
use Fisdap\Ascend\Greatplains\Contracts\Support\JsonSerializable;
use Fisdap\Ascend\Greatplains\Contracts\Support\Arrayable;
use Fisdap\Ascend\Greatplains\Contracts\Models\Transformers\CustomerTransformer as CustomerTransformerInterface;
use Fisdap\Ascend\Greatplains\Contracts\Models\Transformers\PersistentEntityTransformer;
use Fisdap\Ascend\Greatplains\Contracts\Models\Transformers\EntityTransformer;
use Fisdap\Ascend\Greatplains\Contracts\Customer;
use Fisdap\Ascend\Greatplains\Contracts\Models\Entity;
use Fisdap\Ascend\Greatplains\Phpunit\TestCase;
use \Mockery as mockery;

/**
 * Class CustomerTransformerTest
 *
 * Tests for teh customer transformer class
 *
 * @package Fisdap\Ascend\Greatplains\Phpunit\Models\Transformers
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class CustomerTransformerTest extends TestCase
{
    /**
     * Test class has correct contracts
     */
    public function testCustomerTransformerHasCorrectContracts()
    {
        $transformer = new CustomerTransformer();

        $this->assertInstanceOf(JsonSerializable::class, $transformer);
        $this->assertInstanceOf(Arrayable::class, $transformer);
        $this->assertInstanceOf(CustomerTransformerInterface::class, $transformer);
        $this->assertInstanceOf(PersistentEntityTransformer::class, $transformer);
        $this->assertInstanceOf(EntityTransformer::class, $transformer);
    }

    /**
     * Test customer transformer can set customer
     */
    public function testCustomerTransformerCanSetCustomer()
    {
        $customer = mockery::mock(Customer::class);

        $transformer = new CustomerTransformer();
        $this->assertInstanceOf(CustomerTransformerInterface::class, $transformer->setCustomer($customer));
    }

    /**
     * Test customer transformer can get entity
     */
    public function testCustomerTransformerCanGetEntity()
    {
        $customer = mockery::mock(Customer::class);

        $transformer = new CustomerTransformer();
        $transformer->setCustomer($customer);

        $this->assertInstanceOf(Entity::class, $transformer->getEntity());
    }

    /**
     * Test customer transformer is persistent transformer
     */
    public function testCustomerTransformerIsPersistentTransformer()
    {
        $addressCollection = mockery::mock(AddressCollectionInterface::class);
        $addressCollection->shouldReceive('toArray')->andReturn([]);

        $customer = mockery::mock(Customer::class);
        $customer->shouldReceive('getId')->andReturn('id');
        $customer->shouldReceive('getName')->andReturn('Jason');
        $customer->shouldReceive('getAddresses')->andReturn($addressCollection);


        $transformer = new CustomerTransformer();
        $transformer->setCustomer($customer);

        $this->assertStringMatchesFormat('api/customers', $transformer->getPersistentLocation());
        $this->assertStringMatchesFormat('{"Id":"id","Name":"Jason","Addresses":[]}', $transformer->getPersistentData());
        $this->assertJson($transformer->getPersistentData());
        $this->assertInstanceOf(CustomerTransformerInterface::class, $transformer->setResponse(json_encode(['Data'])));
    }
}
