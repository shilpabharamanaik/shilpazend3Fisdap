<?php namespace User\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use User\EntityUtils;

/**
 * Entity for scenarios.
 *
 * @Entity(repositoryClass="Fisdap\Data\Scenario\DoctrineScenarioRepository")
 * @Table(name="fisdap2_scenarios")
 *
 * @todo Write some unit tests!
 * @todo Write setters/getters and other core functionality
 */
class Scenario
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="Patient")
     */
    protected $patient;
    
    /**
     * @Column(type="string", nullable=true)
     */
    protected $title;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return Patient
     */
    public function getPatient()
    {
        return $this->patient;
    }

    /**
     * @param mixed $patient
     */
    public function setPatient($patient)
    {
        $this->patient = $patient;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @param mixed $notes
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;
    }

    /**
     * @return mixed
     */
    public function getPatientInformation()
    {
        return $this->patient_information;
    }

    /**
     * @param mixed $patient_information
     */
    public function setPatientInformation($patient_information)
    {
        $this->patient_information = $patient_information;
    }

    /**
     * @return mixed
     */
    public function getPatientWeight()
    {
        return $this->patient_weight;
    }

    /**
     * @param mixed $patient_weight
     */
    public function setPatientWeight($patient_weight)
    {
        $this->patient_weight = $patient_weight;
    }

    /**
     * @return WeightUnit
     */
    public function getWeightUnit()
    {
        return $this->weight_unit;
    }

    /**
     * @param mixed $weight_unit
     */
    public function setWeightUnit($weight_unit)
    {
        $this->weight_unit = $weight_unit;
    }

    /**
     * @return mixed
     */
    public function getSampleSigns()
    {
        return $this->sample_signs;
    }

    /**
     * @param mixed $sample_signs
     */
    public function setSampleSigns($sample_signs)
    {
        $this->sample_signs = $sample_signs;
    }

    /**
     * @return mixed
     */
    public function getSampleAllergies()
    {
        return $this->sample_allergies;
    }

    /**
     * @param mixed $sample_allergies
     */
    public function setSampleAllergies($sample_allergies)
    {
        $this->sample_allergies = $sample_allergies;
    }

    /**
     * @return mixed
     */
    public function getSampleMedications()
    {
        return $this->sample_medications;
    }

    /**
     * @param mixed $sample_medications
     */
    public function setSampleMedications($sample_medications)
    {
        $this->sample_medications = $sample_medications;
    }

    /**
     * @return mixed
     */
    public function getSamplePriorHistory()
    {
        return $this->sample_prior_history;
    }

    /**
     * @param mixed $sample_prior_history
     */
    public function setSamplePriorHistory($sample_prior_history)
    {
        $this->sample_prior_history = $sample_prior_history;
    }

    /**
     * @return mixed
     */
    public function getSampleLastOralIntake()
    {
        return $this->sample_last_oral_intake;
    }

    /**
     * @param mixed $sample_last_oral_intake
     */
    public function setSampleLastOralIntake($sample_last_oral_intake)
    {
        $this->sample_last_oral_intake = $sample_last_oral_intake;
    }

    /**
     * @return mixed
     */
    public function getSampleEvents()
    {
        return $this->sample_events;
    }

    /**
     * @param mixed $sample_events
     */
    public function setSampleEvents($sample_events)
    {
        $this->sample_events = $sample_events;
    }

    /**
     * @return mixed
     */
    public function getOpqrstOnset()
    {
        return $this->opqrst_onset;
    }

    /**
     * @param mixed $opqrst_onset
     */
    public function setOpqrstOnset($opqrst_onset)
    {
        $this->opqrst_onset = $opqrst_onset;
    }

    /**
     * @return mixed
     */
    public function getOpqrstProvocation()
    {
        return $this->opqrst_provocation;
    }

    /**
     * @param mixed $opqrst_provocation
     */
    public function setOpqrstProvocation($opqrst_provocation)
    {
        $this->opqrst_provocation = $opqrst_provocation;
    }

    /**
     * @return mixed
     */
    public function getOpqrstQuality()
    {
        return $this->opqrst_quality;
    }

    /**
     * @param mixed $opqrst_quality
     */
    public function setOpqrstQuality($opqrst_quality)
    {
        $this->opqrst_quality = $opqrst_quality;
    }

    /**
     * @return mixed
     */
    public function getOpqrstRadiation()
    {
        return $this->opqrst_radiation;
    }

    /**
     * @param mixed $opqrst_radiation
     */
    public function setOpqrstRadiation($opqrst_radiation)
    {
        $this->opqrst_radiation = $opqrst_radiation;
    }

    /**
     * @return mixed
     */
    public function getOpqrstSeverity()
    {
        return $this->opqrst_severity;
    }

    /**
     * @param mixed $opqrst_severity
     */
    public function setOpqrstSeverity($opqrst_severity)
    {
        $this->opqrst_severity = $opqrst_severity;
    }

    /**
     * @return mixed
     */
    public function getOpqrstTime()
    {
        return $this->opqrst_time;
    }

    /**
     * @param mixed $opqrst_time
     */
    public function setOpqrstTime($opqrst_time)
    {
        $this->opqrst_time = $opqrst_time;
    }

    /**
     * @return mixed
     */
    public function getPhysicalHeent()
    {
        return $this->physical_heent;
    }

    /**
     * @param mixed $physical_heent
     */
    public function setPhysicalHeent($physical_heent)
    {
        $this->physical_heent = $physical_heent;
    }

    /**
     * @return mixed
     */
    public function getPhysicalNeck()
    {
        return $this->physical_neck;
    }

    /**
     * @param mixed $physical_neck
     */
    public function setPhysicalNeck($physical_neck)
    {
        $this->physical_neck = $physical_neck;
    }

    /**
     * @return mixed
     */
    public function getPhysicalChest()
    {
        return $this->physical_chest;
    }

    /**
     * @param mixed $physical_chest
     */
    public function setPhysicalChest($physical_chest)
    {
        $this->physical_chest = $physical_chest;
    }

    /**
     * @return mixed
     */
    public function getPhysicalAbdomen()
    {
        return $this->physical_abdomen;
    }

    /**
     * @param mixed $physical_abdomen
     */
    public function setPhysicalAbdomen($physical_abdomen)
    {
        $this->physical_abdomen = $physical_abdomen;
    }

    /**
     * @return mixed
     */
    public function getPhysicalPelvis()
    {
        return $this->physical_pelvis;
    }

    /**
     * @param mixed $physical_pelvis
     */
    public function setPhysicalPelvis($physical_pelvis)
    {
        $this->physical_pelvis = $physical_pelvis;
    }

    /**
     * @return mixed
     */
    public function getPhysicalLowerExtremities()
    {
        return $this->physical_lower_extremities;
    }

    /**
     * @param mixed $physical_lower_extremities
     */
    public function setPhysicalLowerExtremities($physical_lower_extremities)
    {
        $this->physical_lower_extremities = $physical_lower_extremities;
    }

    /**
     * @return mixed
     */
    public function getPhysicalUpperExtremities()
    {
        return $this->physical_upper_extremities;
    }

    /**
     * @param mixed $physical_upper_extremities
     */
    public function setPhysicalUpperExtremities($physical_upper_extremities)
    {
        $this->physical_upper_extremities = $physical_upper_extremities;
    }

    /**
     * @return mixed
     */
    public function getPhysicalPosterior()
    {
        return $this->physical_posterior;
    }

    /**
     * @param mixed $physical_posterior
     */
    public function setPhysicalPosterior($physical_posterior)
    {
        $this->physical_posterior = $physical_posterior;
    }

    /**
     * @return mixed
     */
    public function getAssessmentSpecialConsideration()
    {
        return $this->assessment_special_consideration;
    }

    /**
     * @param mixed $assessment_special_consideration
     */
    public function setAssessmentSpecialConsideration($assessment_special_consideration)
    {
        $this->assessment_special_consideration = $assessment_special_consideration;
    }

    /**
     * @return mixed
     */
    public function getCriticalFailures()
    {
        return $this->critical_failures;
    }

    /**
     * @param mixed $critical_failures
     */
    public function setCriticalFailures($critical_failures)
    {
        $this->critical_failures = $critical_failures;
    }

    /**
     * @return mixed
     */
    public function getDangerousActions()
    {
        return $this->dangerous_actions;
    }

    /**
     * @param mixed $dangerous_actions
     */
    public function setDangerousActions($dangerous_actions)
    {
        $this->dangerous_actions = $dangerous_actions;
    }

    /**
     * @return mixed
     */
    public function getSkills()
    {
        return $this->skills;
    }

    /**
     * @param mixed $skills
     */
    public function setSkills($skills)
    {
        $this->skills = $skills;
    }

    /**
     * @return mixed
     */
    public function getReviews()
    {
        return $this->reviews;
    }

    /**
     * @param mixed $reviews
     */
    public function setReviews($reviews)
    {
        $this->reviews = $reviews;
    }

    /**
     * @return mixed
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param mixed $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param mixed $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @return mixed
     */
    public function getAssets()
    {
        return $this->assets;
    }

    /**
     * @param mixed $assets
     */
    public function setAssets($assets)
    {
        $this->assets = $assets;
    }

    /**
     * @return mixed
     */
    public function getCurveball()
    {
        return $this->curveball;
    }

    /**
     * @param mixed $curveball
     */
    public function setCurveball($curveball)
    {
        $this->curveball = $curveball;
    }
    
    /**
     * @Column(type="text", nullable=true)
     */
    protected $notes;
    
    /**
     * @Column(type="string", nullable=true)
     */
    protected $patient_information;
    
    /**
     * @Column(type="integer", nullable=true)
     */
    protected $patient_weight;
    
    /**
     * @ManyToOne(targetEntity="WeightUnit")
     * @JoinColumn(name="weight_unit_id", referencedColumnName="id")
     */
    protected $weight_unit;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $sample_signs;
    
    /**
     * @Column(type="string", nullable=true)
     */
    protected $sample_allergies;
    
    /**
     * @Column(type="string", nullable=true)
     */
    protected $sample_medications;
    
    /**
     * @Column(type="string", nullable=true)
     */
    protected $sample_prior_history;
    
    /**
     * @Column(type="string", nullable=true)
     */
    protected $sample_last_oral_intake;
    
    /**
     * @Column(type="string", nullable=true)
     */
    protected $sample_events;
    
    /**
     * @Column(type="string", nullable=true)
     */
    protected $opqrst_onset;
    
    /**
     * @Column(type="string", nullable=true)
     */
    protected $opqrst_provocation;
    
    /**
     * @Column(type="string", nullable=true)
     */
    protected $opqrst_quality;
    
    /**
     * @Column(type="string", nullable=true)
     */
    protected $opqrst_radiation;
    
    /**
     * @Column(type="string", nullable=true)
     */
    protected $opqrst_severity;
    
    /**
     * @Column(type="string", nullable=true)
     */
    protected $opqrst_time;
    
    /**
     * @Column(type="string", nullable=true)
     */
    protected $physical_heent;
    
    /**
     * @Column(type="string", nullable=true)
     */
    protected $physical_neck;
    
    /**
     * @Column(type="string", nullable=true)
     */
    protected $physical_chest;
    
    /**
     * @Column(type="string", nullable=true)
     */
    protected $physical_abdomen;
    
    /**
     * @Column(type="string", nullable=true)
     */
    protected $physical_pelvis;
    
    /**
     * @Column(type="string", nullable=true)
     */
    protected $physical_lower_extremities;
    
    /**
     * @Column(type="string", nullable=true)
     */
    protected $physical_upper_extremities;
    
    /**
     * @Column(type="string", nullable=true)
     */
    protected $physical_posterior;
    
    /**
     * @Column(type="string", nullable=true)
     */
    protected $assessment_special_consideration;
    
    /**
     * @Column(type="text")
     */
    protected $critical_failures;
    
    /**
     * @Column(type="text")
     */
    protected $dangerous_actions;
    
    /**
     * @OneToMany(targetEntity="ScenarioSkill", mappedBy="scenario")
     */
    protected $skills;
    
    /**
     * @OneToMany(targetEntity="ScenarioReview", mappedBy="scenario")
     */
    protected $reviews;
    
    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $author;
    
    /**
     * @ManyToOne(targetEntity="ScenarioState")
     * @JoinColumn(name="scenario_state_id", referencedColumnName="id")
     */
    protected $state;
    
    /**
     * @ManyToMany(targetEntity="AssetLegacy")
     * @JoinTable(name="fisdap2_scenario_assets",
     *      joinColumns={@JoinColumn(name="scenario_id", referencedColumnName="id")},
     *      inverseJoinColumns={@JoinColumn(name="asset_id", referencedColumnName="AssetDef_id")}
     *      )
     */
    protected $assets;
    
    /**
     * @Column(type="string")
     */
    protected $curveball;
    
    public function init()
    {
        $this->skills = new ArrayCollection();
        $this->assets = new ArrayCollection();
    }
    
    public function getDescription()
    {
        $description = "";

        $description .= $this->patient->age . "yo " . $this->patient->gender->name . ", " . $this->patient->getComplaintNames();

        return $description;
    }
    
    /**
     * Set the assets for this scenario
     *
     * @param mixed $value
     */
    public function setAssetIds($value)
    {
        if (is_null($value)) {
            $value = array();
        } elseif (!is_array($value)) {
            $value = array($value);
        }
    
        $this->assets->clear();
    
        foreach ($value as $id) {
            $asset = EntityUtils::getEntity('AssetLegacy', $id);
            $this->assets->add($asset);
        }
    }
    
    /**
     * Get an array of Asset IDs
     *
     * @return array
     */
    public function getAssetIds()
    {
        $assets = array();
    
        foreach ($this->assets as $asset) {
            $assets[] = $asset->id;
        }
    
        return $assets;
    }
}
