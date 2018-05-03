<?php namespace Fisdap\Api\Products\SerialNumbers\Events;

use Fisdap\Api\Events\Event;
use Fisdap\Entity\SerialNumberLegacy;


/**
 * Template for serial number (SerialNumberLegacy Entity) events
 *
 * @package Fisdap\Api\Products\SerialNumbers\Events
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @codeCoverageIgnore
 */
abstract class SerialNumberEvent extends Event
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $number;


    /**
     * SerialNumberWasActivated constructor.
     *
     * @param SerialNumberLegacy $serialNumber
     */
    public function __construct(SerialNumberLegacy $serialNumber)
    {
        $this->id = $serialNumber->getId();
        $this->number = $serialNumber->getNumber();
    }


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }
}