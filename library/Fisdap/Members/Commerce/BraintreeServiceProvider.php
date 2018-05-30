<?php namespace Fisdap\Members\Commerce;

use Braintree_Configuration;
use Illuminate\Support\ServiceProvider;

/**
 * Class BraintreeServiceProvider
 *
 * @package Fisdap\Members\Commerce
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class BraintreeServiceProvider extends ServiceProvider
{
    /**
     * @inheritdoc
     */
    public function register()
    {
        $braintreeConfig = $this->app->make('config')->get('braintree');

        Braintree_Configuration::environment($braintreeConfig['environment']);
        Braintree_Configuration::merchantId($braintreeConfig['merchantId']);
        Braintree_Configuration::publicKey($braintreeConfig['publicKey']);
        Braintree_Configuration::privateKey($braintreeConfig['privateKey']);
    }
}
