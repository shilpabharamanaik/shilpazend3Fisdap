<?php namespace Fisdap\Ascend\Greatplains\Phpunit;

use Fisdap\Ascend\Greatplains\Contracts\Models\Transformers\FindCustomerFetcher;
use Fisdap\Ascend\Greatplains\Contracts\Repositories\CustomerRepository;
use Fisdap\Ascend\Greatplains\Contracts\Customer;
use Fisdap\Ascend\Greatplains\GetCustomerCommand;
use Fisdap\Ascend\Greatplains\Phpunit\TestCase;
use \Mockery as mockery;

/**
 * Class GetCustomerCommandTest
 *
 * Test for the get customer command
 *
 * @package Fisdap\Ascend\Greatplains\Phpunit
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class GetCustomerCommandTest extends TestCase
{
    /**
     * Test get customer command can fetch customer
     */
    public function testGetCustomerCommandCanFetchCustomer()
    {
        $customer = mockery::mock(Customer::class);

        $fetcher = mockery::mock(FindCustomerFetcher::class);
        $fetcher->shouldReceive('setId')->andReturnSelf();
        $fetcher->shouldReceive('getCustomer')->andReturn($customer);

        $repo = mockery::mock(CustomerRepository::class);
        $repo->shouldReceive('getOneByEntityFetcher')->andReturn($fetcher);

        $command = new GetCustomerCommand($repo, $fetcher);

        $this->assertInstanceOf(Customer::class, $command->handle('id123'));
    }
}
