<?php namespace Fisdap\Api\Products\Http;

use Fisdap\Api\Http\Controllers\Controller;
use Fisdap\Api\Products\Transformation\ProductPackageTransformer;
use Fisdap\Data\Product\Package\ProductPackageRepository;
use Fisdap\Fractal\ResponseHelpers;
use League\Fractal\Manager;
use Swagger\Annotations as SWG;

/**
 * Class ProductsController
 *
 * @package Fisdap\Api\Products\Http
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class ProductPackagesController extends Controller
{
    use ResponseHelpers;
    

    /**
     * @param Manager                   $fractal
     * @param ProductPackageTransformer $transformer
     */
    public function __construct(Manager $fractal, ProductPackageTransformer $transformer)
    {
        $this->fractal = $fractal;
        $this->transformer = $transformer;
    }


    /**
     * @param ProductPackageRepository $productPackageRepository
     *
     * @return \Illuminate\Http\JsonResponse
     * @internal param ProductPackageRepository $productRepository
     *
     * @SWG\Get(
     *     tags={"Products"},
     *     path="/products/packages",
     *     summary="Get a list of product packages",
     *     description="Get a list of product packages",
     *     @SWG\Response(response="200", description="A list of product packages")
     * )
     */
    public function index(ProductPackageRepository $productPackageRepository)
    {
        return $this->respondWithCollection($productPackageRepository->findAll(), $this->transformer);
    }
}
