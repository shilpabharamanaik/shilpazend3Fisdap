<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * Entity class for Legacy Marketing Billboards.
 *
 * @Entity
 * @Table(name="TestBPItems")
 */
class TestBlueprintItemsLegacy extends EntityBaseClass
{
    /**
     * @Id
     * @Column(name="tbpItem_id", type="integer")
     * @GeneratedValue
     */
    protected $id;
    
    /**
     * @ManyToOne(targetEntity="TestBlueprintsLegacy", inversedBy="items")
     * @JoinColumn(name="tbp_id", referencedColumnName="tbp_id")
     */
    protected $blueprint;
    
    /**
     * @OneToOne(targetEntity="ItemMoodleMapLegacy")
     * @JoinColumn(name="Item_id", referencedColumnName="Item_id")
     */
    protected $moodle_map;
    
    /**
     * @OneToOne(targetEntity="TestBlueprintTopicsLegacy")
     * @JoinColumn(name="tbpTopic_id", referencedColumnName="tbpTopic_id")
     */
    protected $topic;
    
    /**
     * @ManyToOne(targetEntity="TestBlueprintColumnsLegacy", inversedBy="items")
     * @JoinColumn(name="tbpColumn_id", referencedColumnName="tbpColumn_id")
     */
    protected $column;
    
    /**
     * @Column(name="Item_id", type="integer")
     */
    protected $item_id;
}
