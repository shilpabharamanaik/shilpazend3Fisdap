<?php namespace Fisdap\Api\Programs\Listeners;

use Fisdap\Api\Products\SerialNumbers\Jobs\CreateSerialNumber;
use Fisdap\Api\Programs\Events\DemoStudentWasCreated;
use Fisdap\Api\Programs\Events\ProgramWasCreated;
use Fisdap\Api\Users\Jobs\CreateUser;
use Fisdap\Api\Users\Jobs\Models\Licenses;
use Fisdap\Api\Users\UserContexts\Jobs\CreateUserContext;
use Fisdap\Api\Users\UserContexts\Roles\Jobs\CreateRoleData;
use Fisdap\Data\CertificationLevel\CertificationLevelRepository;
use Fisdap\Data\Product\ProductRepository;
use Fisdap\Data\Program\ProgramLegacyRepository;
use Fisdap\Entity\CertificationLevel;
use Fisdap\Entity\Product;
use Fisdap\Entity\ProgramLegacy;
use Fisdap\Entity\SerialNumberLegacy;
use Fisdap\Logging\Events\EventLogging;
use Illuminate\Contracts\Bus\Dispatcher as BusDispatcher;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Class CreateDemoStudent
 *
 * @package Fisdap\Api\Programs\Listeners\Pipes
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class CreateDemoStudent implements ShouldQueue
{
    use EventLogging;
    
    
    /**
     * @var ProgramLegacyRepository
     */
    private $programLegacyRepository;

    /**
     * @var CertificationLevelRepository
     */
    private $certificationLevelRepository;
    
    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var BusDispatcher
     */
    private $busDispatcher;

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;


    /**
     * CreateDemoStudent constructor.
     *
     * @param ProgramLegacyRepository $programLegacyRepository
     * @param CertificationLevelRepository $certificationLevelRepository
     * @param ProductRepository $productRepository
     * @param BusDispatcher $busDispatcher
     * @param EventDispatcher $eventDispatcher
     */
    public function __construct(
        ProgramLegacyRepository $programLegacyRepository,
        CertificationLevelRepository $certificationLevelRepository,
        ProductRepository $productRepository,
        BusDispatcher $busDispatcher,
        EventDispatcher $eventDispatcher
    ) {
        $this->programLegacyRepository = $programLegacyRepository;
        $this->certificationLevelRepository = $certificationLevelRepository;
        $this->productRepository = $productRepository;
        $this->busDispatcher = $busDispatcher;
        $this->eventDispatcher = $eventDispatcher;
    }


    /**
     * @param ProgramWasCreated $event
     */
    public function handle(ProgramWasCreated $event)
    {
        /** @var ProgramLegacy $program */
        $program = $this->programLegacyRepository->getOneById($event->getId());

        $serial = $this->createDemoSerial($program);

        $createUserContextJob = new CreateUserContext;
        $createUserContextJob->programId = $program->getId();
        $createUserContextJob->certificationLevelId = $serial->getCertificationLevel()->getId();
        $createUserContextJob->endDate = new \DateTime('+4 years');
        $createUserContextJob->role = new CreateRoleData;
        $createUserContextJob->role->name = 'student';
        $createUserContextJob->role->serialNumbers = [$serial->getNumber()];

        $this->busDispatcher->dispatch(new CreateUser(
            $program->getAbbreviation(),
            'Student',
            $username = $program->getProductCodeId(),
            null,
            null,
            $password = '12345',
            'test@fisdap.net',
            true,
            false,
            null,
            null,
            null,
            new Licenses('12345', new \DateTime('+1 year'), $program->getState(), '12345', new \DateTime('+1 year')),
            null,
            null,
            null,
            null,
            [$createUserContextJob]
        ));

        $this->eventDispatcher->fire(new DemoStudentWasCreated($program->getId(), $username, $password));
        
        $this->eventLogInfo('Created Demo Student', ['programId' => $program->getId(), 'username' => $username]);
    }


    /**
     * @param ProgramLegacy $program
     *
     * @return SerialNumberLegacy
     */
    private function createDemoSerial(ProgramLegacy $program)
    {
        $createSerialNumberJob = new CreateSerialNumber;
        $createSerialNumberJob->programId = $program->getId();
        $createSerialNumberJob->graduationDate = new \DateTime('+4 years');


        // If we're dealing with EMS, default to paramedic, otherwise get the first cert level
        if ($program->getProfession()->getId() == 1) {
            $createSerialNumberJob->certificationLevelId = 3;
        } else {
            $createSerialNumberJob->certificationLevelId = $program->getProfession()->getCertifications()->first()->getId();
        }
        
        /** @var CertificationLevel $certificationLevel */
        $certificationLevel = $this->certificationLevelRepository->getOneById($createSerialNumberJob->certificationLevelId);

        // Get all products and assign any applicable products to this account
        $products = $this->productRepository->getProducts(
            $certificationLevel->configuration_blacklist,
            false,
            false,
            true,
            false,
            $program->getProfession()->getId()
        );

        $createSerialNumberJob->productIds = array_map(function (Product $product) {
            return $product->getId();
        }, $products);

        return $this->busDispatcher->dispatch($createSerialNumberJob);
    }
}
