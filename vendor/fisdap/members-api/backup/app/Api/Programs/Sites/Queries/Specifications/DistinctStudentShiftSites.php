<?php namespace Fisdap\Api\Programs\Sites\Queries\Specifications;

use Fisdap\Queries\Specifications\Distinct;
use Happyr\DoctrineSpecification\BaseSpecification;
use Happyr\DoctrineSpecification\Spec;


/**
 * Class DistinctStudentShiftSites
 *
 * @package Fisdap\Api\Programs\Sites\Queries\Specifications
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class DistinctStudentShiftSites extends BaseSpecification
{
    /**
     * @var int
     */
    private $studentId;


    /**
     * @param int       $studentId
     * @param string    $dqlAlias
     */
    public function __construct($studentId, $dqlAlias = null)
    {
        $this->studentId = $studentId;

        parent::__construct($dqlAlias);
    }


    /**
     * @return \Happyr\DoctrineSpecification\Logic\AndX
     */
    public function getSpec()
    {
        return Spec::andX(
            new Distinct,
            new SitesMatchingShifts,
            Spec::eq('student', $this->studentId, 'shift')
        );
    }
}