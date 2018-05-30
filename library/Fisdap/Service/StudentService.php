<?php
/**
 * Created by PhpStorm.
 * User: jmortenson
 * Date: 6/18/14
 * Time: 6:24 PM
 */

namespace Fisdap\Service;

use Fisdap\Data\User\UserRepository;
use Fisdap\Entity\User;

/**
 * Provides transformation and evaluation methods for working with student data
 *
 * Interface StudentService
 * @package Fisdap\Service
 */
interface StudentService
{
    public function shuffleAndAnonymizeStudents(User $user, array $students);

    /**
     * Retrieve student data for a list of student_ids and transform into literal or anonymized list of student names
     * to return a clean array of student names (anonymized per request) keyed with student id
     *
     * @param User $user
     * @param UserRepository $repository
     * @param array $student_ids
     * @param bool $anon
     *
     * @return array
     */
    public function transformStudentIds(User $user, UserRepository $repository, array $studentIds, $makeAnonymous = false);
}
