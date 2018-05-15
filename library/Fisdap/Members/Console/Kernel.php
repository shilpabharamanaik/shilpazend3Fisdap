<?php namespace Fisdap\Members\Console;

use AscendLearning\Lti\Console\ToolProviders\CreateToolProviderCommand;
use AscendLearning\Lti\Console\ToolProviders\DeleteToolProvidersCommand;
use AscendLearning\Lti\Console\ToolProviders\ListToolProvidersCommand;
use Fisdap\Members\Commerce\Console\ExportOrdersCommand;
use Fisdap\Members\Commerce\Console\GenerateCustomerInfoCommand;
use Fisdap\Members\Commerce\Console\ListFailedAccountingOrderExportsCommand;
use Fisdap\Members\Commerce\Console\RetryAccountingOrderExportCommand;
use Fisdap\Members\Foundation\Console\Kernel as ConsoleKernel;
use AscendLearning\Lti\Console\Consumers\CreateConsumerCommand;
use AscendLearning\Lti\Console\Consumers\DeleteConsumersCommand;
use AscendLearning\Lti\Console\Consumers\ListConsumersCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Queue\Console\ListenCommand;
use Illuminate\Queue\Console\WorkCommand;
use Illuminate\Console\Scheduling\ScheduleRunCommand;
use Fisdap\BuildMetadata\BuildMetadataMakeCommand;
use Fisdap\Members\Foundation\Console\DownCommand;
use Fisdap\Members\Foundation\Console\UpCommand;
use Fisdap\Members\Console\Database\InitCommand;
use Fisdap\Members\Console\Database\SeedCommand;
use Fisdap\Members\Scheduler\Console\SendScheduleEmailsCommand;

/**
 * Application Console Kernel
 *
 * @package Fisdap\Members\Console
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        ListenCommand::class,
        WorkCommand::class,
        ScheduleRunCommand::class,

        BuildMetadataMakeCommand::class,

        DownCommand::class,
        UpCommand::class,

        FiddleCommand::class,

        InitCommand::class,
        SeedCommand::class,

        SendScheduleEmailsCommand::class,
        
        ExportOrdersCommand::class,
        GenerateCustomerInfoCommand::class,
        ListFailedAccountingOrderExportsCommand::class,
        RetryAccountingOrderExportCommand::class,

        CreateConsumerCommand::class,
        ListConsumersCommand::class,
        DeleteConsumersCommand::class,

        CreateToolProviderCommand::class,
        ListToolProvidersCommand::class,
        DeleteToolProvidersCommand::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
//        $schedule->command('inspire')->hourly();
        $schedule->command('scheduler:send-schedule-emails -x')->dailyAt('04:00');
    }
}
