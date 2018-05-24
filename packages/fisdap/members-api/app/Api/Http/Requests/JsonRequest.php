<?php namespace Fisdap\Api\Http\Requests;


/**
 * Class JsonRequest
 *
 * @package Fisdap\Api\Http\Requests
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @see https://laracasts.com/discuss/channels/laravel/how-to-validate-json-input-using-requests
 */
abstract class JsonRequest extends Request
{
    /**
     * Get the validator instance for the request.
     *
     * @return \Illuminate\Validation\Validator
     */
    protected function getValidatorInstance()
    {
        $factory = $this->container->make('Illuminate\Validation\Factory');

        if (method_exists($this, 'validator')) {
            return $this->container->call([$this, 'validator'], compact('factory'));
        }

        return $factory->make(
            json_decode($this->getContent(), true),
            $this->container->call([$this, 'rules']),
            $this->messages(),
            $this->attributes()
        );
    }
}
