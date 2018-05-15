<?php namespace Fisdap\Api\Users\UserContexts\Permissions;

use Fisdap\Api\Http\Controllers\Controller;
use Fisdap\Api\Queries\Exceptions\ResourceNotFound;
use Fisdap\Api\Users\UserContexts\Permissions\Finder\FindsPermissions;
use Fisdap\Api\Users\UserContexts\Permissions\Transformation\PermissionTransformer;
use Fisdap\Data\User\UserContext\UserContextRepository;
use Fisdap\Data\User\UserRepository;
use Fisdap\Entity\User;
use Fisdap\Entity\UserContext;
use Fisdap\Fractal\ResponseHelpers;
use Illuminate\Http\JsonResponse;
use League\Fractal\Manager;
use Swagger\Annotations as SWG;
use Illuminate\Http\Response as HttpResponse;

/**
 * Handles HTTP transport for user/role (context) permission-related routes
 *
 * @package Fisdap\Api\Users\UserContexts\Permissions
 */
final class PermissionsController extends Controller
{
    use ResponseHelpers;


    /**
     * @var FindsPermissions
     */
    private $finder;


    /**
     * @param FindsPermissions      $finder
     * @param Manager               $fractal
     * @param PermissionTransformer $transformer
     */
    public function __construct(FindsPermissions $finder, Manager $fractal, PermissionTransformer $transformer)
    {
        $this->finder = $finder;
        $this->fractal = $fractal;
        $this->transformer = $transformer;
    }


    /**
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     tags={"Users"},
     *     path="/users/contexts/permissions",
     *     summary="Get a list of user context permissions with their categories",
     *     description="Get a list of user context permissions with their categories",
     *     @SWG\Response(response="200", description="A list of permissions")
     * )
     */
    public function index()
    {
        return $this->respondWithCollection($this->finder->all(), $this->transformer);
    }


    /**
     * @param int $permissionId
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     tags={"Users"},
     *     path="/users/contexts/permissions/{permissionId}",
     *     summary="Get a user context permission by ID",
     *     description="Get a user context permission by ID",
     *     @SWG\Parameter(name="permissionId", in="path", required=true, type="integer"),
     *     @SWG\Response(response="200", description="A user context permission")
     * )
     */
    public function show($permissionId)
    {
        return $this->respondWithItem($this->finder->one($permissionId), $this->transformer);
    }


    /**
     * @param int $instructorId
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     tags={"Instructors"},
     *     path="/instructors/{instructorId}/permissions",
     *     summary="List all permissions for an instructor, based on their ''Student Groups'' a.k.a. Class Sections",
     *     description="List all permissions for an instructor, based on their ''Student Groups''
            a.k.a. Class Sections",
     *     @SWG\Parameter(name="instructorId", in="path", required=true, type="integer"),
     *     @SWG\Response(response="200", description="A list of permissions"),
     *     @SWG\Response(response="403", description="No permissions found for instructor")
     * )
     */
    public function getInstructorPermissions($instructorId)
    {
        return $this->respondWithCollection($this->finder->getInstructorPermissions($instructorId), $this->transformer);
    }


    /**
     * @param int $userId
     * @param int $userContextId
     * @param UserRepository $userRepository
     * @param UserContextRepository $userContextRepository
     *
     * @return JsonResponse
     *
     * @SWG\Post(
     *     tags={"Users"},
     *     path="/users/{userId}/switch-context/{userContextId}",
     *     summary="Switch User context",
     *     description="Switch User context",
     *     @SWG\Parameter(name="userId", in="path", required=true, type="integer"),
     *     @SWG\Parameter(name="userContextId", in="path", required=true, type="integer"),
     *     @SWG\Response(response="200", description="Context switched")
     * )
     */
    public function switchContext($userId, $userContextId, UserRepository $userRepository, UserContextRepository $userContextRepository)
    {
        /** @var User $user */
        $user = $userRepository->getOneById($userId);
        if (empty($user)) {
            throw new ResourceNotFound("No User found with id '$userId'");
        }

        /** @var UserContext $context */
        $context = $userContextRepository->getOneById($userContextId);
        if (empty($context)) {
            throw new ResourceNotFound("No User Context found with id '$userContextId'");
        }

        if ($user->setCurrentUserContext($context)) {
            $this->setStatusCode(HttpResponse::HTTP_OK);
            $user->save();
        } else {
            $this->setStatusCode(HttpResponse::HTTP_BAD_REQUEST);
        }

        return $this->respondWithArray([]);
    }
}
