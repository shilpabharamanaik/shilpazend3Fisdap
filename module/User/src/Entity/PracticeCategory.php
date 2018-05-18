<?php namespace User\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;


/**
 * Practice Category
 *
 * @Entity(repositoryClass="Fisdap\Data\Practice\DoctrinePracticeCategoryRepository")
 * @Table(name="fisdap2_practice_categories")
 */
class PracticeCategory extends Enumerated
{
    /**
     * @var ProgramLegacy
     * @ManyToOne(targetEntity="ProgramLegacy")
     * @JoinColumn(name="program_id", referencedColumnName="Program_id")
     */
    protected $program;
    
    /**
     * @var CertificationLevel
     * @ManyToOne(targetEntity="CertificationLevel")
     */
    protected $certification_level;
    
    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="PracticeDefinition", mappedBy="category", cascade={"persist"})
     */
    protected $practice_definitions;

    /**
     * @var string the name of this practice category
     * @Column(type="string")
     */
    protected $name;


    public function init()
    {
        $this->practice_definitions = new ArrayCollection();
    }
    
    public function set_certification_level($value)
    {
        $this->certification_level = self::id_or_entity_helper($value, "CertificationLevel");
        return $this;
    }
    
    public function set_program($value)
    {
        $this->program = self::id_or_entity_helper($value, "ProgramLegacy");
        return $this;
    }
    
    public function addPracticeDefinition(PracticeDefinition $def)
    {
        $this->practice_definitions->add($def);
        $def->category = $this;
        $def->certification_level = $this->certification_level;
        $def->program = $this->program;
        
        return $this;
    }
    
    public function removePracticeDefinition(PracticeDefinition $def)
    {
        $this->practice_definitions->removeElement($def);
        $def->category = null;
        
        //Create "uncategorized" category if one doesn't exist, then add this practice definition to it
        
        return $this;
    }
}