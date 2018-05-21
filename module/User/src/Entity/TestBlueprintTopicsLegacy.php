<?php namespace User\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * Entity class for Legacy Marketing Billboards.
 *
 * @Entity
 * @Table(name="TestBPTopics")
 */
class TestBlueprintTopicsLegacy extends EntityBaseClass
{
    /**
     * @Id
     * @Column(name="tbpTopic_id", type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @Column(name="Name", type="string")
     */
    protected $name;
    
    /**
     * @ManyToOne(targetEntity="TestBlueprintSectionsLegacy")
     * @JoinColumn(name="tbpSect_id", referencedColumnName="tbpSect_id")
     */
    protected $section;
}
