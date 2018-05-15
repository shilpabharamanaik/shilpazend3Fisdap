<?php namespace Fisdap\Timestamps;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;

/**
 * For use with Doctrine Entities, this satisfies the HasTimestamps contract
 *
 * @package Fisdap\Timestamps
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
trait EntityTimestampsSupport
{
    /**
     * @var \DateTime
     * @Column(type="datetime")
     */
    protected $created;

    /**
     * @var \DateTime
     * @Column(type="datetime")
     */
    protected $updated;


    /**
     * @PrePersist
     */
    public function created()
    {
        if (!isset($this->created)) {
            $this->created = $this->updated = new \DateTime("now");
        }
    }


    /**
     * @PreUpdate
     */
    public function updated()
    {
        $this->updated = new \DateTime("now");
    }


    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }


    /**
     * @param \DateTime $created
     *
     * @return $this
     */
    public function setCreated(\DateTime $created)
    {
        $this->created = $created;

        return $this;
    }


    /**
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }


    /**
     * @param \DateTime $updated
     *
     * @return $this
     */
    public function setUpdated(\DateTime $updated)
    {
        $this->updated = $updated;

        return $this;
    }
}
