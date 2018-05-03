<?php

namespace Fisdap\Api\Gender;

use Fisdap\Api\Gender\Http\GenderController;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Routing\Router;

/**
 * Class GenderServiceProvider
 * @package Fisdap\Api\Gender
 * @author Isaac White <iwhite@fisdap.net>
 *
 */
final class GenderServiceProvider extends ServiceProvider
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
     *
     * NOTE: Gender is singular and plural.
     */
    public function map(Router $router)
    {
        $router->get('/gender', [
            'as'   => 'gender.index',
            'uses' => GenderController::class . '@index'
        ]);
    }
}

