<?php namespace Fisdap\Queries\Specifications;

use Fisdap\Queries\Specifications\QueryModifiers\PartialAssociations;
use Happyr\DoctrineSpecification\BaseSpecification;


/**
 * Facilitates left joins and partial associations with only their ids
 *
 * @package Fisdap\Queries\Specifications
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class WithAssociationIds extends BaseSpecification
{
    use HandlesAssociations;


    /**
     * @var array
     */
    protected $partials = [];


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
     * @return PartialAssociations
     */
    public function getSpec()
    {
        foreach ($this->associations as $association) {
            $newAlias = $association;
            $dqlAlias = null;

            $nested = explode('.', $association);

            if (count($nested) > 1) {
                // dqlAlias.field
                $newAlias = $nested[1];
                $dqlAlias = $nested[0];
            }

            $this->partials[] = new Partial(['id'], $newAlias, $dqlAlias);
        }

        return new PartialAssociations($this->partials);
    }
}