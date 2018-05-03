<?php namespace Fisdap\Ascend\Greatplains\Contracts\Models\Transformers;

/**
 * Interface PersistentEntityTransformer
 *
 * Must be a persistent entity to be saved
 *
 * @package Fisdap\Ascend\Greatplains\Contracts\Models\Transformers
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
interface PersistentEntityTransformer
{
    /**
     * Get persistent location for data
     *
     * @return mixed
     */
    public function getPersistentLocation();

    /**
     * Get the persistent data
     *
     * @return mixed
     */
    public function getPersistentData();

    /**
     * Set the json response from api into the fetcher class
     *
     * @param $data
     * @return mixed
     */
    public function setResponse($data);
}
