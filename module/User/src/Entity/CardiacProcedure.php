<?php namespace User\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;


/**
 * Cardiac Procedure
 * 
 * @Entity
 * @Table(name="fisdap2_cardiac_procedure")
 */
class CardiacProcedure extends Procedure
{
    /**
     * @Column(type="boolean", nullable=true)
     */
    protected $require_procedure_method;

    /**
     * @Column(type="boolean", nullable=true)
     */
    protected $require_pacing_method;

    public function toArray()
    {
        return [
            "id"                        => $this->getId(),
            "name"                      => $this->getName(),
            "require_procedure_method"  => $this->require_procedure_method,
            "require_pacing_method"     => $this->require_pacing_method,
        ];
    }
}
