<?php namespace Fisdap\Api\ServiceAccounts\Console;

use Fisdap\Api\ServiceAccounts\Entities\ServiceAccountPermission;
use Fisdap\Api\ServiceAccounts\Jobs\ManageServiceAccountPermissions;
use Fisdap\Api\ServiceAccounts\Permissions\Repository\ServiceAccountPermissionsRepository;
use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Collection;

/**
 * Class ManageServiceAccountPermissionsCommand
 *
 * @package Fisdap\Api\ServiceAccounts\Console
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class ManageServiceAccountPermissionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'service-accounts:manage {operation : grant|deny} {oauth2-client-id} {permission-route-names?*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage permissions for a service account';

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
        $permissionsIds = [];

        if (! empty($this->argument('permission-route-names'))) {
            $permissions = new Collection($serviceAccountPermissionsRepository->findBy(
                ['routeName' => $this->argument('permission-route-names')]
            ));

            $permissionsIds = $permissions->map(function (ServiceAccountPermission $permission) {
                return $permission->getId();
            })->all();
        }

        $permissionsManaged = $this->dispatcher->dispatch(new ManageServiceAccountPermissions(
            $this->argument('operation'),
            $this->argument('oauth2-client-id'),
            $permissionsIds
        ));


        $this->info("Managed permissions for service account '{$this->argument('oauth2-client-id')}': " . implode(
            ', ',
                $permissionsManaged
        ));
    }
}
