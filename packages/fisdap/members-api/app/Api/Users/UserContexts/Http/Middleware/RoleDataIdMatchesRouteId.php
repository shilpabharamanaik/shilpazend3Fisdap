<?php namespace Fisdap\Api\Users\UserContexts\Http\Middleware;

use Closure;
use Fisdap\Api\Users\UserContexts\Http\Exceptions\RoleDataIdMismatch;
use Fisdap\Entity\User;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\Request;

/**
 * Ensures current user role data ID matches appropriate route parameter
 *
 * @package Fisdap\Api\Users\UserContexts\Http\Middleware
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class RoleDataIdMatchesRouteId
{
    /**
     * @var User|null
     */
    private $user;


    /**
     * @param AuthManager $auth
     */
    public function __construct(AuthManager $auth)
    {
        $this->user = $auth->guard()->user();
    }


    /**
     * @param Request $request
     * @param Closure $next
     * @param string  $roleName
     *
     * @return mixed
     * @throws \Exception
     */
    public function handle($request, Closure $next, $roleName)
    {
        $RoleDataId = $request->route()->getParameter("{$roleName}Id");

        if ($this->user->context()->getRole()->getName() == $roleName) {
            if ($this->user->context()->getRoleData()->getId() != $RoleDataId) {
                throw new RoleDataIdMismatch("User's $roleName ID does not match route {{$roleName}Id} parameter");
            }
        }

        return $next($request);
    }
}
