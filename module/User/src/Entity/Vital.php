<?php namespace User\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Fisdap\Api\Shifts\Patients\Skills\Jobs\Vitals\SetVitals;
use User\EntityUtils;

/**
 * Vitals
 *
 * @Entity(repositoryClass="Fisdap\Data\Skill\DoctrineVitalRepository")
 * @Table(name="fisdap2_vitals")
 * @HasLifecycleCallbacks
 */
class Vital extends Skill
{
	const viewScriptName = "vital";
	
    /**
	 * @var integer
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue
	 */
	protected $id;
    
    /**
     * @Column(type="string", nullable=true)
     */
    protected $systolic_bp;
    
    /**
     * @Column(type="string", nullable=true)
     */
    protected $diastolic_bp;
    
    /**
     * @Column(type="integer", nullable=true)
     */
    protected $pulse_rate;
    
    /**
     * @ManyToOne(targetEntity="VitalPulseQuality")
     */
    protected $pulse_quality;
    
    /**
     * @Column(type="integer", nullable=true)
     */
    protected $resp_rate;
    
    /**
     * @ManyToOne(targetEntity="VitalRespQuality")
     */
    protected $resp_quality;
    
    /**
     * @Column(type="integer", nullable=true)
     */
    protected $spo2;
    
    /**
     * @Column(type="boolean", nullable=true)
     */
    protected $pupils_equal;
    
    /**
     * @Column(type="boolean", nullable=true)
     */
    protected $pupils_round;
    
    /**
     * @Column(type="boolean", nullable=true)
     */
    protected $pupils_reactive;
    
    /**
     * @Column(type="integer", nullable=true)
     */
    protected $blood_glucose;
    
    /**
     * @Column(type="integer", nullable=true)
     */
    protected $apgar;
    
    /**
     * @Column(type="integer", nullable=true)
     */
    protected $gcs;
    
    /**
     * @ManyToMany(targetEntity="VitalSkin")
     * @JoinTable(name="fisdap2_vitals_skins",
     *  joinColumns={@JoinColumn(name="vital_id", referencedColumnName="id")},
     *  inverseJoinColumns={@JoinColumn(name="skin_id",referencedColumnName="id")})
     */
	protected $skins;
    
    /**
     * @ManyToMany(targetEntity="VitalLungSound")
     * @JoinTable(name="fisdap2_vitals_lung_sounds",
     *  joinColumns={@JoinColumn(name="vital_id", referencedColumnName="id")},
     *  inverseJoinColumns={@JoinColumn(name="lung_sound_id",referencedColumnName="id")})
     */
	protected $lung_sounds;
	
	/**
	 * @Column(type="integer", nullable=true)
	 */
	protected $pain_scale;
	
	/**
	 * @Column(type="string", nullable=true)
	 */
	protected $end_tidal_co2;
	
	/**
     * @var float
     * @Column(type="decimal", scale=2, precision=5, nullable=true)
	 */
	protected $temperature;
	
	/**
	 * @Column(type="string", nullable=true)
	 */
	protected $temperature_units;
    
	public function __clone()
	{
		if ($this->id) {
			//Reset the ID for a new entity
			$this->id = null;
			
			//Get associations before clearing
			$skins = $this->get_skins();
			$lung_sounds = $this->get_lung_sounds();
			
			//Reinitialize associations
			$this->init();
			
			//Re-add old associations but to the new entity
			$this->set_skins($skins);
			$this->set_lung_sounds($lung_sounds);
		}
	}
	
    public function init()
    {
        $this->skins = new ArrayCollection();
        $this->lung_sounds = new ArrayCollection();
		$this->subject = EntityUtils::getEntity('Subject', 1);
    }
	
	public function set_pulse_quality($value)
	{
		$this->pulse_quality = self::id_or_entity_helper($value, "VitalPulseQuality");
	}

	public function setPulseQuality(VitalPulseQuality $pulseQuality = null)
	{
		$this->pulse_quality = $pulseQuality;
	}

	public function getPulseQuality()
	{
		return $this->pulse_quality;
	}

	public function setRespQuality(VitalRespQuality $respQuality = null)
	{
		$this->resp_quality = $respQuality;
	}

	public function getRespQuality()
	{
		return $this->resp_quality;
	}

	public function set_resp_quality($value)
	{
		$this->resp_quality = self::id_or_entity_helper($value, "VitalRespQuality");
	}
	
	public function getBp()
	{
		return $this->systolic_bp . "/" . $this->diastolic_bp;
	}
	
	/**
	 * Add a skin to this vital entity
	 *
	 * @param mixed $id either an ID or VitalSkin entity
	 */
	public function addSkin($id)
	{
		$skin = self::id_or_entity_helper($id, 'VitalSkin');

        // Have to make sure this skin condition doesn't already exist for this vital, otherwise mysql
        // with throw a constraint violation.
        $found = false;
        foreach ($this->skins as $existingSkin) {
            if ($skin->id == $existingSkin->id) {
                $found = true;
                break;
            }
        }

        if (!$found) {
            $this->skins->add($skin);
        }
	}
	
