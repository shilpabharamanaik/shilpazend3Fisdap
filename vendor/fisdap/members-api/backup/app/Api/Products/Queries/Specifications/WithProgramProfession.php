<?php namespace Fisdap\Api\Products\Queries\Specifications;

use Doctrine\ORM\QueryBuilder;
use Fisdap\Entity\Profession;
use Fisdap\Entity\ProgramLegacy;
use Happyr\DoctrineSpecification\Query\QueryModifier;

/**
 * To be used with Fisdap\Data\Product\ProductRepository
 *
 * Ensures UserContext Program Profession matches Product Profession
 *
 * @package Fisdap\Api\Products\Queries\Specifications
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class WithProgramProfession implements QueryModifier
{
    /**
     * @param QueryBuilder $qb
     * @param string       $dqlAlias
     */
    public function modify(QueryBuilder $qb, $dqlAlias)
    {
        $qb->leftJoin(ProgramLegacy::class, 'program', 'WITH', 'userContext.program = program')
            ->innerJoin(
                Profession::class,
                'profession',
                'WITH',
                sprintf('program.profession = profession AND %s.profession = profession', $dqlAlias)
            );
    }
}
