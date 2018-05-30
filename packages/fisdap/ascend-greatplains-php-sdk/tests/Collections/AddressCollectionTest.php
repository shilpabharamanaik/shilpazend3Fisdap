<?php namespace Fisdap\Ascend\Greatplains\Phpunit\Collections;

use Fisdap\Ascend\Greatplains\Collections\AddressCollection;
use Fisdap\Ascend\Greatplains\Contracts\AddressCollection as AddressCollectionInterface;
use Fisdap\Ascend\Greatplains\Contracts\Support\Arrayable;
use Fisdap\Ascend\Greatplains\Contracts\Address;
use Fisdap\Ascend\Greatplains\Phpunit\TestCase;
use \Mockery as mockery;

/**
 * Class AddressCollectionTest
 *
 * Tests for address collection
 *
 * @package Fisdap\Ascend\Greatplains\Phpunit\Collections
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class AddressCollectionTest extends TestCase
{
    /**
     * Test address collection has correct contracts
     */
    public function testAddressCollectionHasCorrectContracts()
    {
        $addressCollection = new AddressCollection();

        $this->assertInstanceOf(AddressCollectionInterface::class, $addressCollection);
        $this->assertInstanceOf(Arrayable::class, $addressCollection);
    }

    /**
     * Test can append address to collection
     */
    public function testCanAppendAddressToCollection()
    {
        $addressCollection = new AddressCollection();
        $address = mockery::mock(Address::class);

        $this->assertInstanceOf(AddressCollectionInterface::class, $addressCollection->append($address));
    }

    /**
     * Test can get address collection to array
     */
    public function testCanGetAddressCollectionAsArray()
    {
        $addressCollection = new AddressCollection();
        $address = mockery::mock(Address::class);
        $address->shouldReceive('toArray')->andReturn([]);

        $addressCollection->append($address);

        $this->assertCount(1, $addressCollection->toArray());
    }
}
