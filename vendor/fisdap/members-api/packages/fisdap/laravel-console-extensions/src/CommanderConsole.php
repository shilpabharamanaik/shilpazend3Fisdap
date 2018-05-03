<?php namespace Fisdap\Console;

use App;
use InvalidArgumentException;
use ReflectionClass;


/**
 * Like CommanderTrait, this enables CommandBus functionality for Artisan/Symfony console commands
 *
 * @package Fisdap\Console
 */
trait CommanderConsole
{
    /**
     * Execute the command
     *
     * @param  string $command
     * @param  array  $input
     * @param  array  $decorators
     *
     * @return mixed
     */
    public function executeCommand($command, array $input, $decorators = [])
    {
        $command = $this->mapInputToCommand($command, $input);

        $bus = $this->getCommandBus();

        // If any decorators are passed, we'll
        // filter through and register them
        // with the CommandBus, so that they
        // are executed first.
        foreach ($decorators as $decorator) {
            $bus->decorate($decorator);
        }

        return $bus->execute($command);
    }


    /**
     * Fetch the command bus
     *
     * @return mixed
     */
    protected function getCommandBus()
    {
        return App::make('Laracasts\Commander\CommandBus');
    }


    /**
     * Map an array of input to a command's properties.
     * - Code courtesy of Taylor Otwell.
     *
     * @param  string $command
     * @param  array  $input
     *
     * @throws InvalidArgumentException
     *
     * @return mixed
     */
    protected function mapInputToCommand($command, array $input)
    {
        $dependencies = [];

        $class = new ReflectionClass($command);

        foreach ($class->getConstructor()->getParameters() as $parameter) {
            $name = $parameter->getName();

            if (array_key_exists($name, $input)) {
                $dependencies[] = $input[$name];
            } elseif ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
            } else {
                throw new InvalidArgumentException("Unable to map input to command: {$name}");
            }
        }

        return $class->newInstanceArgs($dependencies);
    }
}