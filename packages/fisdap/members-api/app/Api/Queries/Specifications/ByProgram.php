<?php namespace Fisdap\Api\Queries\Specifications;

use Happyr\DoctrineSpecification\BaseSpecification;
use Happyr\DoctrineSpecification\Spec;

/**
 * Class ByProgram
 *
 * @package Fisdap\Api\Queries\Specifications
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class ByProgram extends BaseSpecification
{
    /**
     * @var int
     */
    private $programId;


    /**
     * @param int $programId
     * @param string $dqlAlias
     */
    public function __construct($programId, $dqlAlias = null)
    {
        $this->programId = $programId;

        parent::__construct($dqlAlias);
    }


    /**
     * @return \Happyr\DoctrineSpecification\Filter\Comparison
     */
    public function getSpec()
    {
        return Spec::eq('program', $this->programId);
    }
}
