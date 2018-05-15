<?php namespace Fisdap\Api\Shifts\Patients\Skills;

use Fisdap\Api\Jobs\Job;
use Fisdap\Api\Jobs\RequestHydrated;
use Fisdap\Entity\Patient;
use Fisdap\Entity\ShiftLegacy;

/**
 * Class AbstractSkills
 * @package Fisdap\Api\Shifts\Patients\Skills
 * @author  Isaac White <isaac.white@ascendlearning.com>
 * @author  Nick Karnick <nkarnick@fisdap.net>
 *
 */
abstract class AbstractSkills extends Job implements RequestHydrated
{
    /**
     * @var integer
     */
    public $id;

    /**
     * @var integer
     * @SWG\Property(type="integer", example=12345)
     */
    public $uuid;

    /**
     * @var integer|null
     * @SWG\Property(type="integer", example=5)
     */
    public $size = null;

    /**
     * @var boolean
     * @SWG\Property(type="boolean", example=true)
     */
    public $success;

    /**
     * @var integer
     * @SWG\Property(type="integer", example=2)
     */
    public $attempts;

    /**
     * @var integer
     * @SWG\Property(type="integer", example=1)
     */
    public $skillOrder;

    /**
     * @var boolean
     * @SWG\Property(type="boolean", example=true)
     */
    public $performed;

    /**
     * @var Patient
     * @see Patient
     */
    protected $patient;

    /**
     * @var ShiftLegacy
     * @see ShiftLegacy
     */
    protected $shift;

    /**
     * @param $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param Patient $patient
     */
    public function setPatient(Patient $patient)
    {
        $this->patient = $patient;
    }

    /**
     * @return Patient
     */
    public function getPatient()
    {
        return $this->patient;
    }

    /**
     * @param ShiftLegacy $shift
     */
    public function setShift(ShiftLegacy $shift)
    {
        $this->shift = $shift;
    }

    /**
     * @return ShiftLegacy
     */
    public function getShift()
    {
        return $this->shift;
    }
}
