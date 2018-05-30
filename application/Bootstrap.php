<?php
//ini_set('session.save_path', dirname(__FILE__).'../tmp/sessions');

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Fisdap\WhoopsRun;
use Illuminate\Contracts\Container\Container;
use Monolog\Handler\ChromePHPHandler;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\GroupHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Fisdap\Members\Shifts\ShiftEventsSubscriber;
use Doctrine\Common\Proxy\Autoloader as ProxyAutoloader;

class Bootstrap
{
    /**
     * @var Container
     */
    protected $container;
    
    public function __construct()
    {
        global $container;

        $this->container = $container;
    }
    public static function run()
    {
        self::prepare();

        //$response = self::$frontController->dispatch();
        //self::sendResponse($response);
    }


    public static function prepare()
    {
        //echo "Bootstap";exit;
        //self::setIlluminateContainer();

        //self::_initXhprof();
        self::_initDebugBar();

        self::_initHost();
        self::_initLogger();

        self::_initCss();
        self::_initBugsnag();
        self::_initExceptionLogger();
        self::_initErrorHandler();
        self::_initDb();
        self::_initDoctrineAdditions();
        self::_initSession();
        self::_initCache();
    }
    /**
     * @param Container $container
     */
    public function setIlluminateContainer(Container $container)
    {
        $this->container = $container;
    }


    /**
     * @return Container
     */
    public function getIlluminateContainer()
    {
        return $this->container;
    }


    /**
     * Initialize Xhprof
     * Passive PHP profiling
     * @todo remove in favor of blackfire.io
     */
    protected function _initXhprof()
    {
        if (env('XHPROF_ENABLED') == true && extension_loaded('xhprof')) {
            include_once '/usr/local/zend/share/pear/xhprof_lib/utils/xhprof_lib.php';
            include_once '/usr/local/zend/share/pear/xhprof_lib/utils/xhprof_runs.php';
            xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
        }
    }


    /**
     * Initialize Debug Bar
     *
     * Use the PHP Debug Bar in development (but not on CLI)
     */
    protected function _initDebugBar()
    {
        if (APPLICATION_ENV == 'development' && env('DEBUG_BAR_ENABLED') == true && PHP_SAPI != 'cli') {
            $debugBar = new \DebugBar\DebugBar();

            $debugBar->addCollector(new DebugBar\DataCollector\PhpInfoCollector());
            $debugBar->addCollector(new DebugBar\DataCollector\MessagesCollector());
            $debugBar->addCollector(new DebugBar\DataCollector\RequestDataCollector());
            $debugBar->addCollector(new DebugBar\DataCollector\MemoryCollector());
            $debugBar->addCollector(new DebugBar\DataCollector\TimeDataCollector());

            if (env('XHPROF_ENABLED') == true && extension_loaded('xhprof')) {
                $debugBar->addCollector(new Fisdap\DebugBar\DataCollector\Xhprof\XhprofCollector());
            }

            // Add Monolog collector
            $this->bootstrap('Logger');
            $logger = \Zend_Registry::get('logger');
            if ($logger instanceof \Monolog\Logger) {
                $debugBar->addCollector(new DebugBar\Bridge\MonologCollector($logger));
            }

            \Zend_Registry::set('debugBar', $debugBar);
        }
    }


    /**
     * Create application URL for use in e-mails/crons
     */
    protected function _initHost()
    {
        switch (APPLICATION_ENV) {
            case 'development':
                $host_addr = 'https://members.fisdapdev.net';
                break;
            case 'qa':
                $host_addr = 'https://members.fisdapqa.net';
                break;
            case 'staging':
                $host_addr = 'https://members.fisdapstage.net';
                break;
            default:
                $host_addr = 'https://members.fisdap.net';
                break;
        }

        Zend_Registry::set('host', $host_addr);
    }


