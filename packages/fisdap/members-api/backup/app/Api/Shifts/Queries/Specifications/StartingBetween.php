<?php namespace Fisdap\Api\Shifts\Queries\Specifications;

use Happyr\DoctrineSpecification\BaseSpecification;
use Happyr\DoctrineSpecification\Spec;

/**
 * Class StartingBetween
 *
 * @package Fisdap\Api\Shifts\Queries\Specifications
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class StartingBetween extends BaseSpecification
{
    /**
     * @var \DateTime
     */
    private $onOrAfter;

    /**
     * @var \DateTime
     */
    private $onOrBefore;


    /**
     * @param \DateTime   $onOrAfter
     * @param \DateTime   $onOrBefore
     * @param string|null $dqlAlias
     */
    public function __construct(\DateTime $onOrAfter, \DateTime $onOrBefore, $dqlAlias = null)
    {
        $this->onOrAfter = $onOrAfter;
        $this->onOrBefore = $onOrBefore;

        parent::__construct($dqlAlias);
    }


    /**
     * @return \Happyr\DoctrineSpecification\Logic\AndX
     */
    public function getSpec()
    {
        return Spec::andX(
            Spec::gte('start_datetime', $this->onOrAfter),
            Spec::lte('start_datetime', $this->onOrBefore)
        );
    }
}
