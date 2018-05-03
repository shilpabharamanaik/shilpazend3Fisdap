<?php namespace Fisdap\Api\Products\SerialNumbers\Jobs;

use Fisdap\Api\Jobs\Job;
use Fisdap\Api\Jobs\RequestHydrated;
use Fisdap\Api\Products\Finder\FindsProducts;
use Fisdap\Api\Products\SerialNumbers\Events\SerialNumberWasCreated;
use Fisdap\Api\Products\SerialNumbers\Jobs\Models\SerialNumber;
use Fisdap\Data\CertificationLevel\CertificationLevelRepository;
use Fisdap\Data\ClassSection\ClassSectionLegacyRepository;
use Fisdap\Data\Program\ProgramLegacyRepository;
use Fisdap\Data\SerialNumber\SerialNumberLegacyRepository;
use Fisdap\Entity\CertificationLevel;
use Fisdap\Entity\SerialNumberLegacy;
use Illuminate\Contracts\Bus\Dispatcher as BusDispatcher;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use Swagger\Annotations as SWG;

/**
 * A Job (Command) for creating a serial number (SerialNumberLegacy Entity)
 *
 * @package Fisdap\Api\Products\SerialNumbers\Jobs
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @todo add validation rules
 *
 * @SWG\Definition(
 *     definition="SerialNumber",
 *     required={"programId", "certificationLevelId"},
 * )
 */
final class CreateSerialNumber extends Job implements RequestHydrated
{
    /**
     * @var string|null
     * @SWG\Property(type="string", example="ABC123")
     */
    public $uuid = null;

    /**
     * @var int
     * @SWG\Property(example=287)
     */
    public $programId;

    /**
     * @var string|null
     * @SWG\Property(type="string")
     */
    public $purchaseOrder = null;

    /**
     * @var int[]|null
     * @SWG\Property(type="array", items=@SWG\Items(type="integer"),
     *     description="Must be provided if not specifying any productPackageIds"
     * )
     */
    public $productIds = null;

    /**
     * @var int[]|null
     * @SWG\Property(type="array", items=@SWG\Items(type="integer"),
     *     description="Must be provided if not specifying any productIds"
     * )
     */
    public $productPackageIds = null;

    /**
     * @var int
     * @SWG\Property
     */
    public $certificationLevelId;

    /**
     * @var \DateTime|null
     * @SWG\Property(type="string", format="dateTime")
     */
    public $graduationDate = null;

    /**
     * @var int|null
     * @SWG\Property(type="integer")
     */
    public $studentGroupId = null;

    /**
     * @var int|null
     * @SWG\Property(type="integer")
     */
    public $userContextId = null;


    /**
     * @param SerialNumberLegacyRepository $serialNumberLegacyRepository
     * @param ProgramLegacyRepository      $programLegacyRepository
     * @param FindsProducts                $productsFinder
     * @param CertificationLevelRepository $certificationLevelRepository
     * @param ClassSectionLegacyRepository $classSectionLegacyRepository
     * @param BusDispatcher                $busDispatcher
     * @param EventDispatcher              $eventDispatcher
     *
     * @return SerialNumberLegacy
     */
    public function handle(
        SerialNumberLegacyRepository $serialNumberLegacyRepository,
        ProgramLegacyRepository $programLegacyRepository,
        FindsProducts $productsFinder,
        CertificationLevelRepository $certificationLevelRepository,
        ClassSectionLegacyRepository $classSectionLegacyRepository,
        BusDispatcher $busDispatcher,
        EventDispatcher $eventDispatcher
    ) {
        $serial = new SerialNumberLegacy;

        /** @noinspection PhpParamsInspection */
        $serial->setProgram($programLegacyRepository->getOneById($this->programId));
        $serial->setUUID($this->uuid);
        $serial->purchase_order = $this->purchaseOrder;

        $serial->setConfiguration($productsFinder->getConfigurationValueForProductsOrPackages(
            $this->productIds,
            $this->productPackageIds
        ));

        /** @var CertificationLevel $certificationLevel */
        $certificationLevel = $certificationLevelRepository->getOneById($this->certificationLevelId);
        $serial->setCertificationLevel($certificationLevel);

        $serial->setGraduationDate($this->graduationDate);

        if ($this->studentGroupId !== null) {
            /** @noinspection PhpParamsInspection */
            $serial->setGroup($classSectionLegacyRepository->getOneById($this->studentGroupId));
        }

        $serial->generateUniqueNumber();

        $serialNumberLegacyRepository->store($serial);

        if ($this->userContextId !== null) {
            $serialNumber = new SerialNumber;
            $serialNumber->number = $serial->getNumber();
            $serialNumber->userContextId = $this->userContextId;

            $activateSerialNumberJob = new ActivateSerialNumbers;
            $activateSerialNumberJob->serialNumbers = [$serialNumber];
            
            $busDispatcher->dispatch($activateSerialNumberJob);
        }

        $eventDispatcher->fire(new SerialNumberWasCreated($serial));

        return $serial;
    }
}
