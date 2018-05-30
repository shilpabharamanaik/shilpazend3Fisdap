<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * Entity class for Legacy Program Site Associations.
 *
 * @Entity
 * @Table(name="ProgramSiteData")
 * @HasLifecycleCallbacks
 */
class ProgramSiteLegacy extends EntityBaseClass
{
    /**
     * @Id
     * @Column(name="ProSite_id", type="integer")
     * @GeneratedValue
     */
    protected $id;
    
    /**
     * @ManyToOne(targetEntity="ProgramLegacy")
     * @JoinColumn(name="Program_id", referencedColumnName="Program_id")
     */
    protected $program;
    
    /**
     * @ManyToOne(targetEntity="SiteLegacy")
     * @JoinColumn(name="AmbServ_id", referencedColumnName="AmbServ_id")
     */
    protected $site;
    
    /**
     * @Column(name="Active", type="boolean")
     */
    protected $active = true;
}
