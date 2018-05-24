<?php namespace Fisdap\Data\ClassSection;

use Fisdap\Data\Repository\Repository;


/**
 * Interface ClassSectionLegacyRepository
 *
 * @package Fisdap\Data\ClassSection
 */
interface ClassSectionLegacyRepository extends Repository
{
    /**
     * @param int $instructor_id
     * @param int $section_id
     *
     * @return mixed
     */
    public function getAssociationCountByInstructor($instructor_id, $section_id);


    /**
     * @param int $student_id
     * @param int $section_id
     *
     * @return mixed
     */
    public function getAssociationCountByStudent($student_id, $section_id);


    /**
     * @param int $student_id
     * @param int $section_id
     *
     * @return mixed
     */
    public function getAssociationCountByTa($student_id, $section_id);


    /**
     * @param int $programId
     *
     * @return array
     */
    public function getUniqueYears($programId);


    /**
     * @param int      $programId
     * @param int|null $year
     *
     * @return array
     */
    public function getNamesByProgram($programId, $year = null);


    /**
     * This function just fetches back listings of class sections for a specific program or student,
     * and lets you decide if you want only active groups, inactive groups, or all groups.
     *
     * @param int     $programId      ID of the program that we want to get groups from.  Required.
     * @param bool    $active         Flag for what types of groups to return.  Defaults to null, which will
     *                                return both Active AND Inactive.  True only returns Active, False only
     *                                returns Inactive.
     * @param int     $studentId      Optional ID of the student whose subscribed groups we want to fetch.  Defaults to
     *                                null.
     * @param boolean $optimized      - getArrayResult and entity partials to make things a bit fast
     * @param boolean $just_ids       return just an array of group ids?
     *
     * @return array
     */
    public function getProgramGroups(
        $programId,
        $active = null,
        $studentId = null,
        $optimized = false,
        $just_ids = false
    );


    /**
     * @param int       $programId
     * @param bool|null $active
     *
     * @return array
     */
    public function getFormOptions($programId, $active = null);
} 