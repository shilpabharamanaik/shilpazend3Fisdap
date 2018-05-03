<?php namespace Fisdap\Api\Contact\Http;

use Fisdap\Api\Contact\Jobs\SendSupportEmail;
use Fisdap\Api\Transformation\EnumeratedTransformer;
use Fisdap\Fractal\ResponseHelpers;
use Illuminate\Contracts\Bus\Dispatcher as BusDispatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Routing\Controller;
use League\Fractal\Manager;

/**
 * Class ContactsController
 * @package Fisdap\Api\Contact\Http
 * @author  Isaac White <iwhite@fisdap.net>
 */
final class ContactsController extends Controller
{
    use ResponseHelpers;

    /**
     * ContactsController constructor.
     * @param Manager $fractal
     * @param EnumeratedTransformer $transformer
     */
    public function __construct(Manager $fractal, EnumeratedTransformer $transformer)
    {
        $this->fractal = $fractal;
        $this->transformer = $transformer;
    }

    /**
     * @param SendSupportEmail $supportEmail
     * @param BusDispatcher $busDispatcher
     *
     * @return JsonResponse
     *
     * @SWG\Post(
     *     tags={"Contact Us"},
     *     path="/contact-us",
     *     summary="Send an email to support",
     *     description="Send an email to support",
     *     @SWG\Parameter(
     *      name="Contact", in="body", required=true, schema=@SWG\Schema(ref="#/definitions/Contact-Us")
     *     ),
     *     @SWG\Response(
     *      response="201",
     *      description="Confirmation that an email is queued")
     * )
     */
    public function contactUs(SendSupportEmail $supportEmail, BusDispatcher $busDispatcher)
    {
        $support = $busDispatcher->dispatch($supportEmail);
        
        $this->setStatusCode(HttpResponse::HTTP_CREATED);
        
        return $this->respondWithItem($support, $this->transformer);
    }
}
