<?php namespace Fisdap\Members\Console;

use Illuminate\Console\Command;
use Psy\Configuration;
use Psy\Shell;
use Symfony\Component\Console\Input\InputArgument;
use Zend_Registry;


/**
 * Class FiddleCommand
 *
 * @package Fisdap\Members\Console
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class FiddleCommand extends Command
{
    /**
     * fisdap commands to include in the fiddle shell.
     *
     * @var array
     */
    protected $commandWhitelist = [
//        'clear-compiled', 'down', 'env', 'inspire', 'migrate', 'optimize', 'up',
    ];

    protected $name = "fiddle";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Interact with the Fisdap application';


    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        // make sure CLI is able to handle errors
        $this->getApplication()->setCatchExceptions(false);

        $config = new Configuration;

//        $config->getPresenter()->addCasters(
//            $this->getCasters()
//        );

        $shell = new Shell($config);
        $shell->addCommands($this->getCommands());
        $shell->setIncludes($this->argument('include'));
        $shell->setScopeVariables([
            'config' => Zend_Registry::get('config'),
            'container' => Zend_Registry::get('container'),
            'logger' => Zend_Registry::get('logger'),
            'bugsnag' => Zend_Registry::get('bugsnag'),
            'db' => Zend_Registry::get('db'),
            'em' => Zend_Registry::get('doctrine')->getEntityManager()
        ]);

        $shell->run();
    }

    /**
     * Get fisdap commands to pass through to PsySH.
     *
     * @return array
     */
    protected function getCommands()
    {
        $commands = [];

        foreach ($this->getApplication()->all() as $name => $command) {
            if (in_array($name, $this->commandWhitelist)) {
                $commands[] = $command;
            }
        }

        return $commands;
    }


    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['include', InputArgument::IS_ARRAY, 'Include file(s) before starting fiddle'],
        ];
    }
}