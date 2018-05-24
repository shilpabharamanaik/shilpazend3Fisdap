<?php namespace Fisdap\Api\Programs\Http\Middleware;

use Closure;
use Fisdap\Api\Programs\Http\Exceptions\ProgramIdMismatch;
use Fisdap\Entity\User;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\Request;


/**
 * Ensures current user role program ID matches route 'programId' parameter
 *
 * @package Fisdap\Api\Programs\Http\Middleware
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class UserContextProgramIdMatchesRouteId
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
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $programId = $request->route()->getParameter('programId');

        if ($this->user->getCurrentUserContext()->getProgram()->getId() != $programId) {
            throw new ProgramIdMismatch('User\'s context program ID must match route {programId} parameter');
        }

        return $next($request);
    }
}