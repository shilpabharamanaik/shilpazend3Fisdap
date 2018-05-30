<?php
/**
 * Created by PhpStorm.
 * User: jmortenson
 * Date: 6/18/14
 * Time: 6:24 PM
 */

namespace Fisdap\Service;

/**
 * Provides transformation and evaluation methods for working with subject (patient) data
 *
 * Interface SubjectService
 * @package Fisdap\Service
 */
interface SubjectService
{
    public function makeSubjectIdsArray(\Doctrine\ORM\EntityRepository $repository, $subjectIds);
}
