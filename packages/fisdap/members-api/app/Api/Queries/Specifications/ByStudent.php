<?php namespace Fisdap\Api\Queries\Specifications;

use Happyr\DoctrineSpecification\BaseSpecification;
use Happyr\DoctrineSpecification\Spec;

/**
 * Class ByStudent
 *
 * @package Fisdap\Api\Queries\Specifications
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class ByStudent extends BaseSpecification
{
    /**
     * @var int
     */
    private $studentId;


    /**
     * @param int $studentId
     * @param string $dqlAlias
     */
    public function __construct($studentId, $dqlAlias = null)
    {
        $this->studentId = $studentId;

        parent::__construct($dqlAlias);
    }


    /**
     * @return \Happyr\DoctrineSpecification\Filter\Comparison
     */
    public function getSpec()
    {
        return Spec::eq('student', $this->studentId);
    }
}
