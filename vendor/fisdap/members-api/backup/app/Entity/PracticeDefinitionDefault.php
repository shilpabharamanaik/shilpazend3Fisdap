<?php namespace Fisdap\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Fisdap\EntityUtils;


/**
 * Practice Definition Default
 * This entity will be used to store the default practice definitions to be give to a new program
 *
 * @Entity(repositoryClass="Fisdap\Data\Practice\DoctrinePracticeDefinitionRepository")
 * @Table(name="fisdap2_practice_definitions_defaults")
 */
class PracticeDefinitionDefault extends EntityBaseClass
{
    /**
	 * @var integer
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue
	 */
	protected $id;
    
    /**
	 * @var ProgramLegacy
	 * @ManyToOne(targetEntity="ProgramLegacy")
	 * @JoinColumn(name="program_id", referencedColumnName="Program_id")
     * @todo this may not be needed...investigate
	 */
	protected $program;
    
    /**
     * @var PracticeCategoryDefault
     * @ManyToOne(targetEntity="PracticeCategoryDefault")
     * @JoinColumn(name="category_id", referencedColumnName="id")
     */
    protected $category;
    
    /**
     * @var CertificationLevel
     * @ManyToOne(targetEntity="CertificationLevel")
     */
    protected $certification_level;
    
    /**
     * @var EvalDefLegacy
     * @ManyToOne(targetEntity="EvalDefLegacy")
     * @JoinColumn(name="skillsheet_id", referencedColumnName="EvalDef_id")
     */
    protected $skillsheet;
    
    /**
     * @var ArrayCollection
	 * @ManyToMany(targetEntity="PracticeSkill")
     * @JoinTable(name="fisdap2_practice_definitions_skills_defaults",
     *  joinColumns={@JoinColumn(name="practice_definition_id", referencedColumnName="id")},
     *  inverseJoinColumns={@JoinColumn(name="practice_skill_id",referencedColumnName="id")})
     */
	protected $practice_skills;
    
    /**
     * @var string the name of this practice definition
     * @Column(type="string")
     */
    protected $name;
    
    /**
     * @var string is this practice definition active
     * @Column(type="boolean")
     */
    protected $active = true;
    
    /**
     * @var integer number of practice successful practice items evaluated by a peer
     * @Column(type="integer")
     */
    protected $peer_goal = 0;
    
    /**
     * @var integer number of practice successful practice items evaluated by an instructor
     * @Column(type="integer")
     */
    protected $instructor_goal = 0;
    
    /**
     * @var integer
     * @Column(type="integer")
     */
    protected $eureka_window = 0;
    
    /**
     * @var integer
     * @Column(type="integer")
     */
    protected $eureka_goal = 0;

    /**
     * @Column(type="boolean", nullable=true)
     */
    protected $airway_management_credit;
    
    public function init()
    {
        $this->practice_skills = new ArrayCollection();
    }
    
    public function set_program($value)
    {
        $this->program = self::id_or_entity_helper($value, "ProgramLegacy");
        return $this;
    }
    
    public function set_skillsheet($value)
    {
        $this->skillsheet = self::id_or_entity_helper($value, "EvalDefLegacy");
        return $this;
    }
    
    public function set_certification_level($value)
    {
        $this->certification_level = self::id_or_entity_helper($value, "CertificationLevel");
        return $this;
    }
    
    public function set_category($value)
    {
        $this->category = self::id_or_entity_helper($value, "PracticeCategory");
        return $this;
    }

    
    /**
     * Get an array containing all of the PracticeSkill IDs
     * attached to this PracticeDefinition
     *
     * @return array
     */
    public function getPracticeSkillIds()
    {
        $ids = array();
        foreach ($this->practice_skills as $skill) {
            $ids[] = $skill->id;
        }
        
        return $ids;
    }


    /**
     * Take an array of PracticeSkill IDs and attach them to this
     * PracticeDefinition clearing previously set ones
     *
     * @param array $ids the LabSkill IDs to add
     *
     * @return \Fisdap\Entity\PracticeDefinition
     */
    public function setPracticeSkillIds($ids)
    {
        $this->practice_skills->clear();
        
        if (!is_array($ids)) {
            $ids = array($ids);
        }
        
        foreach($ids as $id) {
            $this->practice_skills->add(EntityUtils::getEntity("PracticeSkill", $id));
        }
        
        return $this;
    }


    /**
     * Get a list of practice definitions
     *
     * @param integer $certificationId
     * @param integer $programId
     *
     * @return array
     */
    public static function getFormOptions($certificationId, $programId, $categories = false)
    {
        $return = array();
        $definitions = EntityUtils::getRepository("PracticeDefinition")->findBy(array("certification_level" => $certificationId, "program" => $programId, "active" => 1));
        
		
        foreach($definitions as $def) {
			if($categories){
				if(!$return[$def->category->name]){
					$return[$def->category->name] = array();
				}

				$return[$def->category->name][$def->id] = $def->name;
			}
			else {
				$return[$def->id] = $def->name;
			}
        }
        
        return $return;
    }
}