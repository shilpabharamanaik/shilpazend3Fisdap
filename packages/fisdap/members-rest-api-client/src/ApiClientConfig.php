<?php namespace Fisdap\Api\Client;

/**
 * Loads mrapi-client-config.php configuration file and provides access to configuration data
 *
 * @package Fisdap\Api\Client
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class ApiClientConfig
{
    /**
     * @var string The base URL of the API server
     */
    private $baseUrl;

    /**
     * @var int The HTTP request timeout in seconds
     */
    private $requestTimeout;

    /**
     * @var int The connection timeout in seconds
     */
    private $connectionTimeout;

    /**
     * @var string The directory path where the config file resides
     */
    private static $configPath = null;


    /**
     * @return static
     */
    public static function getInstance()
    {
        static $instance = null;
        if (null === $instance) {
            $instance = new static();
        }

        return $instance;
    }


    /**
     * @throws \Exception
     */
    private function __construct()
    {
        $this->loadConfig($this->getConfigFile(self::$configPath));
    }


    /**
     * @param null $configPath
     *
     * @return null|string
     */
    private function getConfigFile($configPath = null)
    {
        // src/..
        $libraryRoot = __DIR__ . DIRECTORY_SEPARATOR . '..';

        // vendor/fisdap/members-rest-api-client-php/../../../
        $projectRoot = $libraryRoot . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..';

        $laravel4ConfigRoot = $projectRoot . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'config';
        $zf1ConfigRoot = $projectRoot . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'configs';

        $directories = [
            $configPath,
            $libraryRoot . DIRECTORY_SEPARATOR . 'config', // for testing
            $projectRoot,
            $laravel4ConfigRoot . DIRECTORY_SEPARATOR . 'packages'
                . DIRECTORY_SEPARATOR . 'fisdap' . DIRECTORY_SEPARATOR . 'members-rest-api-client',
            $zf1ConfigRoot
        ];

        $configFile = null;

        foreach ($directories as $directory) {
            if ($directory === null) {
                continue;
            }

            $configFile = $directory . DIRECTORY_SEPARATOR . 'mrapi-client-config.php';

            if (file_exists($configFile)) {
                break;
            }
        }

        return $configFile;
    }


    /**
     * @param string $configFile
     *
     * @throws \Exception
     */
    private function loadConfig($configFile)
    {
        if ($configFile === null) {
            throw new \Exception("Missing configuration file for Members REST API Client Library");
        }

        $config = include($configFile);
        $this->baseUrl = array_get($config, 'baseUrl');

        /*
         * default to local proxy listening on port 8100
         *
         * /etc/hosts on client should have the following entry:
         * 127.0.0.1    mrapi
         */
        if ($this->baseUrl === false) {
            $this->baseUrl = 'http://mrapi:8100';
        }

        $this->requestTimeout = array_get($config, 'requestTimeout');
        $this->connectionTimeout = array_get($config, 'connectionTimeout');
    }


    /**
     * @param string $configPath
     */
    public static function setConfigPath($configPath)
    {
        self::$configPath = $configPath;
    }


    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }


    /**
     * @return int
     */
    public function getRequestTimeout()
    {
        return $this->requestTimeout;
    }


    /**
     * @return int
     */
    public function getConnectionTimeout()
    {
        return $this->connectionTimeout;
    }


    /**
     * Private clone method to prevent cloning of this class
     *
     * @return void
     */
    private function __clone()
    {
    }


    /**
     * Private unserialize method to prevent unserializing this class
     *
     * @return void
     */
    private function __wakeup()
    {
    }
}
