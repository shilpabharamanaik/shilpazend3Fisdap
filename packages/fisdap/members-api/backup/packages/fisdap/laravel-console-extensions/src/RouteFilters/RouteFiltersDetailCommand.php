<?php namespace Fisdap\Console\RouteFilters;

use Illuminate\Foundation\Console\RoutesCommand;
use Illuminate\Routing\Route;


/**
 * Extension of Artisan 'routes' command, providing details on route filters
 *
 * @package Fisdap\Console\RouteFilters
 */
final class RouteFiltersDetailCommand extends RoutesCommand
{
    protected $name = 'routes:filters:detail';

    protected $description = 'List filters for each route';


    protected function getRouteInformation(Route $route)
    {
        $uri = implode('|', $route->methods()) . ' ' . $route->uri();

        return $this->filterRoute([
            'uri'    => $uri,
            'name'   => $route->getName(),
            'before' => $this->getBeforeFilters($route),
            'after'  => $this->getAfterFilters($route),
        ]);
    }


    /**
     * @param array $routes
     */
    protected function displayRoutes(array $routes)
    {
        foreach ($routes as $route) {
            $this->info("URI:\t\t" . $route['uri']);
            $this->line("Name:\t\t" . $route['name']);
            $this->line("Before Routes:\t" . $route['before']);
            $this->line("After Routes:\t" . $route['after']);
            $this->line('');
        }
    }
}