	/**
	 * Remove a skin from this vital entity
	 *
	 * @param mixed $id either an ID or VitalSkin entity
	 */
	public function removeSkin($id)
	{
		$skin = self::id_or_entity_helper($id, 'VitalSkin');
		$this->skins->removeElement($skin);
	}

    /**
     * Remove all skins from vital entity.
     */
	public function removeAllSkin()
    {
        $this->skins->clear();
    }
	
	/**
	 * Add a lung sound to this vital entity
	 *
	 * @param mixed $id either an ID or VitalLungSound entity
	 */
	public function addLungSound($id)
	{
		$lung_sound = self::id_or_entity_helper($id, 'VitalLungSound');

        // Have to make sure this lung sound doesn't already exist for this vital, otherwise mysql
        // with throw a constraint violation.
        $found = false;
        foreach ($this->lung_sounds as $existingLungSound) {
            if ($lung_sound->id == $existingLungSound->id) {
                $found = true;
                break;
            }
        }

        if (!$found) {
            $this->lung_sounds->add($lung_sound);
        }
	}
	
	/**
	 * Remove a lung sound from this vital entity
	 *
	 * @param mixed $id either an ID or VitalLungSound entity
	 */
	public function removeLungSound($id)
	{
		$lung_sound = self::id_or_entity_helper($id, 'VitalLungSound');
		$this->lung_sounds->removeElement($lung_sound);
	}

	public function removeAllLungSounds()
    {
        $this->lung_sounds->clear();
    }

	/**
	 * Set the lung sounds for this vital
	 *
	 * @param mixed $value
	 */
	public function set_lung_sounds($value)
	{
		if (is_null($value)) {
			$value = array();
		} else if (!is_array($value)) {
			$value = array($value);
		}
		
		$this->lung_sounds->clear();
		
		foreach($value as $id) {
			$lungSound = self::id_or_entity_helper($id, 'VitalLungSound');
			$this->lung_sounds->add($lungSound);
		}
	}
	
	/**
	 * Get an array of lung sound IDs
	 *
	 * @return array
	 */
	public function get_lung_sounds()
	{
		$lungSounds = array();
		
		foreach($this->lung_sounds as $lungSound) {
			$lungSounds[] = $lungSound->id;
		}
		
		return $lungSounds;
	}

	/**
	 * Set the skins for this vital
	 *
	 * @param mixed $value
	 */
	public function set_skins($value)
	{
		if (is_null($value)) {
			$value = array();
		} else if (!is_array($value)) {
			$value = array($value);
		}

		$this->skins->clear();

		foreach($value as $id) {
			$skin = self::id_or_entity_helper($id, 'VitalSkin');
			$this->skins->add($skin);
		}
	}

	/**
	 * Set the skins for this vital
	 *
	 * @param mixed $value
	 */
	public function setSkins($value)
	{
		if (is_null($value)) {
			$value = array();
		} else if (!is_array($value)) {
			$value = array($value);
		}

		$this->skins->clear();

		foreach($value as $id) {
			$skin = self::id_or_entity_helper($id, 'VitalSkin');
			$this->skins->add($skin);
		}
	}
	
	/**
	 * Get an array of skin IDs
	 *
	 * @return array
	 */
	public function get_skins()
	{
		$skins = array();
		
		foreach($this->skins as $skin) {
			$skins[] = $skin->id;
		}
		
		return $skins;
	}
	
	public function getViewScriptName()
	{
		return self::viewScriptName;
	}
	
