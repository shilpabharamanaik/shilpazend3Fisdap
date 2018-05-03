<?php namespace Fisdap\Api\ServiceAccounts\Permissions\Jobs;

use Fisdap\Api\Jobs\Job;
use Fisdap\Api\ServiceAccounts\Entities\ServiceAccountPermission;
use Fisdap\Api\ServiceAccounts\Permissions\Repository\ServiceAccountPermissionsRepository;
use Illuminate\Routing\Router;


/**
 * Class CreateServiceAccountPermissions
 *
 * @package Fisdap\Api\ServiceAccounts\Permissions\Jobs
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class CreateServiceAccountPermissions extends Job
{
    /**
     * @var string[]
     */
    private $routeNames;


    /**
     * CreateServiceAccountPermissions constructor.
     *
     * @param string[] $routeNames
     */
    public function __construct(array $routeNames)
    {
        $this->routeNames = $routeNames;
    }


    /**
     * @param Router                              $router
     * @param ServiceAccountPermissionsRepository $serviceAccountPermissionsRepository
     *
     * @return array
     */
    public function handle(Router $router, ServiceAccountPermissionsRepository $serviceAccountPermissionsRepository)
    {
        $routes = $router->getRoutes();
        
        $routeNames = array_filter($this->routeNames, function($routeName) use ($routes) {
            return $routes->hasNamedRoute($routeName);
        });
        
        $serviceAccountPermissionsRepository->storeCollection(array_map(function($routeName) {
            return new ServiceAccountPermission($routeName);
        }, $routeNames));
        
        return $routeNames;
    }
}