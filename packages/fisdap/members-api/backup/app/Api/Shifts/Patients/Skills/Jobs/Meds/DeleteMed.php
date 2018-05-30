<?php namespace Fisdap\Api\Shifts\Patients\Skills\Jobs\Meds;

use Fisdap\Api\Jobs\RequestHydrated;
use Fisdap\Api\Shifts\Patients\Skills\AbstractSkills;
use Fisdap\Data\Skill\MedRepository;
use Fisdap\Entity\Med;

/**
 * A Job command for deleting an medication.
 *
 * Class DeleteMed
 * @package Fisdap\Api\Shifts\Patients\Skills\Jobs\Meds
 * @author  Nick Karnick <nkarnick@fisdap.net>
 *
 * @SWG\Definition(
 *     required={"medicationId"}
 * )
 */
final class DeleteMed extends AbstractSkills implements RequestHydrated
{
    /**
     * @param MedRepository $medRepository
     * @return Med|null
     */
    public function handle(MedRepository $medRepository)
    {
        $med = $medRepository->find($this->id);
        if ($med) {
            $medRepository->destroy($med);
        }

        return $med;
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
