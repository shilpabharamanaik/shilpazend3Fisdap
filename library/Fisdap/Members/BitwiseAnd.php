<?php namespace Fisdap\Members;

use Doctrine\ORM\QueryBuilder;
use Happyr\DoctrineSpecification\Query\QueryModifier;


/**
 * Class BitwiseAnd
 *
 * @package Fisdap\Members
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @todo move this to the happyr/doctrine-specification library
 */
class BitwiseAnd implements QueryModifier
{
    /**
     * @var string
     */
    private $field;
    
    /**
     * @var mixed
     */
    private $value;
    
    /**
     * @var string
     */
    private $dqlAlias;

    
    /**
     * BitwiseAnd constructor.
     *
     * @param string $field
     * @param mixed $value
     * @param string $dqlAlias
     */
    public function __construct($field, $value, $dqlAlias)
    {
        $this->field = $field;
        $this->value = $value;
        $this->dqlAlias = $dqlAlias;
    }

    
    /**
     * @param QueryBuilder $qb
     * @param string       $dqlAlias
     */
    public function modify(QueryBuilder $qb, $dqlAlias)
    {
        $qb->andWhere(sprintf('BIT_AND(%s.%s, %s) > 0', $this->dqlAlias, $this->field, $this->value));
    }
}