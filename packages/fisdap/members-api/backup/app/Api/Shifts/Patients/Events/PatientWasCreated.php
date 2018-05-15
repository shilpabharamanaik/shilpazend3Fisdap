<?php namespace Fisdap\Api\Shifts\Patients\Events;

/**
 * Class PatientWasCreated
 *
 * @package Fisdap\Api\Shifts\Patients\Events
 * @author  Isaac White <iwhite@fisdap.net>
 */
final class PatientWasCreated
{
    /**
     * @var integer
     */
    private $id;

    /**
     * PatientWasCreated constructor.
     * @param $id
     */
    public function __construct($id)
    {
        $this->id   = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
