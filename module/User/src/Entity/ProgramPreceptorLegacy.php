<?php namespace User\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * Legacy Entity class for Program Preceptor Associations.
 *
 * @Entity(repositoryClass="Fisdap\Data\ProgramPreceptor\DoctrineProgramPreceptorLegacyRepository")
 * @Table(name="ProgramPreceptorData")
 */
class ProgramPreceptorLegacy extends EntityBaseClass
{
    /**
     * @Id
     * @Column(name="ProPrecep_id", type="integer")
     * @GeneratedValue
     */
    protected $id;
        
    /**
     * @ManyToOne(targetEntity="ProgramLegacy")
     * @JoinColumn(name="Program_id", referencedColumnName="Program_id")
     */
    protected $program;
        
    /**
     * @ManyToOne(targetEntity="PreceptorLegacy")
     * @JoinColumn(name="Preceptor_id", referencedColumnName="Preceptor_id")
     */
    protected $preceptor;
    
    /**
     * @Column(name="Active", type="boolean")
     */
    protected $active = true;
    
    public function init()
    {
    }
    public function getProgram()
    {
        return $this->preceptor;
    }

    public function setProgram(ProgramLegacy $program)
    {
        $this->program = $program;
    }

    public function getPreceptor()
    {
        return $this->preceptor;
    }

    public function setPreceptor(PreceptorLegacy $preceptor)
    {
        $this->preceptor = $preceptor;
    }
}
