<?php namespace Fisdap\Ascend\Greatplains\Phpunit\Collections;

use Fisdap\Ascend\Greatplains\Collections\InternetAddressCollection;
use Fisdap\Ascend\Greatplains\Contracts\InternetAddress;
use Fisdap\Ascend\Greatplains\Contracts\InternetAddressCollection as InternetAddressCollectionInterface;
use Fisdap\Ascend\Greatplains\Contracts\Support\Arrayable;
use Fisdap\Ascend\Greatplains\Phpunit\TestCase;
use \Mockery as mockery;

/**
 * Class InternetAddressCollectionTest
 *
 * Tests for internet address collection
 *
 * @package Fisdap\Ascend\Greatplains\Phpunit\Collections
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class InternetAddressCollectionTest extends TestCase
{
    /**
     * Test internet address collection has correct contracts
     */
    public function testInternetAddressCollectionHasCorrectContracts()
    {
        $internetAddressCollection = new InternetAddressCollection();

        $this->assertInstanceOf(InternetAddressCollectionInterface::class, $internetAddressCollection);
        $this->assertInstanceOf(Arrayable::class, $internetAddressCollection);
    }

    /**
     * Test internet address collection can append internet address
     */
    public function testInternetAddressCollectionCanAppendInternetAddress()
    {
        $internetAddressCollection = new InternetAddressCollection();
        $internetAddress = mockery::mock(InternetAddress::class);

        $this->assertInstanceOf(InternetAddressCollectionInterface::class, $internetAddressCollection->append($internetAddress));
    }

    /**
     * Test can get internet address collection to array
     */
    public function testCanGetInternetAddressCollectionAsArray()
    {
        $internetAddressCollection = new InternetAddressCollection();
        $internetAddress = mockery::mock(InternetAddress::class);
        $internetAddress->shouldReceive('getType')->andReturn('EmailToAddress');
        $internetAddress->shouldReceive('getValue')->andReturn('jmichels@fisdap.net');

        $internetAddressCollection->append($internetAddress);

        $this->assertCount(1, $internetAddressCollection->toArray());
    }
}
