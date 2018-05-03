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
 * Entity class for Legacy Program Site Associations.
 * 
 * @Entity
 * @Table(name="ProgramSiteAssoc")
 * @HasLifecycleCallbacks
 */
class ProgramSiteShare extends EntityBaseClass
{
    /**
     * @Id
     * @Column(name="Assoc_id", type="integer")
     * @GeneratedValue
     */
    protected $id;
	
    /**
     * @ManyToOne(targetEntity="ProgramLegacy", inversedBy="site_shares")
     * @JoinColumn(name="Program_id", referencedColumnName="Program_id")
     */
    protected $program;
    
    /**
     * @ManyToOne(targetEntity="SiteLegacy")
     * @JoinColumn(name="Site_id", referencedColumnName="AmbServ_id")
     */
    protected $site;
    
    /**
     * @Column(name="Main", type="boolean")
     */
    protected $admin = 0;
    
    /**
     * @Column(name="Approved", type="boolean")
     */
    protected $approved = 0;
    
    /**
     * @var InstructorLegacy
     * @ManyToOne(targetEntity="InstructorLegacy")
     * @JoinColumn(name="Instructor_id", referencedColumnName="Instructor_id")
     */
    protected $requesting_instructor;
    
    /**
     * @codeCoverageIgnore
     * @deprecated
     * @Column(name="DefaultShared", type="boolean")
     */
    protected $default_shared = 0;
    
    /**
     * @Column(type="boolean")
     */
    protected $see_students = 0;
}
