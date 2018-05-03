<?php namespace Fisdap\Api\Shifts\Patients\Skills\Jobs\Airways;

use Fisdap\Api\Jobs\RequestHydrated;
use Fisdap\Api\Shifts\Patients\Skills\AbstractSkills;
use Fisdap\Data\Skill\AirwayRepository;
use Fisdap\Entity\Airway;

/**
 * A Job command for deleting an Airway.
 *
 * Class DeleteAirway
 * @package Fisdap\Api\Shifts\Patients\Skills\Jobs\Airways
 * @author  Nick Karnick <nkarnick@fisdap.net>
 *
 * @SWG\Definition(
 *     required={"airwayId"}
 * )
 */
final class DeleteAirway extends AbstractSkills implements RequestHydrated
{
    /**
     * @param AirwayRepository $airwayRepository
     * @return Airway|null
     */
    public function handle(AirwayRepository $airwayRepository)
    {
        $airway = $airwayRepository->find($this->id);
        if($airway) {
            $airwayRepository->destroy($airway);
        }

        return $airway;
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
