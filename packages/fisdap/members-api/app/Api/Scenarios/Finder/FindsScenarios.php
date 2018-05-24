<?php namespace Fisdap\Api\Scenarios\Finder;

use Fisdap\Api\Queries\Exceptions\ResourceNotFound;

/**
 * Contract for retrieving scenario data
 *
 * @package Fisdap\Api\Scenarios
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
interface FindsScenarios
{
    /**
     * @param int $id Scenario ID
     *
     * @param array $associations
     * @param array $associationIds
     * @param bool $asArray
     * @return mixed
     */
    public function getById(
        $id,
        array $associations = null,
        array $associationIds = null,
        $asArray = false
    );
}