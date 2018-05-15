<?php namespace Fisdap\Api\Shifts\Patients\Skills\Jobs\CardiacInterventions;

use Fisdap\Api\Jobs\RequestHydrated;
use Fisdap\Api\Shifts\Patients\Skills\AbstractSkills;
use Fisdap\Data\Skill\AirwayRepository;
use Fisdap\Data\Skill\CardiacInterventionRepository;
use Fisdap\Entity\Airway;
use Fisdap\Entity\CardiacIntervention;

/**
 * A Job command for deleting a cardiac intervention.
 *
 * Class DeleteCardiacInterventions
 * @package Fisdap\Api\Shifts\Patients\Skills\Jobs\CardiacInterventions
 * @author  Nick Karnick <nkarnick@fisdap.net>
 *
 * @SWG\Definition(
 *     required={"cardiacInterventionId"}
 * )
 */
final class DeleteCardiacIntervention extends AbstractSkills implements RequestHydrated
{
    /**
     * @param CardiacInterventionRepository $cardiacInterventionRepository
     * @return CardiacIntervention|null
     *
     */
    public function handle(CardiacInterventionRepository $cardiacInterventionRepository)
    {
        $cardiac = $cardiacInterventionRepository->find($this->id);
        if ($cardiac) {
            $cardiacInterventionRepository->destroy($cardiac);
        }

        return $cardiac;
    }

    /**
     * Ignore rules, we are destroying the object.
     * @return null
     */
    public function rules()
    {
        return null;
    }
}
