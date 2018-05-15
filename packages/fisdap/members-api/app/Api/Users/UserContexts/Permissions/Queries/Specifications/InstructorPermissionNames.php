<?php namespace Fisdap\Api\Users\UserContexts\Permissions\Queries\Specifications;

use Fisdap\Queries\Specifications\Partial;
use Fisdap\Queries\Specifications\PartialOverrides;
use Happyr\DoctrineSpecification\BaseSpecification;
use Happyr\DoctrineSpecification\Spec;

/**
 * Class InstructorPermissionNames
 *
 * @package Fisdap\Api\Users\UserContexts\Permissions\Queries\Specifications
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class InstructorPermissionNames extends BaseSpecification
{
    /**
     * @var int
     */
    private $instructorId;


    /**
     * @param int         $instructorId
     * @param string|null $dqlAlias
     */
    public function __construct($instructorId, $dqlAlias = null)
    {
        $this->instructorId = $instructorId;

        parent::__construct($dqlAlias);
    }


    /**
     * @return \Happyr\DoctrineSpecification\Logic\AndX
     */
    public function getSpec()
    {
        return Spec::andX(
            new ByInstructor,
            Spec::eq('id', $this->instructorId, 'instructor'),
            new PartialOverrides([
                new Partial(['id', 'name'])
            ])
        );
    }
}
