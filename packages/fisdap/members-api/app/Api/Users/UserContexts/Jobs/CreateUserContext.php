<?php namespace Fisdap\Api\Users\UserContexts\Jobs;

use Carbon\Carbon;
use Fisdap\Api\Jobs\Job;
use Fisdap\Api\Jobs\RequestHydrated;
use Fisdap\Api\Users\UserContexts\Events\UserContextWasCreated;
use Fisdap\Data\CertificationLevel\CertificationLevelRepository;
use Fisdap\Data\Program\ProgramLegacyRepository;
use Fisdap\Data\Role\RoleRepository;
use Fisdap\Data\User\UserContext\UserContextRepository;
use Fisdap\Data\User\UserRepository;
use Fisdap\Entity\CertificationLevel;
use Fisdap\Entity\ProgramLegacy;
use Fisdap\Entity\Role;
use Fisdap\Entity\User;
use Fisdap\Entity\UserContext;
use Illuminate\Contracts\Bus\Dispatcher as BusDispatcher;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Swagger\Annotations as SWG;


/**
 * A Job (Command) for creating a new user context (UserContext Entity)
 *
 * Object properties will be hydrated automatically using JSON from an HTTP request body
 *
 * @package Fisdap\Api\Users\Jobs\Models
 * @author  Ben Getsug <bgetsug@fisdap.net>
 *
 * @SWG\Definition(
 *     definition="UserContext",
 *     required={"role", "programId"}
 * )
 */
final class CreateUserContext extends Job implements RequestHydrated
{
    /**
     * @var int
     * @SWG\Property(description="not required if creating with a user")
     */
    public $userId;

    /**
     * @var \DateTime|null
     * @SWG\Property(type="string", format="dateTime")
     */
    public $startDate = null;

    /**
     * @var \DateTime|null
     * @SWG\Property(type="string", format="dateTime")
     */
    public $endDate = null;

    /**
     * @var bool
     * @SWG\Property
     */
    public $active = true;

    /**
     * @var string|null
     * @SWG\Property(type="string")
     */
    public $email = null;

    /**
     * @var \Fisdap\Api\Users\UserContexts\Roles\Jobs\CreateRoleData
     * @SWG\Property(ref="#/definitions/RoleData")
     */
    public $role;

    /**
     * @var int|null
     * @SWG\Property(type="integer")
     */
    public $certificationLevelId = null;

    /**
     * @var int
     * @SWG\Property
     */
    public $programId;

    /**
     * @var int|null
     * @SWG\Property(type="integer")
     */
    public $courseId = null;

    /**
     * @var bool
     * @SWG\Property
     */
    public $emailAccountInfo = false;


    /**
     * @param UserRepository               $userRepository
     * @param UserContextRepository        $userContextRepository
     * @param CertificationLevelRepository $certificationLevelRepository
     * @param ProgramLegacyRepository      $programLegacyRepository
     * @param RoleRepository               $roleRepository
     * @param BusDispatcher                $busDispatcher
     * @param EventDispatcher              $eventDispatcher
     *
     * @return UserContext
     * @throws \Exception
     */
    public function handle(
        UserRepository $userRepository,
        UserContextRepository $userContextRepository,
        CertificationLevelRepository $certificationLevelRepository,
        ProgramLegacyRepository $programLegacyRepository,
        RoleRepository $roleRepository,
        BusDispatcher $busDispatcher,
        EventDispatcher $eventDispatcher
    ) {
        $userContext = new UserContext;

        /** @var User $user */
        $user = $userRepository->getOneById($this->userId);
        $user->associateUserContext($userContext);
        $userContext->setUser($user);

        $certificationLevel = null;

        if (is_int($this->certificationLevelId)) {
            /** @var CertificationLevel $certificationLevel */
            $certificationLevel = $certificationLevelRepository->getOneById($this->certificationLevelId);
            $userContext->setCertificationLevel($certificationLevel);
        }

        /** @var ProgramLegacy $program */
        $program = $programLegacyRepository->getOneById($this->programId);
        $userContext->setProgram($program);

        $userContext->setCourseId($this->courseId);

        $userContext->setStartDate($this->startDate);

        if ($this->role->name == 'student' && $this->endDate === null) {
            $this->endDate = Carbon::today()->addYear();
        }

        $userContext->setEndDate($this->endDate);

        $userContext->setActive($this->active);

        $userContext->setEmail($this->email);

        $role = $roleRepository->getOneByName($this->role->name);
        $userContext->setRole($role);

        $userContextRepository->store($userContext);

        // todo - eventually permissions/product access (serial numbers) should be associated with the UserContext only

        // create appropriate RoleData Entity
        $this->role->userContext = $userContext;
        $busDispatcher->dispatch($this->role);

        $eventDispatcher->fire(new UserContextWasCreated($userContext));

        return $userContext;
    }


    /**
     * @param UserRepository               $userRepository
     * @param RoleRepository               $roleRepository
     * @param ProgramLegacyRepository      $programLegacyRepository
     * @param CertificationLevelRepository $certificationLevelRepository
     */
    public function validate(
        UserRepository $userRepository,
        RoleRepository $roleRepository,
        ProgramLegacyRepository $programLegacyRepository,
        CertificationLevelRepository $certificationLevelRepository
    ) {
        $certificationLevel = null;

        if ($this->role->name == 'student' && $this->certificationLevelId === null) {
            throw new UnprocessableEntityHttpException('Certification level is required for student contexts');
        }

        if (is_int($this->certificationLevelId)) {
            $certificationLevel = $certificationLevelRepository->getOneById($this->certificationLevelId);
        }

        /** @var User $user */
        $user = $userRepository->getOneById($this->userId);

        /** @var Role $role */
        $role = $roleRepository->getOneByName($this->role->name);

        /** @var ProgramLegacy $program */
        $program = $programLegacyRepository->getOneById($this->programId);

        if ($user->userContextExists($role, $program, $this->endDate, $certificationLevel, $this->courseId)) {
            throw new UnprocessableEntityHttpException(
                'A context already exists with the following criteria: ' . json_encode(get_object_vars($this))
            );
        }
    }


    /**
     * @return array
     */
    public function rules()
    {
        return [
            'role.name' => 'required|in:student,instructor',
            'programId' => 'required|integer',
            'email'     => 'email'
        ];
    }


    /**
     * @return array
     */
    public function messages()
    {
        return [
            'role.name.in' => 'A role must be one of the following types: :values'
        ];
    }
}