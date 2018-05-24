<?php

use Fisdap\Members\Lti\Session\LtiSession;
use Franzl\Lti\ToolProvider;
use Illuminate\Support\Debug\Dumper;
use Psr\Log\LoggerInterface;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\ServerRequestFactory;


/**
 * Class Lti_IndexController
 *
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class Lti_IndexController extends Zend_Controller_Action
{
    /**
     * @var LoggerInterface
     */
    private $logger;


    /**
     * Lti_IndexController constructor.
     *
     * @param LoggerInterface   $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }


    /**
     * @param ToolProvider $toolProvider
     * @param LtiSession   $session
     *
     * @return mixed
     */
    public function indexAction(ToolProvider $toolProvider, LtiSession $session)
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        if (APPLICATION_ENV === 'testing') {
            /** @var Zend_Controller_Request_Http $request */
            $request = $this->getRequest();

            $serverRequest = new ServerRequest(
                [], [], $request->getRequestUri(), $request->getMethod(), 'php://input', [], [],
                [], $request->getPost()
            );

        } else {
            $serverRequest = ServerRequestFactory::fromGlobals();
        }

        if (isset($serverRequest->getParsedBody()['launch_presentation_return_url'])) {
            $session->setReturnUrl($serverRequest->getParsedBody()['launch_presentation_return_url']);
        }

        $this->logger->debug('LTI Request', $serverRequest->getParsedBody());

        return $toolProvider->handleRequest($serverRequest);
    }
    

    public function sessionTestAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        (new Dumper)->dump($_SESSION);
    }
}
