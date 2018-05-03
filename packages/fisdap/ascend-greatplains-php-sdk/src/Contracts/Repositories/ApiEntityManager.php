<?php namespace Fisdap\Ascend\Greatplains\Contracts\Repositories;

use Fisdap\Ascend\Greatplains\Contracts\Services\ApiClient;

/**
 * Interface ApiEntityManager
 *
 * API Entity manager
 *
 * @package Fisdap\Ascend\Greatplains\Contracts\Repositories
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
interface ApiEntityManager extends EntityManager
{
    /**
     * Set the api client to use to save entities
     *
     * @param ApiClient $apiClient
     * @return ApiEntityManager
     */
    public function setApiClient(ApiClient $apiClient);

    /**
     * Get the api client
     *
     * @return ApiClient
     */
    public function getApiClient();
}
