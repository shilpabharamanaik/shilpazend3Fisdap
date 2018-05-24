<?php namespace Fisdap\Api\Auth;

use Fisdap\Api\Queries\Exceptions\ResourceNotFound;
use Fisdap\Api\Users\Finder\FindsUsers;
use Fisdap\Logging\ClassLogging;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;


/**
 * User provider for use with OAuth2 server
 *
 * @package Fisdap\Api\Auth
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class OAuth2UserProvider implements UserProvider
{
    use ClassLogging;
    
    
    /**
     * @var FindsUsers
     */
    private $usersFinder;


    /**
     * OAuth2UserProvider constructor.
     *
     * @param FindsUsers $usersFinder
     */
    public function __construct(FindsUsers $usersFinder)
    {
        $this->usersFinder = $usersFinder;
    }


    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed $identifier
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveById($identifier)
    {
        return null;
    }


    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     *
     * @param  mixed  $identifier
     * @param  string $token
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByToken($identifier, $token)
    {
        return null;
    }


    /**
     * Update the "remember me" token for the given user in storage.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @param  string                                     $token
     *
     * @return void
     */
    public function updateRememberToken(Authenticatable $user, $token)
    {
    }


    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array $credentials
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        $user = null;

        $this->classLogDebug('Retrieving User from database...');

        if (isset($credentials['user_id'])) {
            // Use case: Token retrieved with user credentials (a.k.a. "password") grant type
            $user = $this->usersFinder->findById($credentials['user_id']);
        } elseif ($credentials['client_id'] === 'fisdap-members' && $credentials['userContextId'] > 0) {
            /*
             * Use case: Token retrieved with client credentials grant type
             *           and fisdap-members-user-role-id header is provided
             */
            try {
                $user = $this->usersFinder->findByUserContextId($credentials['userContextId']);
            } catch (ResourceNotFound $e) {
                $this->classLogInfo($e->getMessage());
            }
        }

        return $user;
    }


    /**
     * Validate a user against the given credentials.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @param  array                                      $credentials
     *
     * @return bool
     */
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return false;
    }
}