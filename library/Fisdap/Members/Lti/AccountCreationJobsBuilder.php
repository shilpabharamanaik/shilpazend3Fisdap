<?php namespace Fisdap\Members\Lti;

use Doctrine\Common\Collections\Criteria;
use Fisdap\Api\Products\Finder\FindsProducts;
use Fisdap\Api\Products\SerialNumbers\Jobs\CreateSerialNumber;
use Fisdap\Api\Users\Jobs\CreateUser;
use Fisdap\Api\Users\UserContexts\Jobs\CreateUserContext;
use Fisdap\Api\Users\UserContexts\Roles\Jobs\CreateRoleData;
use Fisdap\Data\Permission\PermissionRepository;
use Fisdap\Entity\Permission;
use Fisdap\Entity\Product;
use Fisdap\Entity\ProductPackage;


/**
 * Class AccountCreationJobsBuilder
 *
 * @package Fisdap\Members\Lti
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class AccountCreationJobsBuilder
{
    /**
     * @var FindsProducts
     */
    private $productsFinder;

    /**
     * @var PermissionRepository
     */
    private $permissionRepository;


    /**
     * AccountCreationJobsBuilder constructor.
     *
     * @param FindsProducts        $productsFinder
     * @param PermissionRepository $permissionRepository
     */
    public function __construct(FindsProducts $productsFinder, PermissionRepository $permissionRepository)
    {
        $this->productsFinder = $productsFinder;
        $this->permissionRepository = $permissionRepository;
    }


    /**
     * @param int                        $programId
     * @param int                        $certificationLevelId
     * @param Product[]|ProductPackage[] $productsOrPackages
     *
     * @return CreateSerialNumber
     */
    public function buildCreateSerialNumberJob($programId, $certificationLevelId, array $productsOrPackages)
    {
        $createSerialNumberJob = new CreateSerialNumber;

        $createSerialNumberJob->programId = $programId;
        $createSerialNumberJob->certificationLevelId = $certificationLevelId;

        foreach ($productsOrPackages as $productOrPackage) {
            switch (true) {
                case $productOrPackage instanceof Product:
                    $createSerialNumberJob->productIds[] = $productOrPackage->getId();
                    break;
                case $productOrPackage instanceof ProductPackage:
                    $createSerialNumberJob->productPackageIds[] = $productOrPackage->getId();
                    break;
            }
        }

        return $createSerialNumberJob;
    }


    /**
     * @param int                        $programId
     * @param int                        $courseId
     * @param string                     $roleName
     * @param Product[]|ProductPackage[] $productsOrPackages
     * @param string[]                   $serialNumbers
     * @param string                     $firstName
     * @param string                     $lastName
     * @param string                     $username
     * @param string                     $email
     * @param string|null                $ltiUserId
     * @param string|null                $psgUserId
     * @param \DateTime|null             $birthDate
     * @param int|null                   $genderId
     * @param int|null                   $ethnicityId
     *
     * @return CreateUser
     */
    public function buildCreateUserJobWithContext(
        $programId,
        $courseId,
        $roleName,
        array $productsOrPackages,
        array $serialNumbers,
        $firstName,
        $lastName,
        $username,
        $email,
        $ltiUserId = null,
        $psgUserId = null,
        \DateTime $birthDate = null,
        $genderId = null,
        $ethnicityId = null
    ) {
        $isDemo = $roleName === 'instructor' ? true : false;

        $createUserJob = $this->buildCreateUserJob(
            $firstName, $lastName, $username, $email, $ltiUserId, $psgUserId, $birthDate, $genderId, $ethnicityId, $isDemo
        );

        $createUserJob->userContexts = [$this->buildCreateUserContextJob(
            $programId, $courseId, $roleName, $productsOrPackages, $serialNumbers
        )];

        return $createUserJob;
    }


    /**
     * @param string         $firstName
     * @param string         $lastName
     * @param string         $username
     * @param string         $email
     * @param string|null    $ltiUserId
     * @param string|null    $psgUserId
     * @param \DateTime|null $birthDate
     * @param int|null       $genderId
     * @param int|null       $ethnicityId
     * @param bool           $isDemo
     *
     * @return CreateUser
     */
    public function buildCreateUserJob(
        $firstName, $lastName, $username, $email, $ltiUserId = null, $psgUserId = null, \DateTime $birthDate = null,
        $genderId = null, $ethnicityId = null, $isDemo = false
    ) {
        return new CreateUser(
            $firstName,
            $lastName,
            $username,
            $ltiUserId,
            $psgUserId,
            null,
            $email,
            $isDemo,
            false,
            null,
            null,
            null,
            null,
            null,
            $birthDate,
            $genderId,
            $ethnicityId
        );
    }


    /**
     * @param int                        $programId
     * @param int                        $courseId
     * @param string                     $roleName
     * @param Product[]|ProductPackage[] $productsOrPackages
     * @param string[]                   $serialNumbers
     * @param int|null                   $userId
     *
     * @return CreateUserContext
     */
    public function buildCreateUserContextJob(
        $programId, $courseId, $roleName, array $productsOrPackages, array $serialNumbers, $userId = null
    ) {
        $createUserContextJob = new CreateUserContext;
        $createUserContextJob->programId = $programId;
        $createUserContextJob->courseId = $courseId;

        $createUserContextJob->role = new CreateRoleData;
        $createUserContextJob->role->name = $roleName;
        $createUserContextJob->role->serialNumbers = $serialNumbers;

        if ($createUserContextJob->role->name == 'student') {
            $createUserContextJob->certificationLevelId = $productsOrPackages[0]->getCertificationLevel()->getId();

            $defaultProgramLengthDays = $productsOrPackages[0]->getCertificationLevel()->getDefaultProgramLengthDays();

            $createUserContextJob->startDate = new \DateTime;
            $createUserContextJob->endDate = new \DateTime("+{$defaultProgramLengthDays} days");
        }

        if ($createUserContextJob->role->name == 'instructor') {
            $createUserContextJob->role->permissionIds = $this->getDefaultInstructorPermissionIds();
        }

        if (is_int($userId)) {
            $createUserContextJob->userId = $userId;
        }

        return $createUserContextJob;
    }


    /**
     * @return int[]
     */
    private function getDefaultInstructorPermissionIds()
    {
        $permissionCriteria = Criteria::create();
        $permissionCriteria->where(
            Criteria::expr()->in('name', ['View All Data', 'View Schedules', 'View Reports'])
        );

        $permissions = $this->permissionRepository->matching($permissionCriteria);

        return $permissions->map(
            function (Permission $permission) {
                return $permission->getId();
            }
        )->toArray();
    }
}