<?php namespace Fisdap\Members\Lti\Session;

use Psr\Log\LoggerInterface;


/**
 * Provides API for managing the 'ltiTools' session namespace
 *
 * @package Fisdap\Members\Lti\Session
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class LtiToolProvidersSession
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \Zend_Session_Namespace
     */
    private $session;


    /**
     * LtiSession constructor.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->session = new \Zend_Session_Namespace('ltiTools', true);
        $this->logger = $logger;
    }


    /**
     * @return array
     */
    public function getLaunchedTools()
    {
        return $this->session->launchedTools ?: [];
    }


    /**
     * @param string $resourceLinkId
     * @param string $logoutUrl
     */
    public function addLaunchedTool($resourceLinkId, $logoutUrl)
    {
        $this->session->launchedTools[$resourceLinkId] = $logoutUrl;
    }


    /**
     * @param string $resourceLinkId
     */
    public function removeLaunchedTool($resourceLinkId)
    {
        unset($this->session->launchedTools[$resourceLinkId]);
    }
}
