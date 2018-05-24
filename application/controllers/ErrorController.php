<?php

class ErrorController extends Zend_Controller_Action
{
    /**
     * @var string
     */
    protected $errorId;

    /**
     * @var ExceptionLogger
     */
    protected $exceptionLogger;


    public function init()
    {
        $this->errorId = uniqid();

        $this->exceptionLogger = Zend_Registry::isRegistered('exceptionLogger') ? Zend_Registry::get('exceptionLogger') : null;
    }


    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');

        if (!$errors) {
            $this->view->message = 'You have reached the error page';
            return;
        }

        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:

                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $this->view->message = 'Page not found';
                $this->view->type = get_class($errors->exception);
                break;
            default:
                // application error
                $this->getResponse()->setHttpResponseCode(500);
                $this->view->message = 'Application error';
                $this->view->type = get_class($errors->exception);
                break;
        }

        // Log exception
        if ($this->exceptionLogger !== null) {
            $this->exceptionLogger->log($errors->exception, $this->errorId);
        }

        // switch to login layout if user is not logged in
        $loggedInUser = \Fisdap\Entity\User::getLoggedInUser();

        if ($loggedInUser === null) {
            $this->view->layout()->setLayout('login');
        }

        // conditionally display exceptions
        if ($this->getInvokeArg('displayExceptions') == true) {
            $this->view->exception = $errors->exception;
        }

        $bugReportLink = "/oldfisdap/redirect?loc=help/Bugs/bugreport.html?error={$this->errorId}";
        $this->view->message = '<a href="'.$bugReportLink.'">Have a bug to report?</a>';
        $this->view->request = $errors->request;


        // make sure New Relic tracks correct transaction
        if (extension_loaded('newrelic')) {
            $requestParams = $errors->request->getParams();
            /** @noinspection PhpUndefinedFunctionInspection */
            newrelic_name_transaction($requestParams['module'] . '/' . $requestParams['controller'] . '/' . $requestParams['action']);
        }
    }
    
    /**
     * Action to display custom error messages.
     */
    public function customAction()
    {
    
    }
}

