<?php namespace Fisdap\Api\Support;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

/**
 * Entity for a failed Laravel queue job
 *
 * @package Fisdap\Api\Support
 * @author  Ben Getsug <bgetsug@fisdap.net>
 *
 * @Entity
 * @Table("FailedJobs")
 */
class FailedQueueJob
{
    /**
     * @var int
     * @Column(type="integer")
     * @Id
     * @GeneratedValue
     */
    protected $id;

    /**
     * @var string
     * @Column(type="text")
     */
    protected $connection;

    /**
     * @var string
     * @Column(type="text")
     */
    protected $queue;

    /**
     * @var string
     * @Column(type="text")
     */
    protected $payload;

    /**
     * @var \DateTime
     * @Column(type="datetime")
     */
    protected $failed_at;


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
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @return string
     */
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * @return string
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @return \DateTime
     */
    public function getFailedAt()
    {
        return $this->failed_at;
    }
}
