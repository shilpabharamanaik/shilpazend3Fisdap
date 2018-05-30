<?php namespace Fisdap\Api\Shifts\PreceptorSignoffs\Jobs;

use Fisdap\Api\Jobs\Job;
use Fisdap\Api\Jobs\RequestHydrated;
use Fisdap\Api\Queries\Exceptions\ResourceNotFound;
use Fisdap\Api\Shifts\Jobs\UpdateShift;
use Fisdap\Api\Shifts\Patients\Jobs\ModifyPatient;
use Fisdap\Data\Patient\PatientRepository;
use Fisdap\Data\Shift\ShiftLegacyRepository;
use Fisdap\Data\Verification\VerificationRepository;
use Fisdap\Entity\Patient;
use Fisdap\Entity\PreceptorSignoff;
use Fisdap\Entity\ShiftLegacy;
use Fisdap\Entity\Verification;
use Illuminate\Contracts\Bus\Dispatcher as BusDispatcher;
use Robo\Task\Bower\Update;
use Swagger\Annotations as SWG;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;

/**
 * An Abstract Job command for used for the
 * Updating Preceptor Signoffs associated with a shift and/or patient id.
 *
 * Class PreceptorSignoffsAbstract
 * @package Fisdap\Api\Shifts\PreceptorSignoffs\Jobs
 * @author  Isaac White <iwhite@fisdap.net>
 *
 * @SWG\Definition(
 *     definition="PreceptorSignoffs",
 *     required={ "shiftId" }
 * )
 */
abstract class PreceptorSignoffsAbstract extends Job implements RequestHydrated
{
    /**
     * @var string|null
     * @SWG\Property(type="string", example="ABC123")
     */
    public $uuid = null;

    /**
     * @var string
     * @SWG\Property(type="string", default="I am a summary")
     */
    public $summary = '';

    /**
     * @var string
     * @SWG\Property(type="string", default="I am the plan")
     */
    public $plan = '';


    /**
     * @var boolean|null
     * @SWG\Property(type="boolean", default=false)
     */
    public $locked = null;

    /**
     * @var \Fisdap\Api\Shifts\PreceptorSignoffs\Jobs\SetRatings[]
     * @SWG\Property(type="array", items=@SWG\Items(ref="#/definitions/Ratings"))
     */
    public $ratings = [];

    /**
     * @var \Fisdap\Api\Jobs\Verifications\SetVerification|null
     * @SWG\Property(type="string", items=@SWG\Items(ref="#/definitions/Verifications"), default={"type":1,"username": "iwhite","password": "12345"})
     */
    public $verification = null;

    /**
     * @var integer|null
     * @see ShiftLegacy
     */
    protected $shiftId = null;

    /**
     * @var integer|null
     * @see Patient
     */
    protected $patientId = null;

    /**
     * @param $shiftId
     */
    public function setShiftId($shiftId)
    {
        $this->shiftId = $shiftId;
    }

    /**
     * @param $patientId
     */
    public function setPatientId($patientId)
    {
        $this->patientId = $patientId;
    }

    /**
     * @param PatientRepository $patientRepository
     * @param ShiftLegacyRepository $shiftLegacyRepository
     * @param VerificationRepository $verificationRepository
     * @param BusDispatcher $busDispatcher
     * @param UpdateShift $updateShiftJob
     * @return PreceptorSignoff
     */
    public function populateSignoff(
        PatientRepository $patientRepository,
        ShiftLegacyRepository $shiftLegacyRepository,
        VerificationRepository $verificationRepository,
        BusDispatcher $busDispatcher,
        UpdateShift $updateShiftJob
    ) {
        if (!empty($this->patientId !== null)) {
            // This is a patient signoff

            $patient = $this->validResource($patientRepository, 'patientId');
            // If locked param is set, we need to propagate it down to the patient level
            // to see if this patient can be locked.
            if ($this->locked == true) {
                $shift = $shiftLegacyRepository->getOneById($patient->getShift()->id);
                if (!$patient->canBeLocked($shift->getType())) {
                    throw new MissingMandatoryParametersException(
                        "Cannot lock Patient. Required fields are missing: ".implode(", ", $patient->getMissingFields())
                    );
                } else {
                    $patient->setLocked($this->locked);
                    $patient->save();
                }
            }

            if ($this->verification !== null) {
                $this->verification->setVerification($patient->verification);
                $this->verification->setPatient($patient);
            }
            $verification = ($this->verification ? $busDispatcher->dispatch($this->verification) : null);


            if ($patient->run && $patient->run->id) {
                $signoff = $this->em->getRepository(PreceptorSignoff::class)->findOneBy(['run' => $patient->run->id]);
            } else {
                $signoff = $this->em->getRepository(PreceptorSignoff::class)->findOneBy(['patient' => $this->patientId]);
            }

            $signoff = $signoff ? $signoff : new PreceptorSignoff;
            $signoff->setPatient($patient);
            if ($verification) {
                $patient->setVerification($verification);
                $patient->run->set_verification($verification);
            } else {
                $patient->setVerification(null);
                $patient->run->set_verification(null);
            }
            // Shallow relation.
            $signoff->setVerification($patient->getVerification());
            $signoff->setRun($patient->run);
            $signoff->setStudent($patient->student);
        } else {
            // This is a shift signoff

            $shift = $this->validResource($shiftLegacyRepository, 'shiftId');
            // If locked param is set, we need to propagate it down to the shift level
            // to see if this shift can be locked.
            if ($this->locked == true) {
                $updateShiftJob->setId($shift->getId());
                $updateShiftJob->locked = $this->locked;
                $shift = $busDispatcher->dispatch($updateShiftJob);
            }

            if ($this->verification !== null) {
                $this->verification->setVerification($shift->verification);
            }
            $verification = $this->verification ? $busDispatcher->dispatch($this->verification) : null;
            $signoff = $this->em->getRepository(PreceptorSignoff::class)->findOneBy(['shift' => $this->shiftId]);
            $signoff = $signoff ? $signoff : new PreceptorSignoff;
            if ($verification) {
                $shift->setVerification($verification);
            } else {
                $shift->setVerification(null);

                $shiftVerifications = $verificationRepository->findBy(['shift' => $shift->getId()]);
                foreach ($shiftVerifications as $ver) {
                    $verificationRepository->destroy($ver);
                }
            }
            $signoff->setVerification($shift->getVerification());
            $shift->locked = $this->locked;
            if ($this->locked !== null) {
                $updateShift = new UpdateShift;
                $updateShift->locked = $this->locked;
                $updateShift->setId($this->shiftId);
                $busDispatcher->dispatch($updateShift);
            }
            $signoff->setShift($shift);
            $signoff->setStudent($shift->student);
        }

        foreach ($this->ratings as $rating) {
            $rating->setSignoff($signoff);
            $rated = $busDispatcher->dispatch($rating);
            $signoff->addRating($rated);
        }
        
        $signoff->setUUID($this->uuid);
        $signoff->setSummary($this->summary);
        $signoff->setPlan($this->plan);

        return $signoff;
    }
    
    public function setSummary($summary)
    {
        $this->summary = $summary;
    }

    public function setPlan($plan)
    {
        $this->plan = $plan;
    }
}
