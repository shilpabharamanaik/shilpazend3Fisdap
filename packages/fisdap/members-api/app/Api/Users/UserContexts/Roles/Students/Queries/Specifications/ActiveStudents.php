<?php namespace Fisdap\Api\Users\UserContexts\Roles\Students\Queries\Specifications;

use Carbon\Carbon;
use Happyr\DoctrineSpecification\BaseSpecification;
use Happyr\DoctrineSpecification\Spec;


/**
 * Class ActiveStudents
 *
 * @package Fisdap\Api\Users\UserContexts\Roles\Students\Queries\Specifications
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class ActiveStudents extends BaseSpecification
{
    /**
     * @return \Happyr\DoctrineSpecification\Logic\AndX
     */
    public function getSpec()
    {
        /*
         * "An 'active' student should be determined by
         * StudentLegacy::$graduation_status.id = 1 AND UserContext::$end_date > (today - 6 months)"
         * ~mmayne
         */

        return Spec::andX(
            Spec::eq('id', 1, 'graduation_status'),
            Spec::gt('end_date', Carbon::parse('6 months ago'), 'userContexts')
        );
    }
}