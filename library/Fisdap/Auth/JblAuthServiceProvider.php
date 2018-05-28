<?php namespace Fisdap\Auth;

use Illuminate\Support\ServiceProvider;
use Fisdap\JBL\Authentication\CurlHttpClient;
use Fisdap\JBL\Authentication\LoggerCurlHttpClient;
use Fisdap\JBL\Authentication\JblRestApiUserAuthentication;


/**
 * Class JblAuthServiceProvider
 *
 * @package Fisdap\Auth
 * @author  Kate Hanson <khanson@fisdap.net>
 */
class JblAuthServiceProvider extends ServiceProvider
{
    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->app->bind(JblRestApiUserAuthentication::class, function(){
            $jblAuthConfig = $this->app->make('config')->get('jbl-auth');

            $httpClient = new LoggerCurlHttpClient(\Zend_Registry::get('logger'), new CurlHttpClient());

            $authenticator = new JblRestApiUserAuthentication($jblAuthConfig['baseUrl'], $httpClient);

            return $authenticator;
        });

    }
}