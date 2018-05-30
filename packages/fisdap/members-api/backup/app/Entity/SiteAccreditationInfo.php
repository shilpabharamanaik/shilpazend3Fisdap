<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * Entity class for Site Accreditation Info.
 *
 * @Entity(repositoryClass="Fisdap\Data\Site\DoctrineSiteAccreditationInfoRepository")
 * @Table(name="fisdap2_site_accreditation_info")
 * @HasLifecycleCallbacks
 */
class SiteAccreditationInfo extends EntityBaseClass
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="ProgramSiteLegacy")
     * @JoinColumn(name="program_site_association_id", referencedColumnName="ProSite_id")
     */
    protected $program_site_association;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $cao;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $phone;

    /**
     * @Column(type="integer", nullable=true)
     */
    protected $distance_from_program;

    /**
     * @Column(type="boolean", nullable=true)
     */
    protected $signed_agreement;

    /**
     * @Column(type="boolean", nullable=true)
     */
    protected $written_policies;

    /**
     * @Column(type="boolean", nullable=true)
     */
    protected $formally_trained_preceptors;

    /**
     * @Column(type="integer", nullable=true)
     */
    protected $preceptor_training_hours;

    /**
     * @Column(type="boolean", nullable=true)
     */
    protected $online_medical_direction;

    /**
     * @Column(type="boolean", nullable=true)
     */
    protected $advanced_life_support;

    /**
     * @Column(type="boolean", nullable=true)
     */
    protected $quality_improvement_program;

    /**
     * @ManyToOne(targetEntity="StudentSupervisionType")
     */
    protected $student_supervision_type;
    
    /**
     * @Column(type="integer", nullable=true)
     */
    protected $active_ems_units;

    /**
     * @Column(type="integer", nullable=true)
     */
    protected $number_of_runs;

    /**
     * @Column(type="integer", nullable=true)
     */
    protected $number_of_trauma_calls;

    /**
     * @Column(type="integer", nullable=true)
     */

    protected $number_of_critical_trauma_calls;

    /**
     * @Column(type="integer", nullable=true)
     */
    protected $number_of_pediatric_calls;

    /**
     * @Column(type="integer", nullable=true)
     */
    protected $number_of_cardiac_arrest_calls;

    /**
     * @Column(type="integer", nullable=true)
     */
    protected $number_of_cardiac_calls;


    public function init()
    {
    }

    // see if we have all the info for this site
    public function isComplete()
    {
        if (is_null($this->cao) ||
            is_null($this->phone) ||
            is_null($this->distance_from_program) ||
            is_null($this->signed_agreement) ||
            is_null($this->written_policies) ||
            is_null($this->formally_trained_preceptors) ||
            ($this->formally_trained_preceptors && is_null($this->preceptor_training_hours)) ||
            is_null($this->student_supervision_type)) {
            return false;
        }
        
        // we have extra criteria for field sites
        if ($this->program_site_association->site->type == "field") {
            if (is_null($this->online_medical_direction) ||
                is_null($this->advanced_life_support) ||
                is_null($this->quality_improvement_program) ||
                is_null($this->active_ems_units) ||
                is_null($this->number_of_runs) ||
                is_null($this->number_of_cardiac_arrest_calls) ||
                is_null($this->number_of_cardiac_calls) ||
                is_null($this->number_of_trauma_calls) ||
                is_null($this->number_of_critical_trauma_calls) ||
                is_null($this->number_of_pediatric_calls)) {
                return false;
            }
        }

        return true;
    }
    
    public function getDistanceDescription()
    {
        if ($this->distance_from_program) {
            return $this->distance_from_program . ' '. \Util_String::pluralize('mile', $this->distance_from_program);
        }
        
        return "";
    }
    
    public function getTrainingHoursDescription()
    {
        if ($this->formally_trained_preceptors === false) {
            return "n/a";
        }
        
        return $this->preceptor_training_hours;
    }
    
    public function getYesNo($property)
    {
        if (!is_null($this->$property)) {
            return ($this->$property) ? "yes" : "no";
        }
        
        return "";
    }

    public function getSupervisionDescription()
    {
        if ($this->student_supervision_type) {
            return $accred_info->student_supervision_type->name == "Site" ? "Hospital personnel" : "Program personnel";
        }
        
        return "";
    }
}
