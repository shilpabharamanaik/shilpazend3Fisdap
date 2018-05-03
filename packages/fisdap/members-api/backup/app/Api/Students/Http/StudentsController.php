<?php namespace Fisdap\Api\Students\Http;

use Fisdap\Api\Http\Controllers\Controller;
use Fisdap\Api\Students\Finder\StudentsFinder;
use Fisdap\Api\Students\Transformation\StudentTransformer;
use Fisdap\Fractal\CommonInputParameters;
use Fisdap\Fractal\ResponseHelpers;
use Doctrine\ORM\EntityManagerInterface;
use League\Fractal\Manager;
use Swagger\Annotations as SWG;

/**
 * Handles HTTP transport and data transformation for student-related routes
 *
 * @package Fisdap\Api\Shifts
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
final class StudentsController extends Controller
{
    use ResponseHelpers, CommonInputParameters;

    private $finder;


    /**
     * @var EntityManagerInterface
     */
    private $em;


    /**
     * @param StudentsFinder $finder
     * @param Manager $fractal
     * @param EntityManagerInterface $em
     * @param StudentTransformer $transformer
     */
    public function __construct(
        StudentsFinder $finder,
        Manager $fractal,
        EntityManagerInterface $em,
        StudentTransformer $transformer
    ) {
        $this->finder = $finder;
        $this->fractal = $fractal;
        $this->em = $em;
        $this->transformer = $transformer;
    }


    /**
     * Retrieve the specified resource from storage
     *
     * @param  string $id
     *
     * @return \Illuminate\Http\JsonResponse
     * @SWG\Get(
     *     tags={"Students"},
     *     path="/students/{studentId}",
     *     summary="Get a student by ID",
     *     description="Get a student by ID",
     *     @SWG\Parameter(name="studentId", in="path", required=true, type="integer"),
     *     @SWG\Response(response="200", description="A student"),
     * )
     */
    public function show($id)
    {
        return $this->respondWithItem(
            $this->finder->getById($id, $this->initAndGetIncludes(), $this->getIncludeIds()),
            $this->transformer
        );
    }
}
