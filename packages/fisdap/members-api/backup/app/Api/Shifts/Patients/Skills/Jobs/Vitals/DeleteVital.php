<?php namespace Fisdap\Api\Shifts\Patients\Skills\Jobs\Vitals;

use Fisdap\Api\Jobs\RequestHydrated;
use Fisdap\Api\Shifts\Patients\Skills\AbstractSkills;
use Fisdap\Data\Skill\VitalRepository;
use Fisdap\Entity\Vital;

/**
 * A Job command for deleting an vital.
 *
 * Class DeleteVital
 * @package Fisdap\Api\Shifts\Patients\Skills\Jobs\Vitals
 * @author  Nick Karnick <nkarnick@fisdap.net>
 *
 * @SWG\Definition(
 *     required={"vitalId"}
 * )
 */
final class DeleteVital extends AbstractSkills implements RequestHydrated
{
    /**
     * @param VitalRepository $vitalRepository
     * @return Vital|null
     */
    public function handle(VitalRepository $vitalRepository)
    {
        $vital = $vitalRepository->find($this->id);
        if ($vital) {
            $vitalRepository->destroy($vital);
        }

        return $vital;
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
