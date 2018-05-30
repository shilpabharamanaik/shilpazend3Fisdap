<?php namespace Fisdap\Api\Client\Students\Gateway;

/**
 * Contract for shifts gateways
 *
 * @package Fisdap\Api\Client\Students\Gateway
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
interface StudentsGateway
{
    /**
     * Get an associative array of shift objects, keyed by id
     *
     * @param int           $studentId
     * @param string[]|null $includes
     * @param string[]|null $includeIds
     * @param string[]|null $states
     * @param string[]|null $types
     * @param string[]|null $startingBetween
     * @param int|null      $firstResult
     * @param int|null      $maxResults
     *
     * @return array
     */
    public function getShifts(
        $studentId,
        array $includes = null,
        array $includeIds = null,
        array $states = null,
        array $types = null,
        array $startingBetween = null,
        $firstResult = null,
        $maxResults = null
    );
}
