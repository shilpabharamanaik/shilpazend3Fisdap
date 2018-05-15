<?php namespace Fisdap\Api\Professions;

use Fisdap\Api\Professions\Http\ProfessionsController;
use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

/**
 * Enables profession-related routes
 *
 * @package Fisdap\Api\Professions
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @codeCoverageIgnore
 */
final class ProfessionsServiceProvider extends ServiceProvider
{
    /**
     * @inheritdoc
     */
    public function boot()
    {
        $router = app('router'); // Router Instance
        parent::boot();
    }


    /**
     * Put program-related routes here
     *
     * ALL NON-RESOURCE CONTROLLERS SHOULD BE NAMED (in order to work properly with New Relic)
     * see http://laravel.com/docs/routing#named-routes
     *
     * @param Router $router
     */
    public function map(Router $router)
    {
        $router->get('professions', [
            'as'   => 'professions.index',
            'uses' => ProfessionsController::class . '@index'
        ]);
    }


    /**
     * @inheritdoc
     */
    public function register()
    {
    }
}
