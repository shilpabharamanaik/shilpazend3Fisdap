<?php namespace AscendLearning\Lti\Entities;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Fisdap\Timestamps\EntityTimestampsSupport;
use Fisdap\Timestamps\HasTimestamps;

/**
 * Class User
 *
 * @package AscendLearning\Lti\Entities
 * @author  Ben Getsug <bgetsug@fisdap.net>
 *
 * @Entity
 * @Table(name="lti_user")
 */
class User implements HasTimestamps
{
    use EntityTimestampsSupport;

    /**
     * @var Consumer
     * @ManyToOne(targetEntity="Consumer", inversedBy="users")
     * @JoinColumn(name="consumer_key", referencedColumnName="consumer_key"),
     * @Id
     */
    private $consumer;

    /**
     * context_id varchar(255) NOT NULL
     * @var Context
     * @ManyToOne(targetEntity="Context")
     * @JoinColumn(name="context_id", referencedColumnName="context_id")
     * @Id
     */
    private $context;

    /**
     * user_id varchar(255) NOT NULL
     * @var string
     * @Column(name="user_id")
     * @Id
     */
    private $id;

    /**
     * lti_result_sourcedid varchar(255) NOT NULL
     * @var string
     * @Column
     */
    private $lti_result_sourcedid;


    /**
     * User constructor.
     *
     * @param Context $context
     * @param string  $id
     * @param string  $lti_result_sourcedid
     */
    public function __construct(Context $context, $id, $lti_result_sourcedid)
    {
        $this->context = $context;
        $this->id = $id;
        $this->lti_result_sourcedid = $lti_result_sourcedid;
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
     * @return Context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param Context $context
     */
    public function setContext(Context $context)
    {
        $this->context = $context;
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
     * @return string
     */
    public function getLtiResultSourcedid()
    {
        return $this->lti_result_sourcedid;
    }

    /**
     * @param string $lti_result_sourcedid
     */
    public function setLtiResultSourcedid($lti_result_sourcedid)
    {
        $this->lti_result_sourcedid = $lti_result_sourcedid;
    }
}
