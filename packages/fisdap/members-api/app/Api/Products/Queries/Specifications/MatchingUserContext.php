<?php namespace Fisdap\Api\Products\Queries\Specifications;

use Doctrine\ORM\QueryBuilder;
use Fisdap\Entity\SerialNumberLegacy;
use Fisdap\Entity\UserContext;
use Happyr\DoctrineSpecification\Query\QueryModifier;

/**
 * To be used with ProductRepository or ProductPackageRepository
 *
 * This will support retrieving a collection of products associated with a Fisdap\Entity\UserContext.
 *
 * @package Fisdap\Api\Products\Queries\Specifications
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class MatchingUserContext implements QueryModifier
{
    /**
     * @param QueryBuilder $qb
     * @param string       $dqlAlias
     */
    public function modify(QueryBuilder $qb, $dqlAlias)
    {
        $qb->innerJoin(
            SerialNumberLegacy::class,
            'serial_number',
            'WITH',
            sprintf('BIT_AND(serial_number.configuration, %s.configuration) > 0', $dqlAlias)
        )->leftJoin(UserContext::class, 'userContext', 'WITH', 'serial_number.userContext = userContext');
    }
}
