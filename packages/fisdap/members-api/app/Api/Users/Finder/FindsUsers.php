<?php namespace Fisdap\Api\Users\Finder;

use Fisdap\Api\Queries\Exceptions\ResourceNotFound;
use Fisdap\Api\ResourceFinder\FindsResources;
use Fisdap\Api\Users\Queries\InstructorStudentQueryParameters;
use Fisdap\Api\Users\Queries\ProgramStudentQueryParameters;
use Fisdap\Entity\User;

/**
 * Contract for retrieving various groups of users
 *
 * @package Fisdap\Api\Users
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
interface FindsUsers extends FindsResources
{
    /**
     * @param int $userContextId
     *
     * @return User
     * @throws \Fisdap\Api\Queries\Exceptions\ResourceNotFound
     */
    public function findByUserContextId($userContextId);


    /**
     * Get a list of all active students in a program
     *
     * @param ProgramStudentQueryParameters $queryParams
     *
     * @return array|null
     * @throws ResourceNotFound
     */
    public function findProgramStudents($queryParams);


    /**
     * Get a list of all active students for an instructor (based on active student groups [ClassSectionLegacy and related entities])
     *
     * @param InstructorStudentQueryParameters $queryParams
     *
     * @return array|null
     * @throws ResourceNotFound
     */
    public function findInstructorStudents($queryParams);


    /**
     * @param string $psgUserId
     *
     * @return User|null
     */
    public function findOneByPsgUserId($psgUserId);


    /**
     * @param string $ltiUserId
     *
     * @return User|null
     */
    public function findOneByLtiUserId($ltiUserId);


    /**
     * @param string $email
     *
     * @return User[]|array
     */
    public function findByEmail($email);
}
