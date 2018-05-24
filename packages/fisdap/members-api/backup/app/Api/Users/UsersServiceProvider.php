<?php namespace Fisdap\Api\Users;

use Fisdap\Api\Users\Finder\FindsUsers;
use Fisdap\Api\Users\Finder\UsersFinder;
use Fisdap\Api\Users\Http\UsersController;
use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;


/**
 * Enables user-related routes, providing REST API endpoint documentation for each, and provides user-related services
 *
 * @package Fisdap\Api\Users
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @codeCoverageIgnore
 */
final class UsersServiceProvider extends ServiceProvider
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
        $router->get('users/{userId}', [
            'middleware' => [
                'userIdMatchesRouteId'
            ],
            'as' => 'users.show',
            'uses' => UsersController::class . '@show'
        ]);

        $router->get('users/{userId}/reset-password', [
            'middleware' => [
                'userIdMatchesRouteId'
            ],
            'as' => 'users.reset-password',
            'uses' => UsersController::class . '@resetPassword'
        ]);
        
        $router->post('users', [
            'middleware' => [
                'userMustBeStaff'
            ],
            'as' => 'users.store',
            'uses' => UsersController::class . '@store',
        ]);
        
        $router->get('programs/{programId}/students', [
            'middleware' => [
                'mustHaveRole:instructor',
                'instructorCanViewAllData',
                'userContextProgramIdMatchesRouteId'
            ],
            'as' => 'programs.students',
            'uses' => UsersController::class . '@getProgramStudents'
        ]);
        
        $router->get('instructors/{instructorId}/students', [
            'middleware' => [
                'mustHaveRole:instructor',
                'roleDataIdMatchesRouteId:instructor',
                'instructorCanViewAllData',
            ],
            'as' => 'instructors.students',
            'uses' => UsersController::class . '@getInstructorStudents'
        ]);
    }


    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->app->singleton(FindsUsers::class, UsersFinder::class);
    }
}