<?php namespace Fisdap\Ascend\Greatplains\Phpunit\Repositories;

use Fisdap\Ascend\Greatplains\Repositories\Repository;
use Fisdap\Ascend\Greatplains\Repositories\CustomerRepository;
use Fisdap\Ascend\Greatplains\Contracts\Repositories\CustomerRepository as CustomerRepositoryInterface;
use Fisdap\Ascend\Greatplains\Contracts\Repositories\Repository as RepositoryInterface;
use Fisdap\Ascend\Greatplains\Phpunit\TestCase;
use \Mockery as mockery;

/**
 * Class CustomerRepositoryTest
 *
 * Tests for customer repository
 *
 * @package Fisdap\Ascend\Greatplains\Phpunit\Repositories
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class CustomerRepositoryTest extends TestCase
{
    /**
     * Test customer repository has correct contracts
     */
    public function testCustomerRepositoryHasCorrectContracts()
    {
        $repo = new CustomerRepository();

        $this->assertInstanceOf(CustomerRepositoryInterface::class, $repo);
        $this->assertInstanceOf(Repository::class, $repo);
        $this->assertInstanceOf(RepositoryInterface::class, $repo);
    }
}
