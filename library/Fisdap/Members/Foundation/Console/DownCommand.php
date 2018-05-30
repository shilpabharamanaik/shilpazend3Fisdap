<?php namespace Fisdap\Members\Foundation\Console;

use Illuminate\Console\Command;

/**
 * Class DownCommand
 *
 * @package Fisdap\Members\Foundation\Console
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class DownCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'down';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Put the application into maintenance mode';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        touch(storage_path().'/down');

        $this->comment('Application is now in maintenance mode.');
    }
}
