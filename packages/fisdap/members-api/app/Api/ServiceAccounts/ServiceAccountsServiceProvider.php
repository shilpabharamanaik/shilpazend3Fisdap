<?php namespace Fisdap\Api\ServiceAccounts;

use Fisdap\Api\ServiceAccounts\Console\AddServiceAccountCommand;
use Fisdap\Api\ServiceAccounts\Console\ManageServiceAccountPermissionsCommand;
use Fisdap\Api\ServiceAccounts\Console\ListServiceAccountsCommand;
use Fisdap\Api\ServiceAccounts\Console\RemoveServiceAccountsCommand;
use Fisdap\Api\ServiceAccounts\Permissions\Console\AddServiceAccountPermissionsCommand;
use Fisdap\Api\ServiceAccounts\Permissions\Console\ListServiceAccountPermissionsCommand;
use Fisdap\Api\ServiceAccounts\Permissions\Console\RemoveServiceAccountPermissionsCommand;
use Illuminate\Support\ServiceProvider;

/**
 * Class ServiceAccountsServiceProvider
 *
 * @package Fisdap\Api\ServiceAccounts
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class ServiceAccountsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->commands([
            'command.service-account.add',
            'command.service-account.remove',
            'command.service-account.list',
            'command.service-account.permissions.add',
            'command.service-account.permissions.remove',
            'command.service-account.permissions.list',
            'command.service-account.manage',
        ]);
    }


    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->app->bind('command.service-account.add', AddServiceAccountCommand::class);
        $this->app->bind('command.service-account.remove', RemoveServiceAccountsCommand::class);
        $this->app->bind('command.service-account.list', ListServiceAccountsCommand::class);
        $this->app->bind('command.service-account.permissions.add', AddServiceAccountPermissionsCommand::class);
        $this->app->bind('command.service-account.permissions.remove', RemoveServiceAccountPermissionsCommand::class);
        $this->app->bind('command.service-account.permissions.list', ListServiceAccountPermissionsCommand::class);
        $this->app->bind('command.service-account.manage', ManageServiceAccountPermissionsCommand::class);
    }
}
