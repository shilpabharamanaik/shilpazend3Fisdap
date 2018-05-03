<?php namespace Fisdap\Api\Shifts\Patients\Skills\Jobs\OtherInterventions;


use Doctrine\ORM\EntityManagerInterface;
use Fisdap\Api\Shifts\Patients\Skills\AbstractSkills;
use Fisdap\Entity\OtherIntervention;
use Fisdap\Entity\OtherProcedure;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;

/**
 * Class SetOtherInterventions
 * @package Fisdap\Api\Shifts\Patients\Skills\Jobs\OtherInterventions
 * @author  Isaac White <isaac.white@ascendlearning.com>
 * 
 * @SWG\Definition(
 *     definition="Other_Intervention",
 *     description="This is a model representation of a default Other Intervention",
 *     required={ "procedureId", "performed" }
 * )
 */
final class SetOtherInterventions extends AbstractSkills
{
    /**
     * @var integer
     * @SWG\Property(type="integer", example=0)
     */
    public $otherInterventionId = 0;

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

    public function setOtherInterventionId($otherInterventionId) {
        $this->otherInterventionId = $otherInterventionId;
    }

    /**
     * @param EntityManagerInterface $em
     * @param EventDispatcher $eventDispatcher
     * @return OtherIntervention|null
     */
    public function handle(
        EntityManagerInterface $em,
        EventDispatcher $eventDispatcher
    )
    {
        $this->em = $em;

        // Try to grab an existing Other Intervention. If not found, create a new one.
        $otherInterventions = $this->validResourceEntityManager(OtherIntervention::class, $this->otherInterventionId);
        $otherInterventions = $otherInterventions ? $otherInterventions : new OtherIntervention;

        $otherInterventions->set_patient($this->patient);
        $otherInterventions->set_shift($this->shift);

        $otherInterventions->setProcedure($this->validResourceEntityManager(OtherProcedure::class, $this->procedureId, true));
        
        $otherInterventions->setDefaultSkills($this);
        
        $eventDispatcher->fire($this->otherInterventionId);
        
        return $otherInterventions;
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'procedureId' => 'required|integer',
            'performed'   => 'required|boolean',
        ];
    }
}

