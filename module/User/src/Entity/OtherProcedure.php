<?php namespace User\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;

/**
 * Other Procedure
 *
 * @Entity
 * @Table(name="fisdap2_other_procedure")
 */
class OtherProcedure extends Procedure
{
    /**
     * @Column(type="string")
     */
    protected $name;

    /**
     * @Column(type="boolean", nullable=true)
     */
    protected $require_success;

    /**
     * @Column(type="boolean", nullable=true)
     */
    protected $require_attempts;

    /**
     * @Column(type="boolean", nullable=true)
     */
    protected $require_size;

    public function toArray()
    {
        return [
            "id"                => $this->getId(),
            "name"              => $this->getName(),
            "require_success"   => $this->require_success,
            "require_attempts"  => $this->require_attempts,
            "require_size"      => $this->require_size,
        ];
    }
}
