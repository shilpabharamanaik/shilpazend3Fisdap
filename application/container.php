<?php

use Fisdap\Members\Foundation\Application;
use Illuminate\Events\Dispatcher;

$container = new Application(realpath(__DIR__.'/../'));

// only allow one instance of the container
$container->setInstance($container);

// Bind Zend_Application to Container
$container->instance('Zend_Application', $application);

// register self
$container->instance('Illuminate\Contracts\Foundation\Application', $container);
$container->instance('Illuminate\Contracts\Container\Container', $container);
$container->instance('Illuminate\Container\Container', $container);

// register event dispatcher, this needs to happen here because dependencies from other service providers
$container->singleton('events', function ($app) {
    return (new Dispatcher($app))->setQueueResolver(function () use ($app) {
        return $app->make('Illuminate\Contracts\Queue\Factory');
    });
});
$container->alias('events', 'Illuminate\Contracts\Events\Dispatcher');
$container->alias('events', 'Illuminate\Events\Dispatcher');

// register console kernel
$container->singleton('Illuminate\Contracts\Console\Kernel', 'Fisdap\Members\Console\Kernel');

$container->registerConfiguredProviders();

Zend_Registry::set('container', $container);

return $container;