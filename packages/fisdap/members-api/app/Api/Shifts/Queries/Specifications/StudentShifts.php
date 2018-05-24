<?php namespace Fisdap\Api\Shifts\Queries\Specifications;

use Fisdap\Api\Queries\Specifications\ByStudent;
use Fisdap\Api\Shifts\Queries\ShiftQueryParameters;
use Happyr\DoctrineSpecification\BaseSpecification;
use Happyr\DoctrineSpecification\Spec;


/**
 * Class StudentShifts
 *
 * @package Fisdap\Api\Shifts\Queries\Specifications
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
final class StudentShifts extends BaseSpecification
{
    /**
     * @var ShiftQueryParameters
     */
    private $queryParams;


    /**
     * @param ShiftQueryParameters $queryParams
     * @param string               $dqlAlias
     */
    public function __construct(ShiftQueryParameters $queryParams, $dqlAlias = null)
    {
        $this->queryParams = $queryParams;

        parent::__construct($dqlAlias);
    }


    /**
     * @return \Happyr\DoctrineSpecification\Logic\AndX
     */
    public function getSpec()
    {
        return Spec::andX(
            new Shifts($this->queryParams),
            new ByStudent($this->queryParams->getStudentIds()[0]),
            ($this->queryParams->getDateFrom() ? Spec::gt('updated', $this->queryParams->getDateFrom()) : null)
        );
    }
}
