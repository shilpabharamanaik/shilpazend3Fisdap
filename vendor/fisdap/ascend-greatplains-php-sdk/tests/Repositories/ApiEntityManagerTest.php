<?php namespace Fisdap\Ascend\Greatplains\Phpunit\Repositories;

use Fisdap\Ascend\Greatplains\Contracts\Repositories\EntityManager;
use Fisdap\Ascend\Greatplains\Contracts\Repositories\ApiEntityManager as ApiEntityManagerInterface;
use Fisdap\Ascend\Greatplains\Contracts\Models\Transformers\PersistentEntityTransformer;
use Fisdap\Ascend\Greatplains\Contracts\Models\Transformers\PersistentEntityFetcher;
use Fisdap\Ascend\Greatplains\Repositories\ApiEntityManager;
use Fisdap\Ascend\Greatplains\Contracts\Services\ApiClient;
use Fisdap\Ascend\Greatplains\Phpunit\TestCase;
use \Mockery as mockery;

/**
 * Class ApiEntityManagerTest
 *
 * API entity manager test
 *
 * @package Fisdap\Ascend\Greatplains\Phpunit\Repositories
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class ApiEntityManagerTest extends TestCase
{
    /**
     * Test api entity manager has correct contracts
     */
    public function testApiEntityManagerHasCorrectContracts()
    {
        $em = new ApiEntityManager();

        $this->assertInstanceOf(EntityManager::class, $em);
        $this->assertInstanceOf(ApiEntityManagerInterface::class, $em);
    }

    /**
     * Test api entity manager is an entity manager
     *
     * @throws \Exception
     */
    public function testApiEntityManagerIsEntityManager()
    {
        $em = new ApiEntityManager();

        $apiClient = mockery::mock(ApiClient::class);
        $apiClient->shouldReceive('post')->andReturn(['Data' => []]);
        $apiClient->shouldReceive('get')->andReturn(['Data' => []]);
        $apiClient->shouldReceive('patch')->andReturn(['Data' => []]);

        $this->assertInstanceOf(ApiEntityManagerInterface::class, $em->setApiClient($apiClient));
        $this->assertInstanceOf(ApiClient::class, $em->getApiClient());

        // Test can persist
        $entityTransformer = mockery::mock(PersistentEntityTransformer::class);
        $entityTransformer->shouldReceive('getPersistentLocation')->andReturn('/endpoint/');
        $entityTransformer->shouldReceive('getPersistentData')->andReturn('json');
        $entityTransformer->shouldReceive('setResponse')->andReturnSelf();
        $this->assertInstanceOf(PersistentEntityTransformer::class, $em->persist($entityTransformer));

        // Test can update
        $this->assertInstanceOf(PersistentEntityTransformer::class, $em->update($entityTransformer));

        $entityFetcher = mockery::mock(PersistentEntityFetcher::class);
        $entityFetcher->shouldReceive('getPersistentLocation')->andReturn('/endpoint/');
        $entityFetcher->shouldReceive('setResponse')->andReturnSelf();
        $this->assertInstanceOf(PersistentEntityFetcher::class, $em->find($entityFetcher));
    }

    /**
     * Test api entity manager requires api client
     *
     * @expectedException \Exception
     */
    public function testApiEntityManagerRequiresApiClient()
    {
        $em = new ApiEntityManager();
        $em->getApiClient();
    }
}
