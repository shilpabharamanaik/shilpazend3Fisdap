<?php namespace Fisdap\Api\Providers;

use Fisdap\Logging\Events\EventLogger;
use Illuminate\Support\Facades\Event ;
use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Swift_Message;

/**
 * Class EventServiceProvider
 *
 * @package Fisdap\Api\Providers
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @codeCoverageIgnore
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'Fisdap\Api\Events\SomeEvent' => [
            'Fisdap\Api\Listeners\EventListener',
        ],
    ];


    /**
     * Register any other events for your application.
     *
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
