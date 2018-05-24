<?php namespace Fisdap\Api\Users\UserContexts;

use Fisdap\Api\Users\UserContexts\Permissions\Finder\CachingPermissionsFinder;
use Fisdap\Api\Users\UserContexts\Permissions\Finder\FindsPermissions;
use Fisdap\Api\Users\UserContexts\Permissions\Finder\PermissionsFinder;
use Fisdap\Api\Users\UserContexts\Permissions\PermissionsController;
use Illuminate\Auth\AuthManager;
use Illuminate\Cache\Repository as Cache;
use Illuminate\Config\Repository as Config;
use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;


/**
 * Enables user context-related routes, providing REST API endpoint documentation for each,
 * and provides user context-related services such as permissions finding, etc.
 *
 * @package Fisdap\Api\Users\UserContexts
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @codeCoverageIgnore
 */
final class UserContextsServiceProvider extends ServiceProvider
{
    /**
     * @inheritdoc
     */
    public function boot(Router $router)
    {
        parent::boot($router);
    }


    /**
     * Put user-related routes here
     *
     * ALL NON-RESOURCE CONTROLLERS SHOULD BE NAMED (in order to work properly with New Relic)
     * see http://laravel.com/docs/routing#named-routes
     *
     * @param Router $router
     */
    public function map(Router $router)
    {
        $router->get('/users/contexts/permissions', [
            'middleware' => [
                'mustHaveRole:instructor',
            ],
            'as' => 'users.contexts.permissions.index',
            'uses' => PermissionsController::class . '@index'
        ]);
        
        $router->get('/users/contexts/permissions/{permissionId}', [
            'middleware' => [
                'mustHaveRole:instructor',
            ],
            'as' => 'users.contexts.permissions.show',
            'uses' => PermissionsController::class . '@show'
        ]);
        
        $router->get('/instructors/{instructorId}/permissions', [
            'middleware' => [
                'mustHaveRole:instructor',
                'roleDataIdMatchesRouteId:instructor',
            ],
            'as' => 'instructors.permissions',
            'uses' => PermissionsController::class . '@getInstructorPermissions'
        ]);

        $router->post('users/{userId}/switch-context/{userContextId}', [
            'middleware' => [
                'userIdMatchesRouteId',
                'contextBelongsToUser'
            ],
            'as' => 'users.userId.switch-context.userContextId',
            'uses' => PermissionsController::class . '@switchContext'
        ]);
    }


    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->app->singleton(FindsPermissions::class, function() {
            return new CachingPermissionsFinder(
                $this->app->make(AuthManager::class),
                $this->app->make(PermissionsFinder::class),
                $this->app->make(Cache::class),
                $this->app->make(Config::class)
            );
        });
    }
}