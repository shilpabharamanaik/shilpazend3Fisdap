<?php namespace Fisdap\Ascend\Greatplains\Phpunit\Models\Transformers;

use Fisdap\Ascend\Greatplains\Contracts\Models\Transformers\PersistentEntityFetcher;
use Fisdap\Ascend\Greatplains\Contracts\Models\Transformers\FindCustomerFetcher as FindCustomerFetcherInterface;
use Fisdap\Ascend\Greatplains\Models\Transformers\FindCustomerFetcher;
use Fisdap\Ascend\Greatplains\Contracts\Customer;
use Fisdap\Ascend\Greatplains\Phpunit\TestCase;
use \Mockery as mockery;

/**
 * Class FindCustomerFetcherTest
 *
 * Tests for find customer fetcher transformer
 *
 * @package Fisdap\Ascend\Greatplains\Phpunit\Models\Transformers
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class FindCustomerFetcherTest extends TestCase
{
    /**
     * Test find customer fetcher has correct contracts
     */
    public function testFindCustomerFetcherHasCorrectContracts()
    {
        $fetcher = new FindCustomerFetcher();

        $this->assertInstanceOf(PersistentEntityFetcher::class, $fetcher);
        $this->assertInstanceOf(FindCustomerFetcherInterface::class, $fetcher);
    }

    /**
     * Find customer fetcher fulfills persistent entity
     */
    public function testFindCustomerFetcherFulfillsPersistentEntity()
    {
        $fetcher = new FindCustomerFetcher();
        $fetcher->setId('1');

        $this->assertStringMatchesFormat("api/customers/1", $fetcher->getPersistentLocation());

        $response = [
            'Data' => [
                'Id'                => '123',
                'Name'              => 'jason',
                'Addresses'         => [],
            ]
        ];

        $this->assertInstanceOf(PersistentEntityFetcher::class, $fetcher->setResponse($response));
    }

    /**
     * Test find customer fetcher fulfills interface
     *
     * @throws \Exception
     */
    public function testFindCustomerFetcherFulfillsInterface()
    {
        $fetcher = new FindCustomerFetcher();

        $this->assertInstanceOf(FindCustomerFetcherInterface::class, $fetcher->setId('1'));
        $this->assertStringMatchesFormat('1', $fetcher->getId());

        $response = [
            'Data' => [
                'Id'                => '123',
                'Name'              => 'jason',
                'Addresses'         => [],
            ]
        ];

        $fetcher->setResponse($response);

        $this->assertInstanceOf(Customer::class, $fetcher->getCustomer());
    }

    /**
     * Test find customer fetcher requires id
     * @expectedException \Exception
     */
    public function testFindCustomerFetcherRequiresId()
    {
        $fetcher = new FindCustomerFetcher();
        $fetcher->getId();
    }
}
