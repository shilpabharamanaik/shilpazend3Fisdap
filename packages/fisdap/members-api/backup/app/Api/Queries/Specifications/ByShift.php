<?php namespace Fisdap\Api\Queries\Specifications;

use Happyr\DoctrineSpecification\BaseSpecification;
use Happyr\DoctrineSpecification\Spec;


/**
 * Class ByShift
 *
 * @package Fisdap\Api\Queries\Specifications
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class ByShift extends BaseSpecification
{
    /**
     * @var int
     */
    private $shiftId;


    /**
     * @param int $shiftId
     * @param string $dqlAlias
     */
    public function __construct($shiftId, $dqlAlias = null)
    {
        $this->shiftId = $shiftId;

        parent::__construct($dqlAlias);
    }


    /**
     * @return \Happyr\DoctrineSpecification\Filter\Comparison
     */
    public function getSpec()
    {
        return Spec::eq('shift', $this->shiftId);
    }
}