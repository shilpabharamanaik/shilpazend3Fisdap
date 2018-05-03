<?php namespace Fisdap\Api\ServiceAccounts\Console;

use Fisdap\Api\ServiceAccounts\Entities\ServiceAccount;
use Fisdap\Api\ServiceAccounts\Entities\ServiceAccountPermission;
use Fisdap\Api\ServiceAccounts\Repository\ServiceAccountsRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;


/**
 * Class ListServiceAccountsCommand
 *
 * @package Fisdap\Api\ServiceAccounts\Console
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class ListServiceAccountsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'service-accounts:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all service accounts';
    
    /**
     * @var ServiceAccountsRepository
     */
    private $serviceAccountsRepository;


    /**
     * Create a new command instance.
     *
     * @param ServiceAccountsRepository $serviceAccountsRepository
     */
    public function __construct(ServiceAccountsRepository $serviceAccountsRepository)
    {
        parent::__construct();

        $this->serviceAccountsRepository = $serviceAccountsRepository;
    }

    
    public function handle()
    {
        $serviceAccounts = new Collection($this->serviceAccountsRepository->findAll());
     
        $this->table(['OAuth2 Client ID', 'Name', 'Permissions'], $serviceAccounts->map(function (ServiceAccount $serviceAccount) {
            return [
                $serviceAccount->getOauth2ClientId(), 
                $serviceAccount->getName(), 
                implode(PHP_EOL, $serviceAccount->getPermissions()->map(function (ServiceAccountPermission $permission) {
                    return $permission->getRouteName();
                })->toArray())
            ];
        }));
    }
}
