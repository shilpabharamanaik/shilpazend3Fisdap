<?php namespace Fisdap\Api\Commerce\OrderPermissions\Http;

use Fisdap\Api\Commerce\OrderPermissions\OrderPermissionTransformer;
use Fisdap\Api\Http\Controllers\Controller;
use Fisdap\Data\Order\Permission\OrderPermissionRepository;
use Fisdap\Fractal\ResponseHelpers;
use Illuminate\Http\JsonResponse;
use League\Fractal\Manager;
use Swagger\Annotations as SWG;

/**
 * Handles HTTP transport and data transformation for order permission-related routes
 *
 * @package Fisdap\Api\Commerce\OrderPermissions\Http
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class OrderPermissionsController extends Controller
{
    use ResponseHelpers;


    /**
     * @param Manager                       $fractal
     * @param OrderPermissionTransformer    $transformer
     */
    public function __construct(Manager $fractal, OrderPermissionTransformer $transformer)
    {
        $this->fractal = $fractal;
        $this->transformer = $transformer;
    }


    /**
     * @param OrderPermissionRepository $orderPermissionRepository
     *
     * @return JsonResponse
     *
     * @SWG\Get(
     *     tags={"Commerce"},
     *     path="/commerce/orders/permissions",
     *     summary="Get a list of purchasing (''order'') permissions",
     *     description="Get a list of purchasing (''order'') permissions",
     *     @SWG\Response(response="200", description="A list of order permissions"),
     * )
     */
    public function index(OrderPermissionRepository $orderPermissionRepository)
    {
        return $this->respondWithCollection(
            $orderPermissionRepository->findAll(),
            $this->transformer
        );
    }
}
