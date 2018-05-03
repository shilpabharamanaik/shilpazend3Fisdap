<?php namespace Fisdap\Ascend\Greatplains\Services;

use Fisdap\Ascend\Greatplains\Contracts\Services\ApiClient;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

/**
 * Class AscendGreatPlainsHttpGateway
 *
 * Customer http gateway used to connect to ascend great plains
 *
 * @package Fisdap\Ascend\Greatplains\Services
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class AscendGreatPlainsHttpGateway implements ApiClient
{
    /**
     * The base uri of the api client
     *
     * @var string
     */
    protected $baseUri;

    /**
     * The ascend api key
     *
     * @var string
     */
    protected $apiKey;

    /**
     * The ascend app id
     *
     * @var string
     */
    protected $appId;

    /**
     * The number of seconds to use for timeout
     *
     * @var integer
     */
    protected $timeout;

    /**
     * Whether or not to debug the request
     *
     * @var bool
     */
    protected $debug = false;

    /**
     * Describes the SSL certificate verification behavior of a request.
     *
     * @var bool
     */
    protected $verify;

    /**
     * @var Client
     */
    protected $client;

    /**
     * AscendGreatPlainsHttpGateway constructor.
     *
     * @param $baseUri
     * @param $apiKey
     * @param $appId
     * @param int $timeout
     * @param bool $debug
     * @param bool $verify
     */
    public function __construct($baseUri, $apiKey, $appId, $timeout = 20, $debug = false, $verify = false)
    {
        $this->baseUri = $baseUri;
        $this->apiKey = $apiKey;
        $this->appId = $appId;
        $this->timeout = $timeout;
        $this->debug = $debug;
        $this->verify = $verify;
    }

    /**
     * Make a GET request and return array from json
     *
     * @param $endpoint
     * @return array
     */
    public function get($endpoint)
    {
        $response = $this->getClient('GET', $endpoint)
            ->get($endpoint, ['debug' => $this->debug, 'verify' => $this->verify]);
        return $this->jsonResponse($response);
    }

    /**
     * Make a POST request and return array from json
     *
     * @param $endpoint
     * @param null $data
     * @return array
     */
    public function post($endpoint, $data = null)
    {
        $response = $this->getClient('POST', $endpoint, $data)
            ->post($endpoint, ['body' => $data, 'debug' => $this->debug, 'verify' => $this->verify]);
        return $this->jsonResponse($response);
    }

    /**
     * Make a PATCH request and return array from json
     *
     * @param $endpoint
     * @param null $data
     * @return mixed
     */
    public function patch($endpoint, $data = null)
    {
        $response = $this->getClient('PATCH', $endpoint, $data)
            ->patch($endpoint, ['body' => $data, 'debug' => $this->debug, 'verify' => $this->verify]);
        return $this->jsonResponse($response);
    }

    /**
     * Get the response as json
     *
     * @param ResponseInterface $response
     * @return mixed
     */
    protected function jsonResponse(ResponseInterface $response)
    {
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Set the guzzle client
     *
     * @param Client $client
     * @return $this
     */
    public function setClient(Client $client)
    {
        $this->client = $client;
        return $this;
    }

    /**
     * Get the guzzle client to use for making api request
     *
     * @param string $method
     * @param string $endpoint
     * @param null|string $data
     * @return Client
     */
    protected function getClient($method, $endpoint, $data = null)
    {
        if (!$this->client) {
            return new Client([
                'base_uri'    => $this->baseUri,
                'timeout'     => $this->timeout,
                'http_errors' => true,
                'headers'     => [
                    'Authorization' => $this->getAuthorizationHeader($method, $endpoint, $data),
                    'Accept'        => 'application/json',
                    'Content-Type'  => 'application/json'
                ]
            ]);
        }

        return $this->client;
    }

    /**
     * Build the authorization header
     *
     * @param string $method
     * @param string $endpoint
     * @param null|string $data
     * @return string
     */
    protected function getAuthorizationHeader($method, $endpoint, $data = null)
    {
        $httpMethod = $method;
        $requestUri = strtolower(urlencode($this->baseUri . $endpoint));
        $timestamp = time();
        $nonce = uniqid();
        $content = ($data) ? md5($data) : '';

        $rawSignatureData = $this->appId . $httpMethod . $requestUri . $timestamp . $nonce . $content;

        $signature = hash_hmac('sha256', $rawSignatureData, $this->apiKey);

        return 'amx ' . $this->appId . ':' . $signature . ':' . $nonce . ':' . $timestamp;
    }
}
