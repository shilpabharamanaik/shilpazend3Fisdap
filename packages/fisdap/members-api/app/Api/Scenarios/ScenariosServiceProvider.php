<?php namespace Fisdap\Api\Scenarios;

use Fisdap\Api\Scenarios\Http\ScenariosController;
use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;


/**
 * Enables scenario-related routes, providing REST API endpoint documentation for each, and provides scenario-related services
 *
 * @package Fisdap\Api\Scenarios
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
final class ScenariosServiceProvider extends ServiceProvider
{
    /**
     * @inheritdoc
     */
    public function boot(Router $router)
    {
        parent::boot($router);
    }

    /**
     * Put scenario-related routes here
     *
     * ALL NON-RESOURCE CONTROLLERS SHOULD BE NAMED (in order to work properly with New Relic)
     * see http://laravel.com/docs/routing#named-routes
     *
     * @param Router $router
     */
    public function map(Router $router)
    {
        $router->get('scenarios/{scenarioId}/alsi', [
            'as' => 'scenarios.scenarioId.alsi',
            'uses' => ScenariosController::class . '@alsi'
        ]);
    }


    /**
     * @inheritdoc
     */
    public function register()
    {
        //$this->app->singleton(FindsUsers::class, UsersFinder::class);
    }
}