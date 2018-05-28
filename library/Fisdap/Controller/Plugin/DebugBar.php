<?php
use DebugBar\StandardDebugBar;

/**
 * Created by PhpStorm.
 * User: jmortenson
 * Date: 11/10/14
 * Time: 11:36 AM
 */

/**
 * Controller plugin to make the PHP Debug Bar available to the view
 */

/**
 * @package    Fisdap
 * @subpackage Controllers
 */
class Fisdap_Controller_Plugin_DebugBar extends Zend_Controller_Plugin_Abstract
{
    /**
     * @var StandardDebugBar
     */
    protected $debugBar = NULL;

    /**
     * @var boolean
     */
    protected $debugBarEnabled = FALSE;

    public function __construct() {
        $this->debugBarEnabled = (\Zend_Registry::isRegistered('debugBar'));
        if ($this->debugBarEnabled) {
            $this->debugBar = \Zend_Registry::get('debugBar');
        }
    }

    /**
     * If we are in development and debug bar is enabled, then add it to the view
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request) {
        // Don't do anything if the debugBar is not enabled.
        if (!$this->debugBarEnabled) {
            return;
        }

        // get the view
        $view = Zend_Controller_Front::getInstance()
            ->getParam('bootstrap')
            ->getResource('view');

        // Get the debug bar
        $renderer = $this->getRenderer();

        // Put the debugbar renderer object in the view
        $view->debugBarRenderer = $renderer;
    }

    public function dispatchLoopShutdown()
    {
        // Don't do anything if the debug bar is not enabled
        if (!$this->debugBarEnabled) {
            return;
        }

        // XHPROF
        if (env('XHPROF_ENABLED') == true && extension_loaded('xhprof')) {
            $profiler_namespace = 'members-dev';  // namespace for your application
            $xhprof_data = xhprof_disable();
            $xhprof_runs = new XHProfRuns_Default();
            $run_id = $xhprof_runs->save_run($xhprof_data, $profiler_namespace);

            // url to the XHProf UI libraries (change the host name and path)
            $profiler_url = sprintf('http://xhprof.jmortenson.members.fisdapdev.net/index.php?run=%s&source=%s', $run_id, $profiler_namespace);
            //$linkToProfile = 'Xhprof <a href="' . $profiler_url . '" target="_blank">Profiler output</a>';

            //$this->debugBar['messages']->addMessage($linkToProfile);
            $this->debugBar['xhprof']->setUrl($profiler_url);
        }

        // Get PHP Debug Bar Renderer
        $renderer = $this->getRenderer();

        $html = $renderer->render() . "</body>";
        $response = $this->getResponse();
        // $response->setBody(preg_replace('/(<\/head>)/i', $this->_headerOutput() . '$1', $response->getBody()));
        $response->setBody(str_ireplace('</body>', $html, $response->getBody()));
    }

    private function getRenderer() {
        $renderer = $this->debugBar->getJavascriptRenderer();

        // Set baseUrl to tell Debug Bar where to get CSS/JS assets
        // @todo right now this depends on setting up a symlink at public/js/debugbar - fix by adding a controller for loading vendor resources
        $renderer->setBaseUrl('/js/debugbar');

        return $renderer;
    }
}