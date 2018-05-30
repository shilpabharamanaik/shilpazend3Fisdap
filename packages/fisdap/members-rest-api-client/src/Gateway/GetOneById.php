<?php namespace Fisdap\Api\Client\Gateway;

/**
 * Trait for supporting the RetrievesById contract
 *
 * @package Fisdap\Api\Client\Gateway
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
trait GetOneById
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
    public function getOneById($id, array $includes = null, array $includeIds = null)
    {
        return $this->client->get(static::$uriRoot . "/$id", [
            'query' => [
                'includes'     => is_array($includes) ? implode(',', $includes) : null,
                'includeIds'   => is_array($includeIds) ? implode(',', $includeIds) : null,
            ],
            'responseType' => $this->responseType
        ]);
    }
}
