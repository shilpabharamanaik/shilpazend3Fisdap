<?php namespace Fisdap\Members\ServiceProviders;

use Illuminate\Validation\ValidationServiceProvider as IlluminateValidationServiceProvider;

/**
 * Class ValidationServiceProvider
 *
 * @package Fisdap\Members\ServiceProviders
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class ValidationServiceProvider extends IlluminateValidationServiceProvider
{
    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->registerValidationResolverHook();

        $this->registerValidationFactory();
    }
}
