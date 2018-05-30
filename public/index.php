<?php

use Zend\Mvc\Application;
use Zend\Stdlib\ArrayUtils;

$siteRootDir = __DIR__;
define('APPLICATION_PATH', $siteRootDir . '/../application');
set_include_path(
APPLICATION_PATH . '/models' . PATH_SEPARATOR
. APPLICATION_PATH . '/modules/default/controllers' . PATH_SEPARATOR
. APPLICATION_PATH . '/controllers' . PATH_SEPARATOR
. APPLICATION_PATH . PATH_SEPARATOR
. $siteRootDir . '/library' . PATH_SEPARATOR
. get_include_path()
);

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
/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
chdir(dirname(__DIR__));

// Decline static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server') {
    $path = realpath(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    if (__FILE__ !== $path && is_file($path)) {
        return false;
    }
    unset($path);
}


// Composer autoloading
include __DIR__ . '/../vendor/autoload.php';

if (! class_exists(Application::class)) {
    throw new RuntimeException(
        "Unable to load application.\n"
        . "- Type `composer install` if you are developing locally.\n"
        . "- Type `vagrant ssh -c 'composer install'` if you are using Vagrant.\n"
        . "- Type `docker-compose run zf composer install` if you are using Docker.\n"
    );
}

// Retrieve configuration
$appConfig = require __DIR__ . '/../config/application.config.php';
if (file_exists(__DIR__ . '/../config/development.config.php')) {
    $appConfig = ArrayUtils::merge($appConfig, require __DIR__ . '/../config/development.config.php');
}
$application  = Application::init($appConfig);
//require __DIR__ . '/../init_autoloader.php';
//echo "aaa";exit;
// Run the application!
$application->run();
