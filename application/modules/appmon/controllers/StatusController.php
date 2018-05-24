<?php
use Fisdap\AppHealthChecks\HealthChecks\ChecksHealth;
use Fisdap\AppHealthChecks\HealthChecks\HealthCheck;
use Illuminate\Config\Repository as Config;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Response as HttpResponse;
use Psr\Log\LoggerInterface;


/**
 * Appmon_StatusController class
 *
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class Appmon_StatusController extends Zend_Controller_Action
{
    const STATUS_SUCCESS = 'OK';
    const STATUS_FAILURE = 'FAILED';


    /**
     * @var Container
     */
    private $container;

    /**
     * @var Config
     */
    private $config;

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
     * @param Container       $container
     * @param Config          $config
     * @param LoggerInterface $logger
     */
    public function __construct(Container $container, Config $config, LoggerInterface $logger)
    {
        $this->container = $container;
        $this->config = $config;
        $this->logger = $logger;
    }


    /**
     * /appmon/status route
     */
    public function indexAction()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $this->view->headTitle('Health Check :: Status');

        /** @noinspection PhpUndefinedMethodInspection */
        $this->_helper->layout()->disableLayout();

        // prevent results from being cached
        $this->_response
            ->setHeader('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT')
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate')
            ->setHeader('Cache-Control', 'post-check=0, pre-check=0', false)
            ->setHeader('Pragma', 'no-cache');

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

        if ($this->_request->getParam('format') == 'json' or $this->_request->getHeader('Accept') == 'application/json') {
            /** @var ChecksHealth $check */
            foreach ($this->completedChecks as $check) {
                $name = $check->getName();

                $data['completedChecks'][$name]['status'] = $check->getStatus();
                $data['completedChecks'][$name]['runTime'] = $check->getRunTime();
                $data['completedChecks'][$name]['error'] = $check->getError();
            }

            $this->_helper->json($data, true);
        } else {

            $data['completedChecks'] = $this->completedChecks;

            $this->view->assign($data);
            $this->_response->setHttpResponseCode($httpStatus);
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