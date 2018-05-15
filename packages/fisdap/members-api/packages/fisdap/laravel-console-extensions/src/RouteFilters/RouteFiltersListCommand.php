<?php namespace Fisdap\Console\RouteFilters;

use Event;
use Illuminate\Foundation\Console\RoutesCommand;
use Illuminate\Routing\Route;
use Jeremeamia\SuperClosure\ClosureParser;

/**
 * Extension of Artisan 'routes' command, providing list of all route filters
 *
 * @package Fisdap\Console\RouteFilters
 */
final class RouteFiltersListCommand extends RoutesCommand
{
    protected $name = 'routes:filters:list';

    protected $description = 'List filters for each route';


    protected function getRouteInformation(Route $route)
    {
        return $this->filterRouteAsClass($route);
    }


    protected function filterRouteAsClass(Route $route)
    {
        if (($this->option('name') && !str_contains($route->getName(), $this->option('name'))) ||
            $this->option('path') && !str_contains(
                implode('|', $route->methods()) . ' ' . $route->uri(),
                $this->option('path')
            )
        ) {
            return null;
        }

        return $route;
    }


    /**
     * @param Route[] $routes
     */
    protected function displayRoutes(array $routes)
    {
        $routeFilters = [];

        foreach ($routes as $route) {
            $before = array_keys($route->beforeFilters());

            $before = array_unique(array_merge($before, $this->getPatternFilters($route)));

            $after = array_keys($route->afterFilters());

            $routeFilters = array_unique(array_merge($routeFilters, $before, $after));
        }

        natsort($routeFilters);

        foreach ($routeFilters as &$routeFilter) {
            $listenerClosure = Event::getListeners('router.filter: ' . $routeFilter)[0];

            $reflectionFunction = new \ReflectionFunction($listenerClosure);

            $closureParser = new ClosureParser($reflectionFunction);

            $listenerName = $closureParser->getUsedVariables()['listener'];

            if (str_contains($listenerName, '@')) {
                $className = explode('@', $listenerName)[0];
                $reflectionClass = new \ReflectionClass($className);
                $description = substr(explode("\n", $reflectionClass->getDocComment())[1], 3);
            } else {
                $description = 'UNKNOWN';
            }

            $routeFilter = [$routeFilter, $listenerName, $description];
        }

        $this->table(['Filter Name', 'Callback', 'Description'], $routeFilters);
    }
}
