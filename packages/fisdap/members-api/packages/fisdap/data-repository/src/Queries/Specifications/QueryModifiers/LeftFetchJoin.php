<?php namespace Fisdap\Queries\Specifications\QueryModifiers;

use Doctrine\ORM\QueryBuilder;
use Happyr\DoctrineSpecification\Query\QueryModifier;

/**
 * Class LeftFetchJoin
 *
 * @package Fisdap\Queries\Specifications\QueryModifiers
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class LeftFetchJoin implements QueryModifier
{
    /**
     * @var string field
     */
    private $field;

    /**
     * @var string alias
     */
    private $newAlias;

    /**
     * @var string dqlAlias
     */
    private $dqlAlias;


    /**
     * @param string $field
     * @param string $newAlias
     * @param string $dqlAlias
     */
    public function __construct($field, $newAlias, $dqlAlias = null)
    {
        $this->field = $field;
        $this->newAlias = $newAlias;
        $this->dqlAlias = $dqlAlias;
    }


    /**
     * @param QueryBuilder $qb
     * @param string       $dqlAlias
     */
    public function modify(QueryBuilder $qb, $dqlAlias)
    {
        if ($this->dqlAlias !== null) {
            $dqlAlias = $this->dqlAlias;
        }

        $qb->addSelect($this->newAlias)
            ->leftJoin(sprintf('%s.%s', $dqlAlias, $this->field), $this->newAlias);
    }
}
