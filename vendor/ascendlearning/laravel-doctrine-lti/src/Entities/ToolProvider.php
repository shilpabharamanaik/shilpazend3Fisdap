<?php namespace AscendLearning\Lti\Entities;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

/**
 * Class ToolProvider
 *
 * @package AscendLearning\Lti\Entities
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @Entity
 * @Table(name="lti_tool_providers")
 */
class ToolProvider
{
    /**
     * @var int
     * @Column(type="integer")
     * @Id
     * @GeneratedValue
     */
    private $id;

    /**
     * @var string
     * @Column(type="string")
     */
    private $launchUrl;

    /**
     * @var string
     * @Column(type="string", nullable=true)
     */
    private $logoutUrl;

    /**
     * @var string
     * @Column(type="string", nullable=true)
     */
    private $logoUrl;

    /**
     * @var string
     */
    private static $defaultOauthConsumerKey;

    /**
     * @var string
     * @Column(type="string", nullable=true)
     */
    private $oauthConsumerKey;

    /**
     * @var string
     * @Column(type="string")
     */
    private $secret;

    /**
     * @var string
     * @Column(type="string", nullable=true)
     */
    private $resourceLinkTitle;

    /**
     * @var string
     * @Column(type="string", nullable=true)
     */
    private $resourceLinkDescription;

    /**
     * @var string
     * @Column(type="string", nullable=true)
     */
    private $contextId;

    /**
     * @var string
     * @Column(type="string", nullable=true)
     */
    private $contextTitle;

    /**
     * @var array
     * @Column(type="array", nullable=true)
     */
    private $customParameters;


    /**
     * ToolProvider constructor.
     *
     * @param string $launchUrl
     * @param string $secret
     */
    public function __construct($launchUrl, $secret)
    {
        $this->launchUrl = $launchUrl;
        $this->secret = $secret;
    }


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @return string
     */
    public function getLaunchUrl()
    {
        return $this->launchUrl;
    }


    /**
     * @param string $launchUrl
     */
    public function setLaunchUrl($launchUrl)
    {
        $this->launchUrl = $launchUrl;
    }


    /**
     * @return string
     */
    public function getLogoutUrl()
    {
        return $this->logoutUrl;
    }


    /**
     * @param string $logoutUrl
     */
    public function setLogoutUrl($logoutUrl)
    {
        $this->logoutUrl = $logoutUrl;
    }


    /**
     * @return string
     */
    public function getLogoUrl()
    {
        return $this->logoUrl;
    }


    /**
     * @param string $logoUrl
     */
    public function setLogoUrl($logoUrl)
    {
        $this->logoUrl = $logoUrl;
    }


    /**
     * @param string $defaultOauthConsumerKey
     */
    public static function setDefaultOauthConsumerKey($defaultOauthConsumerKey)
    {
        self::$defaultOauthConsumerKey = $defaultOauthConsumerKey;
    }
    

    /**
     * @return string
     */
    public function getOauthConsumerKey()
    {
        return $this->oauthConsumerKey ?: self::$defaultOauthConsumerKey;
    }


    /**
     * @param string $oauthConsumerKey
     */
    public function setOauthConsumerKey($oauthConsumerKey)
    {
        $this->oauthConsumerKey = $oauthConsumerKey;
    }


    /**
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
    }


    /**
     * @param string $secret
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;
    }


    /**
     * @return string
     */
    public function getResourceLinkTitle()
    {
        return $this->resourceLinkTitle;
    }


    /**
     * @param string $resourceLinkTitle
     */
    public function setResourceLinkTitle($resourceLinkTitle)
    {
        $this->resourceLinkTitle = $resourceLinkTitle;
    }


    /**
     * @return string
     */
    public function getResourceLinkDescription()
    {
        return $this->resourceLinkDescription;
    }


    /**
     * @param string $resourceLinkDescription
     */
    public function setResourceLinkDescription($resourceLinkDescription)
    {
        $this->resourceLinkDescription = $resourceLinkDescription;
    }


    /**
     * @return string
     */
    public function getContextId()
    {
        return $this->contextId;
    }


    /**
     * @param string $contextId
     */
    public function setContextId($contextId)
    {
        $this->contextId = $contextId;
    }


    /**
     * @return string
     */
    public function getContextTitle()
    {
        return $this->contextTitle;
    }


    /**
     * @param string $contextTitle
     */
    public function setContextTitle($contextTitle)
    {
        $this->contextTitle = $contextTitle;
    }


    /**
     * @return array|null
     */
    public function getCustomParameters()
    {
        return $this->customParameters;
    }


    /**
     * @param array|null $customParameters
     */
    public function setCustomParameters(array $customParameters = null)
    {
        $this->customParameters = $customParameters;
    }
}
