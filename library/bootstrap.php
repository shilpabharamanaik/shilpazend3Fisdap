<?php

// include composer autoloader
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Foundation\Application;

require(__DIR__ . '/../vendor/autoload.php');

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
if (!defined('APPLICATION_ENV')) {
    if (file_exists('/etc/fisdap/env')) {
        define('APPLICATION_ENV', trim(file_get_contents('/etc/fisdap/env')));
    } else {
        define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development'));
    }
}

// load helper functions
require(APPLICATION_PATH . '/helpers.php');

// Define release stage & set PHP error reporting level per environment
switch (APPLICATION_ENV) {
    case 'production':
        define('RELEASE_STAGE', 'Prod');
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT & ~E_NOTICE);
        break;
    case 'staging':
        define('RELEASE_STAGE', 'Stage');
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT & ~E_NOTICE);
        break;
    case 'qa':
        define('RELEASE_STAGE', 'QA');
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT & ~E_NOTICE);
        break;
    default:
        define('RELEASE_STAGE', 'Dev');
        error_reporting(E_ALL);
        break;
}

$config = require(__DIR__ . '/../application/config.php');

// Create application
$application = new Zend_Application(
    APPLICATION_ENV,
    array('config' => $config)
);

// Setup IoC Container / Illuminate Application and register service providers
/** @var Application|Container $container */
$container = require(__DIR__ . '/../application/container.php');
//print_r($container); exit;
// Make container available in Bootstrap
/** @noinspection PhpUndefinedMethodInspection */
$bootstrap = $application->getBootstrap()->setIlluminateContainer($container);

// Setup Front Controller and Dispatcher
$frontController = Zend_Controller_Front::getInstance();
$dispatcher = new Zend_Controller_Dispatcher_Standard($container);
$frontController->setDispatcher($dispatcher);

// Bootstrap app and run
$application->bootstrap();

return $application;
