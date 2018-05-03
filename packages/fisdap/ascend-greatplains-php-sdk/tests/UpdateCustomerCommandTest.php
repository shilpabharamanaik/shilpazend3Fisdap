<?php namespace Fisdap\Ascend\Greatplains\Phpunit;

use Fisdap\Ascend\Greatplains\Contracts\Models\Transformers\CustomerTransformer;
use Fisdap\Ascend\Greatplains\Contracts\Repositories\CustomerRepository;
use Fisdap\Ascend\Greatplains\Contracts\Factories\CreateCustomerBuilder;
use Fisdap\Ascend\Greatplains\UpdateCustomerCommand;
use Fisdap\Ascend\Greatplains\Contracts\Customer;
use Fisdap\Ascend\Greatplains\Phpunit\TestCase;
use \Mockery as mockery;

/**
 * Class UpdateCustomerCommandTest
 *
 * Tests for update customer command
 *
 * @package Fisdap\Ascend\Greatplains\Phpunit
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class UpdateCustomerCommandTest extends TestCase
{
    /**
     * Test update customer command can update customer
     */
    public function testUpdateCustomerCommandCanCreateCustomer()
    {
        $customer = mockery::mock(Customer::class);

        $customerTransformer = mockery::mock(CustomerTransformer::class);
        $customerTransformer->shouldReceive('setCustomer')->andReturnSelf();

        $customerRepo = mockery::mock(CustomerRepository::class);
        $customerRepo->shouldReceive('update')->andReturn($customerTransformer);

        $createCustomerBuilder = mockery::mock(CreateCustomerBuilder::class);
        $createCustomerBuilder->shouldReceive('buildCustomerEntity')->andReturn($customer);

        $command = new UpdateCustomerCommand($customerRepo, $customerTransformer);

        $this->assertInstanceOf(Customer::class, $command->handle($createCustomerBuilder));
    }
}
