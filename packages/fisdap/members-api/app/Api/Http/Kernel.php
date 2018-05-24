<?php namespace Fisdap\Api\Http;

use Barryvdh\Cors\HandleCors;
use Fisdap\Api\Auth\Http\Middleware\OAuth2ResourceServer;
use Fisdap\Api\Bootstrap\ConfigureLogging;
use Fisdap\Api\Http\Middleware\DebugResponse;
use Fisdap\Api\Programs\Http\Middleware\GoalSetProgramMismatch;
use Fisdap\Api\Programs\Http\Middleware\UserCanViewStudents;
use Fisdap\Api\Programs\Http\Middleware\UserContextProgramIdMatchesRouteId;
use Fisdap\Api\Programs\Http\Middleware\UserHasReportAccess;
use Fisdap\Api\Shifts\Attachments\Http\Middleware\ShiftAttachmentCreationUserContextLimit;
use Fisdap\Api\Shifts\Http\Middleware\InstructorHasWritePermissionForShiftType;
use Fisdap\Api\Shifts\Http\Middleware\ShiftStudentProgramMatchesUserContextProgram;
use Fisdap\Api\Users\Http\Middleware\ContextBelongsToUser;
use Fisdap\Api\Users\Http\Middleware\UserIdMatchesRouteId;
use Fisdap\Api\Users\Staff\Http\Middleware\StaffAccess;
use Fisdap\Api\Users\Staff\Http\Middleware\UserMustBeStaff;
use Fisdap\Api\Users\UserContexts\Http\Middleware\MustHaveRole;
use Fisdap\Api\Users\UserContexts\Http\Middleware\RoleDataIdMatchesRouteId;
use Fisdap\Api\Users\UserContexts\Roles\Instructors\Http\Middleware\InstructorCanViewAllData;
use Fisdap\Api\Users\UserContexts\Roles\Students\Http\Middleware\StudentHasSkillsTrackerOrScheduler;
use Fisdap\BuildMetadata\VersionRangeMiddleware;
use Fisdap\ErrorHandling\PostMaxSizeMiddleware;
use Illuminate\Foundation\Http\Kernel as HttpKernel;


class Kernel extends HttpKernel
{
    /**
     * The bootstrap classes for the application.
     *
     * @var array
     */
    protected $bootstrappers = [
        \Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables::class,
        \Illuminate\Foundation\Bootstrap\LoadConfiguration::class,
		ConfigureLogging::class,
        \Illuminate\Foundation\Bootstrap\HandleExceptions::class,
        \Illuminate\Foundation\Bootstrap\RegisterFacades::class,
        \Illuminate\Foundation\Bootstrap\SetRequestForConsole::class,
        \Illuminate\Foundation\Bootstrap\RegisterProviders::class,
        \Illuminate\Foundation\Bootstrap\BootProviders::class,
    ];

    /**
     * The application's global HTTP middleware stack.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,

        HandleCors::class,
        PostMaxSizeMiddleware::class,
        DebugResponse::class,
        OAuth2ResourceServer::class,
        StaffAccess::class,
        VersionRangeMiddleware::class,
    ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'userMustBeStaff' => UserMustBeStaff::class,
        'userIdMatchesRouteId' => UserIdMatchesRouteId::class,
        'mustHaveRole' => MustHaveRole::class,
        'contextBelongsToUser' => ContextBelongsToUser::class,

        'goalSetProgramMismatch' => GoalSetProgramMismatch::class,
        'userHasReportAccess' => UserHasReportAccess::class,
        'userCanViewStudents' => UserCanViewStudents::class,

        'userContextProgramIdMatchesRouteId' => UserContextProgramIdMatchesRouteId::class,
        'roleDataIdMatchesRouteId' => RoleDataIdMatchesRouteId::class,

        'instructorCanViewAllData' => InstructorCanViewAllData::class,
        'instructorHasWritePermissionForShiftType' => InstructorHasWritePermissionForShiftType::class,

        'shiftStudentProgramMatchesUserContextProgram' => ShiftStudentProgramMatchesUserContextProgram::class,
        'shiftAttachmentCreationUserContextLimit' => ShiftAttachmentCreationUserContextLimit::class,

        'studentHasSkillsTrackerOrScheduler' => StudentHasSkillsTrackerOrScheduler::class,
    ];
}
