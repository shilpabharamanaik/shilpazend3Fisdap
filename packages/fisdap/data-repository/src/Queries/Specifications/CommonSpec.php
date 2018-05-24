<?php namespace Fisdap\Queries\Specifications;

use Happyr\DoctrineSpecification\BaseSpecification;
use Happyr\DoctrineSpecification\Spec;


/**
 * Class CommonSpec
 *
 * @package Fisdap\Queries\Specifications
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
abstract class CommonSpec extends BaseSpecification
{
    /**
     * @param string[]|null $associations
     * @param int[]|null    $associationIds
     *
     * @return \Happyr\DoctrineSpecification\Logic\AndX
     */
    public static function makeSpecWithAssociations(array $associations = null, array $associationIds = null)
    {
        $spec = Spec::andX();

        if ($associations !== null) {
            $spec->andX(new WithAssociations($associations));
        }

        if ($associationIds !== null) {
            $spec->andX(new WithAssociationIds($associationIds));
        }

        return $spec;
    }
}