<?php namespace Fisdap\Queries\Specifications;

use Doctrine\ORM\QueryBuilder;
use Happyr\DoctrineSpecification\Query\QueryModifier;

/**
 * Class PartialOverrides
 *
 * This should only be placed last in a chain of Specs, as it will
 * completely overwrite the DQL 'select' with the specified Partials
 *
 * @package Fisdap\Queries\Specifications
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class PartialOverrides implements QueryModifier
{
    /**
     * @var array
     */
    protected $partials;


    /**
     * @param array  $partials an array of Partial objects
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
        $qb->resetDQLPart('select');

        foreach ($this->partials as $partial) {
            if (! $partial instanceof Partial) {
                throw new \Exception('Got a ' . gettype($partial) . ' when a Partial object was expected');
            }

            if ($partial->newAlias !== null) {
                $qb->addSelect(sprintf('partial %s.{%s}', $partial->newAlias, implode(', ', $partial->fields)));
            } else {
                $qb->addSelect(sprintf('partial %s.{%s}', $partial->dqlAlias ?: $dqlAlias, implode(', ', $partial->fields)));
            }
        }
    }
}
