<?php namespace Fisdap\Api\Shifts\Patients\Skills\Jobs\OtherInterventions;

use Fisdap\Api\Jobs\RequestHydrated;
use Fisdap\Api\Shifts\Patients\Skills\AbstractSkills;
use Fisdap\Data\Skill\OtherInterventionRepository;
use Fisdap\Entity\OtherIntervention;

/**
 * A Job command for deleting an other intervention.
 *
 * Class DeleteOtherIntervention
 * @package Fisdap\Api\Shifts\Patients\Skills\Jobs\OtherInterventions
 * @author  Nick Karnick <nkarnick@fisdap.net>
 *
 * @SWG\Definition(
 *     required={"otherInterventionId"}
 * )
 */
final class DeleteOtherIntervention extends AbstractSkills implements RequestHydrated
{
    /**
     * @param OtherInterventionRepository $otherInterventionRepository
     * @return OtherIntervention|null
     */
    public function handle(OtherInterventionRepository $otherInterventionRepository)
    {
        $other = $otherInterventionRepository->find($this->id);
        if($other) {
            $otherInterventionRepository->destroy($other);
        }

        return $other;
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
