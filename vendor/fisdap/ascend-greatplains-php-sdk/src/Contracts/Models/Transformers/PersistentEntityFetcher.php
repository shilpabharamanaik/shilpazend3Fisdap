<?php namespace Fisdap\Ascend\Greatplains\Contracts\Models\Transformers;

/**
 * Interface PersistentEntityFetcher
 *
 * Class used to fetch a persistent entity from data access layer
 *
 * @package Fisdap\Ascend\Greatplains\Contracts\Models\Transformers
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
interface PersistentEntityFetcher
{
    /**
     * Get persistent location for data
     *
     * @return mixed
     */
    public function getPersistentLocation();

    /**
     * Set the json response from api into the fetcher class
     *
     * @param $data
     * @return mixed
     */
    public function setResponse($data);
}
