<?php namespace Fisdap\Api\VerificationTypes\Http;

use Fisdap\Api\Http\Controllers\Controller;
use Doctrine\ORM\EntityManagerInterface;
use Fisdap\Api\Transformation\EnumeratedTransformer;
use Fisdap\Entity\VerificationType;
use Fisdap\Fractal\ResponseHelpers;
use League\Fractal\Manager;

/**
 * Class VerificationTypeController
 * @package Fisdap\Api\VerificationType\Http
 * @author  Isaac White <iwhite@fisdap.net>
 */
final class VerificationTypesController extends Controller
{
    use ResponseHelpers;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(Manager $fractal, EnumeratedTransformer $transformer, EntityManagerInterface $em)
    {
        $this->fractal     = $fractal;
        $this->transformer = $transformer;
        $this->em          = $em;
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     tags={"VerificationType"},
     *     path="/verification-types",
     *     summary="Return a list of all verification-type types",
     *     description="Return a list of all verification-type types. The Response Model show one such record.",
     *     @SWG\Response(response=200, description="A list of verification-type types. The Response Model show one such record.")
     * )
     */
    public function index()
    {
        return $this->respondWithCollection($this->em->getRepository(VerificationType::class)->findAll(), $this->transformer);
    }
}
