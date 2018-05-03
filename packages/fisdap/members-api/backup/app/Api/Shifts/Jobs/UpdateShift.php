<?php namespace Fisdap\Api\Shifts\Jobs;

use Fisdap\Api\Jobs\Job;
use Fisdap\Api\Jobs\RequestHydrated;
use Fisdap\Api\Queries\Exceptions\ResourceNotFound;
use Fisdap\Api\Shifts\Events\ShiftWasUpdated;
use Fisdap\Data\Base\BaseLegacyRepository;
use Fisdap\Data\Patient\PatientRepository;
use Fisdap\Data\Shift\ShiftLegacyRepository;
use Fisdap\Data\ShiftAttendance\ShiftAttendanceRepository;
use Fisdap\Data\Site\SiteLegacyRepository;
use Fisdap\Entity\BaseLegacy;
use Fisdap\Entity\ProgramLegacy;
use Fisdap\Entity\ShiftAttendence;
use Fisdap\Entity\ShiftHistory;
use Fisdap\Entity\ShiftLegacy;
use Fisdap\Entity\SiteLegacy;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use Swagger\Annotations as SWG;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;

/**
 * A Job (Command) for updating a shift (ShiftLegacy Entity)
 *
 * @package Fisdap\Api\Shifts\Jobs
 * @author  Nick Karnick <nkarnick@fisdap.net>
 *
 * @SWG\Definition(
 *     definition="Shift"
 * )
 */
final class UpdateShift extends Job implements RequestHydrated
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     * @SWG\Property(example=3663)
     */
    public $siteId;

    /**
     * @var int
     * @SWG\Property(example=16704)
     */
    public $baseId;

    /**
     * @var string
     * @SWG\Property(example="field")
     */
    public $type;

    /**
     * @var boolean
     * @SWG\Property(example=false)
     */
    public $locked;

    /**
     * @var \DateTime
     * @SWG\Property(example="2016-01-01 18:00:00")
     */
    public $startDatetime;

    /**
     * @var int
     * @SWG\Property(example=1)
     */
    public $attendanceId;

    /**
     * @var float
     * @SWG\Property(example=8)
     */
    public $hours;

    /**
     * @var string|null
     * @SWG\Property(type="string", example="These are attendance comments")
     */
    public $attendanceComments = null;

    /**
     * @param int $shiftId
     */
    public function setId($shiftId)
    {
        $this->id = $shiftId;
    }

    /**
     * @param ShiftLegacyRepository $shiftLegacyRepository
     * @param PatientRepository $patientRepository
     * @param SiteLegacyRepository $siteLegacyRepository
     * @param BaseLegacyRepository $baseLegacyRepository
     * @param ShiftAttendanceRepository $shiftAttendanceRepository
     * @param AuthManager $auth
     * @param EventDispatcher $eventDispatcher
     * @return ShiftLegacy
     */
    public function handle(
        ShiftLegacyRepository $shiftLegacyRepository,
        PatientRepository $patientRepository,
        SiteLegacyRepository $siteLegacyRepository,
        BaseLegacyRepository $baseLegacyRepository,
        ShiftAttendanceRepository $shiftAttendanceRepository,
        AuthManager $auth,
        EventDispatcher $eventDispatcher
    ) {

        /** @var ShiftLegacy $shift */
        $shift = $shiftLegacyRepository->find($this->id);

        if (empty($shift)) {
            throw new ResourceNotFound("No Shift found with id '$this->id'.");
        }

        if (isset($this->type)) {
            if ($this->type == 'field' || $this->type == 'lab' || $this->type == 'clinical') {
                $shift->setType($this->type);
            } else {
                throw new ResourceNotFound("Shift type \"" . $this->type . "\" not recognized. Please use either 'field', 'lab', or 'clinical'.");
            }
        }

        // If the Shift is being locked
        if ($this->locked == true) {
            $patients = $patientRepository->findBy(["shift" => $this->id]);

            $badPatients = array();

            // Check if each Patient belonging to this Shift has the required information needed for locking.
            foreach ($patients as $patient) {
                if (!$patient->canBeLocked($shift->getType())) {
                    array_push($badPatients, $patient->getId());
                }
            }

            // If any of the Patients fail the check, the Shift lock fails. Return a message with
            // the IDs of the bad Patients.
            if (sizeof($badPatients) > 0) {
                throw new MissingMandatoryParametersException(
                    "Shift cannot be locked because one or more Patients are missing required fields: ".
                    implode(", ", $badPatients)
                );
            }
        }

        if (! is_null($this->siteId)) {
            /** @var SiteLegacy $site */
            $site = $siteLegacyRepository->find($this->siteId);

            $shift->setSite($site);

            if (empty($site)) {
                throw new ResourceNotFound("No Site found with id '$this->siteId'.");
            }
        }

        if (! is_null($this->baseId)) {
            /** @var BaseLegacy $base */
            $base = $baseLegacyRepository->find($this->baseId);

            $shift->setBase($base);

            if (empty($base)) {
                throw new ResourceNotFound("No Base found with id '$this->baseId'.");
            }
        }

        if (! is_null($this->locked)) {
            if ($shift->getLocked() != $this->locked) {
                $changeType = ($this->locked) ? 1 : 2;
                
                $history = new ShiftHistory();
                $history->shift = $shift;
                $history->change = $shift->id_or_entity_helper($changeType, 'ShiftHistoryChange');

                $user = $auth->guard()->user();

                if ($user->id) {
                    $history->user = $user;
                }

                $shift->histories->add($history);

                //only set the first locked timestamp if it's currently a null date
                if ($shift->first_locked == new \DateTime("0000-00-00 00:00:00")) {
                    $shift->setFirst_locked(new \DateTime("now"));
                }

                if (!$this->locked) {
                    $shift->audited = false;
                }
            }

            $shift->setLocked($this->locked);
        }
        
        if (! is_null($this->startDatetime)) {
            $shift->setStart_datetime($this->startDatetime);
        }
        
        if (! is_null($this->attendanceId)) {
            /** @var ShiftAttendence $attendance */
            $attendance = $shiftAttendanceRepository->find($this->attendanceId);

            $shift->setAttendance($attendance);

            if (empty($attendance)) {
                throw new ResourceNotFound("No Attendance found with id '$this->attendanceId'.");
            }
        }
        
        if (! is_null($this->hours)) {
            $shift->setHours($this->hours);
        }
        
        if (! is_null($this->attendanceComments)) {
            $shift->setAttendanceComments($this->attendanceComments);
        }

        $shiftLegacyRepository->update($shift);
        
        $eventDispatcher->fire(new ShiftWasUpdated($shift->getId()));

        return $shift;
    }
}
