<?php namespace Fisdap\Api\Users\Http\Middleware;

use Fisdap\Api\Users\Http\Exceptions\UserIdMismatch;
use Closure;
use Fisdap\Entity\User;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\Request;

/**
 * Ensures current user ID matches route 'userId' parameter
 *
 * @package Fisdap\Api\Users\Http\Middleware
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class UserIdMatchesRouteId
{
    /**
     * @var User
     */
    private $user;


    /**
     * @param AuthManager  $auth
     */
    public function __construct(AuthManager $auth)
    {
        $this->user = $auth->guard()->user();
    }


    /**
     * @param Request $request
     * @param Closure $next
     */
    public function handle($request, Closure $next)
    {
        $userId = $request->route()->getParameter('userId');

        if ($this->user->getId() != $userId) {
            throw new UserIdMismatch('User\'s ID does not match route {userId} parameter');
        }

        return $next($request);
    }
}
