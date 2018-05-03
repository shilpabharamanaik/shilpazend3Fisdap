<?php namespace Fisdap\Members\ServiceProviders;

use Fisdap\Api\Users\Finder\FindsUsers;
use Fisdap\Api\Users\Finder\UsersFinder;
use Fisdap\Api\Users\UserContexts\Events\UserContextWasCreated;
use Fisdap\Api\Users\UserContexts\Listeners\SendInstructorNewStudentInfo;
use Fisdap\Api\Users\UserContexts\Permissions\Events\PermissionsWereSet;
use Fisdap\Api\Users\UserContexts\Permissions\Listeners\RecordPermissionsHistory;
use Fisdap\Members\Foundation\Support\Providers\EventServiceProvider;


/**
 * Class UsersServiceProvider
 *
 * @package Fisdap\Members\ServiceProviders
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @todo this is basically duplicated from Members API...fix
 */
class UsersServiceProvider extends EventServiceProvider
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


    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->app->singleton(FindsUsers::class, UsersFinder::class);
    }
}