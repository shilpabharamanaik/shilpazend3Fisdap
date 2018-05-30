<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Controller
 * @subpackage Dispatcher
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */
use Illuminate\Contracts\Container\Container;

/** Zend_Loader */
require_once 'Zend/Loader.php';

/** Zend_Controller_Dispatcher_Abstract */
require_once 'Zend/Controller/Dispatcher/Abstract.php';

/**
 * @category   Zend
 * @package    Zend_Controller
 * @subpackage Dispatcher
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Controller_Dispatcher_Standard extends Zend_Controller_Dispatcher_Abstract
{
    /**
     * Current dispatchable directory
     * @var string
     */
    protected $_curDirectory;

    /**
     * Current module (formatted)
     * @var string
     */
    protected $_curModule;

    /**
     * Controller directory(ies)
     * @var array
     */
    protected $_controllerDirectory = array();

    /**
     * @var Container
     */
    protected $container;


    /**
     * Constructor: Set current module to default value
     *
     * @param Container $container
     * @param  array    $params
     */
    public function __construct(Container $container, array $params = array())
    {
        $this->container = $container;
        parent::__construct($params);
        $this->_curModule = $this->getDefaultModule();
    }


    /**
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }


    /**
     * @param Container $container
     */
    public function setContainer($container)
    {
        $this->container = $container;
    }


    /**
     * Add a single path to the controller directory stack
     *
     * @param string $path
     * @param string $module
     * @return Zend_Controller_Dispatcher_Standard
     */
    public function addControllerDirectory($path, $module = null)
    {
        if (null === $module) {
            $module = $this->_defaultModule;
        }

        $module = (string) $module;
        $path   = rtrim((string) $path, '/\\');

        $this->_controllerDirectory[$module] = $path;
        return $this;
    }


    /**
     * Set controller directory
     *
     * @param array|string $directory
     * @param null         $module
     *
     * @return Zend_Controller_Dispatcher_Standard
     * @throws Zend_Controller_Exception
     */
    public function setControllerDirectory($directory, $module = null)
    {
        $this->_controllerDirectory = array();

        if (is_string($directory)) {
            $this->addControllerDirectory($directory, $module);
        } elseif (is_array($directory)) {
            foreach ((array) $directory as $module => $path) {
                $this->addControllerDirectory($path, $module);
            }
        } else {
            require_once 'Zend/Controller/Exception.php';
            throw new Zend_Controller_Exception('Controller directory spec must be either a string or an array');
        }

        return $this;
    }

    /**
     * Return the currently set directories for Zend_Controller_Action class
     * lookup
     *
     * If a module is specified, returns just that directory.
     *
     * @param  string $module Module name
     * @return array|string Returns array of all directories by default, single
     * module directory if module argument provided
     */
    public function getControllerDirectory($module = null)
    {
        if (null === $module) {
            return $this->_controllerDirectory;
        }

        $module = (string) $module;
        if (array_key_exists($module, $this->_controllerDirectory)) {
            return $this->_controllerDirectory[$module];
        }

        return null;
    }

    /**
     * Remove a controller directory by module name
     *
     * @param  string $module
     * @return bool
     */
    public function removeControllerDirectory($module)
    {
        $module = (string) $module;
        if (array_key_exists($module, $this->_controllerDirectory)) {
            unset($this->_controllerDirectory[$module]);
            return true;
        }
        return false;
    }

    /**
     * Format the module name.
     *
     * @param string $unformatted
     * @return string
     */
    public function formatModuleName($unformatted)
    {
        if (($this->_defaultModule == $unformatted) && !$this->getParam('prefixDefaultModule')) {
            return $unformatted;
        }

        return ucfirst($this->_formatName($unformatted));
    }

    /**
     * Format action class name
     *
     * @param string $moduleName Name of the current module
     * @param string $className Name of the action class
     * @return string Formatted class name
     */
    public function formatClassName($moduleName, $className)
    {
        return $this->formatModuleName($moduleName) . '_' . $className;
    }

    /**
     * Convert a class name to a filename
     *
     * @param string $class
     * @return string
     */
    public function classToFilename($class)
    {
        return str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';
    }

    /**
     * Returns TRUE if the Zend_Controller_Request_Abstract object can be
     * dispatched to a controller.
     *
     * Use this method wisely. By default, the dispatcher will fall back to the
     * default controller (either in the module specified or the global default)
     * if a given controller does not exist. This method returning false does
     * not necessarily indicate the dispatcher will not still dispatch the call.
     *
     * @param Zend_Controller_Request_Abstract $request
     * @return boolean
     */
    public function isDispatchable(Zend_Controller_Request_Abstract $request)
    {
        $className = $this->getControllerClass($request);
        if (!$className) {
            return false;
        }

        $finalClass  = $className;
        if (($this->_defaultModule != $this->_curModule)
            || $this->getParam('prefixDefaultModule')) {
            $finalClass = $this->formatClassName($this->_curModule, $className);
        }
        if (class_exists($finalClass, false)) {
            return true;
        }

        $fileSpec    = $this->classToFilename($className);
        $dispatchDir = $this->getDispatchDirectory();
        $test        = $dispatchDir . DIRECTORY_SEPARATOR . $fileSpec;
        return Zend_Loader::isReadable($test);
    }


    /**
     * @param Zend_Controller_Request_Abstract  $request
     * @param Zend_Controller_Response_Abstract $response
     *
     * @throws \Exception
     * @throws Zend_Controller_Dispatcher_Exception
     * @throws \Zend_Controller_Exception
     *
     * @see inspired by https://github.com/jeroenvandergeer/zf-ioc
     */
    public function dispatch(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response)
    {
        $this->setResponse($response);

        $controller = $this->getController($request);
        $action = $this->getActionMethod($request);
        $request->setDispatched(true);
        $obLevel = ob_get_level();

        if (!$this->outputBufferingIsDisabled()) {
            ob_start();
        }

        try {
            $this->dispatchToController($controller, $action);
        } catch (\Exception $e) {
            $this->cleanOutputBufferToLevel($obLevel);
            throw $e;
        }

        if (!$this->outputBufferingIsDisabled()) {
            $content = ob_get_clean();
            $response->appendBody($content);
        }
        // Destroy the page controller instance and reflection objects
        $controller = null;
    }


    /**
     * @param $controller
     * @param $action
     */
    protected function dispatchToController(Zend_Controller_Action $controller, $action)
    {
        $helperBroker = new Zend_Controller_Action_HelperBroker($controller);
        $helperBroker->notifyPreDispatch();
        $controller->preDispatch();
        if ($controller->getRequest()->isDispatched()) {
            if (!isset($classMethods) || null === $classMethods) {
                $classMethods = get_class_methods($controller);
            }
            if (!($this->getResponse()->isRedirect())) {
                if (in_array($action, $classMethods)) {
                    $this->call($controller, $action);
                } else {
                    $this->call($controller, '__call', array($action));
                }
            }
            $controller->postDispatch();
        }
        $helperBroker->notifyPostDispatch();
    }


    /**
     * @param Zend_Controller_Request_Abstract $request
     *
     * @return false|string
     * @throws Zend_Controller_Dispatcher_Exception
     * @throws \Zend_Controller_Exception
     */
    protected function getClassName(Zend_Controller_Request_Abstract $request)
    {
        if (!$this->isDispatchable($request)) {
            $controller = $request->getControllerName();
            if (!$this->getParam('useDefaultControllerAlways') && !empty($controller)) {
                require_once 'Zend/Controller/Dispatcher/Exception.php';
                throw new Zend_Controller_Dispatcher_Exception(
                    'Invalid controller specified ('.$request->getControllerName().')'
                );
            }
            $className = $this->getDefaultControllerClass($request);
        } else {
            $className = $this->getControllerClass($request);
            if (!$className) {
                $className = $this->getDefaultControllerClass($request);
            }
        }

        return $className;
    }


    /**
     * @param $className
     *
     * @return string
     */
    protected function getModuleClassName($className)
    {
        $moduleClassName = $className;
        if (($this->_defaultModule != $this->_curModule)
            || $this->getParam('prefixDefaultModule')
        ) {
            $moduleClassName = $this->formatClassName($this->_curModule, $className);
            return $moduleClassName;
        }

        return $moduleClassName;
    }


    /**
     * @param Zend_Controller_Request_Abstract $request
     *
     * @return mixed
     */
    protected function getController(Zend_Controller_Request_Abstract $request)
    {
        $className = $this->getClassName($request);
        $this->loadClass($className);
        $moduleClassName = $this->getModuleClassName($className);

        /** @var Zend_Controller_Action $controller */
        $controller = $this->getContainer()->make($moduleClassName);

        $this->assertControllerIsValid($controller);

        $controller->setRequest($request)
            ->setResponse($this->getResponse())
            ->setInvokeArgs($this->getParams())
            ->initHelperBroker()
            ->init();

        return $controller;
    }


    /**
     * @param $controller
     *
     * @throws Zend_Controller_Dispatcher_Exception
     */
    protected function assertControllerIsValid($controller)
    {
        if (!($controller instanceof Zend_Controller_Action_Interface) &&
            !($controller instanceof Zend_Controller_Action)
        ) {
            require_once 'Zend/Controller/Dispatcher/Exception.php';
            throw new Zend_Controller_Dispatcher_Exception(
                'Controller "'.get_class($controller).'" is not an instance of Zend_Controller_Action_Interface'
            );
        }
    }


    /**
     * @param \Zend_Controller_Action $controller
     * @param                         $action
     * @param array                   $params
     */
    protected function call(Zend_Controller_Action $controller, $action, $params = array())
    {
        $this->getContainer()->call(array($controller, $action), $params);
    }


    /**
     * @param $obLevel
     */
    protected function cleanOutputBufferToLevel($obLevel)
    {
        $curObLevel = ob_get_level();
        if ($curObLevel > $obLevel) {
            do {
                ob_get_clean();
                $curObLevel = ob_get_level();
            } while ($curObLevel > $obLevel);
        }
    }


    /**
     * @return mixed
     */
    protected function outputBufferingIsDisabled()
    {
        $disableOb = $this->getParam('disableOutputBuffering');
        return !empty($disableOb);
    }


    /**
     * Load a controller class
     *
     * Attempts to load the controller class file from
     * {@link getControllerDirectory()}.  If the controller belongs to a
     * module, looks for the module prefix to the controller class.
     *
     * @param string $className
     * @return string Class name loaded
     * @throws Zend_Controller_Dispatcher_Exception if class not loaded
     */
    public function loadClass($className)
    {
        $finalClass  = $className;
        if (($this->_defaultModule != $this->_curModule)
            || $this->getParam('prefixDefaultModule')) {
            $finalClass = $this->formatClassName($this->_curModule, $className);
        }
        if (class_exists($finalClass, false)) {
            return $finalClass;
        }

        $dispatchDir = $this->getDispatchDirectory();
        $loadFile    = $dispatchDir . DIRECTORY_SEPARATOR . $this->classToFilename($className);

        if (Zend_Loader::isReadable($loadFile)) {
            include_once $loadFile;
        } else {
            require_once 'Zend/Controller/Dispatcher/Exception.php';
            throw new Zend_Controller_Dispatcher_Exception('Cannot load controller class "' . $className . '" from file "' . $loadFile . "'");
        }

        if (!class_exists($finalClass, false)) {
            require_once 'Zend/Controller/Dispatcher/Exception.php';
            throw new Zend_Controller_Dispatcher_Exception('Invalid controller class ("' . $finalClass . '")');
        }

        return $finalClass;
    }


    /**
     * Get controller class name
     *
     * Try request first; if not found, try pulling from request parameter;
     * if still not found, fallback to default
     *
     * @param Zend_Controller_Request_Abstract $request
     *
     * @return false|string Returns class name on success
     * @throws Zend_Controller_Exception
     */
    public function getControllerClass(Zend_Controller_Request_Abstract $request)
    {
        $controllerName = $request->getControllerName();
        if (empty($controllerName)) {
            if (!$this->getParam('useDefaultControllerAlways')) {
                return false;
            }
            $controllerName = $this->getDefaultControllerName();
            $request->setControllerName($controllerName);
        }

        $className = $this->formatControllerName($controllerName);

        $controllerDirs      = $this->getControllerDirectory();
        $module = $request->getModuleName();
        if ($this->isValidModule($module)) {
            $this->_curModule    = $module;
            $this->_curDirectory = $controllerDirs[$module];
        } elseif ($this->isValidModule($this->_defaultModule)) {
            $request->setModuleName($this->_defaultModule);
            $this->_curModule    = $this->_defaultModule;
            $this->_curDirectory = $controllerDirs[$this->_defaultModule];
        } else {
            require_once 'Zend/Controller/Exception.php';
            throw new Zend_Controller_Exception('No default module defined for this application');
        }

        return $className;
    }

    /**
     * Determine if a given module is valid
     *
     * @param  string $module
     * @return bool
     */
    public function isValidModule($module)
    {
        if (!is_string($module)) {
            return false;
        }

        $module        = strtolower($module);
        $controllerDir = $this->getControllerDirectory();
        foreach (array_keys($controllerDir) as $moduleName) {
            if ($module == strtolower($moduleName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retrieve default controller class
     *
     * Determines whether the default controller to use lies within the
     * requested module, or if the global default should be used.
     *
     * By default, will only use the module default unless that controller does
     * not exist; if this is the case, it falls back to the default controller
     * in the default module.
     *
     * @param Zend_Controller_Request_Abstract $request
     * @return string
     */
    public function getDefaultControllerClass(Zend_Controller_Request_Abstract $request)
    {
        $controller = $this->getDefaultControllerName();
        $default    = $this->formatControllerName($controller);
        $request->setControllerName($controller)
                ->setActionName(null);

        $module              = $request->getModuleName();
        $controllerDirs      = $this->getControllerDirectory();
        $this->_curModule    = $this->_defaultModule;
        $this->_curDirectory = $controllerDirs[$this->_defaultModule];
        if ($this->isValidModule($module)) {
            $found = false;
            if (class_exists($default, false)) {
                $found = true;
            } else {
                $moduleDir = $controllerDirs[$module];
                $fileSpec  = $moduleDir . DIRECTORY_SEPARATOR . $this->classToFilename($default);
                if (Zend_Loader::isReadable($fileSpec)) {
                    $found = true;
                    $this->_curDirectory = $moduleDir;
                }
            }
            if ($found) {
                $request->setModuleName($module);
                $this->_curModule    = $this->formatModuleName($module);
            }
        } else {
            $request->setModuleName($this->_defaultModule);
        }

        return $default;
    }

    /**
     * Return the value of the currently selected dispatch directory (as set by
     * {@link getController()})
     *
     * @return string
     */
    public function getDispatchDirectory()
    {
        return $this->_curDirectory;
    }

    /**
     * Determine the action name
     *
     * First attempt to retrieve from request; then from request params
     * using action key; default to default action
     *
     * Returns formatted action name
     *
     * @param Zend_Controller_Request_Abstract $request
     * @return string
     */
    public function getActionMethod(Zend_Controller_Request_Abstract $request)
    {
        $action = $request->getActionName();
        if (empty($action)) {
            $action = $this->getDefaultAction();
            $request->setActionName($action);
        }

        return $this->formatActionName($action);
    }
}
