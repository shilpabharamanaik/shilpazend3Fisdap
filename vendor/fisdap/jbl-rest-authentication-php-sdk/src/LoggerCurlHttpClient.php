<?php namespace Fisdap\JBL\Authentication;

use Fisdap\JBL\Authentication\Contracts\HttpClient;
use Psr\Log\LoggerInterface;

/**
 * Class LoggerCurlHttpClient
 *
 * Logger curl http client
 *
 * @package Fisdap\JBL\Authentication
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class LoggerCurlHttpClient implements HttpClient
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * LoggerCurlHttpClient constructor.
     *
     * @param LoggerInterface $logger
     * @param HttpClient $httpClient
     */
    public function __construct(LoggerInterface $logger, HttpClient $httpClient)
    {
        $this->logger = $logger;
        $this->httpClient = $httpClient;
    }

    /**
     * POST a request
     *
     * @param string $endpoint
     * @param null|array $data
     * @return mixed
     */
    public function post($endpoint, $data = null)
    {
        $this->logger->info('Making POST request', ['endpoint' => $endpoint, 'data' => $data]);

        $response = $this->httpClient->post($endpoint, $data);

        $this->logger->info('Response from POST request', ['response' => $response]);

        return $response;
    }

    /**
     * Make a GET request
     *
     * @param string $endpoint
     * @param null|array $data
     * @return mixed
     */
    public function get($endpoint, $data = null)
    {
        $this->logger->info('Making GET request', ['endpoint' => $endpoint, 'data' => $data]);

        $response = $this->httpClient->get($endpoint, $data);

        $this->logger->info('Response from GET request', ['response' => $response]);

        return $response;
    }

    /**
     * Set headers
     *
     * @param array $headers
     * @return HttpClient
     */
    public function setHeaders($headers = [])
    {
        $this->httpClient->setHeaders($headers);
        return $this;
    }
}
