<?php namespace Fisdap\Api\Programs\Settings\Events;

/**
 * Class ProgramSettingsWasUpdated
 *
 * @package Fisdap\Api\Programs\Settings\Events
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
final class ProgramSettingsWasUpdated
{
    /**
     * @var integer
     */
    private $id;

    /**
     * ProgramSettingsWasUpdated constructor.
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