	public function getProcedureText($html=true){
		$textParts1 = array();
		
		if(isset($this->systolic_bp) && $this->systolic_bp != "" && $this->systolic_bp != NULL &&
		   isset($this->diastolic_bp) && $this->diastolic_bp != "" && $this->diastolic_bp != NULL){
			$textParts1[] = 'BP ' . $this->getBp();
		}
		
		if(isset($this->pulse_rate) && isset($this->pulse_quality)){
			$textParts1[] = 'P ' . $this->pulse_rate . ', ' . $this->pulse_quality->name;
		}
		
		if(isset($this->resp_rate) && isset($this->resp_quality)){
			$textParts1[] = 'R ' . $this->resp_rate . ', ' . $this->resp_quality->name;
		}
		
		if(isset($this->spo2)){
			$textParts1[] = 'SpO2 ' . $this->spo2 . '%';
		}
		
		$textParts2 = array();
		
		if(count($this->skins) > 0){
			$skinTypes = array();
			foreach($this->skins as $skinType){
				$skinTypes[] = $skinType->name;
			}
			
			$textParts2[] = implode(', ', $skinTypes);
		}
		
		if ($this->pupils_equal == 1 && $this->pupils_round == 1 && $this->pupils_reactive == 1) {
			$textParts2[] = "PERRL: yes";
		} else if ($this->pupils_equal !== null || $this->pupils_round !== null || $this->pupils_reactive !== null) {
			$textParts2[] = "PERRL: no";
		}
		
		if(count($this->lung_sounds) > 0){
			$soundTypes = array();
			foreach($this->lung_sounds as $soundType){
				$soundTypes[] = $soundType->name;
			}
			
			$textParts2[] = implode(', ', $soundTypes);
		}
		
		if(isset($this->blood_glucose)){
			$textParts2[] = "BGL " . $this->blood_glucose . " mg/dL";
		}
		
		if(isset($this->apgar)){
			$textParts2[] = "APGAR " . $this->apgar;
		}
		
		if(isset($this->gcs)){
			$textParts2[] = "GCS " . $this->gcs;
		}
		
		if(isset($this->pain_scale)){
			$textParts2[] = "Pain " . $this->pain_scale;
		}
		
		if(isset($this->pain_scale)){
			$textParts2[] = "ETCO2 " . $this->end_tidal_co2;
		}
		
		if($this->temperature != ''){
			$textParts2[] = "Temp " . $this->temperature . " " . $this->temperature_units;
		}

		$shiftType = "";
		if (!is_null($this->shift) && !is_null($this->shift->type)) {
		    $shiftType = $this->shift->type;
        }
		
		if ($html) {
			$line1 = "<span class='summary-details {$shiftType}'>" . implode('; ', $textParts1) . "</span><br />";
			$line2 = "<span class='summary-details'>" . implode('; ', $textParts2) . "</span>";

			return $line1 . $line2;
		} else {
			$line1 = implode('; ', $textParts1) . "\n";
			$line2 = implode('; ', $textParts2) . "\n";

			return ucwords(self::viewScriptName) . "\n" . $line1 . $line2 . "\n";
		}
	}
	
	public function getHookIds()
	{
		return array();
	}

	/**
	 * @param SetVitals $vitalModel
	 */
	public function setVitalInfo(SetVitals $vitalModel)
	{
		$this->systolic_bp = $vitalModel->systolicBP;
		$this->diastolic_bp = $vitalModel->diastolicBP;
		$this->pulse_rate  = $vitalModel->pulseRate;
		$this->setPulseQuality($vitalModel->pulseQuality);
		$this->resp_rate = $vitalModel->respiratoryRate;
		$this->setRespQuality($vitalModel->respiratoryQuality);
		$this->spo2 = $vitalModel->spo2;
		$this->pupils_equal = $vitalModel->pupilsEqual;
		$this->pupils_round = $vitalModel->pupilsRound;
		$this->pupils_reactive = $vitalModel->pupilsReactive;
		$this->blood_glucose = $vitalModel->bloodGlucose;
		$this->apgar = $vitalModel->apgar;
		$this->gcs = $vitalModel->gcs;
		$this->pain_scale = $vitalModel->painScale;
	    $this->end_tidal_co2 = $vitalModel->endTidalCo2;
		$this->temperature = $vitalModel->temperatureValue;
		$this->temperature_units = $vitalModel->temperatureUnits;

        $this->removeAllSkin();
		foreach((array) $vitalModel->skinConditionIds as $skinCondition) {
			$this->addSkin($skinCondition);
		}

		$this->removeAllLungSounds();
		foreach((array) $vitalModel->lungSoundIds as $lungSound) {
			$this->addLungSound($lungSound);
		}
	}

	/**
	 * @return array
	 */
	public function toArray()
	{
		$skills = parent::toArray();
        $skills['vitalId'] = $this->id;
		$skills['systolicBP'] = $skills['systolic_bp'];
		$skills['diastolicBP'] = $skills['diastolic_bp'];
		$skills['pulseRate'] = $skills['pulse_rate'];
		$skills['pulseQualityId'] = $this->getPulseQuality() ? $this->getPulseQuality()->id : null;
		$skills['respiratoryRate'] = $skills['resp_rate'];
		$skills['respiratoryQualityId'] = $this->getRespQuality() ? $this->getRespQuality()->id : null;
		$skills['pupilsEqual'] = $skills['pupils_equal'];
		$skills['pupilsRound'] = $skills['pupils_round'];
		$skills['pupilsReactive'] = $skills['pupils_reactive'];
		$skills['bloodGlucose'] = $skills['blood_glucose'];
		$skills['painScale'] = $skills['pain_scale'];
		$skills['endTidalCo2'] = $skills['end_tidal_co2'];
		$skills['temperatureValue'] = $skills['temperature'];
		$skills['temperatureUnits'] = $skills['temperature_units'];
		$skills['skinConditionIds'] = $this->get_skins() ? $this->get_skins() : null;
		$skills['lungSoundIds'] = $this->get_lung_sounds() ? $this->get_lung_sounds() : null;

		unset(
			$skills['systolic_bp'],
			$skills['diastolic_bp'],
			$skills['pulse_rate'],
			$skills['resp_rate'],
			$skills['pupils_equal'],
			$skills['pupils_round'],
			$skills['pupils_reactive'],
			$skills['blood_glucose'],
			$skills['pain_scale'],
			$skills['end_tidal_co2'],
			$skills['temperature'],
			$skills['temperature_units']
		);

		return $skills;
	}
}
