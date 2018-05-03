<?php namespace Fisdap\Api\Shifts\Http;

use Carbon\Carbon;
use Fisdap\Api\Http\Controllers\Controller;
use Fisdap\Api\Shifts\Finder\ShiftsFinder;
use Fisdap\Api\Shifts\Jobs\CreateShift;
use Illuminate\Contracts\Bus\Dispatcher as BusDispatcher;
use Illuminate\Http\Response as HttpResponse;
use Fisdap\Api\Shifts\Finder\FindsShifts;
use Fisdap\Api\Shifts\Jobs\UpdateShift;
use Fisdap\Api\Shifts\Queries\ShiftQueryParameters;
use Fisdap\Api\Shifts\Transformation\ShiftTransformer;
use Fisdap\Fractal\CommonInputParameters;
use Fisdap\Fractal\ResponseHelpers;
use Doctrine\ORM\EntityManagerInterface;
use Request;
use League\Fractal\Manager;
use Swagger\Annotations as SWG;

/**
 * Handles HTTP transport and data transformation for shift-related routes
 *
 * @package Fisdap\Api\Shifts
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
final class ShiftsController extends Controller
{
    use ResponseHelpers, CommonInputParameters;

    /**
     * @var FindsShifts
     */
    private $finder;


    /**
     * @var EntityManagerInterface
     */
    private $em;


    /**
     * @param ShiftsFinder $finder
     * @param Manager $fractal
     * @param EntityManagerInterface $em
     * @param ShiftTransformer $transformer
     */
    public function __construct(
        ShiftsFinder $finder,
        Manager $fractal,
        EntityManagerInterface $em,
        ShiftTransformer $transformer
    ) {
        $this->finder = $finder;
        $this->fractal = $fractal;
        $this->em = $em;
        $this->transformer = $transformer;
    }


    /**
     * @param $studentId
     *
     * @param CreateShift $createShiftJob
     * @param BusDispatcher $busDispatcher
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Post(
     *     tags={"Students"},
     *     path="/students/{studentId}/shifts",
     *     summary="Creates a shift",
     *     description="Create a shift for the provided student",
     *     @SWG\Parameter(name="studentId", in="path", required=true, type="integer", default=148286),
     *     @SWG\Parameter(
     *      name="Shift", in="body", required=true, schema=@SWG\Schema(ref="#/definitions/Shift")
     *     ),
     *     @SWG\Response(response="201", description="Shift created",
     *          schema=@SWG\Schema(
     *              properties={
     *                  @SWG\Property(
     *                      property="data", type="array", items=@SWG\Schema(ref="#/definitions/Shift"))
     *      }))
     * )
     */
    public function createShift($studentId, CreateShift $createShiftJob, BusDispatcher $busDispatcher)
    {
        $createShiftJob->setStudentId($studentId);
        $shift = $busDispatcher->dispatch($createShiftJob);

        $this->setStatusCode(HttpResponse::HTTP_CREATED);

        return $this->respondWithItem($shift, $this->transformer);
    }

    /**
     * Updates a shift
     *
     * @param int $shiftId
     * @param UpdateShift $updateShiftJob
     * @param BusDispatcher $busDispatcher
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Patch(
     *     tags={"Shifts"},
     *     path="/shifts/{shiftId}",
     *     summary="Update a shift",
     *     description="Update a shift",
     *     @SWG\Parameter(name="shiftId", in="path", required=true, type="integer"),
     *     @SWG\Parameter(
     *      name="Shift", in="body", required=true, schema=@SWG\Schema(ref="#/definitions/Shift")
     *     ),
     *     @SWG\Response(response="200", description="Updated shift"),
     * )
     */
    public function updateShift($shiftId, UpdateShift $updateShiftJob, BusDispatcher $busDispatcher)
    {
        $updateShiftJob->setId($shiftId);
        $shift = $busDispatcher->dispatch($updateShiftJob);

        $this->setStatusCode(HttpResponse::HTTP_OK);

        return $this->respondWithItem($shift, $this->transformer);
    }

    /**
     * Retrieve the specified resource from storage
     *
     * @param  string $id
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     tags={"Shifts"},
     *     path="/shifts/{shiftId}",
     *     summary="Get a shift by ID",
     *     description="Get a shift by ID",
     *     @SWG\Parameter(name="shiftId", in="path", required=true, type="integer"),
     *     @SWG\Parameter(name="includes", in="query", type="array", items=@SWG\Items(type="string"),
     *     collectionFormat="csv", enum={"site","base","patients","attachments"}),
     *     @SWG\Parameter(name="includeIds", in="query", type="array", items=@SWG\Items(type="string"),
     *     collectionFormat="csv", enum={"site","base","patients","attachments"}),
     *     @SWG\Response(response="200", description="A shift"),
     * )
     */
    public function show($id)
    {
        return $this->respondWithItem(
            $this->finder->getById($id, $this->initAndGetIncludes(), $this->getIncludeIds()),
            $this->transformer
        );
    }

    /**
     * Get a shift list for a student
     *
     * @param int $studentId
     *
     * @return \Illuminate\Http\JsonResponse
     * @todo support querying by modified dates?
     *
     * @SWG\Get(
     *     tags={"Students"},
     *     path="/students/{studentId}/shifts",
     *     summary="Get a list of all shifts for a student",
     *     description="Get a list of all shifts for a student",
     *     @SWG\Parameter(name="studentId", in="path", required=true, type="integer"),
     *     @SWG\Parameter(name="includes", in="query", type="array", items=@SWG\Items(type="string"),
     *     collectionFormat="csv", enum={"site","base","patients","attachments"}),
     *     @SWG\Parameter(name="includeIds", in="query", type="array", items=@SWG\Items(type="string"),
     *     collectionFormat="csv", enum={"site","base","patients","attachments"}),
     *     @SWG\Parameter(name="states", in="query", type="array", items=@SWG\Items(type="string"),
     *     collectionFormat="csv", enum={"locked","late","past","future"}),
     *     @SWG\Parameter(name="types", in="query", type="array", items=@SWG\Items(type="string"),
     *     collectionFormat="csv", enum={"clinical","field","lab"}),
     *     @SWG\Parameter(name="startingBetween", in="query", type="string",
             description="A pair of comma-separated PHP DateTime-compatible strings representing the on-or-after date
             and the on-or-before date (i.e. 'last week,today' or '2014-10-28,2014-11-05').
             See http://php.net/manual/en/datetime.formats.relative.php"
     *     ),
     *     @SWG\Parameter(name="firstResult", in="query", type="integer", description="Result offset for pagination.
            Use 0 for no offset."),
     *     @SWG\Parameter(name="maxResults", in="query", type="integer", description="Max results limit for
            pagination"),
     *     @SWG\Parameter(name="includeLocked", in="query", type="boolean", description="Whether or not to include
            locked shifts. Locked shift are included by default."),
     *     @SWG\Parameter(name="dateFrom", in="query", type="string", description="UTC timestamp for returning only
            records that have been modified since provided timestamp."),
     *     @SWG\Response(response="200", description="A list of shifts"),
     * )
     */
    public function getStudentShifts($studentId)
    {
        $queryParams = new ShiftQueryParameters();
        $queryParams->setStudentIds([$studentId])
            ->setAssociations($this->initAndGetIncludes())
            ->setAssociationIds($this->getIncludeIds())
            ->setStartingBetween($this->getStartingBetween())
            ->setStates($this->getStates())
            ->setType($this->getType())
            ->setFirstResult($this->getFirstResult())
            ->setMaxResults($this->getMaxResults())
            ->setIncludeLocked(Request::get('includeLocked'));
        $queryParams->setDateFrom(Request::get('dateFrom'));

        return $this->respondWithCollection($this->finder->getStudentShifts($queryParams), $this->transformer);
    }


    /**
     * Get a shift list for a program
     *
     * @param int $programId
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     tags={"Programs"},
     *     path="/programs/{programId}/shifts",
     *     summary="Get a list of all shifts for a program",
     *     description="Get a list of all shifts for a program",
     *     @SWG\Parameter(name="programId", in="path", required=true, type="integer"),
     *     @SWG\Parameter(name="includes", in="query", type="array", items=@SWG\Items(type="string"),
     *     collectionFormat="csv", enum={"site","base","patients","attachments"}),
     *     @SWG\Parameter(name="includeIds", in="query", type="array", items=@SWG\Items(type="string"),
     *     collectionFormat="csv", enum={"site","base","patients","attachments"}),
     *     @SWG\Parameter(name="states", in="query", type="array", items=@SWG\Items(type="string"),
     *     collectionFormat="csv", enum={"locked","late","past","future"}),
     *     @SWG\Parameter(name="types", in="query", type="array", items=@SWG\Items(type="string"),
     *     collectionFormat="csv", enum={"clinical","field","lab"}),
     *     @SWG\Parameter(name="startingBetween", in="query", type="string",
            description="A pair of comma-separated PHP DateTime-compatible strings representing the on-or-after date
            and the on-or-before date (i.e. 'last week,today' or '2014-10-28,2014-11-05').
            See http://php.net/manual/en/datetime.formats.relative.php"
     *     ),
     *     @SWG\Parameter(name="firstResult", in="query", type="integer", description="Result offset for pagination.
            Use 0 for no offset."),
     *     @SWG\Parameter(name="maxResults", in="query", type="integer", description="Max results limit for
            pagination"),
     *     @SWG\Parameter(name="includeLocked", in="query", type="boolean", description="Whether or not to include
            locked shifts. Locked shift are included by default."),
     *     @SWG\Parameter(name="dateFrom", in="query", type="string", description="UTC timestamp for returning only
            records that have been modified since provided timestamp."),
     *     @SWG\Response(response="200", description="A list of shifts"),
     * )
     */
    public function getProgramShifts($programId)
    {
        $queryParams = new ShiftQueryParameters();
        $queryParams->setProgramIds([$programId])
            ->setAssociations($this->initAndGetIncludes())
            ->setAssociationIds($this->getIncludeIds())
            ->setStartingBetween($this->getStartingBetween())
            ->setStates($this->getStates())
            ->setType($this->getType())
            ->setFirstResult($this->getFirstResult())
            ->setMaxResults($this->getMaxResults())
            ->setIncludeLocked(Request::get('includeLocked'));
        $queryParams->setDateFrom(Request::get('dateFrom'));

        return $this->respondWithCollection($this->finder->getProgramShifts($queryParams), $this->transformer);
    }


    /**
     * Get a shift list for an instructor based on their class sections / student groups
     *
     * @param int $instructorId
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     tags={"Instructors"},
     *     path="/instructors/{instructorId}/shifts",
     *     summary="List all shifts for an instructor, based on their ''Student Groups'' a.k.a. Class Sections",
     *     description="List all shifts for an instructor, based on their ''Student Groups'' a.k.a. Class Sections",
     *     @SWG\Parameter(name="instructorId", in="path", required=true, type="integer"),
     *     @SWG\Parameter(name="includes", in="query", type="array", items=@SWG\Items(type="string"),
     *     collectionFormat="csv", enum={"site","base","patients","attachments"}),
     *     @SWG\Parameter(name="includeIds", in="query", type="array", items=@SWG\Items(type="string"),
     *     collectionFormat="csv", enum={"site","base","patients","attachments"}),
     *     @SWG\Parameter(name="states", in="query", type="array", items=@SWG\Items(type="string"),
     *     collectionFormat="csv", enum={"locked","late","past","future"}),
     *     @SWG\Parameter(name="types", in="query", type="array", items=@SWG\Items(type="string"),
     *     collectionFormat="csv", enum={"clinical","field","lab"}),
     *     @SWG\Parameter(name="startingBetween", in="query", type="string",
            description="A pair of comma-separated PHP DateTime-compatible strings representing the on-or-after date
            and the on-or-before date (i.e. 'last week,today' or '2014-10-28,2014-11-05').
            See http://php.net/manual/en/datetime.formats.relative.php"
     *     ),
     *     @SWG\Parameter(name="firstResult", in="query", type="integer", description="Result offset for pagination.
            Use 0 for no offset."),
     *     @SWG\Parameter(name="maxResults", in="query", type="integer", description="Max results limit for
            pagination"),
     *     @SWG\Parameter(name="includeLocked", in="query", type="boolean", description="Whether or not to include
            locked shifts. Locked shift are included by default."),
     *     @SWG\Parameter(name="dateFrom", in="query", type="string", description="UTC timestamp for returning only
            records that have been modified since provided timestamp."),
     *     @SWG\Response(response="200", description="A list of shifts"),
     * )
     */
    public function getInstructorShifts($instructorId)
    {
        $queryParams = new ShiftQueryParameters();
        $queryParams->setInstructorIds([$instructorId])
            ->setAssociations($this->initAndGetIncludes())
            ->setAssociationIds($this->getIncludeIds())
            ->setStartingBetween($this->getStartingBetween())
            ->setStates($this->getStates())
            ->setType($this->getType())
            ->setFirstResult($this->getFirstResult())
            ->setMaxResults($this->getMaxResults())
            ->setIncludeLocked(Request::get('includeLocked'));
        $queryParams->setDateFrom(Request::get('dateFrom'));

        return $this->respondWithCollection($this->finder->getInstructorShifts($queryParams), $this->transformer);
    }


    /**
     * Parameter for querying shifts during a time period
     *
     * @return Carbon[]
     */
    private function getStartingBetween()
    {
        $startingBetween = null;

        if (Request::has('startingBetween')) {
            $dateTimes = explode(',', Request::get('startingBetween'));
            $startingBetween[0] = Carbon::parse($dateTimes[0]);
            $startingBetween[1] = Carbon::parse($dateTimes[1]);
        }

        return $startingBetween;
    }


    /**
     * Parameter for querying shifts by their state
     *
     * @return array
     */
    private function getStates()
    {
        return Request::has('states') ? explode(',', Request::get('states')) : null;
    }


    /**
     * Parameter for querying shifts by type
     *
     * @return string
     */
    private function getType()
    {
        return Request::has('type') ? (string) Request::get('type') : null;
    }
}
