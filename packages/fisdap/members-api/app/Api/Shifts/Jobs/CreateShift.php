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
use Fisdap\Data\Student\StudentLegacyRepository;
use Fisdap\Entity\BaseLegacy;
use Fisdap\Entity\ShiftAttendence;
use Fisdap\Entity\ShiftLegacy;
use Fisdap\Entity\SiteLegacy;
use Fisdap\Entity\StudentLegacy;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use Swagger\Annotations as SWG;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;

/**
 * A Job (Command) for creating a shift (ShiftLegacy Entity)
 *
 * @package Fisdap\Api\Shifts\Jobs
 * @author  Nick Karnick <nkarnick@fisdap.net>
 *
 */
final class CreateShift extends Job implements RequestHydrated
{
    /**
     * @var int
     */
    public $studentId;

    /**
     * @var int
     */
    public $siteId;

    /**
     * @var int
     */
    public $baseId;

    /**
     * @var string
     */
    public $type;

    /**
     * @var boolean
     */
    public $locked;

    /**
     * @var \DateTime
     */
    public $startDatetime;

    /**
     * @var int
     */
    public $attendanceId;

    /**
     * @var float
     */
    public $hours;

    /**
     * @var string|null
     */
    public $attendanceComments = null;

    /**
     * @param int $studentId
     */
    public function setStudentId($studentId)
    {
        $this->studentId = $studentId;
    }

    /**
     * @param ShiftLegacyRepository $shiftLegacyRepository
     * @param StudentLegacyRepository $studentLegacyRepository
     * @param SiteLegacyRepository $siteLegacyRepository
     * @param BaseLegacyRepository $baseLegacyRepository
     * @param ShiftAttendanceRepository $shiftAttendanceRepository
     * @param EventDispatcher $eventDispatcher
     * @return ShiftLegacy
     */
    public function handle(
        ShiftLegacyRepository $shiftLegacyRepository,
        StudentLegacyRepository $studentLegacyRepository,
        SiteLegacyRepository $siteLegacyRepository,
        BaseLegacyRepository $baseLegacyRepository,
        ShiftAttendanceRepository $shiftAttendanceRepository,
        EventDispatcher $eventDispatcher
    ) {

        /** @var ShiftLegacy $shift */
        $shift = new ShiftLegacy();

        /** @var StudentLegacy $student */
        $student = $studentLegacyRepository->getOneById($this->studentId);

        if (empty($student)) {
            throw new ResourceNotFound("No Student found with id '$this->studentId'.");
        }

        $shift->setStudent($student);

        if (isset($this->type)) {
            if ($this->type == 'field' || $this->type == 'lab' || $this->type == 'clinical') {
                $shift->setType($this->type);
            } else {
                throw new ResourceNotFound("Shift type \"" . $this->type . "\" not recognized. Please use either 'field', 'lab', or 'clinical'.");
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

        // This is a new Shift, we shouldn't lock.
        $shift->setLocked(false);

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

        $shiftLegacyRepository->store($shift);
        
        $eventDispatcher->fire(new ShiftWasUpdated($shift->getId()));

        return $shift;
    }
}
