<?php namespace Fisdap\ErrorHandling;

use Illuminate\Http\Response as HttpResponse;
use Illuminate\Routing\Controller;
use Input;
use Response;

/**
 * This controller is just used for testing error responses
 *
 * @package Fisdap\ErrorHandling
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class TestErrorController extends Controller
{
    /**
     * @var ErrorHandler
     */
    private $errorHandler;


    /**
     * @param ErrorHandler $errorHandler
     */
    public function __construct(ErrorHandler $errorHandler)
    {
        $this->errorHandler = $errorHandler;
    }


    /**
     * @param int $httpCode
     *
     * @return \Response
     */
    public function error($httpCode)
    {
        switch ($httpCode) {
            case HttpResponse::HTTP_OK:
                return Response::json(['data' => "An HTTP response code of '200' is not an error"]);
            case HttpResponse::HTTP_BAD_REQUEST:
                return $this->errorHandler->errorWrongArgs();
                break;
            case HttpResponse::HTTP_UNAUTHORIZED:
                return $this->errorHandler->errorUnauthorized();
                break;
            case HttpResponse::HTTP_FORBIDDEN:
                return $this->errorHandler->errorForbidden();
                break;
            case HttpResponse::HTTP_NOT_FOUND:
                return $this->errorHandler->errorNotFound();
                break;
            case HttpResponse::HTTP_INTERNAL_SERVER_ERROR:
                return $this->errorHandler->errorInternalError();
                break;
            default:
                return $this->errorHandler->errorWrongArgs("Errors with an HTTP response code of '$httpCode' are not handled by this application");
                break;
        }
    }


    public function exception()
    {
        $exceptionClassName = Input::get('e');
        $message = Input::get('m');

        throw new $exceptionClassName($message);
    }


    public function fatal()
    {
        new Foo;
    }
}
