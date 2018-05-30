<?php namespace Fisdap\Api\Users\Queries\Specifications;

use Fisdap\Queries\Specifications\QueryModifiers\LeftFetchJoin;
use Happyr\DoctrineSpecification\BaseSpecification;
use Happyr\DoctrineSpecification\Spec;

/**
 * Class UsersWithContextsAndProgram
 *
 * @package Fisdap\Api\Users\Queries\Specifications
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class UsersWithContextsAndProgram extends BaseSpecification
{
    /**
     * @return \Happyr\DoctrineSpecification\Logic\AndX
     */
    public function getSpec()
    {
        return Spec::andX(
            new LeftFetchJoin('staff', 'staff'),
            new LeftFetchJoin('current_user_context', 'current_user_context'),
            new LeftFetchJoin('userContexts', 'userContexts'),
            new LeftFetchJoin('certification_level', 'certification_level', 'userContexts'),
            new LeftFetchJoin('role', 'role', 'userContexts'),
            new LeftFetchJoin('program', 'program', 'userContexts'),
            new LeftFetchJoin('instructorRoleData', 'instructor', 'userContexts'),
            new LeftFetchJoin('studentRoleData', 'student', 'userContexts'),
            new LeftFetchJoin('graduation_status', 'graduation_status', 'student')
        );
    }
}
