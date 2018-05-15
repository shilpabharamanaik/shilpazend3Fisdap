<?php namespace Fisdap\Api\Programs\Sites\Bases\Finder;

use Fisdap\Api\Queries\Exceptions\ResourceNotFound;
use Fisdap\Api\ResourceFinder\FindsResources;

/**
 * Contract for retrieving bases
 *
 * @package Fisdap\Api\Programs\Sites\Bases
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
interface FindsBases extends FindsResources
{
    /**
     * @param int $studentId
     *
     * @return array
     * @throws ResourceNotFound
     */
    public function findDistinctStudentShiftBases($studentId);
}
