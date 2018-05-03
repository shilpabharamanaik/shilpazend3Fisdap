<?php namespace Fisdap\Api\Client\HttpClient;

use Fisdap\Api\Client\Exceptions\MembersRestApiClientError;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;


/**
 * Extension of Guzzle HTTP client
 *
 * @package Fisdap\Api\Client
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class HttpClient extends Client implements HttpClientInterface
{
    /**
     * Overrides default Guzzle request sending
     *
     * Forces response to be returned as object and errors to include message from REST API
     *
     * @param string $method
     * @param null   $uri
     * @param array  $options
     *
     * @return null|object|\object[]
     * @throws MembersRestApiClientError
     */
    public function request($method, $uri = null, array $options = [])
    {
        $returnJsonAsArray = false;

        if (isset($options['responseType'])) {
            if ($options['responseType'] == 'array') {
                $returnJsonAsArray = true;
            }
        }

        try {
            $response = parent::request($method, $uri, $options);
            $jsonObject = json_decode($response->getBody()->getContents(), $returnJsonAsArray);

            if ($jsonObject === null) {
                return null;
            }

            return $returnJsonAsArray ? $jsonObject['data'] : $jsonObject->data;

        } catch (RequestException $e) {
            $errorResponse = json_decode($e->getResponse()->getBody()->getContents(), $returnJsonAsArray);

            $errorMessage = $returnJsonAsArray ? $errorResponse['error']['message'] : $errorResponse->error->message;

            switch ($e->getResponse()->getStatusCode()) {
                case 401:
                    throw new UnauthorizedHttpException($errorMessage);
                    break;
                case 403:
                    throw new AccessDeniedHttpException($errorMessage);
                    break;
                case 404:
                    return null;
                    break;
                default:
                    if (empty($errorMessage)) {
                        $errorMessage = $e->getMessage();
                    }
                    throw new MembersRestApiClientError($errorMessage, $e->getRequest(), $e->getResponse());
                    break;
            }
        }
    }
}