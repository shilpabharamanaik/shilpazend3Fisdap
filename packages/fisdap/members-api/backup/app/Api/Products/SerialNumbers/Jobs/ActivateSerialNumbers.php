<?php namespace Fisdap\Api\Products\SerialNumbers\Jobs;

use Fisdap\Api\Jobs\Job;
use Fisdap\Api\Jobs\RequestHydrated;
use Fisdap\Api\Products\SerialNumbers\Events\SerialNumbersWereActivated;
use Fisdap\Api\Products\SerialNumbers\Events\SerialNumberWasActivated;
use Fisdap\Api\Products\SerialNumbers\Exception\InvalidSerialNumber;
use Fisdap\Api\Products\SerialNumbers\Jobs\Models\SerialNumber;
use Fisdap\Api\Users\UserContexts\Jobs\CreateUserContext;
use Fisdap\Api\Users\UserContexts\Roles\Jobs\CreateRoleData;
use Fisdap\Data\SerialNumber\SerialNumberLegacyRepository;
use Fisdap\Data\User\UserContext\UserContextRepository;
use Fisdap\Entity\ClassSectionLegacy;
use Fisdap\Entity\InstructorLegacy;
use Fisdap\Entity\SerialNumberLegacy;
use Fisdap\Entity\StudentLegacy;
use Fisdap\Entity\UserContext;
use Illuminate\Contracts\Bus\Dispatcher as BusDispatcher;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;


/**
 * A Job (Command) for activating a serial number (SerialNumberLegacy Entity)
 *
 * @package Fisdap\Api\Products\SerialNumbers\Jobs
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @todo add validation rules
 */
final class ActivateSerialNumbers extends Job implements RequestHydrated
{
    /**
     * @var \Fisdap\Api\Products\SerialNumbers\Jobs\Models\SerialNumber[]
     */
    public $serialNumbers;


    /**
     * @var UserContextRepository
     */
    private $userContextRepository;

    /**
     * @var BusDispatcher
     */
    private $busDispatcher;


    /**
     * @param SerialNumberLegacyRepository $serialNumberLegacyRepository
     * @param UserContextRepository        $userContextRepository
     * @param BusDispatcher                $busDispatcher
     * @param EventDispatcher              $eventDispatcher
     *
     * @throws \Exception
     */
    public function handle(
        SerialNumberLegacyRepository $serialNumberLegacyRepository,
        UserContextRepository $userContextRepository,
        BusDispatcher $busDispatcher,
        EventDispatcher $eventDispatcher
    ) {
        $this->userContextRepository = $userContextRepository;
        $this->busDispatcher = $busDispatcher;

        $serialNumberEntities = [];

        foreach ($this->serialNumbers as $serialNumber) {
            $serialNumberEntity = $serialNumberLegacyRepository->getOneByNumber($serialNumber->number);

            if ($serialNumberEntity->getActivationDate() instanceof \DateTime) {
                throw new UnprocessableEntityHttpException('Serial number already activated');
            }

            $serialNumberEntity->setActivationDate();

            $userContext = $this->getUserContext($serialNumber, $serialNumberEntity);

            $serialNumberEntity->setUserContext($userContext);
            $userContext->addSerialNumber($serialNumberEntity);

            $graduationDate = $serialNumberEntity->getGraduationDate();

            if ($graduationDate instanceof \DateTime) {
                $userContext->setEndDate($graduationDate);
            }

            $this->populateStudentGroup($userContext, $serialNumberEntity);

            $this->setupLegacyAssociations($serialNumberEntity, $userContext);

            $serialNumberLegacyRepository->update($serialNumberEntity);

            $eventDispatcher->fire(new SerialNumberWasActivated($serialNumberEntity));

            $serialNumberEntities[] = $serialNumberEntity;
        }

        $eventDispatcher->fire(new SerialNumbersWereActivated($serialNumberEntities));
    }


    /**
     * @param SerialNumber       $serialNumber
     * @param SerialNumberLegacy $serialNumberEntity
     *
     * @return UserContext
     * @throws \Exception
     */
    private function getUserContext(SerialNumber $serialNumber, SerialNumberLegacy $serialNumberEntity)
    {
        if ($serialNumber->userId !== null && $serialNumber->userContextId === null) {
            return $this->createUserContextFromSerialNumber($serialNumber, $serialNumberEntity);
        }

        if ($serialNumber->userContextId === null) {
            throw new InvalidSerialNumber('SerialNumber must specify at least a userContextId');
        }

        return $this->userContextRepository->getOneById($serialNumber->userContextId);
    }


    /**
     * @param SerialNumber       $serialNumber
     * @param SerialNumberLegacy $serialNumberEntity
     *
     * @return UserContext
     */
    private function createUserContextFromSerialNumber(SerialNumber $serialNumber, SerialNumberLegacy $serialNumberEntity)
    {
        $createUserContextJob = new CreateUserContext;
        $createUserContextJob->userId = $serialNumber->userId;
        $createUserContextJob->programId = $serialNumberEntity->getProgram()->getId();
        $createUserContextJob->certificationLevelId = $serialNumberEntity->getCertificationLevel()->getId();
        $createUserContextJob->role = new CreateRoleData;
        $createUserContextJob->role->name = 'student';

        return $this->busDispatcher->dispatch($createUserContextJob);
    }


    /**
     * @param UserContext        $userContext
     * @param SerialNumberLegacy $serialNumber
     *
     * @throws \Exception
     */
    private function populateStudentGroup(UserContext $userContext, SerialNumberLegacy $serialNumber)
    {
        if ($userContext->getRoleData() instanceof StudentLegacy && $serialNumber->getGroup() instanceof ClassSectionLegacy) {

            /** @noinspection PhpParamsInspection */
            $serialNumber->getGroup()->addStudent($userContext->getRoleData());
        }
    }


    /**
     * @param SerialNumberLegacy $serialNumber
     * @param UserContext        $userContext
     *
     * @throws \Exception
     * @todo eliminate the need for RoleData and User associations
     */
    private function setupLegacyAssociations(SerialNumberLegacy $serialNumber, UserContext $userContext)
    {
        switch (get_class($userContext->getRoleData())) {
            case StudentLegacy::class:
                /** @noinspection PhpParamsInspection */
                $serialNumber->setStudent($userContext->getRoleData());
                break;
            case InstructorLegacy::class:
                /** @noinspection PhpParamsInspection */
                $serialNumber->setInstructor($userContext->getRoleData());
                break;
        }

        $serialNumber->setUser($userContext->getUser());
        $userContext->getUser()->addSerialNumber($serialNumber);
    }
}