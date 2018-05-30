<?php namespace Fisdap\Api\Programs\Events;

use Fisdap\Api\Programs\Listeners\CreateDefaultNarrativeSection;
use Fisdap\Api\Programs\Listeners\CreateDefaultPracticeDefinitions;
use Fisdap\Api\Programs\Listeners\CreateDemoSites;
use Fisdap\Api\Programs\Listeners\CreateDemoStudent;
use Fisdap\Api\Programs\Listeners\GenerateProductCodeId;
use Fisdap\Api\Programs\Listeners\ProgramSettings\EstablishProgramSettings;
use Fisdap\Api\Programs\Listeners\SendNewProgramNotification;
use Illuminate\Contracts\Pipeline\Pipeline as PipelineContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Pipeline\Pipeline;

/**
 * Registers events related to programs (Program Entity)
 *
 * @package Fisdap\Api\Programs\Events
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @codeCoverageIgnore
 */
final class ProgramEventsServiceProvider extends ServiceProvider
{
    protected $listen = [
        ProgramWasCreated::class => [
            EstablishProgramSettings::class,
            GenerateProductCodeId::class,
            CreateDemoStudent::class,
            CreateDemoSites::class,
            CreateDefaultPracticeDefinitions::class,
            CreateDefaultNarrativeSection::class
        ],
        DemoStudentWasCreated::class => [
            SendNewProgramNotification::class
        ]
    ];
    
    
    public function register()
    {
        $this->app->when(EstablishProgramSettings::class)
            ->needs(PipelineContract::class)
            ->give(function () {
                return new Pipeline($this->app);
            });
    }
}
