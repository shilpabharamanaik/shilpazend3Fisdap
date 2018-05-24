<?php namespace Fisdap\Api\Client\Gateway;


/**
 * Contract for retrieving a single resource by ID
 *
 * @package Fisdap\Api\Client\Gateway
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
interface RetrievesById
{
    /**
     * Get a resource by its id
     *
     * @param mixed         $id
     * @param string[]|null $includes
     * @param string[]|null $includeIds
     *
     * @return object
     */
    public function getOneById($id, array $includes = null, array $includeIds = null);
}