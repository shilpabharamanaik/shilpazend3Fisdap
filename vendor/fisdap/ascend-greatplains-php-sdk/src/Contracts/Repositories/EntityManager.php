<?php namespace Fisdap\Ascend\Greatplains\Contracts\Repositories;

use Fisdap\Ascend\Greatplains\Contracts\Models\Transformers\PersistentEntityTransformer;
use Fisdap\Ascend\Greatplains\Contracts\Models\Transformers\PersistentEntityFetcher;

/**
 * Interface EntityManager
 *
 * Entity manager to persist objects
 *
 * @package Fisdap\Ascend\Greatplains\Contracts\Repositories
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
interface EntityManager
{
    /**
     * Store the customer entity into the API
     *
     * @param PersistentEntityTransformer $transformer
     * @return PersistentEntityTransformer
     */
    public function persist(PersistentEntityTransformer $transformer);

    /**
     * Find an entity and return the entity fetcher with response
     *
     * @param PersistentEntityFetcher $entityFetcher
     * @return PersistentEntityFetcher
     */
    public function find(PersistentEntityFetcher $entityFetcher);

    /**
     * Update the entity into the api
     *
     * @param PersistentEntityTransformer $transformer
     * @return PersistentEntityTransformer
     * @throws \Exception
     */
    public function update(PersistentEntityTransformer $transformer);
}
