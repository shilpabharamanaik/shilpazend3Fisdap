<?php namespace Fisdap\Data\Requirement;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Fisdap\Data\Repository\Repository;


/**
 * Interface RequirementRepository
 *
 * @package Fisdap\Data\Requirement
 */
interface RequirementRepository extends Repository
{
    /**
     * @param array $userContextIds
     * @param array $requirements
     *
     * @return mixed
     */
    public function getRequirementAttachmentsByUserContexts($userContextIds, $requirements = []);


    /**
     * @param int        $program_id
     * @param bool|true  $include_program_level
     * @param bool|true  $include_site_level
     * @param bool|true  $include_shared_level
     * @param bool|false $just_ids
     * @param array      $filters
     *
     * @return mixed
     */
    public function getRequirements($program_id, $include_program_level = true, $include_site_level = true, $include_shared_level = true, $just_ids = false, $filters = []);


    /**
     * @param int|int[] $userContextIds
     *
     * @return mixed
     * @codeCoverageIgnore
     * @deprecated
     */
    public function updateCompliance($userContextIds);


    /**
     * @param int $programId
     * @param int $requirementId
     * @param $active
     *
     * @return mixed
     */
    public function toggleRequirement($programId, $requirementId, $active);


    /**
     * @param int      $requirement_id
     * @param int|null $program_id
     *
     * @return mixed
     */
    public function getUserContextIdsByRequirement($requirement_id, $program_id = null);


    /**
     * @param int        $requirementId
     * @param int        $programId
     * @param bool|false $networkOnly
     *
     * @return mixed
     */
    public function getAttachmentSummariesByRequirement($requirementId, $programId, $networkOnly = false);


    /**
     * Return an array keyed by requirement id, with a corresponding array of all UserContext IDs attached to that
     * requirement in a given program
     *
     * @param array $req_ids
     * @param       $program_id
     *
     * @return array
     */
    public function getAttachedUserContextIdsByRequirements(array $req_ids, $program_id);


    /**
     * @param int       $program_id
     * @param bool|true $include_program_level
     * @param bool|true $include_site_level
     * @param bool|true $include_shared_level
     *
     * @return mixed
     */
    public function getFormOptions($program_id, $include_program_level = true, $include_site_level = true, $include_shared_level = true);


    /**
     * @param int $userContextId
     *
     * @return bool
     */
    public function isProgramCompliant($userContextId);


    /**
     * @param int $userContextId
     * @param int $siteId
     * @param int $program_id
     *
     * @return bool
     */
    public function isProgramSiteCompliant($userContextId, $siteId, $program_id);


    /**
     * @param int $userContextId
     * @param int $siteId
     *
     * @return bool
     */
    public function isGlobalSiteCompliant($userContextId, $siteId);
}