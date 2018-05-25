<?php namespace Fisdap\Members\Foundation\Console;

use Illuminate\Console\Command;


/**
 * Class UpCommand
 *
 * @package Fisdap\Members\Foundation\Console
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class UpCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'up';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bring the application out of maintenance mode';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        @unlink(storage_path().'/down');

        $this->info('Application is now live.');
    }
}