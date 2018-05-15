<?php namespace Fisdap\Entity;

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
 * @Table(name="TestBPSections")
 */
class TestBlueprintSectionsLegacy extends EntityBaseClass
{
    /**
     * @Id
     * @Column(name="tbpSect_id", type="integer")
     * @GeneratedValue
     */
    protected $id;
    
    /**
     * @Column(name="Name", type="string")
     */
    protected $name;
    
    /**
     * @ManyToOne(targetEntity="TestBlueprintsLegacy", inversedBy="section")
     * @JoinColumn(name="tbp_id", referencedColumnName="tbp_id")
     */
    protected $blueprint;
    
    /**
     * @Column(name="Goal", type="integer")
     */
    protected $goal;
    
    /**
     * @Column(name="SectionType", type="string")
     */
    protected $section_type;
    
    /**
     * @Column(name="ScoreDisplay", type="string", nullable=true)
     * If set, this property determines whether a custom format is used in displaying the score.
     * Available values include: absoluteNumberOnly, sliderBadToGood, sliderGoodToBad
     * Default is null, which means "use the standard formatting"
     */
    protected $score_display;
    
    /**
     * @Column(name="GradeScale", nullable=true)
     * If set, this property deterimines that the grade for this section should be transformed
     * to another number according to a custom scale, instead of presented to the user as-is
     * Default is null, which means "present as-is, untransformed"
     */
    protected $grade_scale;
}
