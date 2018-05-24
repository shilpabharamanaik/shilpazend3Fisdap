<?php namespace Fisdap\Api\Shifts\Patients\Skills\Jobs\Ivs;

use Fisdap\Api\Jobs\RequestHydrated;
use Fisdap\Api\Shifts\Patients\Skills\AbstractSkills;
use Fisdap\Data\Skill\IvRepository;
use Fisdap\Entity\Iv;

/**
 * A Job command for deleting an iv.
 *
 * Class DeleteIv
 * @package Fisdap\Api\Shifts\Patients\Skills\Jobs\Ivs
 * @author  Nick Karnick <nkarnick@fisdap.net>
 *
 * @SWG\Definition(
 *     required={"ivId"}
 * )
 */
final class DeleteIv extends AbstractSkills implements RequestHydrated
{
    /**
     * @param IvRepository $ivRepository
     * @return Iv|null
     */
    public function handle(IvRepository $ivRepository)
    {
        $iv = $ivRepository->find($this->id);
        if($iv) {
            $ivRepository->destroy($iv);
        }

        return $iv;
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