    protected function _initLogger()
    {
        $logger = new Logger('members');

        if (APPLICATION_ENV == 'development' || APPLICATION_ENV == 'testing') {
            $DEVELOPMENT_LOGGING_LEVEL = constant(getenv('DEVELOPMENT_LOGGING_LEVEL'));
            $loggingLevel = $DEVELOPMENT_LOGGING_LEVEL ?: Logger::DEBUG;
        } else {
            $loggingLevel = Logger::INFO;
        }

        $handlers = array(
            new StreamHandler(
                IN_HOME_DIR === true ? APPLICATION_PATH . '/../data/members.log' : '/var/log/fisdap/members.log',
                $loggingLevel,
                true,
                APPLICATION_ENV == 'development' || APPLICATION_ENV == 'testing' ? 0666 : null
            )
        );

        // only enable FirePHP and ChromePHP handlers in development
        if (APPLICATION_ENV == 'development') {
            $handlers = array_merge(
                $handlers,
                array(
                    new FirePHPHandler(),
                    new ChromePHPHandler()
                )
            );
        }

        $logger->pushHandler(new GroupHandler($handlers));

        Zend_Registry::set('logger', $logger);
        $this->container->instance('Psr\Log\LoggerInterface', $logger);
    }


    /**
     * Compile SCSS
     */
    protected function _initCss()
    {
        /**
         * If in production, the CSS should already be compiled so we don't want to waste our processing time.
         */
        if (APPLICATION_ENV == "production" || PHP_SAPI == 'cli') {
            return;
        }

        /**
         * Compile the source SCSS through scssc() and save the output CSS into the destination.
         */
        $scss = new scssc();
        $scss->setImportPaths("../public/scss/");

        $css = $scss->compile('@import "headers.scss"');
        file_put_contents("../public/css/styleguide/headers.css", $css);

        $css = $scss->compile('@import "products.scss"');
        file_put_contents("../public/css/styleguide/products.css", $css);
    }


    /**
     * Setup Bugsnag
     */
    protected function _initBugsnag()
    {
        $bugsnagConfig = $this->getOption('bugsnag');
        $bugsnag = new Bugsnag_Client($bugsnagConfig['apikey']);

        $bugsnag->setReleaseStage(RELEASE_STAGE);

        // these must be set to false in order for fatal errors to be handled properly
        $bugsnag->setAutoNotify(false)->setBatchSending(false);

        Zend_Registry::set('bugsnag', $bugsnag);
    }


    protected function _initExceptionLogger()
    {
        $this->bootstrap(array('logger', 'bugsnag'));

        $exceptionLogger = new ExceptionLogger();

        Zend_Registry::set('exceptionLogger', $exceptionLogger);
        $this->container->instance('ExceptionLogger', $exceptionLogger);
    }


    /**
     * Initialize Error Handler
     *
     * Use Whoops in development (but not on CLI) and custom error handling everywhere else
     */
    protected function _initErrorHandler()
    {
        $this->bootstrap('exceptionLogger');

        if (APPLICATION_ENV == 'development' && env('WHOOPS_ENABLED') == true) {

            // use standard error handlers on CLI
            if (PHP_SAPI == 'cli') {
                $this->_registerErrorHandlers();

                return;
            }

            // this line forces Zend Framework to throw exceptions and let us handle them
            Zend_Controller_Front::getInstance()->throwExceptions(true);

            // create new Whoops object
            $whoops = new WhoopsRun();

            // filter out errors as desired
            $silenceLevels = bitwiseConstants(getenv('WHOOPS_SILENCE_LEVELS'));
            if (isset($silenceLevels)) {
                $whoops->silenceErrorsInPaths('/.*/', $silenceLevels);
            }

            // adding an error handler, pretty one ;)
            $prettyPageHandler = new Whoops\Handler\PrettyPageHandler();
            $whoops->pushHandler($prettyPageHandler);

            // respond in JSON for AJAX requests
            $jsonHandler = new Whoops\Handler\JsonResponseHandler();
            $jsonHandler->onlyForAjaxRequests(true);
            $jsonHandler->addTraceToOutput(true);
            $whoops->pushHandler($jsonHandler);

            // set Whoops as default error handler
            $whoops->register();
        } else {
            if (PHP_SAPI != 'cli') {
                // enable custom router to support handling of fatal errors by Zend_Controller_Plugin_ErrorHandler
                $front = Zend_Controller_Front::getInstance();
                $front->setRouter(new ErrorRouter());
            }

            // use standard error handlers in non-dev environments
            $this->_registerErrorHandlers();
        }
    }


