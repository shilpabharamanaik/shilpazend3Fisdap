<?php namespace User\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;


/**
 * Entity class for Legacy Marketing Billboards.
 * 
 * @Entity
 * @Table(name="TestBluePrints")
 */
class TestBlueprintsLegacy extends EntityBaseClass
{
	/**
	 * @Id
	 * @Column(name="tbp_id", type="integer")
	 * @GeneratedValue
	 */
	protected $id;
	
	/**
	 * @OneToMany(targetEntity="TestBlueprintSectionsLegacy", mappedBy="blueprint")
	 * @JoinColumn(name="tbp_id", referencedColumnName="tbp_id")
	 */
	protected $sections;
	
	/**
	 * @OneToMany(targetEntity="TestBlueprintItemsLegacy", mappedBy="blueprint")
	 * @JoinColumn(name="tbp_id", referencedColumnName="tbp_id")
	 */
	protected $items;
    
    /**
	 * @OneToMany(targetEntity="TestBlueprintColumnsLegacy", mappedBy="blueprint")
	 * @JoinColumn(name="tbp_id", referencedColumnName="tbp_id")
	 */
	protected $columns;
    
    /**
     * @Column(name="Name", type="string")
     */
    protected $name;
    
    /**
     * @Column(name="CreationDate", type="date")
     */
    protected $created;
    
    /**
     * @Column(name="Project_id", type="integer")
     */
    protected $project_id;
    
    public function init()
    {
        $this->sections = new ArrayCollection();
        $this->items = new ArrayCollection();
        $this->columns = new ArrayCollection();
    }
}