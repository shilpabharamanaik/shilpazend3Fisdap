<?php namespace Fisdap\Ascend\Greatplains\Repositories;

use Fisdap\Ascend\Greatplains\Contracts\Models\Transformers\PersistentEntityFetcher;
use Fisdap\Ascend\Greatplains\Contracts\Repositories\EntityManager;
use Fisdap\Ascend\Greatplains\Contracts\Repositories\Repository;
use Fisdap\Ascend\Greatplains\Contracts\Models\Transformers\PersistentEntityTransformer;

/**
 * Class BaseRepository
 *
 * Base repository functions
 *
 * @package Fisdap\Ascend\Greatplains\Repositories
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
abstract class BaseRepository implements Repository
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * Get new entity manager
     *
     * @return EntityManager
     * @throws \Exception
     */
    public function getEntityManager()
    {
        if (!$this->entityManager) {
            throw new \Exception('Repository requires entity manager to persist data');
        }
        return $this->entityManager;
    }

    /**
     * Set the entity manager
     *
     * @param EntityManager $entityManager
     * @return Repository
     */
    public function setEntityManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        return $this;
    }

    /**
     * Store the entity into the persistence layer
     *
     * @param PersistentEntityTransformer $transformer
     * @return PersistentEntityTransformer
     */
    public function store(PersistentEntityTransformer $transformer)
    {
        return $this->getEntityManager()->persist($transformer);
    }

    /**
     * Find a resource by using an entity fetcher
     *
     * @param PersistentEntityFetcher $entityFetcher
     * @return PersistentEntityFetcher
     */
    public function getOneByEntityFetcher(PersistentEntityFetcher $entityFetcher)
    {
        return $this->getEntityManager()->find($entityFetcher);
    }

    /**
     * Update the entity into the persistence layer
     *
     * @param PersistentEntityTransformer $transformer
     * @return PersistentEntityTransformer
     * @throws \Exception
     */
    public function update(PersistentEntityTransformer $transformer)
    {
        return $this->getEntityManager()->update($transformer);
    }
}
