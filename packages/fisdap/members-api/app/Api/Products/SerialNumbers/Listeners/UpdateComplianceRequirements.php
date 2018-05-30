<?php namespace Fisdap\Api\Products\SerialNumbers\Listeners;

use Fisdap\Api\Compliance\Jobs\AutoAttachRequirements;
use Fisdap\Api\Compliance\Jobs\UpdateCompliance;
use Fisdap\Api\Products\SerialNumbers\Events\SerialNumbersWereActivated;
use Fisdap\Data\SerialNumber\SerialNumberLegacyRepository;
use Fisdap\Entity\InstructorLegacy;
use Fisdap\Entity\SerialNumberLegacy;
use Fisdap\Entity\StudentLegacy;
use Fisdap\Logging\Events\EventLogging;
use Illuminate\Contracts\Bus\Dispatcher as BusDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * An event listener that assigns requirements to a user context, when a serial number (SerialNumberLegacy Entity)
 * has been activated, and the serial number provides access to Fisdap Scheduler
 *
 * @package Fisdap\Api\Products\SerialNumbers\Listeners
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class UpdateComplianceRequirements implements ShouldQueue
{
    use EventLogging;


    /**
     * @var SerialNumberLegacyRepository
     */
    private $serialNumberLegacyRepository;

    /**
     * @var BusDispatcher
     */
    private $busDispatcher;


    /**
     * UpdateComplianceRequirements constructor.
     *
     * @param SerialNumberLegacyRepository $serialNumberLegacyRepository
     * @param BusDispatcher                $busDispatcher
     */
    public function __construct(SerialNumberLegacyRepository $serialNumberLegacyRepository, BusDispatcher $busDispatcher)
    {
        $this->serialNumberLegacyRepository = $serialNumberLegacyRepository;
        $this->busDispatcher = $busDispatcher;
    }


    /**
     * @param SerialNumbersWereActivated $event
     *
     * @throws \Exception
     */
    public function handle(SerialNumbersWereActivated $event)
    {
        $userContexts = [];

        foreach ($event->getSerialNumbers() as $serialNumber) {
            /** @var SerialNumberLegacy $serialNumberEntity */
            $serialNumberEntity = $this->serialNumberLegacyRepository->getOneById($serialNumber->id);

            $userContexts[] = $userContext = $serialNumberEntity->getUserContext();

            switch (get_class($userContext->getRoleData())) {
                case InstructorLegacy::class:
                    $this->busDispatcher->dispatch(new AutoAttachRequirements($userContext));
                    break;
                case StudentLegacy::class:
                    // todo - use ProductsFinder here
                    if (! $serialNumberEntity->hasScheduler()) {
                        return;
                    }
                    $this->busDispatcher->dispatch(new AutoAttachRequirements($userContext));
                    break;
            }
        }

        $this->busDispatcher->dispatch(new UpdateCompliance($userContexts));
    }
}
