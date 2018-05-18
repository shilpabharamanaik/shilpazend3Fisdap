<?php namespace User\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;


/**
 * Practice Category Default
 * This entity will be used to store the default practice categories to be give to a new program
 *
 * @Entity(repositoryClass="Fisdap\Data\Practice\DoctrinePracticeCategoryRepository")
 * @Table(name="fisdap2_practice_categories_defaults")
 */
class PracticeCategoryDefault extends Enumerated
{
    /**
     * @var ProgramLegacy
     * @ManyToOne(targetEntity="ProgramLegacy")
     * @JoinColumn(name="program_id", referencedColumnName="Program_id")
     * @todo this may not be needed...investigate
     */
    protected $program;
    
    /**
     * @var CertificationLevel
     * @ManyToOne(targetEntity="CertificationLevel")
     */
    protected $certification_level;
    
    /**
	 * @var Profession
	 * @ManyToOne(targetEntity="Profession")
	 */
	protected $profession;
    
    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="PracticeDefinitionDefault", mappedBy="category", cascade={"persist"})
     */
    protected $practice_definitions;
    
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
    
    public function set_profession($value)
    {
        $this->profession = self::id_or_entity_helper($value, "Profession");
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