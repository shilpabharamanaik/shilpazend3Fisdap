<?php namespace Fisdap\Api\Products\SerialNumbers\Events;

use Fisdap\Api\Events\Event;
use Fisdap\Api\Products\SerialNumbers\Jobs\Models\SerialNumber;
use Fisdap\Entity\SerialNumberLegacy;


/**
 * Event to fire when a collection of serial numbers (SerialNumberLegacy Entities) have been activated
 *
 * @package Fisdap\Api\Products\SerialNumbers\Events
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @codeCoverageIgnore
 */
final class SerialNumbersWereActivated extends Event
{
    /**
     * @var SerialNumber[]
     */
    private $serialNumbers;


    /**
     * SerialNumbersWereActivated constructor.
     *
     * @param SerialNumberLegacy[] $serialNumberEntities
     */
    public function __construct(array $serialNumberEntities)
    {
        foreach ($serialNumberEntities as $serialNumberEntity) {
            $serialNumber = new SerialNumber;
            $serialNumber->id = $serialNumberEntity->getId();
            $serialNumber->number = $serialNumberEntity->getNumber();
            $serialNumber->userId = $serialNumberEntity->getUserContext()->getUser()->getId();
            $serialNumber->userContextId = $serialNumberEntity->getUserContext()->getId();

            $this->serialNumbers[] = $serialNumber;
        }
    }


    /**
     * @return SerialNumber[]
     */
    public function getSerialNumbers()
    {
        return $this->serialNumbers;
    }
}