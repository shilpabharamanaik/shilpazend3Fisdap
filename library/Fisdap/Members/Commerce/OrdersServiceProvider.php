<?php namespace Fisdap\Members\Commerce;

use Fisdap\Members\Commerce\Listeners\SendOrderToGreatPlains;
use Fisdap\Members\Foundation\Support\Providers\EventServiceProvider;

/**
 * Class OrdersServiceProvider
 * @package Fisdap\Members\Commerce
 * @author  Sam Tape <stape@fisdap.net>
 */
class OrdersServiceProvider extends EventServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        //
    ];

    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    protected $subscribe = [
        SendOrderToGreatPlains::class,
    ];
}
