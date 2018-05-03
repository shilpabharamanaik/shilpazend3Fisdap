<?php namespace Fisdap\Api\Programs\Http\Middleware;

use Closure;
use Fisdap\Api\Programs\Http\Exceptions\NoViewReportsPermission;
use Fisdap\Data\Instructor\InstructorLegacyRepository;
use Fisdap\Data\Permission\PermissionRepository;
use Fisdap\Entity\InstructorLegacy;
use Fisdap\Entity\Permission;
use Fisdap\Entity\User;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\Request;


/**
 * Ensures current user has permissions to view reports
 *
 * @package Fisdap\Api\Programs\Http\Middleware
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
final class UserHasReportAccess
{
    /**
     * @var User|null
     */
    private $user;

    /**
     * @var InstructorLegacyRepository
     */
    private $instructorRepository;

    /**
     * @var PermissionRepository
     */
    private $permissionRepository;


    /**
     * @param AuthManager $auth
     * @param InstructorLegacyRepository $instructorRepository
     * @param PermissionRepository $permissionRepository
     */
    public function __construct(AuthManager $auth, InstructorLegacyRepository $instructorRepository, PermissionRepository $permissionRepository)
    {
        $this->user = $auth->guard()->user();

        $this->instructorRepository = $instructorRepository;
        $this->permissionRepository = $permissionRepository;
    }


    /**
     * @param Request $request
     * @param Closure $next
     */
    public function handle($request, Closure $next)
    {
        // If instructor, we have to check to see if they have the Permission for viewing reports
        // Students are allowed to see reports by default
        if ($this->user->isInstructor()) {
            /** @var InstructorLegacy $instructor */
            $instructor = $this->instructorRepository->findOneBy(["user" => $this->user->getId()]);
            if ($instructor) {
                if (!$instructor->hasPermission(6)) {
                    throw new NoViewReportsPermission("Instructor account does not have permission to view reports.");
                }
            }
        }

        return $next($request);
    }
}