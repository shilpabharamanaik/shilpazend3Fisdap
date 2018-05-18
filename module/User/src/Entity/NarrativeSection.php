<?php namespace User\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;


/**
 * NarrativeSections
 * 
 * @Entity
 * @Table(name="fisdap2_narrative_sections")
 */
class NarrativeSection extends EntityBaseClass
{
    /**
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue
	 */
	protected $id;
    
	/**
	 * @ManyToOne(targetEntity="Narrative", inversedBy="sections")
	 * @JoinColumn(name="narrative_id", referencedColumnName="id")
	 */
	protected $narrative;
        
	/**
	 * @ManyToOne(targetEntity="NarrativeSectionDefinition")
	 * @JoinColumn(name="section_id", referencedColumnName="id", nullable=false)
	 */
	protected $definition;
        
	/**
	 * @Column(type="text", nullable=true)
	 */
	protected $section_text;

	public function setSectionDefinition($value)
	{
		$this->definition = $value;
	}
	
	/**
	 * Set section_text
	 * @param $value
	 */
	public function set_section_text($value)
	{
		$this->section_text = $value === '' ? null : $value;
	}

    /**
     * @return mixed
     */
	public function getSectionText()
	{
		return $this->section_text;
	}

    /**
     * @return array
     */
	public function toArray()
	{
		$definitionId = $this->definition->id;
		$rtv = parent::toArray();
		$rtv['sectionText'] = $rtv['section_text'];
		$rtv['definitionId'] = $definitionId;

		unset(
			$rtv['section_text']
		);

		return $rtv;
	}
}
