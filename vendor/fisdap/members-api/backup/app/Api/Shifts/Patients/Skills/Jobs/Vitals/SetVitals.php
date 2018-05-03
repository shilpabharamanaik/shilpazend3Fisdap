<?php namespace Fisdap\Api\Shifts\Patients\Skills\Jobs\Vitals;

use Doctrine\ORM\EntityManagerInterface;
use Fisdap\Api\Shifts\Patients\Skills\AbstractSkills;
use Fisdap\Entity\Vital;
use Fisdap\Entity\VitalPulseQuality;
use Fisdap\Entity\VitalRespQuality;
use Swagger\Annotations as SWG;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;

/**
 * Class SetVitals
 * @package Fisdap\Api\Shifts\Patients\Skills\Jobs\Vitals
 * @author  Isaac White <isaac.white@ascendlearning.com>
 *
 * @SWG\Definition(
 *     definition="Vital",
 *     required={ "dose", "performed" }
 * )
 */
final class SetVitals extends AbstractSkills
{
    /**
     * @var integer
     * @SWG\Property(type="integer", example=0)
     */
    public $vitalId = 0;

    /**
     * @var string|null
     * @SWG\Property(type="string", example="Ping")
     */
    public $systolicBP;

    /**
     * @var string|null
     * @SWG\Property(type="string", example="Pong")
     */
    public $diastolicBP;
    
    /**
     * @var integer|null
     * @SWG\Property(type="integer", example=60)
     */
    public $pulseRate;

    /**
     * @var integer|null
     * @see VitalPulseQuality
     * @SWG\Property(type="integer", example=3)
     */
    public $pulseQualityId;

    /**
     * @var VitalPulseQuality
     */
    public $pulseQuality;

    /**
     * @var integer|null
     * @SWG\Property(type="integer", example=12)
     */
    public $respiratoryRate;

    /**
     * @var integer|null
     * @see VitalRespQuality
     * @SWG\Property(type="integer", example=2)
     */
    public $respiratoryQualityId;

    /**
     * @var VitalRespQuality
     */
    public $respiratoryQuality;

    /**
     * @var integer|null
     * @SWG\Property(type="integer", example=5)
     */
    public $spo2;

    /**
     * @var boolean|null
     * @SWG\Property(type="boolean", example=true)
     */
    public $pupilsEqual;

    /**
     * @var boolean|null
     * @SWG\Property(type="boolean", example=true)
     */
    public $pupilsRound;

    /**
     * @var boolean|null
     * @SWG\Property(type="boolean", example=true)
     */
    public $pupilsReactive;

    /**
     * @var integer|null
     * @SWG\Property(type="integer", example=5)
     */
    public $bloodGlucose;

    /**
     * @var integer|null
     * @SWG\Property(type="integer", example=5)
     */
    public $apgar;

    /**
     * @var integer|null
     * @SWG\Property(type="integer", example=5)
     */
    public $gcs;

    /**
     * @var array
     * @See VitalSkin
     * @SWG\Property(type="array", items="integer", example={5,6,7}))
     */
    public $skinConditionIds;

    /**
     * @var array
     * @See VitalLungSound
     * @SWG\Property(type="array", items="integer", example={2,3,4})
     */
    public $lungSoundIds;

    /**
     * @var integer|null
     * @SWG\Property(type="integer", description="Pain as relayed on a scale of 1-10", example=5)
     */
    public $painScale;

    /**
     * @var string|null
     * @SWG\Property(type="string", example="A String")
     */
    public $endTidalCo2;

    /**
     * @var float|null
     * @Column(type="decimal", scale=2, precision=5, nullable=true)
     */
    public $temperatureValue;

    /**
     * @var string
     * @SWG\Property(type="string", example="Fahrenheit")
     */
    public $temperatureUnits;

    public function setVitalId($vitalId) {
        $this->vitalId = $vitalId;
    }

    /**
     * @param EntityManagerInterface $em
     * @param EventDispatcher $eventDispatcher
     * @return Vital|null
     */
    public function handle(
        EntityManagerInterface $em,
        EventDispatcher $eventDispatcher
    )
    {
        $this->em = $em;

        // Try to grab an existing Vital. If not found, create a new one.
        $vital = $this->validResourceEntityManager(Vital::class, $this->vitalId);
        $vital = $vital ? $vital : new Vital;

        $vital->set_patient($this->patient);
        $vital->set_shift($this->shift);

        $this->pulseQuality = $this->validResourceEntityManager(VitalPulseQuality::class, $this->pulseQualityId);
        $this->respiratoryQuality = $this->validResourceEntityManager(VitalRespQuality::class, $this->respiratoryQualityId);
        $vital->setVitalInfo($this);     
        $vital->setDefaultSkills($this);

        /**
         * TODO - figure out when to set skill order.
         */
        //$vital->setSkillOrder();

        $eventDispatcher->fire($this->vitalId);

        return $vital; 
    }
    
    public function rules()
    {
        return [
            'performed'      => 'required|boolean',
            'size'           => 'integer',
            'success'        => 'boolean',
            'attempts'       => 'integer',
            'practiceItemId' => 'integer',
            'skillOrder'     => 'integer',
        ];
    }
}

