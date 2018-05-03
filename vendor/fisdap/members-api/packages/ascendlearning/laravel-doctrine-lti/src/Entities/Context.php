<?php namespace AscendLearning\Lti\Entities;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Fisdap\Timestamps\EntityTimestampsSupport;
use Fisdap\Timestamps\HasTimestamps;

/**
 * Class Context
 *
 * @package AscendLearning\Lti\Entities
 * @author  Ben Getsug <bgetsug@fisdap.net>
 *
 * @Entity
 * @Table(name="lti_context")
 * @HasLifecycleCallbacks
 */
class Context implements HasTimestamps
{
    use EntityTimestampsSupport;


    /**
     * consumer_key varchar(255) NOT NULL
     *
     * @var Consumer
     * @ManyToOne(targetEntity="Consumer", inversedBy="contexts")
     * @JoinColumn(name="consumer_key", referencedColumnName="consumer_key")
     */
    private $consumer;

    /**
     * context_id varchar(255) NOT NULL
     *
     * @var string
     * @Column(type="string", name="context_id")
     * @Id
     */
    private $id;

    /**
     * lti_context_id varchar(255) DEFAULT NULL
     *
     * @var string|null
     * @Column(type="string", nullable=true)
     */
    private $lti_context_id = null;

    /**
     * lti_resource_id varchar(255) DEFAULT NULL
     *
     * @var string|null
     * @Column(type="string", nullable=true)
     */
    private $lti_resource_id = null;

    /**
     * title varchar(255) NOT NULL
     *
     * @var string
     * @Column(type="string")
     */
    private $title;

    /**
     * settings text
     *
     * @var string
     * @Column(type="text")
     */
    private $settings;

    /**
     * primary_context_id varchar(255) DEFAULT NULL
     *
     * @var Consumer
     * @ManyToOne(targetEntity="Consumer")
     * @JoinColumn(name="primary_consumer_key", referencedColumnName="consumer_key")
     */
    private $primary_consumer = null;

    /**
     * primary_context_id varchar(255) DEFAULT NULL
     *
     * @var Context
     * @ManyToOne(targetEntity="Context")
     * @JoinColumn(name="primary_context_id", referencedColumnName="context_id")
     */
    private $primary_context = null;

    /**
     * share_approved tinyint(1) DEFAULT NULL
     *
     * @var bool|null
     * @Column(type="boolean", nullable=true)
     */
    private $share_approved = null;


    /**
     * Context constructor.
     *
     * @param Consumer $consumer
     * @param string   $id
     * @param string   $title
     * @param string   $settings
     */
    public function __construct(Consumer $consumer, $id, $title, $settings)
    {
        $this->consumer = $consumer;
        $this->id = $id;
        $this->title = $title;
        $this->settings = $settings;
    }


    /**
     * @return Consumer
     */
    public function getConsumer()
    {
        return $this->consumer;
    }


    /**
     * @param Consumer $consumer
     */
    public function setConsumer(Consumer $consumer)
    {
        $this->consumer = $consumer;
    }


    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }


    /**
     * @return null|string
     */
    public function getLtiContextId()
    {
        return $this->lti_context_id;
    }


    /**
     * @param null|string $lti_context_id
     */
    public function setLtiContextId($lti_context_id)
    {
        $this->lti_context_id = $lti_context_id;
    }


    /**
     * @return null|string
     */
    public function getLtiResourceId()
    {
        return $this->lti_resource_id;
    }


    /**
     * @param null|string $lti_resource_id
     */
    public function setLtiResourceId($lti_resource_id)
    {
        $this->lti_resource_id = $lti_resource_id;
    }


    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }


    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }


    /**
     * @return string
     */
    public function getSettings()
    {
        return $this->settings;
    }


    /**
     * @param string $settings
     */
    public function setSettings($settings)
    {
        $this->settings = $settings;
    }


    /**
     * @return Consumer
     */
    public function getPrimaryConsumer()
    {
        return $this->primary_consumer;
    }


    /**
     * @param Consumer $primary_consumer
     */
    public function setPrimaryConsumer(Consumer $primary_consumer = null)
    {
        $this->primary_consumer = $primary_consumer;
    }


    /**
     * @return Context
     */
    public function getPrimaryContext()
    {
        return $this->primary_context;
    }


    /**
     * @param Context|null $primary_context
     */
    public function setPrimaryContext(Context $primary_context = null)
    {
        $this->primary_context = $primary_context;
    }


    /**
     * @return bool|null
     */
    public function getShareApproved()
    {
        return $this->share_approved;
    }


    /**
     * @param bool|null $share_approved
     */
    public function setShareApproved($share_approved)
    {
        $this->share_approved = $share_approved;
    }
}
