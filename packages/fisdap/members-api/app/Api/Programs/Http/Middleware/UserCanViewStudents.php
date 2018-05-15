<?php namespace Fisdap\Api\Programs\Http\Middleware;

use Closure;
use Fisdap\Api\Programs\Http\Exceptions\NoViewReportsPermission;
use Fisdap\Api\Programs\Http\Exceptions\StudentProgramMismatch;
use Fisdap\Data\Instructor\InstructorLegacyRepository;
use Fisdap\Data\Permission\PermissionRepository;
use Fisdap\Data\Student\StudentLegacyRepository;
use Fisdap\Entity\InstructorLegacy;
use Fisdap\Entity\StudentLegacy;
use Fisdap\Entity\User;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\Request;

/**
 * Ensures current user has permissions to view reports for the provided student(s)
 *
 * @package Fisdap\Api\Programs\Http\Middleware
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
final class UserCanViewStudents
{
    /**
     * @var User|null
     */
    private $user;

    /**
     * @var StudentLegacyRepository
     */
    private $studentLegacyRepository;


    /**
     * @param AuthManager $auth
     * @param StudentLegacyRepository $studentLegacyRepository
     */
    public function __construct(AuthManager $auth, StudentLegacyRepository $studentLegacyRepository)
    {
        $this->user = $auth->guard()->user();
        $this->studentLegacyRepository = $studentLegacyRepository;
    }


    /**
     * @param Request $request
     * @param Closure $next
     */
    public function handle($request, Closure $next)
    {
        $logger = \Zend_Registry::get('logger');
        $programId = $this->user->getCurrentUserContext()->getProgram()->getId();

        // Should never be null, since it's a required parameter...but just in case...
        $studentIds = ($request->query()['studentIds'] != null ? explode(",", $request->query()['studentIds']) : null);

        if ($this->user->isInstructor()) {
            $logger->debug("I'm an instructor");
            foreach ($studentIds as $studentId) {
                /** @var StudentLegacy $student */
                $student = $this->studentLegacyRepository->getOneById($studentId);
                if ($student) {
                    if ($student->getUserContext()->getProgram()->getId() != $programId) {
                        throw new StudentProgramMismatch("Student ID {$studentId} does not belong to your program.");
                    }
                }
            }
        } else {
            $logger->debug("I'm a student");
            foreach ($studentIds as $studentId) {
                /** @var StudentLegacy $student */
                $student = $this->studentLegacyRepository->getOneById($studentId);
                if ($student) {
                    if ($student->getUserContext()->getUser()->getId() != $this->user->getId()) {
                        throw new StudentProgramMismatch("Students can only run reports on themselves.");
                    }
                }
            }
        }

        return $next($request);
    }
}
