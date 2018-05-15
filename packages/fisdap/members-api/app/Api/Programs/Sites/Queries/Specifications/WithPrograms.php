<?php namespace Fisdap\Api\Programs\Sites\Queries\Specifications;

use Doctrine\ORM\QueryBuilder;
use Happyr\DoctrineSpecification\Query\QueryModifier;

/**
 * Class WithPrograms
 *
 * @package Fisdap\Api\Programs\Sites\Queries\Specifications
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class WithPrograms implements QueryModifier
{
    /**
     * @inheritdoc
     */
    public function modify(QueryBuilder $qb, $dqlAlias)
    {
        $qb->leftJoin(sprintf('%s.%s', $dqlAlias, 'program_site_associations'), 'programs');
    }
}
