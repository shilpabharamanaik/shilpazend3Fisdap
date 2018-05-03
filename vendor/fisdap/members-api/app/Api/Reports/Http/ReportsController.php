<?php namespace Fisdap\Api\Reports\Http;

use Doctrine\ORM\EntityManagerInterface;
use Fisdap\Api\Http\Controllers\Controller;
use Fisdap\Api\Programs\Transformation\ProgramTransformer;
use Fisdap\Api\Reports\Finder\FindsReports;
use Fisdap\Api\Reports\Transformation\ReportTransformer;
use Fisdap\Fractal\CommonInputParameters;
use Fisdap\Fractal\ResponseHelpers;
use League\Fractal\Manager;
use Request;
use Swagger\Annotations as SWG;

/**
 * Handles HTTP transport and data transformation for report-related routes
 *
 * @package Fisdap\Api\Reports\Http
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
final class ReportsController extends Controller
{
    use CommonInputParameters, ResponseHelpers;

    /**
     * @var FindsReports
     */
    private $finder;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @param FindsReports                                           $finder
     * @param Manager                                                $fractal
     * @param EntityManagerInterface $em
     * @param \Fisdap\Api\Programs\Transformation\ProgramTransformer $transformer
     */
    public function __construct(
        FindsReports $finder,
        Manager $fractal,
        EntityManagerInterface $em,
        ProgramTransformer $transformer
    ) {
        $this->finder = $finder;
        $this->fractal = $fractal;
        $this->transformer = $transformer;
        $this->em = $em;
    }


    /**
     * Retrieve the specified resource from storage
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     tags={"Programs"},
     *     path="/programs/{programId}/reports/3c2",
     *     summary="Get 3c2 report data",
     *     description="Get 3c2 report data",
     *     @SWG\Parameter(name="programId", in="path", required=true, type="integer"),
     *     @SWG\Parameter(name="goalSetId", in="query", required=true, type="integer"),
     *     @SWG\Parameter(name="startDate", in="query", type="string", format="date", description="UTC timestamp for start date."),
     *     @SWG\Parameter(name="endDate", in="query", type="string", format="date", description="UTC timestamp for end date."),
     *     @SWG\Parameter(name="siteIds", in="query", type="array", items=@SWG\Items(type="integer"),
     *     collectionFormat="csv", description="A comma separated list of site IDs (GET /programs/{programId}/sites)."),
     *     @SWG\Parameter(name="subjectIds", in="query", type="array", items=@SWG\Items(type="integer"),
     *     collectionFormat="csv", description="A comma separated list of subject IDs (GET /patients/subjects)."),
     *     @SWG\Parameter(name="studentIds", in="query", required=true, type="array", items=@SWG\Items(type="integer"),
     *     collectionFormat="csv", description="A comma separated list of student IDs."),
     *     @SWG\Parameter(name="audited", in="query", required=true, type="boolean", default="false", description="Only include audited shifts."),
     *     @SWG\Response(response="200", description="Success")
     * )
     */
    public function run3c2Report()
    {
        // So, this is going to look a bit odd
        return $this->respondWithArray(
            $this->finder->get3c2ReportData(
                Request::get('goalSetId'),
                Request::get('startDate'),
                Request::get('endDate'),
                Request::get('siteIds'),
                Request::get('subjectIds'),
                Request::get('studentIds'),
                Request::get('audited')
            )
        );
    }
}
