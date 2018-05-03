<?php
/**
 * Created by PhpStorm.
 * User: isaac.white
 * Date: 5/2/16
 * Time: 9:55 AM
 */

namespace Fisdap\Api\Ethnicities;


use Fisdap\Api\Ethnicities\Http\EthnicitiesController;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Routing\Router;

/**
 * Class EthnicitiesServiceProvider
 * @package Fisdap\Api\Ethnicities
 * @author Isaac White <iwhite@fisdap.net>
 */
final class EthnicitiesServiceProvider extends ServiceProvider
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
     * @param Router $router
     */
    public function map(Router $router)
    {
        $router->get('/ethnicities', [
            'as'   => 'ethnicities.index',
            'uses' => EthnicitiesController::class . '@index'
        ]);
    }
}