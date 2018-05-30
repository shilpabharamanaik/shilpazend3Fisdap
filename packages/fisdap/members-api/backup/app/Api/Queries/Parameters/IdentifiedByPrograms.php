<?php namespace Fisdap\Api\Queries\Parameters;

use Fisdap\ErrorHandling\Exceptions\InvalidType;

/**
 * Query parameter for program ID(s)
 *
 * @package Fisdap\Api\Queries\Parameters
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
trait IdentifiedByPrograms
{

    /**
     * @var int[]|null
     */
    protected $programIds = null;


    /**
     * @return int[]|null
     */
    public function getProgramIds()
    {
        return $this->programIds;
    }


    /**
     * @param int[] $programIds
     *
     * @return $this
     * @throws InvalidType
     */
    public function setProgramIds(array $programIds)
    {
        foreach ($programIds as $programId) {
            if (! is_numeric($programId)) {
                throw new InvalidType('program id must be an integer');
            }
        }

        $this->programIds = $programIds;

        return $this;
    }
}
