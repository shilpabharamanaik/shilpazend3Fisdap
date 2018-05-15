<?php namespace Fisdap\Api\Shifts\Finder;

use Fisdap\Api\Queries\Exceptions\ResourceNotFound;
use Fisdap\Api\Shifts\Queries\ShiftQueryParameters;

/**
 * Contract for retrieving a single shift or various groups of shifts
 *
 * @package Fisdap\Api\Shifts
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
interface FindsShifts
{
    /**
     * @param int           $id Shift ID
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


    /**
     * @param ShiftQueryParameters $queryParams
     *
     * @return array|null
     * @throws \Fisdap\Api\Queries\Exceptions\ResourceNotFound
     */
    public function getStudentShifts(ShiftQueryParameters $queryParams);


    /**
     * @param ShiftQueryParameters $queryParams
     *
     * @return array|null
     * @throws ResourceNotFound
     */
    public function getProgramShifts(ShiftQueryParameters $queryParams);


    /**
     * @param ShiftQueryParameters $queryParams
     *
     * @return array|null
     * @throws ResourceNotFound
     */
    public function getInstructorShifts(ShiftQueryParameters $queryParams);


    /**
     * @param int $id   Shift ID
     *
     * @return int
     */
    public function getShiftStudentProgramId($id);
}
