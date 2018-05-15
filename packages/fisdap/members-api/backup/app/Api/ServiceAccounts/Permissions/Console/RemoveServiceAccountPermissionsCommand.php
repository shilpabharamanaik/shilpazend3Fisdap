<?php namespace Fisdap\Api\ServiceAccounts\Permissions\Console;

use Fisdap\Api\ServiceAccounts\Entities\ServiceAccountPermission;
use Fisdap\Api\ServiceAccounts\Jobs\DeleteServiceAccountPermissions;
use Fisdap\Api\ServiceAccounts\Permissions\Repository\ServiceAccountPermissionsRepository;
use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Collection;

/**
 * Class RemoveServiceAccountPermissionsCommand
 *
 * @package Fisdap\Api\ServiceAccounts\Permissions\Console
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class RemoveServiceAccountPermissionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'service-accounts:permissions:remove {permission-route-names*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete one or more service account permissions';

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


    /**
     * @param ServiceAccountPermissionsRepository $serviceAccountPermissionsRepository
     */
    public function handle(ServiceAccountPermissionsRepository $serviceAccountPermissionsRepository)
    {
        $permissions = new Collection($serviceAccountPermissionsRepository->findBy(
            ['routeName' => $this->argument('permission-route-names')]
        ));

        $permissionsIds = $permissions->map(function (ServiceAccountPermission $permission) {
            return $permission->getId();
        })->all();

        $this->dispatcher->dispatch(new DeleteServiceAccountPermissions($permissionsIds));

        $this->info('Deleted service account permissions for routes named: ' . implode(
            ', ',
                $this->argument('permission-route-names')
        ));
    }
}
