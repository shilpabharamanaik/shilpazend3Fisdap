<?php namespace Fisdap\Members\Lti\Session;

use Psr\Log\LoggerInterface;


/**
 * Provides API for managing the 'lti' session namespace
 *
 * @package Fisdap\Members\Lti\Session
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class LtiSession
{
    const SCHEDULER = 'scheduler';

    const SKILLS_TRACKER = 'skillsTracker';

    const LEARNING_CENTER = 'learningCenter';

    const MY_FISDAP = 'myFisdap';

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
        $this->session = new \Zend_Session_Namespace('lti', true);
        $this->logger = $logger;
    }


    /**
     * @return string
     */
    public function getReturnUrl()
    {
        return $this->session->returnUrl;
    }


    /**
     * @param string $returnUrl
     */
    public function setReturnUrl($returnUrl)
    {
        $this->session->returnUrl = $returnUrl;
    }


    /**
     * @return string
     */
    public function getContextTitle()
    {
        return $this->session->contextTitle;
    }


    /**
     * @param string $title
     */
    public function setContextTitle($title)
    {
        $this->session->contextTitle = $title;
    }


    /**
     * @return LtiSessionUser
     */
    public function getUser()
    {
        return $this->session->user;
    }


    /**
     * @param LtiSessionUser $user
     */
    public function setUser(LtiSessionUser $user)
    {
        $this->session->user = $user;
    }


    /**
     * @return string
     */
    public function getFisdapModule()
    {
        return $this->session->fisdapModule;
    }


    /**
     * @param $fisdapModule
     */
    public function setFisdapModule($fisdapModule)
    {
        $this->session->fisdapModule = $fisdapModule;
    }


    /**
     * Mark LTI session as launched and get the Fisdap module redirect URL
     *
     * @return string
     */
    public function launchAndGetModuleRedirect()
    {
        $this->launched(true);

        $this->logger->debug('LTI session launched', [
            'LTI Context Title' => $this->getContextTitle(), 'LTI User ID' => $this->getUser()->id
        ]);
        
        return $this->getModuleRedirect();
    }

    
    /**
     * @return string
     */
    public function getModuleRedirect()
    {
        switch ($this->getFisdapModule()) {
            case self::SKILLS_TRACKER:
                return 'skills-tracker/shifts';
            case self::SCHEDULER:
                return 'scheduler';
            case self::LEARNING_CENTER:
                return 'learning-center';
            default:
                return 'my-fisdap';
        }
    }


    /**
     * @return string
     */
    public function getFisdapUsername()
    {
        return $this->session->fisdapUsername;
    }


    /**
     * @param string $fisdapUsername
     */
    public function setFisdapUsername($fisdapUsername)
    {
        $this->session->fisdapUsername = $fisdapUsername;
    }


    /**
     * @return FisdapUserIdentity[]
     */
    public function getFisdapAccounts()
    {
        return $this->session->fisdapAccounts;
    }


    /**
     * @param FisdapUserIdentity[] $fisdapAccounts
     */
    public function setFisdapAccounts(array $fisdapAccounts)
    {
        $this->session->fisdapAccounts = $fisdapAccounts;
    }


    /**
     * Determine/set whether session has a PSG/JBL GUID
     *
     * @param bool|null $hasPsgId
     *
     * @return bool
     */
    public function hasPsgId($hasPsgId = null)
    {
        if (is_null($hasPsgId)) {
            return isset($this->session->hasPsgId) ? $this->session->hasPsgId : false;
        }

        $this->session->hasPsgId = $hasPsgId;

        return $hasPsgId;
    }


    /**
     * Determine/Set status of LTI launch
     *
     * @param bool|null $launched
     *
     * @return bool
     */
    public function launched($launched = null)
    {
        if (is_null($launched)) {
            return isset($this->session->launched) ? $this->session->launched : false;
        }

        $this->session->launched = $launched;

        return $launched;
    }

    /**
     * Clear all values in LTI session
     */
    public function clear()
    {
        $this->session->unsetAll();
    }


    /**
     * @return array
     */
    public function toArray()
    {
        return json_decode(json_encode($this->session->getIterator()->getArrayCopy()), true);
    }
}
