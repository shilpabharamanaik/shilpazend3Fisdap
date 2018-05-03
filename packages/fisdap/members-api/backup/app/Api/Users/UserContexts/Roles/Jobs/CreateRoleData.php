<?php namespace Fisdap\Api\Users\UserContexts\Roles\Jobs;

use Fisdap\Api\Jobs\Job;
use Fisdap\Api\Jobs\RequestHydrated;
use Fisdap\Api\Products\SerialNumbers\Jobs\ActivateSerialNumbers;
use Fisdap\Api\Products\SerialNumbers\Jobs\Models\SerialNumber;
use Fisdap\Api\Users\UserContexts\Permissions\Jobs\SetPermissions;
use Fisdap\Api\Users\UserContexts\Roles\Events\RoleDataWasCreated;
use Fisdap\Api\Users\UserContexts\Roles\Jobs\Models\CommunicationPreferences;
use Fisdap\Api\Users\UserContexts\Roles\RoleDataFactory;
use Fisdap\Data\Instructor\InstructorLegacyRepository;
use Fisdap\Data\Student\StudentLegacyRepository;
use Fisdap\Entity\InstructorLegacy;
use Fisdap\Entity\RoleData;
use Fisdap\Entity\StudentLegacy;
use Fisdap\Entity\UserContext;
use Illuminate\Contracts\Bus\Dispatcher as BusDispatcher;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use Swagger\Annotations as SWG;


/**
 * A Job (Command) for creating role data (RoleData Entities)
 *
 * Object properties will be hydrated automatically using JSON from an HTTP request body
 *
 * @package Fisdap\Api\Users\UserContexts\Roles\Jobs
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @todo add validation rules

 * @SWG\Definition(
 *     definition="RoleData",
 *     required={"name"}
 * )
 */
final class CreateRoleData extends Job implements RequestHydrated
{
    /**
     * @var string
     * @SWG\Property
     */
    public $name;

    /**
     * @var int[]
     * @SWG\Property(type="array", items=@SWG\Items(type="integer"))
     */
    public $permissionIds = [];

    /**
     * @var string[]|null
     * @SWG\Property(type="array", items=@SWG\Items(type="string"))
     */
    public $serialNumbers = null;

    /**
     * @var CommunicationPreferences|null
     * @SWG\Property(ref="#/definitions/RoleCommunicationPreferences")
     */
    public $communicationPreferences = null;

    /**
     * @var UserContext
     */
    public $userContext;


    /**
     * @var InstructorLegacyRepository
     */
    private $instructorLegacyRepository;

    /**
     * @var StudentLegacyRepository
     */
    private $studentLegacyRepository;

    /**
     * @var BusDispatcher
     */
    private $busDispatcher;


    /**
     * @param RoleDataFactory            $roleDataFactory
     * @param InstructorLegacyRepository $instructorLegacyRepository
     * @param StudentLegacyRepository    $studentLegacyRepository
     * @param BusDispatcher              $busDispatcher
     * @param EventDispatcher            $eventDispatcher
     *
     * @return RoleData
     * @throws \Exception
     */
    public function handle(
        RoleDataFactory $roleDataFactory,
        InstructorLegacyRepository $instructorLegacyRepository,
        StudentLegacyRepository $studentLegacyRepository,
        BusDispatcher $busDispatcher,
        EventDispatcher $eventDispatcher
    ) {
        $this->instructorLegacyRepository = $instructorLegacyRepository;
        $this->studentLegacyRepository = $studentLegacyRepository;
        $this->busDispatcher = $busDispatcher;

        $roleData = $roleDataFactory->create($this);

        $roleData->setUserContext($this->userContext);
        $this->userContext->setRoleData($roleData);

        /*
         * todo
         * eventually, this can go away once we make sure that no
         * client code is trying to get the User from the RoleData
         */
        $roleData->setUser($this->userContext->getUser());

        $this->store($roleData);

        $this->busDispatcher->dispatch(new SetPermissions($roleData, $this->permissionIds));

        $this->activateSerialNumbers();

        $eventDispatcher->fire(new RoleDataWasCreated($roleData));

        return $roleData;
    }


    /**
     * @param RoleData $roleData
     */
    private function store(RoleData $roleData)
    {
        switch (get_class($roleData)) {
            case InstructorLegacy::class:
                $this->instructorLegacyRepository->store($roleData);
                break;
            case StudentLegacy::class:
                $this->studentLegacyRepository->store($roleData);
                break;
        }
    }


    private function activateSerialNumbers()
    {
        if ($this->serialNumbers === null) return;

        $serialNumbers = [];

        foreach ($this->serialNumbers as $number) {
            $serialNumber = new SerialNumber;
            $serialNumber->number = $number;
            $serialNumber->userContextId = $this->userContext->getId();
            $serialNumbers[] = $serialNumber;
        }

        $activateSerialNumber = new ActivateSerialNumbers;
        $activateSerialNumber->serialNumbers = $serialNumbers;

        $this->busDispatcher->dispatch($activateSerialNumber);
    }
}