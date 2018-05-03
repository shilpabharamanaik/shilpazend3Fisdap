<?php namespace Fisdap\Ascend\Greatplains\Repositories;

use Fisdap\Ascend\Greatplains\Contracts\Models\Transformers\PersistentEntityFetcher;
use Fisdap\Ascend\Greatplains\Contracts\Models\Transformers\PersistentEntityTransformer;
use Fisdap\Ascend\Greatplains\Contracts\Repositories\ApiEntityManager as ApiEntityManagerInterface;
use Fisdap\Ascend\Greatplains\Contracts\Services\ApiClient;

/**
 * Class ApiEntityManager
 *
 * Entity manager to interact with layer that saves objects to Greatplains api from Ascend
 *
 * @package Fisdap\Ascend\Greatplains\Repositories
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class ApiEntityManager implements ApiEntityManagerInterface
{

    /**
     * @var ApiClient
     */
    protected $apiClient;

    /**
     * Set the api client to use to save entities
     *
     * @param ApiClient $apiClient
     * @return ApiEntityManager
     */
    public function setApiClient(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
        return $this;
    }

    /**
     * Get the api client
     *
     * @return ApiClient
     * @throws \Exception
     */
    public function getApiClient()
    {
        if (!$this->apiClient) {
            throw new \Exception('Entity manager requires api client to persist data');
        }
        return $this->apiClient;
    }

    /**
     * Store the customer entity into the API
     *
     * @param PersistentEntityTransformer $transformer
     * @return PersistentEntityTransformer
     */
    public function persist(PersistentEntityTransformer $transformer)
    {
        $response = $this->getApiClient()->post(
            $transformer->getPersistentLocation(),
            $transformer->getPersistentData()
        );
        $transformer->setResponse($response);

        return $transformer;
    }

    /**
     * Find an entity and return the entity fetcher with response
     *
     * @param PersistentEntityFetcher $entityFetcher
     * @return PersistentEntityFetcher
     */
    public function find(PersistentEntityFetcher $entityFetcher)
    {
        $response = $this->getApiClient()->get($entityFetcher->getPersistentLocation());
        $entityFetcher->setResponse($response);

        return $entityFetcher;
    }

    /**
     * Update the entity into the api
     *
     * @param PersistentEntityTransformer $transformer
     * @return PersistentEntityTransformer
     * @throws \Exception
     */
    public function update(PersistentEntityTransformer $transformer)
    {
        $response = $this->getApiClient()->patch(
            $transformer->getPersistentLocation(),
            $transformer->getPersistentData()
        );
        $transformer->setResponse($response);

        return $transformer;
    }
}
