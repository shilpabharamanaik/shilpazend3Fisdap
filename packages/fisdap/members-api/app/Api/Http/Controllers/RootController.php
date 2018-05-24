<?php namespace Fisdap\Api\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Route;
use Swagger\Annotations as SWG;


/**
 * Class RootController
 *
 * @package Fisdap\Api\Http\Controllers
 * @author  Ben Getsug <bgetsug@fisdap.net>
 *
 * @SWG\Swagger(
 *     swagger="2.0", basePath="/",
 *  @SWG\Info(
 *      title="Fisdap Members API",
 *      version="2.x",
 *      contact=@SWG\Contact(email="developers@fisdap.net")
 *  )
 * )
 */
class RootController extends Controller
{
    public function __construct()
    {
        $this->middleware('userMustBeStaff', ['only' => ['routes']]);
    }


    public function root()
    {
        return new JsonResponse([
            'data' => 'Fisdap Members API'
        ]);
    }


    public function routes()
    {
        $routes = [];

        /** @var Route $route */
        foreach (self::$router->getRoutes() as $route) {
            $routes[] = [
                'name' => $route->getName(),
                'methods' => $route->getMethods(),
                'uri' => $route->getUri(),
                'middleware' => $route->middleware()
            ];
        }

        return new JsonResponse([
            'data' => [
                'routes' => $routes
            ]
        ]);
    }
}