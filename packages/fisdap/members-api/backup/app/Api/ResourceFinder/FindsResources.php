<?php namespace Fisdap\Api\ResourceFinder;

/**
 * Contract for a resource finder
 *
 * @package Fisdap\Api\ResourceFinder
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
interface FindsResources
{
    /**
     * @param int        $id
     * @param array|null $associations
     * @param array|null $associationIds
     * @param bool       $asArray
     *
     * @return mixed
     */
    public function findById($id, array $associations = null, array $associationIds = null, $asArray = false);

}