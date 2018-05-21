<?php namespace User\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;

/**
 * Entity class for Legacy Test Blueprint Columns
 *
 * @Entity
 * @Table(name="TestBPColumns")
 */
class TestBlueprintColumnsLegacy extends EntityBaseClass
{
    /**
     * @Id
     * @Column(name="tbpColumn_id", type="integer")
     * @GeneratedValue
     */
    protected $id;
    
    /**
     * @ManyToOne(targetEntity="TestBlueprintsLegacy", inversedBy="columns")
     * @JoinColumn(name="tbp_id", referencedColumnName="tbp_id")
     */
    protected $blueprint;
    
    /**
     * @Column(name="Name", type="string")
     */
    protected $name;
    
    /**
     * @Column(name="ColumnNumber", type="integer")
     */
    protected $column_number;
    
    /**
     * @OneToMany(targetEntity="TestBlueprintItemsLegacy", mappedBy="column")
     * @JoinColumn(name="tbpColumn_id", referencedColumnName="tbpColumn_id")
     */
    protected $items;
    
    public function init()
    {
        $this->items = new ArrayCollection();
    }
}
