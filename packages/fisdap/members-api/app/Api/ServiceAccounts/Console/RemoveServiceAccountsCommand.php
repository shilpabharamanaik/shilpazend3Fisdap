<?php namespace Fisdap\Api\ServiceAccounts\Console;

use Fisdap\Api\ServiceAccounts\Jobs\DeleteServiceAccounts;
use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\Dispatcher;


/**
 * Class RemoveServiceAccountsCommand
 *
 * @package Fisdap\Api\ServiceAccounts\Console
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class RemoveServiceAccountsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'service-accounts:remove {oauth2-client-ids*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete one or more service accounts';

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
        $this->dispatcher->dispatch(new DeleteServiceAccounts($this->argument('oauth2-client-ids')));
        
        $this->info('Deleted service accounts: ' . implode(', ', $this->argument('oauth2-client-ids')));
    }
}
