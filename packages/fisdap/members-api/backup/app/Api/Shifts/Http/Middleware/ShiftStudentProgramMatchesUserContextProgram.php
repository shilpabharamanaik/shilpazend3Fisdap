<?php namespace Fisdap\Api\Shifts\Http\Middleware;

use Closure;
use Fisdap\Api\Shifts\Finder\FindsShifts;
use Fisdap\Api\Shifts\Http\Exceptions\UserContextProgramMismatch;
use Fisdap\Entity\User;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\Request;


/**
 * Ensures shift student program id matches program id of current user role
 *
 * @package Fisdap\Api\Shifts\Http\Middleware
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class ShiftStudentProgramMatchesUserContextProgram
{
    /**
     * @var FindsShifts
     */
    private $shiftsFinder;

    /**
     * @var User|null
     */
    private $user;


    /**
     * @param AuthManager $auth
     * @param FindsShifts $shiftsFinder
     */
    public function __construct(AuthManager $auth, FindsShifts $shiftsFinder)
    {
        $this->user = $auth->guard()->user();
        $this->shiftsFinder = $shiftsFinder;
    }


    /**
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // get shift student program id
        $shiftId = $request->route()->getParameter('shiftId');
        $shiftStudentProgramId = $this->shiftsFinder->getShiftStudentProgramId($shiftId);

        // match against current user role program
        if ($this->user->context()->getProgram()->getId() !== $shiftStudentProgramId) {
            throw new UserContextProgramMismatch('User context program does not match shift student program');
        }

        return $next($request);
    }
}