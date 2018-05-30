<?php

// initialize phpdotenv to facilitate configuration from environment variables
$dotenv = new Dotenv\Dotenv(APPLICATION_PATH . '/..');
$dotenv->load();

// config files for Zend_Config
$configFiles = [
    'application.ini',
    'couchbase.ini',
    'db.ini',
    'doctrine_cache.ini',
    'doctrine_dbal.ini',
    'doctrine_orm.ini',
    'moodle.ini',
    'piwik.ini',
    'rserve.ini',
    'zend_cache.ini',
];

// support user-defined configuration in 'development' environment
if (APPLICATION_ENV == 'development') {

    // get home directory path, if we're in one
    if (preg_match('/^(\/home\/\w+\/)/', __DIR__, $homeMatches)) {
        define('IN_HOME_DIR', true);

        $homeConfigPath = isset($homeMatches[1]) ? $homeMatches[1] . 'fisdap_configs/' : null;

        if (isset($homeConfigPath)) {
            if (is_dir($homeConfigPath)) {
                $homeConfigFiles = scandir($homeConfigPath);
            }
        }
    } else {
        define('IN_HOME_DIR', false);
    }
} else {
    define('IN_HOME_DIR', false);
}

// support system-level configuration
define('SYSTEM_CONFIG_PATH', '/etc/fisdap/');
$systemConfigFiles = is_dir(SYSTEM_CONFIG_PATH) ? scandir(SYSTEM_CONFIG_PATH) : null;

$config = [];

foreach ($configFiles as $configFile) {
    if (isset($homeConfigFiles)) {
        if (in_array($configFile, $homeConfigFiles)) {
            // use user-defined config file
            $config[] = $homeConfigPath . $configFile;
            continue;
        }
    }

    if (is_array($systemConfigFiles)) {
        if (in_array($configFile, $systemConfigFiles)) {
            // use system-level config file
            $config[] = SYSTEM_CONFIG_PATH . $configFile;
            continue;
        }
    }

    // use default config file
    $config[] = APPLICATION_PATH . '/configs/' . $configFile;
}

return $config;
