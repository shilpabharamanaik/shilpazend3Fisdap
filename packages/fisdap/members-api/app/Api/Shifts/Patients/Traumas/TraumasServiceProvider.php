<?php

namespace Fisdap\Api\Shifts\Patients\Traumas;

use Fisdap\Api\Providers\RouteServiceProvider as ServiceProvider;
use Fisdap\Api\Shifts\Patients\Traumas\Http\TraumaController;
use Illuminate\Routing\Router;

/**
 * Class TraumasServiceProvider
 * @package Fisdap\Api\Patients\Traumas
 * @author  Isaac White <iwhite@fisdap.net>
 */
class TraumasServiceProvider extends ServiceProvider
{
    /**
     * @param Router $router
     */
    public function boot()
    {
        $router = app('router'); // Router Instance
        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @param Router $router
     */
    public function map()
    {
        $router = app('router'); // Router Instance
        $router->group([
            'prefix'     => 'patients/traumas'
        ], function (Router $router) {
            $router->get('causes', [
                'as'   => 'patients.traumas.causes',
                'uses' => TraumaController::class . '@getCauses'
            ]);

            $router->get('intents', [
                'as'   => 'patients.traumas.intents',
                'uses' => TraumaController::class . '@getIntents'
            ]);

            $router->get('mechanisms', [
                'as'   => 'patients.traumas.mechanisms',
                'uses' => TraumaController::class . '@getMechanisms'
            ]);
        });
    }
}
