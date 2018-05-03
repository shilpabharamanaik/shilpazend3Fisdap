<?php namespace Fisdap\Ascend\Greatplains\Phpunit\Factories;

use Fisdap\Ascend\Greatplains\Factories\CreateCustomerBuilder;
use Fisdap\Ascend\Greatplains\Contracts\Factories\CreateCustomerBuilder as CreateCustomerBuilderInterface;
use Fisdap\Ascend\Greatplains\Contracts\Customer;
use Fisdap\Ascend\Greatplains\Phpunit\TestCase;
use \Mockery as mockery;

/**
 * Class CreateCustomerBuilderTest
 *
 * Tests for Create customer builder
 *
 * @package Fisdap\Ascend\Greatplains\Phpunit\Factories
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class CreateCustomerBuilderTest extends TestCase
{
    /**
     * Test create customer builder has correct contracts
     */
    public function testCreateCustomerBuilderHasCorrectContracts()
    {
        $builder = new CreateCustomerBuilder('customerId', 'customerName', []);

        $this->assertInstanceOf(CreateCustomerBuilderInterface::class, $builder);
    }

    /**
     * Test create customer builder can build customer entity
     */
    public function testCreateCustomerBuilderCanBuildCustomerEntity()
    {
        $address = [
            'Id'            => 'addressId',
            'Line1'         => '4908 upton ave',
            'Line2'         => null,
            'Line3'         => null,
            'City'          => 'Minneapolis',
            'State'         => 'MN',
            'PostalCode'    => '55410',
            'CountryRegion' => 'USA',
            'InternetAddresses' => ['EmailToAddress' => 'jmichels@fisdap.net'],
            'ContactPerson' => 'Jason',
            'Phone1'        => ['Value' => '5155551234', 'CountryCode' => 1, 'Extension' => null],
            'Phone2'        => null,
            'Phone3'        => null,
            'Fax'           => null,
        ];

        $builder = new CreateCustomerBuilder(
            'customerId',
            'customerName',
            [$address]
        );

        $this->assertInstanceOf(Customer::class, $builder->buildCustomerEntity());
    }
}
