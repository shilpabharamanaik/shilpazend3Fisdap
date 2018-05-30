<?php namespace Fisdap\Api\Queries\Parameters;

use Fisdap\ErrorHandling\Exceptions\InvalidType;

/**
 * Query parameter for student ID(s)
 *
 * @package Fisdap\Api\Queries\Parameters
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
trait IdentifiedByStudents
{

    /**
     * @var int[]|null
     */
    protected $studentIds = null;


    /**
     * @return int[]|null
     */
    public function getStudentIds()
    {
        return $this->studentIds;
    }


    /**
     * @param int[] $studentIds
     *
     * @return $this
     * @throws InvalidType
     */
    public function setStudentIds(array $studentIds)
    {
        foreach ($studentIds as $studentId) {
            if (! is_numeric($studentId)) {
                throw new InvalidType('student id must be an integer');
            }
        }

        $this->studentIds = $studentIds;

        return $this;
    }
}
