<?php namespace Fisdap\Api\Shifts\Queries\Specifications;

use Fisdap\Queries\Specifications\Partial;
use Fisdap\Queries\Specifications\QueryModifiers\PartialAssociations;
use Fisdap\Queries\Specifications\WithAssociations;
use Happyr\DoctrineSpecification\BaseSpecification;
use Happyr\DoctrineSpecification\Spec;


/**
 * Joins signoff, verification, attendance, creator, and slot assignment to shifts
 *
 * @package Fisdap\Api\Shifts\Queries\Specifications
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class WithEssentialAssociations extends BaseSpecification
{
    private $essentialAssociations = [
        'attendence', // todo - fix spelling in entity/table
        'creator',
        'signoff',
        'slot_assignment',
        'verification'
    ];


    /**
     * @return \Happyr\DoctrineSpecification\Logic\AndX
     */
    public function getSpec()
    {
        return Spec::andX(
            new WithAssociations($this->essentialAssociations),
            new PartialAssociations([
                new Partial(['id', 'user_context'], 'student'),
                new Partial(['id', 'program'], 'user_context', 'student')
            ])
        );
    }
}