    protected function _registerErrorHandlers()
    {
        // enable nice error page when a PHP FATAL error is encountered
        register_shutdown_function(array($this, 'onApplicationShutdown'));

        // enable logging of non-fatal errors
        switch (APPLICATION_ENV) {
            case 'production':
                $errorTypes = E_ALL & ~E_DEPRECATED & ~E_STRICT & ~E_NOTICE;
                break;
            case 'staging':
                $errorTypes = E_ALL & ~E_DEPRECATED & ~E_STRICT & ~E_NOTICE;
                break;
            default:
                $errorTypes = E_ALL | E_STRICT;
                break;
        }

        set_error_handler(array($this, 'handleError'), $errorTypes);
    }


    protected function _initDb()
    {
        /*
         * While this may look silly since we use Doctrine DBAL, please don't delete.
         * This is used in the appmon module for the database health check
         */
        /** @var Zend_Application_Resource_Db $r */
        $r = $this->getPluginResource('db');
        $db = $r->getDbAdapter();

        Zend_Registry::set('db', $db);
        $this->container->instance('Zend_Db_Adapter_Abstract', $db);
        $this->container->alias('Zend_Db_Adapter_Abstract', 'db');

        /*
         * This creates a second connection to the database on every request, BUT we need it because
         * we're still using mysql_real_escape_string() :( ~bgetsug
         */

        // set up mysql_connect() so that it will also route through the correct db
        $dbConfig = $db->getConfig();

        ($GLOBALS["___mysqli_ston"] = mysqli_connect($dbConfig['host'], $dbConfig['username'], $dbConfig['password']));

        $this->bootstrap('DebugBar');
        if (\Zend_Registry::isRegistered('debugBar')) {
            // Add Zend DB PDO object
            $debugBar = \Zend_Registry::get('debugBar');
            if ($debugBar instanceof \DebugBar\DebugBar) {
                $pdo = new DebugBar\DataCollector\PDO\TraceablePDO($db->getConnection());
                $debugBar->addCollector(new Fisdap\DebugBar\DataCollector\ZendDb\ZendDbCollector($pdo));
            }
        }
    }


    /**
     * Initializes Custom Doctrine DQL and Debug Bar query collector for Doctrine
     * @throws Zend_Exception
     * @throws \DebugBar\DebugBarException
     */
    protected function _initDoctrineAdditions()
    {
        $this->bootstrap('doctrine');
        $this->bootstrap('DebugBar');

        /** @var EntityManagerInterface $em */
        $em = \Zend_Registry::get('doctrine')->getEntityManager();

        ProxyAutoloader::register(
            realpath($em->getConfiguration()->getProxyDir()),
            $em->getConfiguration()->getProxyNamespace()
        );

        // custom DQL functions
        $em->getConfiguration()->addCustomDatetimeFunction('YEAR', 'Fisdap\DoctrineDQLYear');

        // DebugBar
        if (\Zend_Registry::isRegistered('debugBar')) {
            $debugBar = \Zend_Registry::get('debugBar');
            if ($debugBar instanceof \DebugBar\DebugBar) {
                // Add Doctrine collection to DebugBar
                $debugStack = new Doctrine\DBAL\Logging\DebugStack();
                $em->getConnection()->getConfiguration()->setSQLLogger($debugStack);
                $debugBar->addCollector(new DebugBar\Bridge\DoctrineCollector($debugStack));
            }
        }

        // register EntityManager and listeners
        $this->container->instance(EntityManager::class, $em);
        $this->container->instance(EntityManagerInterface::class, $em);

        $em->getEventManager()->addEventSubscriber($this->container->make(ShiftEventsSubscriber::class));
    }


    protected function _initSession()
    {
        //	return;
        // skip session creation if we're on the command line
        if (PHP_SAPI == 'cli') {
            return;
        }

        $config = $this->getOption('couchbase');
        //print_r($config); exit;
        //print_r($Zend_Session_SaveHandler_Couchbase($config)); exit;
        //Zend_Session::setSaveHandler(new Zend_Session_SaveHandler_Couchbase($config));

        //Zend_Session::start();

        $session = new Zend_Session_Namespace();
        Zend_Registry::set('session', $session);
    }


