<?php namespace Fisdap\Ascend\Greatplains\Services;

use Fisdap\Ascend\Greatplains\Contracts\Services\ApiClient;
use Psr\Log\LoggerInterface;

/**
 * Class LoggerAscendGreatPlainsHttpGateway
 *
 * Api client with logging capabilities
 *
 * @package Fisdap\Ascend\Greatplains\Services
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class LoggerAscendGreatPlainsHttpGateway implements ApiClient
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ApiClient
     */
    protected $apiClient;

    /**
     * LoggerAscendGreatPlainsHttpGateway constructor.
     *
     * @param LoggerInterface $logger
     * @param ApiClient $apiClient
     */
    public function __construct(LoggerInterface $logger, ApiClient $apiClient)
    {
        $this->logger = $logger;
        $this->apiClient = $apiClient;
    }

    /**
     * Make a GET request and return array response
     *
     * @param string $endpoint
     * @return array
     */
    public function get($endpoint)
    {
        $this->logger->info('Making GET request', ['endpoint' => $endpoint]);

        $response = $this->apiClient->get($endpoint);

        $this->logger->info('Response from GET request', $response);

        return $response;
    }

    /**
     * Make a POST request and return array response
     *
     * @param string $endpoint
     * @param $data
     * @return array
     */
    public function post($endpoint, $data = null)
    {
        $this->logger->info('Making POST request', ['endpoint' => $endpoint, 'data' => $data]);

        $response = $this->apiClient->post($endpoint, $data);

        $this->logger->info('Response from POST request', $response);

        return $response;
    }

    /**
     * Make a PATCH request and return array response
     *
     * @param string $endpoint
     * @param $data
     * @return array
     */
    public function patch($endpoint, $data = null)
    {
        $this->logger->info('Making PATCH request', ['endpoint' => $endpoint, 'data' => $data]);

        $response = $this->apiClient->patch($endpoint, $data);

        $this->logger->info('Response from PATCH request', $response);

        return $response;
    }
}
