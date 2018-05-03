<?php namespace Fisdap\Api\Students\Finder;

use Fisdap\Api\Queries\Exceptions\ResourceNotFound;

/**
 * Contract for retrieving a single student or various groups of students
 *
 * @package Fisdap\Api\Students
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
interface FindsStudents
{
    /**
     * @param int           $id Student ID
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