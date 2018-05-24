<?php namespace Fisdap\Api\Products\Queries\Specifications;

use Fisdap\Queries\Specifications\Partial;
use Fisdap\Queries\Specifications\PartialOverrides;
use Happyr\DoctrineSpecification\BaseSpecification;
use Happyr\DoctrineSpecification\Spec;


/**
 * Class UserContextProducts
 *
 * @package Fisdap\Api\Products\Queries\Specifications
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class UserContextProducts extends BaseSpecification
{
    /**
     * @var int
     */
    private $userContextId;


    /**
     * @param int           $userContextId
     * @param string|null   $dqlAlias
     */
    public function __construct($userContextId, $dqlAlias = null)
    {
        $this->userContextId = $userContextId;

        parent::__construct($dqlAlias);
    }


    /**
     * @return \Happyr\DoctrineSpecification\Logic\AndX
     */
    public function getSpec()
    {
        return Spec::andX(
            new MatchingUserContext,
            new WithProgramProfession,
            Spec::eq('id', $this->userContextId, 'userContext'),
            new PartialOverrides([
                new Partial(['id', 'name'])
            ])
        );
    }
}