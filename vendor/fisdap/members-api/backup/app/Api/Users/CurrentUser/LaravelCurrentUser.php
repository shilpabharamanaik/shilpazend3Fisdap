<?php namespace Fisdap\Api\Users\CurrentUser;

use Fisdap\Entity\User;
use Illuminate\Auth\AuthManager;

/**
 * CurrentUser adapter for Laravel's AuthManager
 *
 * @package Fisdap\Api\Users\CurrentUser
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class LaravelCurrentUser extends CommonCurrentUser
{
    /**
     * @var AuthManager
     */
    private $auth;
    

    /**
     * LaravelCurrentUser constructor.
     *
     * @param AuthManager   $auth
     */
    public function __construct(AuthManager $auth)
    {
        $this->auth = $auth;
    }


    /**
     * @inheritdoc
     */
    public function user()
    {
        return $this->auth->guard()->user();
    }


    /**
     * @inheritdoc
     */
    public function setUser(User $user)
    {
        $this->auth->guard()->setUser($user);
    }
}
