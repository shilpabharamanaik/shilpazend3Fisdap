<?php namespace Fisdap\Api\Users\Http;

use Fisdap\Api\Http\Controllers\Controller;
use Fisdap\Api\Users\Finder\FindsUsers;
use Fisdap\Api\Users\Jobs\CreatePasswordReset;
use Fisdap\Api\Users\Jobs\CreateUser;
use Fisdap\Api\Users\Queries\InstructorStudentQueryParameters;
use Fisdap\Api\Users\Queries\ProgramStudentQueryParameters;
use Fisdap\Api\Users\UserContexts\Queries\StudentQueryParameters;
use Fisdap\Api\Users\UserTransformer;
use Fisdap\Entity\PasswordReset;
use Fisdap\Fractal\CommonInputParameters;
use Fisdap\Fractal\ResponseHelpers;
use Illuminate\Http\JsonResponse;
use Request;
use League\Fractal\Manager;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Contracts\Bus\Dispatcher as BusDispatcher;

/**
 * Handles HTTP transport and data transformation for user-related routes
 *
 * @package Fisdap\Api\Users
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
final class UsersController extends Controller
{
    use ResponseHelpers, CommonInputParameters;


    /**
     * @var FindsUsers
     */
    private $finder;


    /**
     * @param FindsUsers $finder
     * @param Manager $fractal
     * @param UserTransformer $transformer
     */
    public function __construct(FindsUsers $finder, Manager $fractal, UserTransformer $transformer)
    {
        $this->finder = $finder;
        $this->fractal = $fractal;
        $this->transformer = $transformer;
    }


    /**
     * Retrieve the specified resource from storage
     *
     * @param  string $id
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     tags={"Users"},
     *     path="/users/{userId}",
     *     summary="Get a user by ID",
     *     description="Get a user by ID",
     *     @SWG\Parameter(name="userId", in="path", required=true, type="integer"),
     *     @SWG\Response(response="200", description="A user")
     * )
     */
    public function show($id)
    {
        return $this->respondWithItem(
            $this->finder->findById(
                $id,
                $this->initAndGetIncludes(),
                $this->getIncludeIds(),
                true
            ),
            $this->transformer
        );
    }


    /**
     * @param CreateUser        $createUserJob
     * @param BusDispatcher     $busDispatcher
     *
     * @return JsonResponse
     *
     * @SWG\Post(
     *     tags={"Users"},
     *     path="/users",
     *     summary="Create a user",
     *     description="Create a user",
     *     @SWG\Parameter(
     *      name="User", in="body", required=true, schema=@SWG\Schema(ref="#/definitions/User")
     *     ),
     *     @SWG\Response(
     *      response="201",
     *      description="A created user")
     * )
     */
    public function store(CreateUser $createUserJob, BusDispatcher $busDispatcher)
    {
        $user = $busDispatcher->dispatch($createUserJob);

        $this->setStatusCode(HttpResponse::HTTP_CREATED);

        return $this->respondWithItem($user, $this->transformer);
    }


    /**
     * @param int $programId
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     tags={"Programs"},
     *     path="/programs/{programId}/students",
     *     summary="Get a list of (partial) users with the 'student' role for a program",
     *     description="Get a list of (partial) users with the 'student' role for a program",
     *     @SWG\Parameter(name="programId", in="path", required=true, type="integer"),
     *     @SWG\Parameter(name="dateFrom", in="query", type="string", description="UTC timestamp for returning only
            records that have been modified since provided timestamp."),
     *     @SWG\Response(response="200", description="A list of users")
     * )
     */
    public function getProgramStudents($programId)
    {
        $queryParams = new ProgramStudentQueryParameters();
        $queryParams->setProgramId($programId);
        $queryParams->setDateFrom(Request::get('dateFrom'));

        return $this->respondWithCollection($this->finder->findProgramStudents($queryParams), $this->transformer);
    }


    /**
     * @param int $instructorId
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     tags={"Instructors"},
     *     path="/instructors/{instructorId}/students",
     *     summary="List all students for an instructor, based on their ''Student Groups'' a.k.a. Class Sections",
     *     description="List all students for an instructor, based on their ''Student Groups'' a.k.a. Class Sections",
     *     @SWG\Parameter(name="instructorId", in="path", required=true, type="integer"),
     *     @SWG\Parameter(name="dateFrom", in="query", type="string", description="UTC timestamp for returning only
            records that have been modified since provided timestamp."),
     *     @SWG\Response(response="200", description="A list of users")
     * )
     */
    public function getInstructorStudents($instructorId)
    {
        $queryParams = new InstructorStudentQueryParameters();
        $queryParams->setInstructorId($instructorId);
        $queryParams->setDateFrom(Request::get('dateFrom'));

        return $this->respondWithCollection($this->finder->findInstructorStudents($queryParams), $this->transformer);
    }


    /**
     * @param int $userId
     * @param CreatePasswordReset $createPasswordReset
     * @param BusDispatcher $busDispatcher
     *
     * @return JsonResponse
     *
     * @SWG\Post(
     *     tags={"Users"},
     *     path="/users/{userId}/reset-password",
     *     summary="Reset Password",
     *     description="Reset Password",
     *     @SWG\Parameter(name="$userId", in="path", required=true, type="integer"),
     *     @SWG\Response(
     *      response="201",
     *      description="This creates a new password reset email")
     * )
     */
    public function resetPassword(
        $userId,
        CreatePasswordReset $createPasswordReset,
        BusDispatcher $busDispatcher
    ) {
        $createPasswordReset->setUserId($userId);

        /** @var PasswordReset $passwordReset */
        $busDispatcher->dispatch($createPasswordReset);

        $this->setStatusCode(HttpResponse::HTTP_CREATED);

        return $this->respondWithDataArray(['userId' => $userId]);
    }
}