    /**
     * Override _bootstrap() method such that exceptions can be caught
     * and passed to the ErrorController
     *
     * @param null $resource
     */
    protected function _bootstrap($resource = null)
    {
        try {
            parent::_bootstrap($resource);
        } catch (Exception $e) {
            if (PHP_SAPI != 'cli') {
                parent::_bootstrap(array('frontController', 'logger'));
                $front = $this->getResource('frontController');
                $front->registerPlugin(new Application_Plugin_BootstrapError($e));
            } else {
                echo 'Exception in Bootstrap: ' . $e->getMessage() . "\n" . $e->getTraceAsString();
                exit(1);
            }
        }
    }


    /**
     * Error handling function for logging (non-fatal) PHP errors to application log
     *
     * @param int    $errno
     * @param string $errstr
     * @param string $errfile
     * @param int    $errline
     */
    public function handleError($errno, $errstr, $errfile, $errline)
    {
        if (!error_reporting()) {
            return;
        }

        $exceptionLogger = Zend_Registry::get('exceptionLogger');

        $exceptionLogger->log(new ErrorException($errstr, $errno, 0, $errfile, $errline));
    }


    /**
     * Shutdown function transforming PHP fatal errors into exceptions
     *
     * @see http://stackoverflow.com/questions/16284235/zend-framework-error-page-for-php-fatal-errors
     */
    public function onApplicationShutdown()
    {
        $error = error_get_last();
        $wasFatal = ($error && ($error['type'] === E_ERROR) || ($error['type'] === E_USER_ERROR));

        if ($wasFatal) {
            if (PHP_SAPI != 'cli') { // fatal errors are always output on CLI
                $frontController = Zend_Controller_Front::getInstance();
                $errorHandler = $frontController->getPlugin('Zend_Controller_Plugin_ErrorHandler');
                $request = $frontController->getRequest();
                $response = $frontController->getResponse();

                // Add the fatal exception to the response in a format that ErrorHandler will understand
                $response->setException(
                    new ErrorException($error['message'], $error['type'], 0, $error['file'], $error['line'])
                );

                // Call ErrorHandler->_handleError which will forward to the Error controller
                $handleErrorMethod = new ReflectionMethod('Zend_Controller_Plugin_ErrorHandler', '_handleError');
                $handleErrorMethod->setAccessible(true);
                $handleErrorMethod->invoke($errorHandler, $request);

                // Discard any view output from before the fatal
                ob_end_clean();

                // Now display the error controller:
                $frontController->dispatch($request, $response);
            }
        }
    }


    /**
     * Get Zend cache templates ready
     * based on default cache driver in INI
     */
    protected function _initCache()
    {
        $allConfig = Zend_Registry::get('config');
        $config = $allConfig->resources->cacheManager;

        // if there is a default caching engine, set caching
        if ($config->default->backend->name) {
            $defaultBackend = $config->default->backend->toArray();

            $pageCache = array(
                'frontend' => array(
                    'name'    => 'Page',
                    'options' => array(
                        'lifetime'        => 7200,
                        'debug_header'    => false,
                        'default_options' => array(
                            'cache_with_session_variables' => true,
                            'cache_with_cookie_variables'  => true,
                        ),
                    )
                ),
                'backend'  => $defaultBackend,
            );

            $defaultCache = array(
                'frontend' => array(
                    'name'    => 'Core',
                    'options' => array(
                        'lifetime'                => 7200,
                        'automatic_serialization' => true,
                    )
                ),
                'backend'  => $defaultBackend,
            );

            $outputCache = array(
                'frontend' => array(
                    'name'    => 'Frontend_Output',
                    'options' => array(
                        'lifetime' => 7200,
                    )
                ),
                'backend'  => $defaultBackend,
            );

            $manager = new Zend_Cache_Manager;
            $manager->setCacheTemplate('page', $pageCache);
            $manager->setCacheTemplate('default', $defaultCache);
            $manager->setCacheTemplate('output', $outputCache);

            Zend_Registry::set('zendCacheManager', $manager);
            $this->container->instance('Zend_Cache_Manager', $manager);
            $this->container->instance('Zend_Cache_Core', $manager->getCache('default'));
        } else {
            Zend_Registry::set('zendCacheManager', false); // caching is disabled
            $this->container->instance('Zend_Cache_Manager', false);
        }
    }
}
