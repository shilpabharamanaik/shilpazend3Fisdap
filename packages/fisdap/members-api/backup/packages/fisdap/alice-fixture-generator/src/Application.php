<?php namespace Fisdap\AliceFixtureGenerator;

use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Console\Application as IlluminateApplication;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Class Application
 *
 * @package Fisdap\AliceFixtureGenerator
 */
class Application extends IlluminateApplication
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;


    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

        parent::__construct($name = 'Alice Fixture Generator', $version = 'UNKNOWN');
    }


    /**
     * Gets the name of the command based on input.
     *
     * @param InputInterface $input The input interface
     *
     * @return string The command name
     */
    protected function getCommandName(InputInterface $input)
    {
        // This should return the name of your command.
        return 'generate-fixtures';
    }


    /**
     * Gets the default commands that should always be available.
     *
     * @return array An array of default Command instances
     */
    protected function getDefaultCommands()
    {
        // Keep the core default commands to have the HelpCommand
        // which is used when using the --help option
        $defaultCommands = parent::getDefaultCommands();

        $defaultCommands[] = new GenerateCommand($this->em);

        return $defaultCommands;
    }


    /**
     * Overridden so that the application doesn't expect the command
     * name to be the first argument.
     */
    public function getDefinition()
    {
        $inputDefinition = parent::getDefinition();
        // clear out the normal first argument, which is the command name
        $inputDefinition->setArguments();

        return $inputDefinition;
    }
}
