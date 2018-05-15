<?php namespace Fisdap\Data\Practice;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Fisdap\Data\Repository\Repository;

/**
 * Interface PracticeItemRepository
 *
 * @package Fisdap\Data\Practice
 * @copyright 1996-2014 Headwaters Software, Inc.
 */
interface PracticeItemRepository extends Repository
{

    /**
     * Get a list of practice items (evaluated by an instructor) for a given student at given shift types (i.e. field, clinical, lab).
     * Can also be filtered further by a specific Instructor evaluator.
     *
     * @param int      $studentId
     * @param array    $shiftTypes
     * @param int|null $instructorId
     *
     * @return array
     */
    public function getItemsByStudentEvaluatorShiftTypes($studentId, array $shiftTypes, $instructorId = null);
}
