<?php namespace Fisdap\Api\Shifts\Patients\Finder;

use Fisdap\Api\Queries\Exceptions\ResourceNotFound;

/**
 * Contract for retrieving a single patient
 *
 * @package Fisdap\Api\Shifts\Patients
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
interface FindsPatients
{
    /**
     * @param int           $id Patient ID
     * @param string[]|null $associations
     * @param int[]|null    $associationIds
     * @param bool          $asArray
     *
     * @return mixed
     * @throws ResourceNotFound
     */
    public function getById(
        $id,
        array $associations = null,
        array $associationIds = null,
        $asArray = false
    );
}
