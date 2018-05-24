<?php namespace Fisdap\Api\Shifts\PreceptorSignoffs\Http;


use Doctrine\ORM\EntityManagerInterface;
use Fisdap\Api\Http\Controllers\Controller;
use Fisdap\Api\Shifts\PreceptorSignoffs\Transformation\PreceptorSignoffsTransformer;
use Fisdap\Api\Shifts\PreceptorSignoffs\Jobs\ModifySignoff;
use Fisdap\Entity\PreceptorSignoff;
use Fisdap\Fractal\ResponseHelpers;
use Illuminate\Contracts\Bus\Dispatcher as BusDispatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response as HttpResponse;
use League\Fractal\Manager;
use Swagger\Annotations as SWG;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * Class PreceptorSignoffsController
 * @package Fisdap\Api\Shifts\PreceptorSignoffs\Http
 * @author  Isaac White <isaac.white@ascendlearning.com>
 */
final class PreceptorSignoffsController extends Controller
{
    use ResponseHelpers;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * PreceptorSignoffsController constructor.
     * @param Manager $fractal
     * @param PreceptorSignoffsTransformer $transformer
     */
    public function __construct(Manager $fractal, EntityManagerInterface $em, PreceptorSignoffsTransformer $transformer)
    {
        $this->fractal = $fractal;
        $this->transformer = $transformer;
        $this->em = $em;
    }

    /**
     * @param $signoffId
     *
     * @return JsonResponse
     *
     * @SWG\GET(
     *      tags={ "Preceptor Signoffs" },
     *      path="/shifts/patients/signoffs/{signoffId}",
     *      summary="Get the Preceptor Signoff record for a given ID",
     *      description="Get the Preceptor Signoff record for a given ID. By default there are no signoffs, so a signoff must first be created, then this will return a value.",
     *      @SWG\Parameter(name="signoffId", in="path", required=true, type="integer", default=1),
     *      @SWG\Response(response=200, description="Preceptor Signoff Record Returned",
     *          schema=@SWG\Schema(
     *              properties={
     *                  @SWG\Property(
     *                      property="data", type="array", items=@SWG\Schema(ref="#/definitions/PreceptorSignoffs"))
     *      })),
     *      @SWG\Response(response=404, description="Preceptor Signoff Record Not Found",
     *          schema=@SWG\Schema(
     *              properties={
     *                  @SWG\Property(
     *                      property="data", type="array", items="")
     *      }))
     * )
     *
     * @SWG\GET(
     *      tags={ "Preceptor Signoffs" },
     *      path="/shifts/signoffs/{signoffId}",
     *      summary="Get the Preceptor Signoff record for a given ID",
     *      description="Get the Preceptor Signoff record for a given ID. By default there are no signoffs, so a signoff must first be created, then this will return a value.",
     *      @SWG\Parameter(name="signoffId", in="path", required=true, type="integer", default=1),
     *      @SWG\Response(response=200, description="Preceptor Signoff Record Returned",
     *          schema=@SWG\Schema(
     *              properties={
     *                  @SWG\Property(
     *                      property="data", type="array", items=@SWG\Schema(ref="#/definitions/PreceptorSignoffs"))
     *      })),
     *      @SWG\Response(response=404, description="Preceptor Signoff Record Not Found",
     *          schema=@SWG\Schema(
     *              properties={
     *                  @SWG\Property(
     *                      property="data", type="array", items="")
     *      }))
     * )
     */
    public function index($signoffId)
    {
        $response = $this->respondWithItem($this->em->getRepository(PreceptorSignoff::class)->find($signoffId), $this->transformer);

        // Check if we found anything. If not, respond accordingly (404 Not Found)
        if (sizeof($response->getData()->{'data'}) === 0) $response->setStatusCode(HttpResponse::HTTP_NOT_FOUND);

        return $response;
     }

    /**
     * @param $patientId
     * @param ModifySignoff $modifySignoff
     * @param BusDispatcher $busDispatcher
     *
     * @return JsonResponse
     *
     * @SWG\Put(
     *     tags={"Preceptor Signoffs"},
     *     path="/shifts/patients/{patientId}/signoffs",
     *     summary="Updates a Preceptor Signoff record for a patient",
     *     description="This updates the preceptor signoff for a specific patient. To do this for an entire shift use 'shifts/{shiftId}/signoffs'",
     *     @SWG\Parameter(name="patientId", in="path", required=true, type="integer", default="14033228"),
     *     @SWG\Parameter(
     *      name="PreceptorSignoff", in="body", required=true, schema=@SWG\Schema(ref="#/definitions/PreceptorSignoffs")
     *     ),
     *     @SWG\Response(response="200", description="Patient Updated"),
     *     @SWG\Response(response="201", description="Patient Created")
     * )
     */
    public function setPatientSignoff($patientId, ModifySignoff $modifySignoff, BusDispatcher $busDispatcher)
    {
        $modifySignoff->setPatientId($patientId);
        try {
            $signoff = $busDispatcher->dispatch($modifySignoff);

            $this->setStatusCode(HttpResponse::HTTP_OK);
            return $this->respondWithItem($signoff, $this->transformer);
        } catch (ResourceNotFoundException $e) {
            $this->setStatusCode(HttpResponse::HTTP_FORBIDDEN);
            return $this->respondWithArray(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param $shiftId
     * @param ModifySignoff $modifySignoff
     * @param BusDispatcher $busDispatcher
     *
     * @return JsonResponse
     *
     * @SWG\Put(
     *     tags={"Preceptor Signoffs"},
     *     path="/shifts/{shiftId}/signoffs",
     *     summary="Updates a Preceptor Signoff record for all patients associated with the given shift",
     *     description="This updates the preceptor signoff for a specific shift. To do this for a specific patient use 'shifts/patients/{patientId}/signoffs'",
     *     @SWG\Parameter(name="shiftId", in="path", required=true, type="integer", default="4321927"),
     *     @SWG\Parameter(
     *      name="PreceptorSignoff", in="body", required=true, schema=@SWG\Schema(ref="#/definitions/PreceptorSignoffs")
     *     ),
     *     @SWG\Response(response="200", description="Preceptor Signoff Updated"),
     *     @SWG\Response(response="201", description="Preceptor Signoff Created")
     * )
     */
    public function setShiftSignoff($shiftId, ModifySignoff $modifySignoff, BusDispatcher $busDispatcher)
    {
        $modifySignoff->setShiftId($shiftId);
        try {
            $signoff = $busDispatcher->dispatch($modifySignoff);

            $this->setStatusCode(HttpResponse::HTTP_OK);
            return $this->respondWithItem($signoff, $this->transformer);
        } catch (ResourceNotFoundException $e) {
            $this->setStatusCode(HttpResponse::HTTP_FORBIDDEN);
            return $this->respondWithArray(['error' => $e->getMessage()]);
        }

    }
}

