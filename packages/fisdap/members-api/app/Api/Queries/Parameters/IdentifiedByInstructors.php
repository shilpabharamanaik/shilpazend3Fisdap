<?php namespace Fisdap\Api\Queries\Parameters;

use Fisdap\ErrorHandling\Exceptions\InvalidType;


/**
 * Query parameter for instructor ID(s)
 *
 * @package Fisdap\Api\Queries\Parameters
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
trait IdentifiedByInstructors {

    /**
     * @var int[]|null
     */
    protected $instructorIds = null;


    /**
     * @return int[]|null
     */
    public function getInstructorIds()
    {
        return $this->instructorIds;
    }


    /**
     * @param int[] $instructorIds
     *
     * @return $this
     * @throws InvalidType
     */
    public function setInstructorIds(array $instructorIds)
    {
        foreach ($instructorIds as $instructorId) {
            if ( ! is_numeric($instructorId)) {
                throw new InvalidType('instructor id must be an integer');
            }
        }

        $this->instructorIds = $instructorIds;

        return $this;
    }
}