<?php namespace Fisdap\Api\Shifts\Events;

use Fisdap\Api\Events\Event;

/**
 * Class ShiftWasUpdated
 *
 * @package Fisdap\Api\Shifts\Events
 * @author  Nick Karnick <nkarnick@fisdap.net>
 * @codeCoverageIgnore
 */
final class ShiftWasUpdated extends Event
{
    /**
     * @var int
     */
    private $id;


    /**
     * ShiftWasUpdated constructor.
     *
     * @param int           $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
