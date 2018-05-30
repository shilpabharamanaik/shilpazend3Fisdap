<?php namespace Fisdap\Api\Queries\Specifications;

use Happyr\DoctrineSpecification\BaseSpecification;
use Happyr\DoctrineSpecification\Spec;

/**
 * Class ByType
 *
 * @package Fisdap\Api\Queries\Specifications
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class ByType extends BaseSpecification
{
    /**
     * @var string
     */
    private $type;


    /**
     * @param string $type
     * @param string $dqlAlias
     */
    public function __construct($type, $dqlAlias = null)
    {
        $this->type = $type;

        parent::__construct($dqlAlias);
    }


    /**
     * @return \Happyr\DoctrineSpecification\Filter\Comparison
     */
    public function getSpec()
    {
        return Spec::eq('type', $this->type);
    }
}
