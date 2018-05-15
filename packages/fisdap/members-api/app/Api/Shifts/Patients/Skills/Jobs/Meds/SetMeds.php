<?php namespace Fisdap\Api\Shifts\Patients\Skills\Jobs\Meds;

use Doctrine\ORM\EntityManagerInterface;
use Fisdap\Api\Shifts\Patients\Skills\AbstractSkills;
use Fisdap\Entity\Med;
use Fisdap\Entity\MedRoute;
use Fisdap\Entity\MedType;
use Swagger\Annotations as SWG;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;

/**
 * Class SetMeds
 * @package Fisdap\Api\Shifts\Patients\Skills\Jobs\Meds
 * @author  Isaac White <isaac.white@ascendlearning.com>
 *
 * @SWG\Definition(
 *     definition="Medication",
 *     required={ "medicationId",  "routeId", "dose", "performed" }
 * )
 */
final class SetMeds extends AbstractSkills
{
    /**
     * @var integer
     * @SWG\Property(type="integer", example=0)
     */
    public $medicationId = 0;

    /**
     * @var integer
     * @See MedType
     * @SWG\Property(type="integer", example=5)
     */
    public $medicationTypeId;

    /**
     * @var integer
     * @See MedRoute
     * @SWG\Property(type="integer", example=2)
     */
    public $routeId;

    /**
     * @var string
     * @SWG\Property(type="string", example="I am a dose")
     */
    public $dose;

    /**
     * @var boolean
     * @SWG\Property(type="boolean", example=true)
     */
    public $performed;

    /**
     * @var integer
     * @See PracticeItem
     * @SWG\Property(type="integer", example=2)
     * TODO - Determine what practice item id refers to, i.e it is not a fixture.
     */
    public $practiceItemId;

    public function setMedicationId($medicationId)
    {
        $this->medicationId = $medicationId;
    }

    /**
     * @param EntityManagerInterface $em
     * @param EventDispatcher $eventDispatcher
     * @return Med|null
     */
    public function handle(
        EntityManagerInterface $em,
        EventDispatcher $eventDispatcher
    ) {
        $this->em = $em;

        // Try to grab an existing Med. If not found, create a new one.
        $med = $this->validResourceEntityManager(Med::class, $this->medicationId);
        $med = $med ? $med : new Med;

        $med->set_patient($this->patient);
        $med->set_shift($this->shift);

        $med->setMedication($this->validResourceEntityManager(MedType::class, $this->medicationTypeId));
        $med->setRoute($this->validResourceEntityManager(MedRoute::class, $this->routeId));
        $med->setDose($this->dose);
        $med->setPerformedBy($this->performed);
        $med->setDefaultSkills($this);

        $med->setSkillOrder();

        $eventDispatcher->fire($med->getMedication());

        return $med;
    }
    
    public function rules()
    {
        return [
            'medicationTypeId'  => 'required|integer',
            'routeId'           => 'required|integer',
            'dose'              => 'required|string',
            'performed'         => 'boolean',
            'size'              => 'integer',
            'success'           => 'boolean',
            'attempts'          => 'integer',
            'practiceItemId'    => 'integer',
            'skillOrder'        => 'integer',
        ];
    }
}
