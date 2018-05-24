<?php namespace Fisdap\Api\Queries\Specifications;

use Happyr\DoctrineSpecification\BaseSpecification;
use Happyr\DoctrineSpecification\Spec;


/**
 * Class ById
 *
 * @package Fisdap\Api\Queries\Specifications
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class ById extends BaseSpecification
{
    /**
     * @var int
     */
    private $id;


    /**
     * @param int $id
     * @param string $dqlAlias
     */
    public function __construct($id, $dqlAlias = null)
    {
        $this->id = $id;

        parent::__construct($dqlAlias);
    }


    /**
     * @return \Happyr\DoctrineSpecification\Filter\Comparison
     */
    public function getSpec()
    {
        return Spec::eq('id', $this->id);
    }
}