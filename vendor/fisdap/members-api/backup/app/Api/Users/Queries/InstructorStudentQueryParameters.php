<?php namespace Fisdap\Api\Users\Queries;

use DateTimeZone;

/**
 * Encapsulates and validates query parameter data for students
 *
 * @package Fisdap\Api\Users\Queries
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
class InstructorStudentQueryParameters
{
    /**
     * @var int
     */
    protected $instructorId;

    /**
     * @var string|null
     */
    protected $dateFrom = null;

    /**
     * @return int|null
     */
    public function getInstructorId()
    {
        return $this->instructorId;
    }

    /**
     * @param $instructorId
     */
    public function setInstructorId($instructorId)
    {
        $this->instructorId = $instructorId;
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
