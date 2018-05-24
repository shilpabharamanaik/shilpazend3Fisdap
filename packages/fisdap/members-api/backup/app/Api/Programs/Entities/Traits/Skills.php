<?php namespace Fisdap\Api\Programs\Entities\Traits;

use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToMany;
use Fisdap\Entity\AirwayProcedure;
use Fisdap\Entity\CardiacProcedure;
use Fisdap\Entity\IvProcedure;
use Fisdap\Entity\LabAssessment;
use Fisdap\Entity\MedType;
use Fisdap\Entity\OtherProcedure;
use Fisdap\EntityUtils;


/**
 * Class Skills
 *
 * @package Fisdap\Api\Programs\Entities\Traits
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
trait Skills
{
    /**
     * @OneToMany(targetEntity="ProgramMedType", mappedBy="program")
     * @JoinColumn(name="Program_id", referencedColumnName="program_id")
     */
    protected $med_types;

    /**
     * @OneToMany(targetEntity="ProgramLabAssessment", mappedBy="program")
     * @JoinColumn(name="Program_id", referencedColumnName="program_id")
     */
    protected $lab_assessments;

    /**
     * @OneToMany(targetEntity="GoalSet", mappedBy="program")
     * @JoinColumn(name="Program_id", referencedColumnName="program_id")
     */
    protected $goalsets;

    /**
     * @OneToMany(targetEntity="PracticeDefinition", mappedBy="program")
     * @JoinColumn(name="Program_id", referencedColumnName="program_id")
     */
    protected $practice_definitions;

    /**
     * @OneToMany(targetEntity="PracticeCategory", mappedBy="program")
     * @JoinColumn(name="Program_id", referencedColumnName="program_id")
     */
    protected $practice_categories;
    
    /**
     * @OneToMany(targetEntity="ProgramAirwayProcedure", mappedBy="program")
     * @JoinColumn(name="Program_id", referencedColumnName="program_id")
     */
    protected $airway_procedures;

    /**
     * @OneToMany(targetEntity="ProgramCardiacProcedure", mappedBy="program")
     * @JoinColumn(name="Program_id", referencedColumnName="program_id")
     */
    protected $cardiac_procedures;

    /**
     * @OneToMany(targetEntity="ProgramIvProcedure", mappedBy="program")
     * @JoinColumn(name="Program_id", referencedColumnName="program_id")
     */
    protected $iv_procedures;

    /**
     * @OneToMany(targetEntity="ProgramOtherProcedure", mappedBy="program")
     * @JoinColumn(name="Program_id", referencedColumnName="program_id")
     */
    protected $other_procedures;

    
    /**
     * Determine if this program uses skills practice in Field and clinical settings
     * @return boolean
     */
    public function hasSkillsPractice()
    {
        return $this->program_settings->practice_skills_field || $this->program_settings->practice_skills_clinical;
    }
    

    /**
     * Create a default narrative section for a new program
     * @codeCoverageIgnore
     * @deprecated 
     */
    public function createDefaultNarrativeSection()
    {
        $new_section = EntityUtils::getEntity("NarrativeSectionDefinition");
        $new_section->program_id = $this->id;
        $new_section->section_order = 1;
        $new_section->name = "Narrative";
        $new_section->size = 32;
        $new_section->seeded = false;
        $new_section->section_order = 1;
        $new_section->active = true;
        $new_section->save();
    }


    /**
     * Create default lab practice definitions for a new program
     * @codeCoverageIgnore
     * @deprecated
     */
    public function createDefaultPracticeDefinitions()
    {
        $populator = new \Util_PracticePopulator();
        $populator->populatePracticeDefinitions($this);
        $this->save();
    }

    /**
     * @param array $airway_procedures
     */
    public function setAirwayProcedures(array $airway_procedures)
    {
        $this->airway_procedures = $airway_procedures;
    }

    /**
     * @param array $cardiac_procedures
     */
    public function setCardiacProcedures(array $cardiac_procedures)
    {
        $this->cardiac_procedures = $cardiac_procedures;
    }

    /**
     * @param array $iv_procedures
     */
    public function setIvProcedures(array $iv_procedures)
    {
        $this->iv_procedures = $iv_procedures;
    }

    /**
     * @param array $other_procedures
     */
    public function setOtherProcedures(array $other_procedures)
    {
        $this->other_procedures = $other_procedures;
    }

    /**
     * @param array $med_types
     */
    public function setMedTypes(array $med_types)
    {
        $this->med_types = $med_types;
    }

    /**
     * @param array $lab_assessments
     */
    public function setLabAssessments(array $lab_assessments)
    {
        $this->lab_assessments = $lab_assessments;
    }

    /**
     * @return AirwayProcedure[]
     */
    public function getAirwayProcedures()
    {
        return $this->airway_procedures;
    }

    /**
     * @return CardiacProcedure[]
     */
    public function getCardiacProcedures()
    {
        return $this->cardiac_procedures;
    }

    /**
     * @return IvProcedure[]
     */
    public function getIvProcedures()
    {
        return $this->iv_procedures;
    }

    /**
     * @return OtherProcedure[]
     */
    public function getOtherProcedures()
    {
        return $this->other_procedures;
    }

    /**
     * @return MedType[]
     */
    public function getMedTypes()
    {
        return $this->med_types;
    }

    /**
     * @return LabAssessment[]
     */
    public function getLabAssessments()
    {
        return $this->lab_assessments;
    }
}