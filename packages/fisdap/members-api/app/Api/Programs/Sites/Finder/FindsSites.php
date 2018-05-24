<?php namespace Fisdap\Api\Programs\Sites\Finder;

use Fisdap\Api\Programs\Sites\Queries\SiteQueryParameters;
use Fisdap\Api\Queries\Exceptions\ResourceNotFound;
use Fisdap\Api\ResourceFinder\FindsResources;


/**
 * Contract for retrieving sites
 *
 * @package Fisdap\Api\Programs\Sites
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
interface FindsSites extends FindsResources
{
    /**
     * @param SiteQueryParameters $queryParams
     *
     * @return array
     * @throws ResourceNotFound
     */
    public function findProgramSites(SiteQueryParameters $queryParams);


    /**
     * @param int $studentId
     *
     * @return array
     * @throws ResourceNotFound
     */
    public function findDistinctStudentShiftSites($studentId);
}