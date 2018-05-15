<?php namespace Fisdap\ErrorHandling;

use Exception;
use Bugsnag\Client as Bugsnag_Client;
use Illuminate\Config\Repository as Config;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Intouch\Newrelic\Newrelic;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Handles error and exception logging, third-party error tracking, and facilitates JSON output
 *
 * @package Fisdap\ErrorHandling
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class ErrorHandler extends Handler
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Guard
     */
    private $auth;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Bugsnag_Client
     */
    private $bugsnag;

    /**
     * @var Newrelic
     */
    private $newrelic;

    /**
     * @inheritdoc
     */
    protected $dontReport = [];


    /**
     * @param Request         $request
     * @param Config          $config
     * @param Guard     $auth
     * @param LoggerInterface $logger
     * @param Bugsnag_Client  $bugsnag
     * @param Newrelic        $newrelic
     */
    public function __construct(
        Request $request,
        Config $config,
        Guard $auth,
        LoggerInterface $logger,
        Bugsnag_Client $bugsnag,
        Newrelic $newrelic
    ) {
        $this->request = $request;
        $this->config = $config;
        $this->auth = $auth;
        $this->logger = $logger;
        $this->bugsnag = $bugsnag;
        $this->newrelic = $newrelic;
        //parent::__construct($logger);
    }


    /**
     * @inheritdoc
     */
    public function report(Exception $e)
    {
        if ($this->shouldReport($e)) {
            if ($e instanceof NotFoundHttpException) {
                $this->logger->debug($e);
            } else {
                $user = $this->auth->user();
                $userId = $user instanceof Authenticatable ? $user->getAuthIdentifier() : null;

                $this->logger->error($e);
                if (\Auth::check()) {
                    Bugsnag::registerCallback(function ($report) {
                        $report->setUser([
                            'id' => Auth::user()->id,
                            'name' => Auth::user()->name . ' ' . Auth::user()->surname,
                            'email' => Auth::user()->email,
                        ]);
                    });
                }

                $this->newrelic->setUserAttributes($userId);
                $this->newrelic->noticeError($e->getMessage(), $e);
            }
        }
    }


    /**
     * @inheritdoc
     */
    public function render($request, Exception $e)
    {
        $data = [
            'error' => [
                'message'        => $e->getMessage(),
                'code'           => $e->getCode(),
                'file'           => $e->getFile(),
                'line'           => $e->getLine(),
                'exceptionClass' => (new \ReflectionClass($e))->getShortName(),
            ]
        ];
        //print_r($e->getFile());
        if (
            in_array('application/json', $this->request->getAcceptableContentTypes()) or
            $this->config->get('error-handling.forceJsonResponse') === true
        ) {
            return new JsonResponse(
                $data,
                $e instanceof HttpException ? $e->getStatusCode() : 500,
                $e instanceof HttpException ? $e->getHeaders() : []
            );
        }

        return parent::render($request, $e);
    }
}
