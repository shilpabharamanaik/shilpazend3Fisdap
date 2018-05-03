<?php namespace Fisdap\Queries\Specifications;

use Fisdap\Queries\Specifications\QueryModifiers\LeftFetchJoin;
use Happyr\DoctrineSpecification\BaseSpecification;
use Happyr\DoctrineSpecification\Spec;


/**
 * Facilitates left joins for specified entity associations and nested associations
 *
 * @package Fisdap\Queries\Specifications
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class WithAssociations extends BaseSpecification
{
    use HandlesAssociations;


    /**
     * @param string[] $associations
     * @param string   $dqlAlias
     */
    public function __construct(array $associations, $dqlAlias = null)
    {
        $this->associations = $associations;

        $this->ensureParentAssociationsAreIncluded();

        parent::__construct($dqlAlias);
    }


    /**
     * @return \Happyr\DoctrineSpecification\Logic\AndX
     */
    public function getSpec()
    {
        $spec = Spec::andX();

        foreach ($this->associations as $association) {
            $field = $association;
            $newAlias = $association;
            $dqlAlias = null;

            $nested = explode('.', $association);

            if (count($nested) > 1) {
                // dqlAlias.field
                $field = $newAlias = $nested[1];
                $dqlAlias = $nested[0];
            }

            $spec->andX(new LeftFetchJoin($field, $newAlias, $dqlAlias));
        }

        return $spec;
    }
}