<?php namespace Fisdap\Api\Providers;
use Route as Router; 
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;


/**
 * Class RouteServiceProvider
 *
 * @package Fisdap\Api\Providers
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @codeCoverageIgnore
 */
class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to the controller routes in your routes file.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'Fisdap\Api\Http\Controllers';


    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function boot()
    {
		$router = app('router'); // Router Instance
        parent::boot();
    }


    /**
     * Define the routes for the application.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function map()
    {
		$router = app('router'); // Router Instance
        $router->group(['namespace' => $this->namespace], function () {
			$router = app('router'); // Router Instance
            $router->get('/', 'RootController@root');
            $router->get('/routes', 'RootController@routes');
        });
    }
}
