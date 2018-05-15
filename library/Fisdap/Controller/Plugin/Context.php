<?php

use Fisdap\Api\Users\CurrentUser\CurrentUser;
use Fisdap\Entity\User;

/**
 * Controller plugin to detect the users browser and store that info in the
 * Zend_Registry, and provide user identification to piwik analytics & New Relic analytics
 *
 * @todo Look into possibly adding some code to switch out layouts for mobile
 * browsers.
 *
 * @package    Fisdap
 * @subpackage Controllers
 */
class Fisdap_Controller_Plugin_Context extends Zend_Controller_Plugin_Abstract
{
    /**
     * @var Zend_Http_UserAgent
     */
    protected $useragent;

    /**
     * Get the Zend_Http_UserAgent from the app bootstrap, then pull the
     * Zend_Http_UserAgent_Device out of that and stick it in the Zend_Registry
     *
     * @param Zend_Controller_Request_Abstract $request
     */
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
        $bootstrap = Zend_Controller_Front::getInstance()->getParam('bootstrap');

        if ($bootstrap->hasResource('useragent')) {
            $this->useragent = $bootstrap->getResource('useragent');
            Zend_Registry::set('device', $this->useragent->getDevice());
        }
    }

    /**
     * Make a user id, if logged in, available to the view (and hence the Piwik script in the layout: layout.phtml)
     * Piwik tracks via JS, so we need to print the User ID as a string in that block of javascript
     *
     * @param Zend_Controller_Request_Abstract $request
     *
     * @throws Zend_Exception
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {

        // get the view
        $view = Zend_Controller_Front::getInstance()
            ->getParam('bootstrap')
            ->getResource('view');

        /** @var CurrentUser $currentUser */
        $currentUser = Zend_Registry::get('container')->make(CurrentUser::class);
        
        // give the view a userid if logged in
        if (Zend_Auth::getInstance()->hasIdentity()) {
            $user = $currentUser->user();
            $view->currentUserId = $user->getId(); // passing along user ID to piwik
        } else {
            $view->currentUserId = 0; // passing along a 0 to piwik
            $user = null;
        }

        // give the view the proper piwik hostname to use in piwik tracking JS
        // and the piwik tracker site ID
        $config = \Zend_Registry::get('config');
        $view->piwikBaseUrl = $config->piwik->tracking->members->baseUrl;
        $view->piwikSiteId = $config->piwik->tracking->members->siteId;

        // Add user ID and Program ID to New Relic APM/Browser annotations
        // https://docs.newrelic.com/docs/apm/other-features/attributes/collecting-custom-attributes
        if (extension_loaded('newrelic')) {
            // User Id
            newrelic_add_custom_parameter('userId', $view->currentUserId);

            // Let's track the module / controller / action in New Relic too!
            newrelic_add_custom_parameter('appModule', $request->getModuleName());
            newrelic_add_custom_parameter('appController', $request->getControllerName());
            newrelic_add_custom_parameter('appAction', $request->getActionName());

            // more user data
            if ($user instanceof User) {
                // User info
                newrelic_add_custom_parameter('username', $user->getUsername());
                newrelic_add_custom_parameter('userContextId', $currentUser->context()->getId());
                newrelic_add_custom_parameter('roleName', $currentUser->context()->getRole()->getName());
                
                // Program info
                $program = $currentUser->context()->getProgram();
                newrelic_add_custom_parameter('programId', $program->getId());
                newrelic_add_custom_parameter('programName', $program->getName());

                // Set standard properties for New Relic Browser
                // https://docs.newrelic.com/docs/apm/traces/browser-traces/api-options-browser-traces
                // what should be listed as "product"?
                if ($request->getModuleName() != '') {
                    $product = $request->getModuleName();
                } else {
                    // no module? Let's get controller and action
                    $product = $request->getControllerName() . '->' . $request->getActionName();
                }
                newrelic_set_user_attributes($user->getUsername(), $program->getName(), $product);
            }
        }
    }
}
