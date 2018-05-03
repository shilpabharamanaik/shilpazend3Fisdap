<?php namespace Fisdap\Api\Shifts\Patients\Queries;

use DateTimeZone;

/**
 * Encapsulates and validates query parameter data for patients
 *
 * @package Fisdap\Api\Shifts\Patients
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
class PatientQueryParameters
{
    /**
     * @var int|null
     */
    protected $shiftId;
    
    /**
     * @var string|null
     */
    protected $dateFrom = null;

    /**
     * @return int|null
     */
    public function getShiftId()
    {
        return $this->shiftId;
    }
    
    /**
     * @param $shiftId
     */
    public function setShiftId($shiftId)
    {
        $this->shiftId = $shiftId;
    }

    /**
     * @return string|null
     */
    public function getDateFrom()
    {
        return $this->dateFrom;
    }

    /**
     * @param string|null $dateFrom
     */
    public function setDateFrom($dateFrom)
    {
        if ($dateFrom) {
            $dateFrom = date_create_from_format('Y-m-d H:i:s', $dateFrom, new DateTimeZone('UTC'));

            if ($dateFrom == false) {
                throw new \InvalidArgumentException('Parameter \'dateFrom\' formatted incorrectly. ' .
                    'UTC timestamp expected. Ex.: 2017-01-31 00:00:00');
            } else {
                $dateFrom->setTimezone(new DateTimeZone('America/Chicago'));
            }
        } else {
            $dateFrom = null;
        }

        $this->dateFrom = $dateFrom;
    }
}
