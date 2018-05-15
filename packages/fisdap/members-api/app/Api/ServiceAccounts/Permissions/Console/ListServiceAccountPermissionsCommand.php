<?php namespace Fisdap\Api\ServiceAccounts\Permissions\Console;

use Fisdap\Api\ServiceAccounts\Entities\ServiceAccountPermission;
use Fisdap\Api\ServiceAccounts\Permissions\Repository\ServiceAccountPermissionsRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

/**
 * Class ListServiceAccountPermissionsCommand
 *
 * @package Fisdap\Api\ServiceAccounts\Permissions\Console
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class ListServiceAccountPermissionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'service-accounts:permissions:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all service account permissions';
    
    /**
     * @var ServiceAccountPermissionsRepository
     */
    private $serviceAccountPermissionsRepository;


    /**
     * Create a new command instance.
     *
     * @param ServiceAccountPermissionsRepository $serviceAccountPermissionsRepository
     */
    public function __construct(ServiceAccountPermissionsRepository $serviceAccountPermissionsRepository)
    {
        parent::__construct();

        $this->serviceAccountPermissionsRepository = $serviceAccountPermissionsRepository;
    }

    
    public function handle()
    {
        $serviceAccounts = new Collection($this->serviceAccountPermissionsRepository->findAll());
     
        $this->table(['ID', 'Route Name'], $serviceAccounts->map(function (ServiceAccountPermission $permission) {
            return [
                $permission->getId(),
                $permission->getRouteName(),
            ];
        }));
    }
}
