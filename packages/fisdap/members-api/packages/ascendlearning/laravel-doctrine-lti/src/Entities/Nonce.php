<?php namespace AscendLearning\Lti\Entities;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * Class Nonce
 *
 * @package AscendLearning\Lti\Entities
 * @author  Ben Getsug <bgetsug@fisdap.net>
 *
 * @Entity
 * @Table(name="lti_nonce")
 */
class Nonce
{
    /**
     * consumer_key varchar(255) NOT NULL
     * @var Consumer
     * @ManyToOne(targetEntity="Consumer", inversedBy="nonces")
     * @JoinColumn(name="consumer_key", referencedColumnName="consumer_key")
     * @Id
     */
    private $consumer;

    /**
     * value varchar(32) NOT NULL
     * @var string
     * @Column(type="string", length=32)
     * @Id
     */
    private $value;

    /**
     * expires datetime NOT NULL
     * @var \DateTime
     * @Column(type="datetime")
     */
    private $expires;


    /**
     * Nonce constructor.
     *
     * @param Consumer $consumer
     * @param string   $value
     * @param \DateTime $expires
     */
    public function __construct(Consumer $consumer, $value, \DateTime $expires)
    {
        $this->consumer = $consumer;
        $this->value = $value;
        $this->expires = $expires;
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
    public function setConsumer($consumer)
    {
        $this->consumer = $consumer;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
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
