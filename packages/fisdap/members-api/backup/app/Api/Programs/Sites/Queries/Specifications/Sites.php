<?php namespace Fisdap\Api\Programs\Sites\Queries\Specifications;

use Fisdap\Api\Programs\Sites\Queries\SiteQueryParameters;
use Fisdap\Queries\Specifications\CommonSpec;

/**
 * Class Sites
 *
 * @package Fisdap\Api\Programs\Sites\Queries\Specifications
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class Sites extends CommonSpec
{
    /**
     * @var \Fisdap\Api\Programs\Sites\Queries\SiteQueryParameters
     */
    private $queryParams;


    /**
     * @param \Fisdap\Api\Programs\Sites\Queries\SiteQueryParameters $queryParams
     * @param string|null          $dqlAlias
     */
    public function __construct(SiteQueryParameters $queryParams, $dqlAlias = null)
    {
        parent::__construct($dqlAlias);

        $this->queryParams = $queryParams;
    }


    /**
     * @return \Happyr\DoctrineSpecification\Logic\AndX
     */
    public function getSpec()
    {
        return self::makeSpecWithAssociations(
            $this->queryParams->getAssociations(),
            $this->queryParams->getAssociationIds()
        );
    }
}
