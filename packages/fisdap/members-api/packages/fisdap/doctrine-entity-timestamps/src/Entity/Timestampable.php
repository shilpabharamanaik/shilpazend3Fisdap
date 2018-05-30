<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;

/**
 * Base class that will add a created and updated field. The class also adds
 * logic to populate these fields.
 *
 * @HasLifecycleCallbacks
 * @MappedSuperclass
 */
class Timestampable extends EntityBaseClass
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
     */
    public function setCreated(\DateTime $created)
    {
        $this->created = $created;
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
     */
    public function setUpdated(\DateTime $updated)
    {
        $this->updated = $updated;
    }
}
