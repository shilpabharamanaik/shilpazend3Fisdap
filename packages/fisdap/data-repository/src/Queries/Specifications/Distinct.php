<?php namespace Fisdap\Queries\Specifications;

use Doctrine\ORM\QueryBuilder;
use Happyr\DoctrineSpecification\Query\QueryModifier;


/**
 * Class Distinct
 *
 * @package Fisdap\Queries\Specifications
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class Distinct implements QueryModifier
{
    /**
     * @param QueryBuilder $qb
     * @param string       $dqlAlias
     */
    public function modify(QueryBuilder $qb, $dqlAlias)
    {
        $qb->distinct();
    }
}