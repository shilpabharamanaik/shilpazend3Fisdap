<?php namespace Fisdap\Api\Shifts\Queries\Specifications;

use Fisdap\Api\Queries\Specifications\ByProgram;
use Fisdap\Api\Shifts\Queries\ShiftQueryParameters;
use Happyr\DoctrineSpecification\BaseSpecification;
use Happyr\DoctrineSpecification\Spec;

/**
 * Class ProgramShifts
 *
 * @package Fisdap\Api\Shifts\Queries\Specificiations
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
final class ProgramShifts extends BaseSpecification
{
    /**
     * @var ShiftQueryParameters
     */
    private $queryParams;


    /**
     * @param \Fisdap\Api\Shifts\Queries\ShiftQueryParameters $queryParams
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
            new ByProgram($this->queryParams->getProgramIds()[0], 'user_context'),
            ($this->queryParams->getDateFrom() ? Spec::gt('updated', $this->queryParams->getDateFrom()) : null)
        );
    }
}
