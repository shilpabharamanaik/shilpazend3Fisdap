<?php namespace Fisdap\Api\Jobs;

use Closure;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Contracts\Validation\Validator;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Validates Jobs
 *
 * @package Fisdap\Api\Jobs
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class JobValidationPipe
{
    /**
     * The validation factory instance.
     *
     * @var Factory
     */
    protected $factory;

    /**
     * @var Container
     */
    protected $container;


    /**
     * Create a new validating middleware instance.
     *
     * @param Factory   $factory
     * @param Container $container
     */
    public function __construct(Factory $factory, Container $container)
    {
        $this->factory = $factory;
        $this->container = $container;
    }


    /**
     * Validate the command before execution.
     *
     * @param object   $command
     * @param \Closure $next
     *
     */
    public function handle($command, Closure $next)
    {
        if (method_exists($command, 'rules') && is_array($command->rules())) {
            $this->validate($command);
        }

        return $next($command);
    }


    /**
     * Validate the command.
     *
     * @param object $command
     */
    protected function validate($command)
    {
        if (method_exists($command, 'validate')) {
            $this->container->call([$command, 'validate']);
        }

        $messages = method_exists($command, 'messages') ? $command->messages() : [];
        $validator = $this->factory->make($this->getData($command), $command->rules(), $messages);

        if ($validator->fails()) {
            throw new UnprocessableEntityHttpException($this->formatErrors($validator));
        }
    }


    /**
     * Get the data to be validated.
     *
     * @param object $command
     *
     * @return array
     */
    protected function getData($command)
    {
        return json_decode(json_encode($command), true);
    }


    /**
     * @param Validator $validator
     *
     * @return string
     */
    protected function formatErrors(Validator $validator)
    {
        $errors = [];

        foreach ($validator->getMessageBag()->getMessages() as $field => $fieldErrors) {
            $errors[] = "$field - " . implode(', ', $fieldErrors);
        }

        return 'Validation errors: ' . implode(', ', $errors);
    }
}
