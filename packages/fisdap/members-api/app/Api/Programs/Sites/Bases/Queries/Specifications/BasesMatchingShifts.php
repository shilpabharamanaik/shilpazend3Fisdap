<?php namespace Fisdap\Api\Programs\Sites\Bases\Queries\Specifications;

use Doctrine\ORM\QueryBuilder;
use Fisdap\Entity\ShiftLegacy;
use Happyr\DoctrineSpecification\Query\QueryModifier;

/**
 * Class BasesMatchingShifts
 *
 * @package Fisdap\Api\Programs\Sites\Bases\Queries\Specifications
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class BasesMatchingShifts implements QueryModifier
{
    /**
     * @param QueryBuilder $qb
     * @param string       $dqlAlias
     */
    public function modify(QueryBuilder $qb, $dqlAlias)
    {
        $qb->innerJoin(ShiftLegacy::class, 'shift', 'WITH', sprintf('shift.base = %s', $dqlAlias));
    }
}
