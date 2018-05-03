<?php namespace Fisdap\JBL\Authentication;

use Curl\Curl;
use Fisdap\JBL\Authentication\Contracts\HttpClient;
use Fisdap\JBL\Authentication\Exceptions\AuthenticationFailedException;
use Fisdap\JBL\Authentication\Exceptions\RequestException;
use Fisdap\JBL\Authentication\Exceptions\ServerException;

/**
 * Class CurlHttpClient
 *
 * Http client wrapper around curl
 *
 * @package Fisdap\JBL\Authentication
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class CurlHttpClient implements HttpClient
{
    /**
     * @var array
     */
    private $headers;

    /**
     * @var Curl
     */
    private $curl;

    /**
     * A standard object with response data from server
     *
     * @var mixed
     */
    private $responseData;

    /**
     * POST a request
     *
     * @param string $endpoint
     * @param null|array $data
     * @return mixed
     */
    public function post($endpoint, $data = null)
    {
        $this->buildHeaders()->buildErrorHandler()->buildSuccessHandler();

        $this->getCurl()->post($endpoint, $data);
        $this->getCurl()->close();

        return $this->getResponseData();
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
        $this->buildHeaders()->buildErrorHandler()->buildSuccessHandler();

        $this->getCurl()->get($endpoint, $data);
        $this->getCurl()->close();

        return $this->getResponseData();
    }

    /**
     * Set curl
     *
     * @param Curl $curl
     * @return $this
     */
    public function setCurl(Curl $curl)
    {
        $this->curl = $curl;
        return $this;
    }

    /**
     * Get the curl object for this request
     *
     * @return Curl
     */
    protected function getCurl()
    {
        if (!$this->curl) {
            $this->curl = new Curl();
        }
        return $this->curl;
    }

    /**
     * Set response data
     *
     * @param $data
     * @return $this
     */
    protected function setResponseData($data)
    {
        $this->responseData = $data;
        return $this;
    }

    /**
     * Get response data
     *
     * @return mixed
     */
    protected function getResponseData()
    {
        return $this->responseData;
    }

    /**
     * Set headers
     *
     * @param array $headers
     * @return $this
     */
    public function setHeaders($headers = [])
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * Build the headers
     *
     * @return $this
     */
    protected function buildHeaders()
    {
        if (!empty($this->headers) && is_array($this->headers)) {
            foreach ($this->headers as $key => $value) {
                $this->getCurl()->setHeader($key, $value);
            }
        }

        return $this;
    }

    /**
     * Build the error handler
     *
     * @return $this
     */
    protected function buildErrorHandler()
    {
        $this->getCurl()->error(function () {
            $this->getCurl()->close();
            $this->throwError($this->getCurl());
        });
        return $this;
    }

    /**
     * Build the success handler
     *
     * @return $this
     */
    protected function buildSuccessHandler()
    {
        $this->getCurl()->success(function () {
            $this->setResponseData($this->getCurl()->response);
        });
        return $this;
    }

    /**
     * Throw an error when the request fails
     *
     * @param Curl $curl
     * @throws AuthenticationFailedException
     * @throws RequestException
     * @throws ServerException
     */
    protected function throwError(Curl $curl)
    {
        switch ($curl->httpStatusCode) {
            case 401:
                throw new AuthenticationFailedException($curl->httpErrorMessage, $curl->httpStatusCode);
                break;
            case 500:
                throw new ServerException($curl->httpErrorMessage, $curl->httpStatusCode);
                break;
            default:
                throw new RequestException($curl->errorMessage, $curl->errorCode);
        }
    }
}
