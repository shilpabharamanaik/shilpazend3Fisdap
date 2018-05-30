<?php namespace Fisdap\Members\Config;

use Illuminate\Config\Repository;
use Illuminate\Support\ServiceProvider;
use Zend\Mvc\Application;
use Zend\Config\Config;
use Zend_Registry;

/**
 * Class ConfigServiceProvider
 *
 * @package Fisdap\Members\Config
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class ConfigServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        /** @var Zend_Application $zendApplication */
        $zendApplication = $this->app->make('Zend_Application');

        // get config from this Bootstrap
        $config = new Config($zendApplication->getConfig(), true);
        
        \Zend_Registry::set('config', $config);
        
        // Laravel-style configs
        $config->merge(new \Zend\Config\Config(require APPLICATION_PATH . '/configs/braintree.php'));
        $config->merge(new \Zend\Config\Config(require APPLICATION_PATH . '/configs/database.php'));
        $config->merge(new \Zend\Config\Config(require APPLICATION_PATH . '/configs/health-checks.php'));
        $config->merge(new \Zend\Config\Config(require APPLICATION_PATH . '/configs/idms.php'));
        $config->merge(new \Zend\Config\Config(require APPLICATION_PATH . '/configs/lti.php'));
        $config->merge(new \Zend\Config\Config(require APPLICATION_PATH . '/configs/mail.php'));
        $config->merge(new \Zend\Config\Config(require APPLICATION_PATH . '/configs/queue.php'));
        $config->merge(new \Zend\Config\Config(require APPLICATION_PATH . '/configs/view.php'));
        $config->merge(new \Zend\Config\Config(require APPLICATION_PATH . '/configs/great-plains-api.php'));
        $config->merge(new \Zend\Config\Config(require APPLICATION_PATH . '/configs/jbl-auth.php'));
        $config->setReadOnly();
        $this->app->instance(Config::class, $config);

        // bind a Laravel-style config separately, as the 'config' binding is used by other Laravel/Illuminate components
        $this->app->instance(Repository::class, new Repository($config->toArray()));
        $this->app->alias(Repository::class, 'config');
    }
}
