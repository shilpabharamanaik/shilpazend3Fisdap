<?php namespace Fisdap\Api\Users\UserContexts\Roles\Students\Queries\Specifications;

use Doctrine\ORM\QueryBuilder;
use Happyr\DoctrineSpecification\Query\QueryModifier;


/**
 * Modifies query to select partial rootEntity->student->program->id
 *
 * @package Fisdap\Api\Users\UserContexts\Roles\Students\Queries\Specifications
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class AssociatedStudentProgramId implements QueryModifier
{
    /**
     * @inheritdoc
     */
    public function modify(QueryBuilder $qb, $dqlAlias)
    {
        $qb->resetDQLPart('select');

        $qb->select(sprintf('partial %s.{%s}', $dqlAlias, 'id'))
            ->addSelect(sprintf('partial %s.{%s}', 'program', 'id'))
            ->addSelect(sprintf('partial %s.{%s}', 'student', 'id'))
            ->leftJoin(sprintf('%s.%s', $dqlAlias, 'student'), 'student')
            ->leftJoin(sprintf('%s.%s', 'student', 'program'), 'program');
    }
}