<?php namespace Fisdap\Api\Products\Http;

use Fisdap\Api\Http\Controllers\Controller;
use Fisdap\Api\Products\Finder\FindsProducts;
use Fisdap\Api\Products\Transformation\ProductTransformer;
use Fisdap\Data\Product\ProductRepository;
use Fisdap\Fractal\ResponseHelpers;
use Illuminate\Http\JsonResponse;
use League\Fractal\Manager;
use Swagger\Annotations as SWG;

/**
 * Class ProductsController
 *
 * @package Fisdap\Api\Products\Http
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class ProductsController extends Controller
{
    use ResponseHelpers;

    
    /**
     * @var FindsProducts
     */
    private $finder;


    /**
     * @param FindsProducts      $finder
     * @param Manager            $fractal
     * @param ProductTransformer $transformer
     */
    public function __construct(FindsProducts $finder, Manager $fractal, ProductTransformer $transformer)
    {
        $this->finder = $finder;
        $this->fractal = $fractal;
        $this->transformer = $transformer;
    }


    /**
     * @param ProductRepository $productRepository
     *
     * @return JsonResponse
     *
     * @SWG\Get(
     *     tags={"Products"},
     *     path="/products",
     *     summary="Get a list of products",
     *     description="Get a list of products",
     *     @SWG\Response(response="200", description="A list of products")
     * )
     */
    public function index(ProductRepository $productRepository)
    {
        return $this->respondWithCollection($productRepository->findAll(), $this->transformer);
    }
}
