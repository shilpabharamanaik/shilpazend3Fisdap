<?php namespace Fisdap\Api\Shifts\Patients\Skills\Jobs\CardiacInterventions;

use Doctrine\ORM\EntityManagerInterface;
use Fisdap\Api\Shifts\Patients\Skills\AbstractSkills;
use Fisdap\Entity\CardiacIntervention;
use Fisdap\Entity\CardiacPacingMethod;
use Fisdap\Entity\CardiacProcedure;
use Fisdap\Entity\CardiacProcedureMethod;
use Fisdap\Entity\RhythmType;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use Swagger\Annotations as SWG;

/**
 * Class SetCardiacInterventions
 * @package Fisdap\Api\Shifts\Patients\Skills\Jobs\CardiacInterventions
 * @author  Isaac White <isaac.white@ascendlearning.com>
 *
 * @SWG\Definition(
 *     definition="Cardiac_Intervention",
 *     description="This is a model representation of a default Cardiac Intervention",
 *     required={ "procedureId", "performed",
 *                "rhythmTypeId", "rhythmPerformed", "ectopyIds" }
 * )
 */
final class SetCardiacInterventions extends AbstractSkills
{
    /**
     * @var integer
     * @SWG\Property(type="integer", example=0)
     */
    public $cardiacId = 0;

    /**
     * @var integer
     * @SWG\Property(type="integer", example=5)
     */
    public $procedureId;

    /**
     * @var boolean
     * @SWG\Property(type="boolean", example=true)
     */
    public $performed;

    /**
     * @var integer
     * @SWG\Property(type="integer", example=2)
     */
    public $pacingMethodId;

    /**
     * @var integer
     * @SWG\Property(type="integer", example=2)
     */
    public $procedureMethodId;

    /**
     * @var integer
     * @SWG\Property(type="integer", example=4)
     */
    public $rhythmTypeId;

    /**
     * @var boolean
     * @SWG\Property(type="boolean", example=true)
     */
    public $rhythmPerformed;

    /**
     * @var boolean
     * @SWG\Property(type="boolean", example=false)
     */
    public $twelveLead;

    /**
     * @var array|null
     * @SWG\Property(type="array", items="integer", example={3,5})
     */
    public $ectopyIds;

    public function setCardiacId($cardiacId)
    {
        $this->cardiacId = $cardiacId;
    }

    /**
     * @param EntityManagerInterface $em
     * @param EventDispatcher $eventDispatcher
     *
     * @return CardiacIntervention
     */
    public function handle(
        EntityManagerInterface $em,
        EventDispatcher $eventDispatcher
    ) {
        $this->em = $em;

        // Try to grab an existing Cardiac Intervention. If not found, create a new one.
        $cardiacIntervention = $this->validResourceEntityManager(CardiacIntervention::class, $this->cardiacId);
        $cardiacIntervention = $cardiacIntervention ? $cardiacIntervention : new CardiacIntervention;

        $cardiacIntervention->set_patient($this->patient);
        $cardiacIntervention->set_shift($this->shift);

        $cardiacIntervention->setProcedure($this->validResourceEntityManager(CardiacProcedure::class, $this->procedureId));
        $cardiacIntervention->setPacingMethod($this->validResourceEntityManager(CardiacPacingMethod::class, $this->pacingMethodId));
        $cardiacIntervention->setRhythmType($this->validResourceEntityManager(RhythmType::class, $this->rhythmTypeId, true));
        $cardiacIntervention->setProcedureMethod($this->validResourceEntityManager(CardiacProcedureMethod::class, $this->procedureMethodId));
        $cardiacIntervention->set_ectopies($this->ectopyIds);

        $cardiacIntervention->setPerformedBy($this->performed);
        $cardiacIntervention->rhythm_performed_by = $this->rhythmPerformed;
        $cardiacIntervention->twelve_lead = $this->twelveLead;
        
        $cardiacIntervention->setDefaultSkills($this);
        
        $eventDispatcher->fire($this->cardiacId);

        return $cardiacIntervention;
    }
}
