<?php namespace Fisdap\Api\Programs\Sites\Queries\Specifications;

use Fisdap\Api\Programs\Sites\Queries\SiteQueryParameters;
use Fisdap\Api\Queries\Specifications\ByProgram;
use Happyr\DoctrineSpecification\BaseSpecification;
use Happyr\DoctrineSpecification\Spec;


/**
 * Class ProgramSites
 *
 * @package Fisdap\Api\Programs\Sites\Queries\Specifications
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class ProgramSites extends BaseSpecification
{
    /**
     * @var SiteQueryParameters
     */
    private $queryParams;


    /**
     * @param \Fisdap\Api\Programs\Sites\Queries\SiteQueryParameters $queryParams
     * @param string               $dqlAlias
     */
    public function __construct(SiteQueryParameters $queryParams, $dqlAlias = null)
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
            new Sites($this->queryParams),
            new WithPrograms,
            new ByProgram($this->queryParams->getProgramIds()[0], 'programs'),
            Spec::orderBy('name', 'ASC')
        );
    }
}