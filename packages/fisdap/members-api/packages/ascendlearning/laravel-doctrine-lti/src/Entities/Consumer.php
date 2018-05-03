<?php namespace AscendLearning\Lti\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Fisdap\Timestamps\EntityTimestampsSupport;
use Fisdap\Timestamps\HasTimestamps;

/**
 * Class Consumer
 *
 * @package AscendLearning\Lti\Entities
 * @author  Ben Getsug <bgetsug@fisdap.net>
 *
 * @Entity
 * @Table(name="lti_consumer")
 * @HasLifecycleCallbacks
 */
class Consumer implements HasTimestamps
{
    use EntityTimestampsSupport;


    /**
     * consumer_key varchar(255) NOT NULL
     * @var string
     * @Column(type="string", name="consumer_key")
     * @Id
     */
    private $consumer_key;

    /**
     * name varchar(45) NOT NULL
     * @var string
     * @Column(type="string", length=45)
     */
    private $name;

    /**
     * secret varchar(32) NOT NULL
     * @var string
     * @Column(type="string", length=32)
     */
    private $secret;

    /**
     * lti_version varchar(12) DEFAULT NULL
     * @var string|null
     * @Column(type="string", length=12, nullable=true)
     */
    private $lti_version = null;

    /**
     * consumer_name varchar(255) DEFAULT NULL
     * @var string|null
     * @Column(type="string", nullable=true)
     */
    private $consumer_name = null;

    /**
     * consumer_version varchar(255) DEFAULT NULL
     * @var string|null
     * @Column(type="string", nullable=true)
     */
    private $consumer_version = null;

    /**
     * consumer_guid varchar(255) DEFAULT NULL
     * @var string|null
     * @Column(type="string", nullable=true)
     */
    private $consumer_guid = null;

    /**
     * css_path varchar(255) DEFAULT NULL
     * @var string|null
     * @Column(type="string", nullable=true)
     */
    private $css_path = null;

    /**
     * protected tinyint(1) NOT NULL
     * @var bool
     * @Column(type="boolean")
     */
    private $protected;

    /**
     * enabled tinyint(1) NOT NULL
     * @var bool
     * @Column(type="boolean")
     */
    private $enabled;

    /**
     * enable_from datetime DEFAULT NULL
     * @var |DateTime|null
     * @Column(type="datetime", nullable=true)
     */
    private $enable_from = null;

    /**
     * enable_until datetime DEFAULT NULL
     * @var \DateTime|null
     * @Column(type="datetime", nullable=true)
     */
    private $enable_until = null;

    /**
     * last_access date DEFAULT NULL,
     * @var \DateTime|null
     * @Column(type="date", nullable=true)
     */
    private $last_access = null;


    /**
     * @var ArrayCollection|Context[]
     * @OneToMany(targetEntity="Context", mappedBy="consumer", cascade={"remove"})
     */
    private $contexts;

    /**
     * @var ArrayCollection|User[]
     * @OneToMany(targetEntity="User", mappedBy="consumer", cascade={"remove"})
     */
    private $users;

    /**
     * @var ArrayCollection|Nonce[]
     * @OneToMany(targetEntity="Nonce", mappedBy="consumer", cascade={"remove"})
     */
    private $nonces;


    /**
     * Consumer constructor.
     *
     * @param string $consumer_key
     * @param string $name
     * @param string $secret
     * @param bool   $protected
     * @param bool   $enabled
     */
    public function __construct($consumer_key, $name, $secret, $protected, $enabled)
    {
        $this->consumer_key = $consumer_key;
        $this->name = $name;
        $this->secret = $secret;
        $this->protected = $protected;
        $this->enabled = $enabled;

        $this->contexts = new ArrayCollection;
        $this->users = new ArrayCollection;
        $this->nonces = new ArrayCollection;
    }


    /**
     * @return string
     */
    public function getKey()
    {
        return $this->consumer_key;
    }

    /**
     * @param string $consumer_key
     */
    public function setKey($consumer_key)
    {
        $this->consumer_key = $consumer_key;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
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
    public function getLtiVersion()
    {
        return $this->lti_version;
    }

    /**
     * @param string $lti_version
     */
    public function setLtiVersion($lti_version)
    {
        $this->lti_version = $lti_version;
    }

    /**
     * @return string
     */
    public function getConsumerName()
    {
        return $this->consumer_name;
    }

    /**
     * @param string $consumer_name
     */
    public function setConsumerName($consumer_name)
    {
        $this->consumer_name = $consumer_name;
    }

    /**
     * @return string
     */
    public function getConsumerVersion()
    {
        return $this->consumer_version;
    }

    /**
     * @param string $consumer_version
     */
    public function setConsumerVersion($consumer_version)
    {
        $this->consumer_version = $consumer_version;
    }

    /**
     * @return string
     */
    public function getConsumerGuid()
    {
        return $this->consumer_guid;
    }

    /**
     * @param string $consumer_guid
     */
    public function setConsumerGuid($consumer_guid)
    {
        $this->consumer_guid = $consumer_guid;
    }

    /**
     * @return string
     */
    public function getCssPath()
    {
        return $this->css_path;
    }

    /**
     * @param string $css_path
     */
    public function setCssPath($css_path)
    {
        $this->css_path = $css_path;
    }

    /**
     * @return boolean
     */
    public function isProtected()
    {
        return $this->protected;
    }

    /**
     * @param boolean $protected
     */
    public function setProtected($protected)
    {
        $this->protected = $protected;
    }

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param boolean $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * @return \DateTime|null
     */
    public function getEnableFrom()
    {
        return $this->enable_from;
    }

    /**
     * @param \DateTime|null $enable_from
     */
    public function setEnableFrom(\DateTime $enable_from = null)
    {
        $this->enable_from = $enable_from;
    }

    /**
     * @return \DateTime|null
     */
    public function getEnableUntil()
    {
        return $this->enable_until;
    }

    /**
     * @param \DateTime|null $enable_until
     */
    public function setEnableUntil(\DateTime $enable_until = null)
    {
        $this->enable_until = $enable_until;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastAccess()
    {
        return $this->last_access;
    }

    /**
     * @param \DateTime|null $last_access
     */
    public function setLastAccess(\DateTime $last_access = null)
    {
        $this->last_access = $last_access;
    }


    /**
     * @return ArrayCollection|Context[]
     */
    public function getContexts()
    {
        return $this->contexts;
    }

    /**
     * @return ArrayCollection|User[]
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @return ArrayCollection|Nonce[]
     */
    public function getNonces()
    {
        return $this->nonces;
    }
}
