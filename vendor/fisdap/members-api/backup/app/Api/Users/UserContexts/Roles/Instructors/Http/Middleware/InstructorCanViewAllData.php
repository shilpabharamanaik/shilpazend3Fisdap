<?php namespace Fisdap\Api\Users\UserContexts\Roles\Instructors\Http\Middleware;

use Closure;
use Fisdap\Api\Users\UserContexts\Roles\Instructors\Http\Exceptions\InvalidPermission;
use Fisdap\Api\Users\UserContexts\Roles\Instructors\Permissions\ViewAllData;
use Fisdap\Entity\User;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\Request;


/**
 * Ensures that instructor has 'view all data' permission
 *
 * @package Fisdap\Api\Users\UserContexts\Roles\Instructors\Http\RouteFilters
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class InstructorCanViewAllData
{
    /**
     * @var User|null
     */
    private $user;

    /**
     * @var ViewAllData
     */
    private $viewAllData;


    /**
     * @param AuthManager $auth
     * @param ViewAllData $viewAllData
     */
    public function __construct(AuthManager $auth, ViewAllData $viewAllData)
    {
        $this->user = $auth->guard()->user();
        $this->viewAllData = $viewAllData;
    }


    /**
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->user->context()->getRole()->getName() == 'instructor') {
            if ( ! $this->viewAllData->permitted($this->user->context())) {
                throw new InvalidPermission("Instructor must have 'view all data' permission");
            }
        }

        return $next($request);
    }
}