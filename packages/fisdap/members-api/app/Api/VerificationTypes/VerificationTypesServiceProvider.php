<?php namespace Fisdap\Api\VerificationTypes;


use Fisdap\Api\VerificationTypes\Http\VerificationTypesController;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Routing\Router;

/**
 * Class VerificationTypeServiceProvider
 * @package Fisdap\Api\VerificationType
 * @author  Isaac White <iwhite@fisdap.net>
 */
final class VerificationTypesServiceProvider extends ServiceProvider
{
    /**
     * @param Router $router
     */
    public function boot()
    {
		$router = app('router'); // Router Instance
        parent::boot($router);
    }

    /**
     * Define route to obtain all verification types.
     * 
     * @param Router $router
     */
    public function map()
    {
		$router = app('router'); // Router Instance
        $router->get('verification-types', [
            'as'   => 'verification-types.index',
            'uses' => VerificationTypesController::class . '@index'
        ]);
    }
}

