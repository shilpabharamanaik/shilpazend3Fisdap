<?php namespace Fisdap\AppHealthChecks;

use Fisdap\AppHealthChecks\HealthChecks\ChecksHealth;
use Fisdap\AppHealthChecks\HealthChecks\HealthCheck;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Routing\Controller;
use Psr\Log\LoggerInterface;

/**
 * Handles HTTP transport for health check status route
 *
 * @package Fisdap\AppHealthChecks
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class HealthChecksController extends Controller
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
     * @var Container
     */
    private $container;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $completedChecks = [];

    /**
     * @var int
     */
    private $brokenHealthCheckCount = 0;


    /**
     * @param Request         $request
     * @param Config          $config
     * @param Container       $container
     * @param LoggerInterface $logger
     */
    public function __construct(
        Request $request,
        Config $config,
        Container $container,
        LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->config = $config;
        $this->container = $container;
        $this->logger = $logger;
    }


    public function status()
    {
        $this->runChecks();


        // prepare response
        if (HealthCheck::$totalErrors > 0) {
            $httpStatus = HttpResponse::HTTP_INTERNAL_SERVER_ERROR;
        } else {
            $httpStatus = HttpResponse::HTTP_OK;
        }

        $data = [
            'totalRunTime' => HealthCheck::$totalRunTime,
            'brokenHealthCheckCount' => $this->brokenHealthCheckCount,
        ];

        if ($this->request->get('format') == 'json' or $this->request->header('Accept') == 'application/json') {
            /** @var ChecksHealth $check */
            foreach ($this->completedChecks as $check) {
                $name = $check->getName();

                $data['completedChecks'][$name]['status'] = $check->getStatus();
                $data['completedChecks'][$name]['runTime'] = $check->getRunTime();
                $data['completedChecks'][$name]['error'] = $check->getError();
            }

            return new JsonResponse($data, $httpStatus);
        } else {
            $data['completedChecks'] = $this->completedChecks;

            return response()->view('health-checks::status', $data)->setStatusCode($httpStatus);
        }
    }


    private function runChecks()
    {
        // get enabled health checks from config
        $enabledChecks = $this->config->get('health-checks.enabledChecks');

        if (empty($enabledChecks)) {
            return;
        }

        // resolve from IoC and run check()
        foreach ($enabledChecks as $enabledCheck) {
            try {
                /** @var ChecksHealth $check */
                $check = $this->container->make($enabledCheck);

                $check->check();

                $this->completedChecks[] = $check;
            } catch (\Exception $e) {
                $this->brokenHealthCheckCount++;
                $this->logger->critical('Unable to execute health check: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            }
        }
    }
}
