<?php namespace Fisdap\Api\Shifts\Queries\Specifications\States;

use Happyr\DoctrineSpecification\BaseSpecification;
use Happyr\DoctrineSpecification\Spec;


/**
 * Class Late
 *
 * @package Fisdap\Api\Shifts\Queries\Specifications\States
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class Late extends BaseSpecification
{
    /**
     * @return \Happyr\DoctrineSpecification\Filter\Comparison
     */
    public function getSpec()
    {
        return Spec::eq('late', true);
    }
}