<?php namespace Fisdap\Api\Users\UserContexts\Events;

use Fisdap\Api\Users\UserContexts\Listeners\SendInstructorNewStudentInfo;
use Fisdap\Api\Users\UserContexts\Permissions\Events\PermissionsWereSet;
use Fisdap\Api\Users\UserContexts\Permissions\Listeners\RecordPermissionsHistory;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;


/**
 * Registers events related to user contexts (UserContext Entity)
 *
 * @package Fisdap\Api\Users\UserContexts\Events
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @codeCoverageIgnore
 */
final class UserContextEventsServiceProvider extends ServiceProvider
{
    protected $listen = [
        UserContextWasCreated::class => [
            SendInstructorNewStudentInfo::class

            // todo - send new account/context

            // todo - add to mailing list
        ],
        PermissionsWereSet::class => [
            RecordPermissionsHistory::class
        ]
    ];
}