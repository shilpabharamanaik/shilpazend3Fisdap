<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * Entity class for Event repeat information.
 *
 * @Entity
 * @Table(name="RepeatInfo")
 */
class RepeatInfo extends EntityBaseClass
{
    /**
     * @var integer
     * @Id
     * @Column(name="Repeat_id", type="integer")
     * @GeneratedValue
     */
    protected $id;
    
    /**
     * @var ProgramLegacy
     * @ManyToOne(targetEntity="ProgramLegacy")
     * @JoinColumn(name="Program_id", referencedColumnName="Program_id")
     */
    protected $program;

    /**
     * @var SiteLegacy
     * @ManyToOne(targetEntity="SiteLegacy")
     * @JoinColumn(name="AmbServ_id", referencedColumnName="AmbServ_id")
     */
    protected $site;
    
    /**
     * @var BaseLegacy
     * @ManyToOne(targetEntity="BaseLegacy")
     * @JoinColumn(name="StartBase_id", referencedColumnName="Base_id")
     */
    protected $base;
    
    /**
     * @var float
     * @Column(name="Hours"), type="float", precision=5, scale=2)
     */
    protected $duration = 0.00;
    
    /**
     * @var string
     * @Column(name="Type", type="string")
     */
    protected $type = "field";
    
    public function init()
    {
    }
    
    public function set_site($value)
    {
        $this->site = self::id_or_entity_helper($value, 'SiteLegacy');
    }
}
