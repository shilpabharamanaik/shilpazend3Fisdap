<?php namespace Fisdap\Api\ServiceAccounts\Permissions\Console;

use Fisdap\Api\ServiceAccounts\Permissions\Jobs\CreateServiceAccountPermissions;
use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\Dispatcher;

/**
 * Class AddServiceAccountPermissionsCommand
 *
 * @package Fisdap\Api\ServiceAccounts\Permissions\Console
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class AddServiceAccountPermissionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'service-accounts:permissions:add {route-names*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add one or more service account permissions';

    /**
     * @var Dispatcher
     */
    private $dispatcher;


    /**
     * Create a new command instance.
     *
     * @param Dispatcher $dispatcher
     */
    public function __construct(Dispatcher $dispatcher)
    {
        parent::__construct();

        $this->dispatcher = $dispatcher;
    }


    public function handle()
    {
        $routeNames = $this->dispatcher->dispatch(new CreateServiceAccountPermissions($this->argument('route-names')));

        $invalidRoutes = array_diff($this->argument('route-names'), $routeNames);
        
        if (!empty($invalidRoutes)) {
            $this->error('Invalid route names: ' . implode(', ', $invalidRoutes));
        }
        
        if (!empty($routeNames)) {
            $this->info('Created service account permissions for routes named: ' . implode(', ', $routeNames));
        }
    }
}
