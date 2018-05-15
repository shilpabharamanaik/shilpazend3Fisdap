<?php namespace Fisdap\Api\Users\UserContexts\Permissions\Finder;

use Fisdap\Api\Queries\Exceptions\ResourceNotFound;
use Fisdap\Api\Users\UserContexts\Permissions\Queries\Specifications\ByInstructor;
use Fisdap\Api\Users\UserContexts\Permissions\Queries\Specifications\InstructorPermissionNames;
use Fisdap\Data\Permission\PermissionRepository;
use Fisdap\ErrorHandling\Exceptions\NoPermissions;
use Happyr\DoctrineSpecification\Spec;

/**
 * Service for retrieving and determining user permissions
 *
 * @package Fisdap\Api\Users\Permissions
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class PermissionsFinder implements FindsPermissions
{
    /**
     * @var PermissionRepository
     */
    protected $repository;


    /**
     * @param PermissionRepository $repository
     */
    public function __construct(PermissionRepository $repository)
    {
        $this->repository = $repository;
    }


    /**
     * @inheritdoc
     */
    public function all()
    {
        return $this->repository->findAll();
    }


    /**
     * @inheritdoc
     */
    public function one($permissionId)
    {
        $permission = $this->repository->getOneById($permissionId);

        if ($permission === null) {
            throw new ResourceNotFound("No permission was found with id '$permissionId'");
        }

        return $permission;
    }


    /**
     * @inheritdoc
     */
    public function getInstructorPermissions($instructorId)
    {
        $permissions = $this->repository->match(Spec::andX(
            new ByInstructor,
            Spec::eq('id', $instructorId, 'instructor')
        ), Spec::asArray());

        if (empty($permissions)) {
            throw new NoPermissions("No permissions found for instructor with id '$instructorId'");
        }

        return $permissions;
    }


    /**
     * @inheritdoc
     */
    public function getInstructorPermissionNames($instructorId)
    {
        $permissions = $this->repository->match(new InstructorPermissionNames($instructorId), Spec::asArray());

        if (empty($permissions)) {
            throw new NoPermissions("No permissions found for instructor with id '$instructorId'");
        }

        $names = array_unique(array_pluck($permissions, 'name'));

        return $names;
    }


    /**
     * @inheritdoc
     */
    public function hasInstructorPermission($permissionName, array $instructorPermissions)
    {
        return count(
            array_where(
                $instructorPermissions,
                function ($key, $value) use ($permissionName) {
                    return preg_match("/$permissionName/i", $value);
                }
            )
        ) > 0;
    }
}
