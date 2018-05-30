<?php namespace Fisdap\Api\Shifts\PreceptorSignoffs\Jobs;

use Doctrine\ORM\EntityManagerInterface;
use Fisdap\Api\Shifts\Jobs\UpdateShift;
use Fisdap\Data\Patient\PatientRepository;
use Fisdap\Data\Shift\ShiftLegacyRepository;
use Fisdap\Data\Verification\VerificationRepository;
use Fisdap\Entity\PreceptorSignoff;
use Illuminate\Contracts\Bus\Dispatcher as BusDispatcher;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;

/**
 * Class ModifySignoff
 * @package Fisdap\Api\Shifts\PreceptorSignoffs\Jobs
 * @author  Isaac White <isaac.white@ascendlearning.com>
 */
final class ModifySignoff extends PreceptorSignoffsAbstract
{
    /**
     * @param PatientRepository $patientRepository
     * @param ShiftLegacyRepository $shiftLegacyRepository
     * @param VerificationRepository $verificationRepository
     * @param EventDispatcher $eventDispatcher
     * @param BusDispatcher $busDispatcher
     * @param UpdateShift $updateShiftJob
     * @param EntityManagerInterface $em
     * @return PreceptorSignoff
     * @internal param ModifyPatient $modifyPatientJob
     */
    public function handle(
        PatientRepository $patientRepository,
        ShiftLegacyRepository $shiftLegacyRepository,
        VerificationRepository $verificationRepository,
        EventDispatcher $eventDispatcher,
        BusDispatcher $busDispatcher,
        UpdateShift $updateShiftJob,
        EntityManagerInterface $em
    ) {
        $this->em = $em;
        $signoff = $this->populateSignoff(
            $patientRepository,
            $shiftLegacyRepository,
            $verificationRepository,
            $busDispatcher,
            $updateShiftJob
        );
        
        $signoff->save();
        
        $eventDispatcher->fire($signoff);

        return $signoff;
    }
}
