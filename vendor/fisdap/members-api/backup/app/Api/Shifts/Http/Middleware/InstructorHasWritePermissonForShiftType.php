<?php namespace Fisdap\Api\Shifts\Http\Middleware;

use Closure;
use Fisdap\Api\Shifts\Finder\FindsShifts;
use Fisdap\Api\Shifts\Http\Exceptions\NoEditPermission;
use Fisdap\Api\Users\UserContexts\Permissions\Finder\FindsPermissions;
use Fisdap\Entity\ShiftLegacy;
use Fisdap\Entity\User;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\Request;


/**
 * Ensures instructor has write permissions for a shift type
 *
 * @package Fisdap\Api\Shifts\Http\Middleware
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class InstructorHasWritePermissionForShiftType
{
    /**
     * @var FindsShifts
     */
    private $shiftsFinder;

    /**
     * @var FindsPermissions
     */
    private $permissionsFinder;

    /**
     * @var User|null
     */
    private $user;


    /**
     * @param AuthManager      $auth
     * @param FindsShifts      $shiftsFinder
     * @param FindsPermissions $permissionsFinder
     */
    public function __construct(
        AuthManager $auth,
        FindsShifts $shiftsFinder,
        FindsPermissions $permissionsFinder
    ) {
        $this->user = $auth->guard()->user();
        $this->shiftsFinder = $shiftsFinder;
        $this->permissionsFinder = $permissionsFinder;
    }


    /**
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     * @throws \Exception
     */
    public function handle($request, Closure $next)
    {
        $shiftId = $request->route()->getParameter('shiftId');

        if ($this->user->context()->getRole()->getName() == 'instructor') {

            /** @var ShiftLegacy $shift */
            $shift = $this->shiftsFinder->getById($shiftId);

            $instructorPermissions = $this->permissionsFinder->getInstructorPermissionNames(
                $this->user->context()->getRoleData()->getId()
            );

            if ( ! $this->permissionsFinder->hasInstructorPermission(
                "edit {$shift->getType()} data", $instructorPermissions
            )) {
                throw new NoEditPermission(
                    "Instructor does not have edit permission for '{$shift->getType()}' shifts"
                );
            }
        }

        return $next($request);
    }
}