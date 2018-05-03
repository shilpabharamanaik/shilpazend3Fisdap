<?php namespace Fisdap\Api\Users\UserContexts\Roles\Students\Http\Middleware;

use Closure;
use Fisdap\Api\Users\UserContexts\Roles\Students\Http\Exceptions\NoProductAccess;
use Fisdap\Api\Users\UserContexts\Roles\Students\Permissions\HasSkillsTrackerOrScheduler;
use Fisdap\Entity\User;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\Request;


/**
 * Ensures that student has Skills Tracker or Scheduler
 *
 * @package Fisdap\Api\Users\UserContexts\Roles\Students\Http\Middleware
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class StudentHasSkillsTrackerOrScheduler
{
    /**
     * @var User|null
     */
    private $user;

    /**
     * @var HasSkillsTrackerOrScheduler
     */
    private $hasSkillsTrackerOrScheduler;


    /**
     * @param AuthManager                 $auth
     * @param HasSkillsTrackerOrScheduler $hasSkillsTrackerOrScheduler
     */
    public function __construct(AuthManager $auth, HasSkillsTrackerOrScheduler $hasSkillsTrackerOrScheduler) {
        $this->user = $auth->guard()->user();
        $this->hasSkillsTrackerOrScheduler = $hasSkillsTrackerOrScheduler;
    }


    /**
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->user->context()->getRole()->getName() == 'student') {
            if ( ! $this->hasSkillsTrackerOrScheduler->permitted($this->user->context())) {
                throw new NoProductAccess(
                    'As a student, you do not have access to Skills Tracker or Scheduler'
                );
            }
        }

        return $next($request);
    }
}