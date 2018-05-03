<?php namespace User\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;


/**
 * Entity class for Legacy Program Base Associations.
 * 
 * @Entity
 * @Table(name="ProgramBaseData")
 * @HasLifecycleCallbacks
 */
class ProgramBaseLegacy extends EntityBaseClass
{
    /**
     * @Id
     * @Column(name="ProSite_id", type="integer")
     * @GeneratedValue
     */
    protected $id;
	
    /**
     * @ManyToOne(targetEntity="ProgramLegacy", inversedBy="program_base_associations")
     * @JoinColumn(name="Program_id", referencedColumnName="Program_id")
     */
    protected $program;
    
    /**
     * @ManyToOne(targetEntity="BaseLegacy")
     * @JoinColumn(name="Base_id", referencedColumnName="Base_id")
     */
    protected $base;
    
    /**
     * @Column(name="Active", type="boolean")
     */
    protected $active = true;
}