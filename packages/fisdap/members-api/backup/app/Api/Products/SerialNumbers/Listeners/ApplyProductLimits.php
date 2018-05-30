<?php namespace Fisdap\Api\Products\SerialNumbers\Listeners;

use Fisdap\Api\Products\SerialNumbers\Events\SerialNumberWasActivated;
use Fisdap\Data\SerialNumber\SerialNumberLegacyRepository;
use Fisdap\Data\Student\StudentLegacyRepository;
use Fisdap\Entity\SerialNumberLegacy;
use Fisdap\Entity\StudentLegacy;
use Fisdap\Logging\Events\EventLogging;

/**
 * An event listener for applying product limits to a student (StudentLegacy Entity),
 * when a serial number (SerialNumberLegacy Entity) has been activated
 *
 * @package Fisdap\Api\Products\SerialNumbers\Listeners
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class ApplyProductLimits
{
    use EventLogging;


    /**
     * @var SerialNumberLegacyRepository
     */
    private $serialNumberLegacyRepository;

    /**
     * @var StudentLegacyRepository
     */
    private $studentLegacyRepository;


    /**
     * ApplyProductLimits constructor.
     *
     * @param SerialNumberLegacyRepository $serialNumberLegacyRepository
     * @param StudentLegacyRepository      $studentLegacyRepository
     */
    public function __construct(
        SerialNumberLegacyRepository $serialNumberLegacyRepository,
        StudentLegacyRepository $studentLegacyRepository
    ) {
        $this->serialNumberLegacyRepository = $serialNumberLegacyRepository;
        $this->studentLegacyRepository = $studentLegacyRepository;
    }


    /**
     * @param SerialNumberWasActivated $event
     */
    public function handle(SerialNumberWasActivated $event)
    {
        /** @var SerialNumberLegacy $serialNumber */
        $serialNumber = $this->serialNumberLegacyRepository->getOneById($event->getId());

        // todo - discuss this logic with domain expert...it seems weirdly nested
        if ($serialNumber->hasProductLimits(true)) {
            // if there aren't any unlimited products (in a case where a student may have both)
            if (! $serialNumber->hasProductLimits(false)) {
                $roleData = $serialNumber->getUserContext()->getRoleData();

                if ($roleData instanceof StudentLegacy) {
                    $roleData->field_shift_limit = 10;
                    $roleData->clinical_shift_limit = 10;

                    $this->studentLegacyRepository->update($roleData);
                }
            }
        }
    }
}
