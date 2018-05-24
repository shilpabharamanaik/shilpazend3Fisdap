<?php namespace Fisdap\Api\Http\Batching;

use Fisdap\Api\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Swagger\Annotations as SWG;


/**
 * Handles HTTP transport for batch requests
 *
 * @todo - leverage https://github.com/dingo/api ?
 *
 * @package Fisdap\Api\Http\Batching
 * @author Ben Getsug <bgetsug@fisdap.net>
 */
class BatchController extends Controller
{
    /**
     * @var Request
     */
    protected $request;


    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }


    /**
     * Internally processes a set of GET requests and returns a single JSON response
     *
     * @return JsonResponse
     *
     * @see http://blog.antoine-augusti.fr/2014/04/laravel-calling-your-api/
     * @see https://github.com/teepluss/laravel4-api
     *
     * @SWG\Definition(definition="GetRequestCollection", properties={
     *     @SWG\Property(property="requests", type="array", items=@SWG\Items(ref="#/definitions/GetRequest"), required={"requests"})
     * })
     *
     * @SWG\Definition(definition="GetRequest", properties={
     *     @SWG\Property(property="name", type="string", example="shifts"),
     *     @SWG\Property(property="uri", type="string", example="students/148286/shifts"),
     *     @SWG\Property(property="params", ref="#/definitions/RequestParams")
     * })
     *
     * @SWG\Definition(definition="RequestParams",
     *     properties={
     *      @SWG\Property(property="key", type="string", example="value"),
     *      @SWG\Property(property="anotherKey", type="string", example="anotherValue")
     *     },
     * )
     *
     * @SWG\Post(
     *     tags={"Batch Requests"},
     *     path="/batch/get",
     *     description="Process a batch of GET requests",
     *     @SWG\Parameter(name="requests", in="body", schema=@SWG\Schema(ref="#/definitions/GetRequestCollection")),
     *     @SWG\Response(
     *      response="200",
     *      description="A list GET responses"
     *     )
     * )
     */
    public function processGetRequests()
    {
        $requests = $this->request->get('requests');
        
        $results = [];

        // todo - eventually use Gearman to enable parallel execution of these
        foreach($requests as $request){

            $name = $request['name'];
            $uri = $request['uri'];

            $params = isset($request['params']) ? $request['params'] : [];

            /** @var Request $internalRequest */
            $internalRequest = $this->request->create($uri, 'GET', $params);
            $this->request->replace($internalRequest->input());
            $response = static::$router->dispatch($internalRequest);

            $content = $response->getContent();

            $results[$name] = json_decode($content);
        }
        
        return new JsonResponse(['data' => $results]);
    }

    /**
     * Internally processes a set of DELETE requests and returns a single JSON response
     *
     * @return JsonResponse
     *
     * @see http://blog.antoine-augusti.fr/2014/04/laravel-calling-your-api/
     * @see https://github.com/teepluss/laravel4-api
     *
     * @SWG\Definition(definition="DeleteRequestCollection", properties={
     *     @SWG\Property(property="requests", type="array", items=@SWG\Items(ref="#/definitions/DeleteRequest"), required={"requests"})
     * })
     *
     * @SWG\Definition(definition="DeleteRequest", properties={
     *     @SWG\Property(property="name", type="string", example="shifts"),
     *     @SWG\Property(property="uri", type="string", example="shifts/patients/14033228"),
     *     @SWG\Property(property="params", ref="#/definitions/RequestParams")
     * })
     *
     * @SWG\Post(
     *     tags={"Batch Requests"},
     *     path="/batch/delete",
     *     description="Process a batch of DELETE requests",
     *     @SWG\Parameter(name="requests", in="body", schema=@SWG\Schema(ref="#/definitions/DeleteRequestCollection")),
     *     @SWG\Response(
     *      response="200",
     *      description="A list Delete responses"
     *     )
     * )
     */
    public function processDeleteRequests()
    {
        $requests = $this->request->get('requests');

        $results = [];

        // todo - eventually use Gearman to enable parallel execution of these
        foreach($requests as $request){

            $name = $request['name'];
            $uri = $request['uri'];

            $params = isset($request['params']) ? $request['params'] : [];

            /** @var Request $internalRequest */
            $internalRequest = $this->request->create($uri, 'DELETE', $params);
            $this->request->replace($internalRequest->input());
            $response = static::$router->dispatch($internalRequest);

            $content = $response->getContent();

            $results[$name] = json_decode($content);
        }

        return new JsonResponse(['data' => $results]);
    }
}