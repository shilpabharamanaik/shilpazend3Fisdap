<?php namespace Fisdap\Api\Programs\Sites\Queries\Specifications;

use Doctrine\ORM\QueryBuilder;
use Happyr\DoctrineSpecification\Query\QueryModifier;

/**
 * Class SitesMatchingShifts
 *
 * @package Fisdap\Api\Programs\Sites\Queries\Specifications
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class SitesMatchingShifts implements QueryModifier
{
    /**
     * @param QueryBuilder $qb
     * @param string       $dqlAlias
     */
    public function modify(QueryBuilder $qb, $dqlAlias)
    {
        $qb->innerJoin(sprintf('%s.shift', $dqlAlias), 'shift');
    }
}
