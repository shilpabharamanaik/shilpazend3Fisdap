<?php namespace Fisdap\Api\Shifts\Queries\Specifications\States;

use Carbon\Carbon;
use Happyr\DoctrineSpecification\BaseSpecification;
use Happyr\DoctrineSpecification\Spec;

/**
 * Class Past
 *
 * @package Fisdap\Api\Shifts\Queries\Specifications\States
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class Past extends BaseSpecification
{
    /**
     * @return \Happyr\DoctrineSpecification\Filter\Comparison
     */
    public function getSpec()
    {
        return Spec::lte('start_datetime', Carbon::parse('today'));
    }
}
