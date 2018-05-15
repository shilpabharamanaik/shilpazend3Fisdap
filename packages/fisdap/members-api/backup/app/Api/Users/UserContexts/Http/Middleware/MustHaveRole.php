<?php namespace Fisdap\Api\Users\UserContexts\Http\Middleware;

use Closure;
use Fisdap\Api\Users\UserContexts\Http\Exceptions\InadequateRole;
use Fisdap\Entity\User;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\Request;

/**
 * Ensures that current user (context) has specific role
 *
 * @package Fisdap\Api\Users\UserContexts\Http\Middleware
 * @author  Ben Getsug <bgetsug@fisdap.net>
 *
 */
final class MustHaveRole
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
     * @return \Response
     */
    public function handle($request, Closure $next, $roleName)
    {
        if ($this->user->context()->getRole()->getName() !== $roleName) {
            throw new InadequateRole("Endpoint requires '$roleName' user role");
        }

        return $next($request);
    }
}
