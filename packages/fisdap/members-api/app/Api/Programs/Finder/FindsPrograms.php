<?php namespace Fisdap\Api\Programs\Finder;

use Fisdap\Api\ResourceFinder\FindsResources;
use Fisdap\Entity\ProgramLegacy;

/**
 * Contract for retrieving programs
 *
 * @package Fisdap\Api\Programs
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
interface FindsPrograms extends FindsResources
{
    /**
     * @param $id
     * @param array|null $associations
     * @param array|null $associationIds
     * @return ProgramLegacy
     */
    public function getById($id, array $associations = null, array $associationIds = null);
}
