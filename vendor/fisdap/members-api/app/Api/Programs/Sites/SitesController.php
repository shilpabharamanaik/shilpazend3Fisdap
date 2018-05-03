<?php namespace Fisdap\Api\Programs\Sites;

use Fisdap\Api\Http\Controllers\Controller;
use Fisdap\Api\Programs\Sites\Finder\FindsSites;
use Fisdap\Api\Programs\Sites\Queries\SiteQueryParameters;
use Fisdap\Fractal\CommonInputParameters;
use Fisdap\Fractal\ResponseHelpers;
use League\Fractal\Manager;
use Swagger\Annotations as SWG;

/**
 * Handles HTTP transport and data transformation for site-related routes
 *
 * @package Fisdap\Api\Programs\Sites
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class SitesController extends Controller
{
    use ResponseHelpers, CommonInputParameters;


    /**
     * @var FindsSites
     */
    private $finder;


    /**
     * @param FindsSites      $finder
     * @param Manager         $fractal
     * @param SiteTransformer $transformer
     */
    public function __construct(FindsSites $finder, Manager $fractal, SiteTransformer $transformer)
    {
        $this->finder = $finder;
        $this->fractal = $fractal;
        $this->transformer = $transformer;
    }


    /**
     * Retrieve the specified site from storage
     *
     * @param  int $siteId
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     tags={"Programs"},
     *     path="/programs/sites/{siteId}",
     *     summary="Get a site by ID",
     *     description="Get a site by ID",
     *     @SWG\Parameter(name="siteId", in="path", required=true, type="integer"),
     *     @SWG\Parameter(
     *      name="includes", in="query", type="array", items=@SWG\Items(type="string"), collectionFormat="csv",
     *      enum={"bases"}
     *     ),
     *     @SWG\Parameter(
     *      name="includeIds", in="query", type="array", items=@SWG\Items(type="string"), collectionFormat="csv",
     *      enum={"bases"}
     *     ),
     *     @SWG\Response(response="200", description="A site"),
     * )
     */
    public function show($siteId)
    {
        return $this->respondWithItem(
            $this->finder->findById($siteId, $this->initAndGetIncludes(), $this->getIncludeIds(), true),
            $this->transformer
        );
    }


    /**
     * @param int $programId
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Fisdap\ErrorHandling\Exceptions\InvalidType
     *
     * @SWG\Get(
     *     tags={"Programs"},
     *     path="/programs/{programId}/sites",
     *     summary="List all sites associated with a program",
     *     description="List all sites associated with a program",
     *     @SWG\Parameter(name="programId", in="path", required=true, type="integer"),
     *     @SWG\Parameter(
     *      name="includes", in="query", type="array", items=@SWG\Items(type="string"), collectionFormat="csv",
     *      enum={"bases"}
     *     ),
     *     @SWG\Parameter(
     *      name="includeIds", in="query", type="array", items=@SWG\Items(type="string"), collectionFormat="csv",
     *      enum={"bases"}
     *     ),
     *     @SWG\Parameter(name="firstResult", in="query", type="integer"),
     *     @SWG\Parameter(name="maxResults", in="query", type="integer"),
     *     @SWG\Response(response="200", description="A list of sites"),
     * )
     */
    public function getProgramSites($programId)
    {
        $queryParams = new SiteQueryParameters();
        $queryParams->setProgramIds([$programId])
            ->setAssociations($this->initAndGetIncludes())
            ->setAssociationIds($this->getIncludeIds())
            ->setFirstResult($this->getFirstResult())
            ->setMaxResults($this->getMaxResults());

        return $this->respondWithCollection($this->finder->findProgramSites($queryParams), $this->transformer);
    }


    /**
     * @param int $studentId
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     tags={"Students"},
     *     path="/students/{studentId}/shifts/sites/distinct",
     *     summary="List distinct sites across all a student's shifts",
     *     description="List distinct sites across all a student's shifts",
     *     @SWG\Parameter(name="studentId", in="path", required=true, type="integer"),
     *     @SWG\Response(response="200", description="A list of sites"),
     * )
     */
    public function getDistinctStudentShiftSites($studentId)
    {
        return $this->respondWithCollection(
            $this->finder->findDistinctStudentShiftSites($studentId),
            $this->transformer
        );
    }
}
