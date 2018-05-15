<?php namespace Fisdap\Api\Shifts\Queries\Specifications;

use Fisdap\Api\Queries\Specifications\ByType;
use Fisdap\Api\Shifts\Queries\ShiftQueryParameters;
use Fisdap\Api\Shifts\Queries\Specifications\States\Future;
use Fisdap\Api\Shifts\Queries\Specifications\States\Late;
use Fisdap\Api\Shifts\Queries\Specifications\States\Locked;
use Fisdap\Api\Shifts\Queries\Specifications\States\Past;
use Fisdap\Api\Shifts\Queries\Specifications\States\Unlocked;
use Fisdap\Queries\Specifications\CommonSpec;
use Happyr\DoctrineSpecification\Logic\AndX;
use Happyr\DoctrineSpecification\Spec;

/**
 * Class Shifts
 *
 * @package Fisdap\Api\Shifts\Queries\Specifications
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
final class Shifts extends CommonSpec
{
    /**
     * @var ShiftQueryParameters
     */
    private $queryParams;


    /**
     * @param ShiftQueryParameters $queryParams
     * @param string|null          $dqlAlias
     */
    public function __construct(ShiftQueryParameters $queryParams, $dqlAlias = null)
    {
        $this->queryParams = $queryParams;

        parent::__construct($dqlAlias);
    }


    /**
     * @return AndX
     */
    public function getSpec()
    {
        $spec = self::makeSpecWithAssociations($this->queryParams->getAssociations(), $this->queryParams->getAssociationIds());

        $spec->andX(new WithEssentialAssociations);

        $startingOnOrAfter = $this->queryParams->getStartingOnOrAfter();
        $startingOnOrBefore = $this->queryParams->getStartingOnOrBefore();
        $includeLocked = $this->queryParams->getIncludeLocked();

        if ($startingOnOrAfter !== null and $startingOnOrBefore !== null) {
            $spec->andX(new StartingBetween($startingOnOrAfter, $startingOnOrBefore));
        }

        // If includeLocked param is not provided, default to true.
        if (!$includeLocked) {
            $spec->andX(Spec::eq('locked', false));
        }

        $states = $this->queryParams->getStates();

        if ($states !== null) {
            $this->applyStateSpecs($states, $spec);
        }

        $type = $this->queryParams->getType();

        if ($type !== null) {
            $spec->andX(new ByType($type));
        }

        return $spec;
    }


    /**
     * @param array $states
     * @param AndX $spec
     */
    private function applyStateSpecs(array $states, AndX $spec)
    {
        foreach ($states as $state) {
            switch ($state) {
                case 'locked':
                    $spec->andX(new Locked);
                    break;
                case 'unlocked':
                    $spec->andX(new Unlocked);
                    break;
                case 'late':
                    $spec->andX(new Late);
                    break;
                case 'past':
                    $spec->andX(new Past);
                    break;
                case 'future':
                    $spec->andX(new Future);
                    break;
            }
        }
    }
}
