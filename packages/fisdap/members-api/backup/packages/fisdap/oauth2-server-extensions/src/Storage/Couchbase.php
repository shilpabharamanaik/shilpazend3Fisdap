<?php namespace Fisdap\OAuth\Storage;

use OAuth2\Storage\AccessTokenInterface;
use OAuth2\Storage\AuthorizationCodeInterface;
use OAuth2\Storage\ClientCredentialsInterface;
use OAuth2\Storage\JwtBearerInterface;
use OAuth2\Storage\RefreshTokenInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Couchbase
 *
 * Couchbase storage based on \OAuth2\Storage\MongoDB
 *
 * NOTE: Passwords are stored in plaintext, which is never
 * a good idea.  Be sure to override this for your application
 *
 * @todo    Don't store passwords plaintext for client secrets.
 *
 * @package Fisdap\OAuth\Storage
 * @author  Alex Stevenson
 */
class Couchbase implements
    AuthorizationCodeInterface,
               AccessTokenInterface,
               ClientCredentialsInterface,
               RefreshTokenInterface,
               JwtBearerInterface
{
    protected $db;
    protected $config;

    /**
     * @var LoggerInterface
     */
    protected $logger;


    /**
     * Takes either an already built connection, or an array containing connection information.
     *
     * @param mixed           $connection Array containing the options to connect to the couchbase server with | Instantiated
     *                                    \Couchbase object.
     * @param Array           $config     Array containing the prefixes to use as couchbase ID prefixes for the different
     *                                    elements.  These will override the built in ones, just send in indices you wish to
     *                                    change.
     *
     * @param LoggerInterface $logger
     */
    public function __construct($connection, $config = [], LoggerInterface $logger = null)
    {
        if ($connection instanceof \Couchbase) {
            $this->db = $connection;
        } else {
            if (!is_array($connection)) {
                throw new \InvalidArgumentException(
                    'First argument to \Fisdap\OAuth\Storage\Couchbase must be an instance of \Couchbase or a configuration array'
                );
            }
            global $app;
            echo $app::VERSION;
            exit;
            $this->db = new \Couchbase($connection['hosts'], $connection['username'], $connection['password'], $connection['bucket']);
        }

        $this->config = array_merge(
            [
                'client_prefix'        => 'fisdap_client_',
                'access_token_prefix'  => 'oauth_access_token_',
                'refresh_token_prefix' => 'oauth_refresh_token_',
                'code_prefix'          => 'oauth_authorization_code_',
                'user_prefix'          => 'oauth_user_',
                'jwt_prefix'           => 'oauth_jwt_',
            ],
            $config
        );

        $this->logger = $logger;
    }


    // ClientCredentialsInterface
    /**
     * Checks to see that the client credentials are legitimate.
     *
     * @param String $client_id     Username for the client login
     * @param String $client_secret Password for the client login
     *
     * @return boolean True on success, False on failure.
     */
    public function checkClientCredentials($client_id, $client_secret = null)
    {
        $client = $this->getClientDetails($client_id);

        if ($client) {
            return ($client_id === $client['client_id'] && $client_secret === $client['client_secret']);
        }

        return false;
    }


    /**
     * Checks to see if the client is 'public.'  A public client has no client_secret.
     *
     * @param String $client_id ID of the client to test.
     *
     * @return boolean True if the client is public, false if not.
     */
    public function isPublicClient($client_id)
    {
        $client = $this->getClientDetails($client_id);

        if ($client) {
            return empty($client->client_secret);
        }

        return false;
    }


    // ClientInterface
    /**
     * Fetches information about a specific client.
     *
     * @param String $client_id ID of the client to pull info for.
     *
     * @return array representing the client's information | boolean False if client was not found.
     */
    public function getClientDetails($client_id)
    {
        try {
            $clientDetails = $this->db->get($this->config['client_prefix'] . $client_id);

            return json_decode($clientDetails, true);
        } catch (\Exception $e) {
            $this->logException('warning', $e);
            return false;
        }
    }


    /**
     * Updates a clients details.
     *
     * @param String $client_id     ID of the client to update.
     * @param String $client_secret Updated client password.
     * @param String $redirect_uri  URI to redirect to after successful client login.
     * @param String $grant_types   Space separated list of grants to allow for this client
     * @param String $scope         Scope of the client
     * @param String $user_id       User_id of the client
     *
     * @return boolean True on success
     */
    public function setClientDetails(
        $client_id,
        $client_secret = null,
        $redirect_uri = null,
        $grant_types = null,
        $scope = null,
        $user_id = null
    ) {
        $client = compact('client_id', 'client_secret', 'redirect_uri', 'grant_types', 'scope', 'user_id');
        $client['type'] = 'oauth_client';

        try {
            $this->db->set($this->config['client_prefix'] . $client_id, json_encode($client));
            return true;
        } catch (\Exception $e) {
            $this->logException('critical', $e);
            return false;
        }
    }


    /**
     * Checks to see if grants are defined on a client, and if so, checks to see if the passed in grant is one of those
     * defined grants.
     *
     * @param String $client_id  ID of the client to check
     * @param String $grant_type Name of the grant to test for on the client.
     *
     * @return boolean True on success, false if there were no defined client grants, or the requested grant was not
     *                 found.
     */
    public function checkRestrictedGrantType($client_id, $grant_type)
    {
        $details = $this->getClientDetails($client_id);

        if (isset($details->grant_types)) {
            $grant_types = explode(' ', $details->grant_types);

            return in_array($grant_type, $grant_types);
        }

        // Return true if no grants are defined.  Not restricted.
        return true;
    }


    // AccessTokenInterface
    /**
     * Fetches a stored access token from the server.
     *
     * @param String $access_token An access token that has previously been issued to a user
     *
     * @return Array containing the token information | boolean False if the token was not found.
     */
    public function getAccessToken($access_token)
    {
        try {
            $token = $this->db->get($this->config['access_token_prefix'] . $access_token);
            return json_decode($token, true);
        } catch (\Exception $e) {
            $this->logException('warning', $e);
            return false;
        }
    }


    /**
     * Saves a new access token down.
     *
     * @param String $access_token Token generated from the OAuth library
     * @param String $client_id    Client ID that requested this token
     * @param String $user_id      ID of the user requesting the token (in our case, a Fisdap user ID)
     * @param int    $expires      Unix timestamp when this token should expire
     * @param String $scope        Scope that will be issued for this token.
     *
     * @return boolean True on success.
     */
    public function setAccessToken($access_token, $client_id, $user_id, $expires, $scope = null)
    {
        $accessToken = compact('access_token', 'client_id', 'user_id', 'expires', 'scope');
        $accessToken['type'] = "oauth_access_token";

        $expiryTime = $expires - time();

        try {
            $this->db->set($this->config['access_token_prefix'] . $access_token, json_encode($accessToken), $expiryTime);
            return true;
        } catch (\Exception $e) {
            $this->logException('critical', $e);
            return false;
        }
    }


    // AuthorizationCodeInterface
    /**
     * @param string $code
     *
     * @return bool|mixed
     */
    public function getAuthorizationCode($code)
    {
        try {
            $authCode = $this->db->get($this->config['code_prefix'] . $code);
            return json_decode($authCode, true);
        } catch (\Exception $e) {
            $this->logException('warning', $e);
            return false;
        }
    }


    /**
     * @param string $authorization_code
     * @param string $client_id
     * @param string $user_id
     * @param string $redirect_uri
     * @param int    $expires
     * @param null   $scope
     * @param null   $id_token
     *
     * @return bool
     */
    public function setAuthorizationCode(
        $authorization_code,
        $client_id,
        $user_id,
        $redirect_uri,
        $expires,
        $scope = null,
        $id_token = null
    ) {
        $code = compact('authorization_code', 'client_id', 'user_id', 'redirect_uri', 'expires', 'scope', 'id_token');
        $code['type'] = "oauth_authorization_code";

        $expiryTime = $expires - time();

        try {
            $this->db->set($this->config['code_prefix'] . $authorization_code, json_encode($code), $expiryTime);
            return true;
        } catch (\Exception $e) {
            $this->logException('critical', $e);
            return false;
        }
    }


    /**
     * Manually expires a token.
     *
     * This is not actually called in our code anywhere, but is required by an interface.  We
     * are leveraging couchbase's automagic expiration to delete the tokens.
     *
     * @param string $code
     *
     * @return true
     */
    public function expireAuthorizationCode($code)
    {
        return true;
    }


    // RefreshTokenInterface
    /**
     * Retrieves a saved refresh token from couchbase.
     *
     * @param String $refresh_token A refresh token that has previously been issued to a user.
     *
     * @return \stdClass containing the refresh token information | boolean False if the requested refresh token does
     *                   not exist
     */
    public function getRefreshToken($refresh_token)
    {
        try {
            $token = $this->db->get($this->config['refresh_token_prefix'] . $refresh_token);
            return json_decode($token, true);
        } catch (\Exception $e) {
            $this->logException('warning', $e);
            return false;
        }
    }


    /**
     * Saves a new refresh token down.
     *
     * @param String $refresh_token Token to save (generated by OAuth lib)
     * @param String $client_id     ID of the client that issued this refresh token
     * @param String $user_id       User_id that the refresh token belongs to (a Fisdap user_id in our case)
     * @param int    $expires       Unix timestamp when this token should expire
     * @param String $scope         Scope to be applied to this token
     *
     * @return bool
     */
    public function setRefreshToken($refresh_token, $client_id, $user_id, $expires, $scope = null)
    {
        $refreshToken = compact('refresh_token', 'client_id', 'user_id', 'expires', 'scope');

        $refreshToken['type'] = "oauth_refresh_token";

        $expiryTime = $expires - time();

        try {
            $this->db->set($this->config['refresh_token_prefix'] . $refresh_token, json_encode($refreshToken), $expiryTime);
            return true;
        } catch (\Exception $e) {
            $this->logException('critical', $e);
            return false;
        }
    }


    /**
     * Removes a refresh token from the database.
     *
     * @param String $refresh_token Refresh token to remove
     *
     * @return boolean True on success.
     */
    public function unsetRefreshToken($refresh_token)
    {
        try {
            $this->db->delete($this->config['refresh_token_prefix'] . $refresh_token);
            return true;
        } catch (\Exception $e) {
            $this->logException('critical', $e);
            return false;
        }
    }


    /**
     * Gets the scope currently defined for a client.
     *
     * @param String $client_id ID of the client to get scope information for.
     *
     * @return boolean True on success | boolean False if the client was not found | NULL if the client has no defined
     *                 scope.
     */
    public function getClientScope($client_id)
    {
        $client = $this->getClientDetails($client_id);

        if ($client === false) {
            return false;
        }

        if (isset($client->scope)) {
            return $client->scope;
        }

        return null;
    }


    // These functions are required by various interfaces, but are not currently implemented and will throw an error if accessed.
    public function getClientKey($client_id, $subject)
    {
        throw new \Exception('getClientKey() for the Couchbase driver is currently unimplemented.');
    }


    public function getJti($client_id, $subject, $audience, $expiration, $jti)
    {
        //TODO: Needs couchbase implementation.
        throw new \Exception('getJti() for the Couchbase driver is currently unimplemented.');
    }


    public function setJti($client_id, $subject, $audience, $expiration, $jti)
    {
        //TODO: Needs couchbase implementation.
        throw new \Exception('setJti() for the Couchbase driver is currently unimplemented.');
    }


    /**
     * @param string $severity
     * @param \Exception $exception
     */
    protected function logException($severity, $exception)
    {
        if ($this->logger instanceof LoggerInterface) {
            $this->logger->$severity($exception->getMessage() . ' at ' . $exception->getFile() . ':' . $exception->getLine());
            $this->logger->debug($exception->getTraceAsString());
        }
    }
}
