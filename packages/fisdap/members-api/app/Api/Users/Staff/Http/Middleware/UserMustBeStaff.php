<?php namespace Fisdap\Api\Users\Staff\Http\Middleware;

use Closure;
use Fisdap\Entity\User;
use Illuminate\Auth\AuthManager;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Ensures current user is a Fisdap employee
 *
 * @package Fisdap\Api\Users\Staff\Http\Middleware
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class UserMustBeStaff
{
    /**
     * @var AuthManager
     */
    private $auth;


    /**
     * @param AuthManager $auth
     */
    public function __construct(AuthManager $auth)
    {
        $this->auth = $auth;
    }


    public function handle($request, Closure $next)
    {
        /** @var User $user */
        $user = $this->auth->guard()->user();
        
        if (! $user->isStaff()) {
            throw new AccessDeniedHttpException('User must be Fisdap employee');
        }

        return $next($request);
    }
}
