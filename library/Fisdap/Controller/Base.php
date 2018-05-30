<?php

use Fisdap\Api\Users\CurrentUser\CurrentUser;
use Zend\Mvc\Controller\AbstractActionController;

/**
 * Base class that other controllers can extend from.
 * @package    Fisdap
 * @subpackage Controllers
 */
class Fisdap_Controller_Base extends AbstractActionController
{
    /**
     * View object
     * @var Zend_View_Interface|Zend_View
     */
    public $view;

    /**
     * @var Monolog\Logger
     */
    protected $logger;

    /**
     * @var Zend_Db_Adapter_Abstract
     */
    protected $db;

    /**
     * @var Zend_Session_Namespace
     */
    protected $globalSession;

    /**
     * FlashMessenger
     *
     * @var Zend_Controller_Action_Helper_FlashMessenger
     */
    protected $flashMessenger = null;

    /**
     * @var \Fisdap\Entity\User
     * @deprecated
     */
    protected $user;

    /**
     * @var \Fisdap\Entity\UserContext
     * @deprecated
     */
    protected $userContext;

    /**
     * Entity Manager
     *
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Illuminate\Container\Container
     */
    protected $container;

    /**
     * @var CurrentUser|null
     */
    protected $currentUser = null;

    /**
     * Put the logger, db, session, and flash messenger into the controller
     */
    public function init()
    {
        $this->logger = \Zend_Registry::get('logger');
        $this->db = \Zend_Registry::get('db');
        $this->globalSession = \Zend_Registry::get('session');
        $this->flashMessenger = $this->_helper->getHelper('FlashMessenger');
        $this->em = \Fisdap\EntityUtils::getEntityManager();
        $this->container = \Zend_Registry::get('container');
        $this->currentUser = $this->container->make(CurrentUser::class);

        // todo - remove use of deprecated user and user context, but keep logic around displaying login layout
        if (Zend\Authentication::getInstance()->hasIdentity()) {
            //			$this->user = \Fisdap\Entity\User::getByUsername(Zend_Auth::getInstance()->getIdentity());
//            $this->userContext = $this->user->getCurrentUserContext();
            $this->user = $this->currentUser->user();
            $this->userContext = $this->currentUser->context();
        } else {
            $this->view->layout()->setLayout('login');
        }
    }

    /**
     * Overrides the current action and will instead display an error message
     *
     * @param string $msg the error message to be displayed.
     * @param string $module the module to foward to, defaults to "default"
     */
    protected function displayError($msg = null, $module = "default")
    {
        $this->view->errorMessage = $msg;
        $this->forward('custom', 'error', $module);
        return;
    }

    /**
     * Overrides the current action and will instead display an error message
     *
     * @param mixed $permissionId string | int the ID or name of the failed permission
     * @param string $module the module to foward to, defaults to "default"
     */
    protected function displayPermissionError($permissionId, $module = "default")
    {
        if (is_int($permissionId)) {
            $permission = \Fisdap\EntityUtils::getEntity("Permission", $permissionId);
        } else {
            $permission = \Fisdap\EntityUtils::getRepository("Permission")->findOneByName($permissionId);
        }

        $this->view->errorMessage = "You need \"" . $permission->name . "\" in order to view this page.";
        $this->forward('custom', 'error', $module);
        return;
    }

    public function developmentOnly()
    {
        if (APPLICATION_ENV != 'development') {
            throw new Exception('Function only available in development environment');
        }
    }

    /**
     * Redirect user to a legacy page
     *
     * @param string $url
     */
    protected function _redirectToLegacy($url, $loopback = 0)
    {
        $this->redirect("/oldfisdap/redirect/?loopback=$loopback&loc=" . $url);
    }
}
