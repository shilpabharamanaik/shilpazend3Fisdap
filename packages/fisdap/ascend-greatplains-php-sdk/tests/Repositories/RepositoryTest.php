<?php namespace Fisdap\Ascend\Greatplains\Phpunit\Repositories;

use Fisdap\Ascend\Greatplains\Contracts\Repositories\EntityManager;
use Fisdap\Ascend\Greatplains\Repositories\BaseRepository;
use Fisdap\Ascend\Greatplains\Repositories\Repository;
use Fisdap\Ascend\Greatplains\Contracts\Models\Transformers\PersistentEntityTransformer;
use Fisdap\Ascend\Greatplains\Contracts\Models\Transformers\PersistentEntityFetcher;
use Fisdap\Ascend\Greatplains\Phpunit\TestCase;
use \Mockery as mockery;

/**
 * Class RepositoryTest
 *
 * Tests for base repository
 *
 * @package Fisdap\Ascend\Greatplains\Phpunit\Repositories
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class RepositoryTest extends TestCase
{
    public function testRepositoryHasCorrectContracts()
    {
        $repo = new Repository();
        $this->assertInstanceOf(BaseRepository::class, $repo);
    }

    /**
     * Test repository has entity manager
     *
     * @throws \Exception
     */
    public function testRepositoryHasEntityManager()
    {
        $em = mockery::mock(EntityManager::class);

        $repo = new Repository();

        $this->assertInstanceOf(Repository::class, $repo->setEntityManager($em));
        $this->assertInstanceOf(EntityManager::class, $repo->getEntityManager());
    }

    /**
     * @expectedException \Exception
     */
    public function testRepositoryRequiresEntityManager()
    {
        $repo = new Repository();
        $repo->getEntityManager();
    }

    /**
     * Test repository can store entity
     */
    public function testRepositoryCanStoreEntity()
    {
        $transformer = mockery::mock(PersistentEntityTransformer::class);

        $em = mockery::mock(EntityManager::class);
        $em->shouldReceive('persist')->andReturn($transformer);

        $repo = new Repository();
        $repo->setEntityManager($em);

        $this->assertInstanceOf(PersistentEntityTransformer::class, $repo->store($transformer));
    }

    /**
     * Test repository can get one by entity fetcher
     */
    public function testRepositoryCanGetOneByEntityFetcher()
    {
        $fetcher = mockery::mock(PersistentEntityFetcher::class);

        $em = mockery::mock(EntityManager::class);
        $em->shouldReceive('find')->andReturn($fetcher);

        $repo = new Repository();
        $repo->setEntityManager($em);

        $this->assertInstanceOf(PersistentEntityFetcher::class, $repo->getOneByEntityFetcher($fetcher));
    }

    /**
     * Test repository can update entity
     */
    public function testRepositoryCanUpdateEntity()
    {
        $transformer = mockery::mock(PersistentEntityTransformer::class);

        $em = mockery::mock(EntityManager::class);
        $em->shouldReceive('update')->andReturn($transformer);

        $repo = new Repository();
        $repo->setEntityManager($em);

        $this->assertInstanceOf(PersistentEntityTransformer::class, $repo->update($transformer));
    }
}
