<?php namespace Fisdap\Api\Users\UserContexts\Permissions\Queries\Specifications;

use Doctrine\ORM\QueryBuilder;
use Fisdap\Entity\InstructorLegacy;
use Happyr\DoctrineSpecification\Query\QueryModifier;

/**
 * Class ByInstructor
 *
 * @package Fisdap\Api\Users\UserContexts\Permissions\Queries\Specifications
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class ByInstructor implements QueryModifier
{
    /**
     * @param QueryBuilder $qb
     * @param string       $dqlAlias
     */
    public function modify(QueryBuilder $qb, $dqlAlias)
    {
        $qb->innerJoin(
            InstructorLegacy::class,
            'instructor',
            'WITH',
            sprintf('BIT_AND(instructor.permissions, %s.bit_value) > 0', $dqlAlias)
        );
    }
}
