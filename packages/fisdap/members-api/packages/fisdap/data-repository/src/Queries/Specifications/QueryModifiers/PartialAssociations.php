<?php namespace Fisdap\Queries\Specifications\QueryModifiers;

use Doctrine\ORM\QueryBuilder;
use Fisdap\Queries\Specifications\Partial;
use Happyr\DoctrineSpecification\Query\QueryModifier;

/**
 * Class PartialAssociations
 *
 * @package Fisdap\Queries\Specifications\QueryModifiers
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class PartialAssociations implements QueryModifier
{
    /**
     * @var array
     */
    private $partials;


    /**
     * @param array  $partials
     */
    public function __construct(array $partials)
    {
        $this->partials = $partials;
    }


    /**
     * @param QueryBuilder $qb
     * @param string       $dqlAlias
     *
     * @throws \Exception
     */
    public function modify(QueryBuilder $qb, $dqlAlias)
    {
        foreach ($this->partials as $partial) {
            if (! $partial instanceof Partial) {
                throw new \Exception('Got a ' . gettype($partial) . ' when a Partial object was expected');
            }

            $qb->addSelect(sprintf('partial %s.{%s}', $partial->newAlias, implode(', ', $partial->fields)))
                ->leftJoin(sprintf('%s.%s', $partial->dqlAlias ?: $dqlAlias, $partial->newAlias), $partial->newAlias);
        }
    }
}
