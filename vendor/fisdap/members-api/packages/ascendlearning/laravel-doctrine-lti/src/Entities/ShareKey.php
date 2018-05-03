<?php namespace AscendLearning\Lti\Entities;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * Class ShareKey
 *
 * @package AscendLearning\Lti\Entities
 * @author  Ben Getsug <bgetsug@fisdap.net>
 *
 * @Entity
 * @Table(name="lti_share_key")
 */
class ShareKey
{
    /**
     * share_key_id varchar(32) NOT NULL
     *
     * @var string
     * @Column(name="share_key_id", type="string")
     * @Id
     */
    private $id;

    /**
     * primary_context_id varchar(255) NOT NULL
     *
     * @var Consumer
     * @ManyToOne(targetEntity="Consumer")
     * @JoinColumn(name="primary_consumer_key", referencedColumnName="consumer_key")
     */
    private $primary_consumer;

    /**
     * primary_context_id varchar(255) NOT NULL
     *
     * @var Context
     * @ManyToOne(targetEntity="Context")
     * @JoinColumn(name="primary_context_id", referencedColumnName="context_id")
     */
    private $primary_context;

    /**
     * auto_approve tinyint(1) NOT NULL
     *
     * @var bool
     * @Column(type="boolean")
     */
    private $auto_approve;

    /**
     * expires datetime NOT NULL
     *
     * @var \DateTime
     * @Column(type="datetime")
     */
    private $expires;


    /**
     * ShareKey constructor.
     *
     * @param string    $id
     * @param Context   $primary_context
     * @param bool      $auto_approve
     * @param \DateTime $expires
     */
    public function __construct($id, Context $primary_context, $auto_approve, \DateTime $expires)
    {
        $this->id = $id;
        $this->primary_context = $primary_context;
        $this->auto_approve = $auto_approve;
        $this->expires = $expires;
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
     * @return Consumer
     */
    public function getPrimaryConsumer()
    {
        return $this->primary_consumer;
    }


    /**
     * @param Consumer $primary_consumer
     */
    public function setPrimaryConsumer(Consumer $primary_consumer)
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
     * @param Context $primary_context
     */
    public function setPrimaryContext(Context $primary_context)
    {
        $this->primary_context = $primary_context;
    }


    /**
     * @return boolean
     */
    public function isAutoApprove()
    {
        return $this->auto_approve;
    }


    /**
     * @param boolean $auto_approve
     */
    public function setAutoApprove($auto_approve)
    {
        $this->auto_approve = $auto_approve;
    }


    /**
     * @return \DateTime
     */
    public function getExpires()
    {
        return $this->expires;
    }


    /**
     * @param \DateTime $expires
     */
    public function setExpires(\DateTime $expires)
    {
        $this->expires = $expires;
    }
}
