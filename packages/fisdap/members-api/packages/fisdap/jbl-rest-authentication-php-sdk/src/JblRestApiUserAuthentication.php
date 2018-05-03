<?php namespace Fisdap\JBL\Authentication;

use Fisdap\JBL\Authentication\Contracts\EmailPasswordAuthenticator;
use Fisdap\JBL\Authentication\Contracts\HttpClient;
use Fisdap\JBL\Authentication\Contracts\UserByIdAuthenticator;
use Fisdap\JBL\Authentication\Contracts\UserByProductIdAuthenticator;

/**
 * Class JblRestApiUserAuthentication
 *
 * Connect to the JBL user authentication REST API
 *
 * @package Fisdap\JBL\Authentication
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class JblRestApiUserAuthentication implements UserByProductIdAuthenticator, UserByIdAuthenticator, EmailPasswordAuthenticator
{
    const EMAIL_ADDRESS_REQUEST_HEADER = 'emailaddress';
    const PASSWORD_REQUEST_HEADER = 'password';
    const USER_ID_REQUEST_HEADER = 'userId';
    const PRODUCT_ID_REQUEST_HEADER = 'productId';
    const API_LOGIN_URL_PATH = 'api/jbl/login/user';
    const API_LOGIN_USER_BY_ID_URL_PATH = 'api/authenticate/userbyid';
    const API_LOGIN_USER_BY_PRODUCT_ID_URL_PATH = 'api/authenticate/userbyproductid';

    /**
     * Base URL to use to connect to JBL authentication REST api
     *
     * @var string
     */
    private $baseUrl;

    /**
     * @var CurlHttpClient
     */
    private $httpClient;

    /**
     * JblRestApiUserAuthentication constructor.
     *
     * @param string $baseUrl
     * @param HttpClient $httpClient
     */
    public function __construct($baseUrl, HttpClient $httpClient)
    {
        $this->baseUrl = $baseUrl;
        $this->httpClient = $httpClient;
    }

    /**
     * Authenticate a user with JBL by using email and password
     *
     * @param string $email
     * @param string $password
     * @return mixed
     */
    public function authenticateWithEmailPassword($email, $password)
    {
        return $this->httpClient
            ->setHeaders(
                [
                    self::EMAIL_ADDRESS_REQUEST_HEADER => $email,
                    self::PASSWORD_REQUEST_HEADER => $password
                ]
            )
            ->post($this->generateRequestUrl(self::API_LOGIN_URL_PATH));
    }

    /**
     * Authenticate a JBL user by ID
     *
     * @param string $emailOrJblUserId
     * @return mixed
     */
    public function authenticateUserById($emailOrJblUserId)
    {
        return $this->httpClient
            ->setHeaders(
                [
                    self::USER_ID_REQUEST_HEADER => $emailOrJblUserId
                ]
            )
            ->get($this->generateRequestUrl(self::API_LOGIN_USER_BY_ID_URL_PATH));
    }

    /**
     * Authenticate user by product id
     *
     * @param $userProductId
     * @return mixed
     */
    public function authenticateUserByProductId($userProductId)
    {
        return $this->httpClient
            ->setHeaders(
                [
                    self::PRODUCT_ID_REQUEST_HEADER => $userProductId
                ]
            )
            ->get($this->generateRequestUrl(self::API_LOGIN_USER_BY_PRODUCT_ID_URL_PATH));
    }

    /**
     * Get the generated url to use when making request
     *
     * @param string $url
     * @param bool $useBaseUrl
     * @return string
     */
    protected function generateRequestUrl($url, $useBaseUrl = true)
    {
        return ($useBaseUrl) ? $this->baseUrl . $url : $url;
    }
}
