<?php namespace Fisdap\Ascend\Greatplains\Contracts\Repositories;

use Fisdap\Ascend\Greatplains\Contracts\Models\Transformers\PersistentEntityTransformer;
use Fisdap\Ascend\Greatplains\Contracts\Models\Transformers\PersistentEntityFetcher;

/**
 * Interface Repository
 *
 * Interact with entity manager to persist objects
 *
 * @package Fisdap\Ascend\Greatplains\Contracts\Repositories
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
interface Repository
{
    /**
     * Get new entity manager
     *
     * @return EntityManager
     */
    public function getEntityManager();

    /**
     * Set the entity manager
     *
     * @param EntityManager $entityManager
     * @return Repository
     */
    public function setEntityManager(EntityManager $entityManager);

    /**
     * Store the entity into the persistence layer
     *
     * @param PersistentEntityTransformer $transformer
     * @return PersistentEntityTransformer
     */
    public function store(PersistentEntityTransformer $transformer);

    /**
     * Find a resource by using an entity fetcher
     *
     * @param PersistentEntityFetcher $entityFetcher
     * @return PersistentEntityFetcher
     */
    public function getOneByEntityFetcher(PersistentEntityFetcher $entityFetcher);

    /**
     * Update the entity into the persistence layer
     *
     * @param PersistentEntityTransformer $transformer
     * @return PersistentEntityTransformer
     * @throws \Exception
     */
    public function update(PersistentEntityTransformer $transformer);
}
