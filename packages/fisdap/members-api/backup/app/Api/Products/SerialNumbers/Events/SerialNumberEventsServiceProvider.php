<?php namespace Fisdap\Api\Products\SerialNumbers\Events;

use Fisdap\Api\Products\SerialNumbers\Listeners\ApplyProductLimits;
use Fisdap\Api\Products\SerialNumbers\Listeners\UpdateComplianceRequirements;
use Fisdap\Api\Products\SerialNumbers\Listeners\EnrollUserInMoodleCourses;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;


/**
 * Registers listeners for serial number events
 *
 * @package Fisdap\Api\Products\SerialNumbers\Events
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @codeCoverageIgnore
 */
final class SerialNumberEventsServiceProvider extends ServiceProvider
{
    /**
     * @inheritdoc
     */
    protected $listen = [
        SerialNumberWasActivated::class => [
            ApplyProductLimits::class,
            EnrollUserInMoodleCourses::class
        ],
        SerialNumbersWereActivated::class => [
            UpdateComplianceRequirements::class,
        ]
    ];
}