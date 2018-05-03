<?php namespace Fisdap\Api\Shifts\Patients\Skills\Jobs\Airways;

use Doctrine\ORM\EntityManagerInterface;
use Fisdap\Api\Shifts\Patients\Skills\AbstractSkills;
use Fisdap\Data\AirwayManagement\AirwayManagementRepository;
use Fisdap\Entity\Airway;
use Fisdap\Entity\AirwayManagement;
use Fisdap\Entity\AirwayProcedure;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use Swagger\Annotations as SWG;
use Illuminate\Contracts\Bus\Dispatcher as BusDispatcher;

/**
 * Class SetAirways
 * @author  Isaac White <isaac.white@ascendlearning.com>
 * 
 * @SWG\Definition(
 *     definition="Airway",
 *     description="This is a model representation of a default Airway",
 *     required={ "procedureId" }
 * )
 */
final class SetAirways extends AbstractSkills
{
    /**
     * @var integer
     * @SWG\Property(type="integer", example=0)
     */
    public $airwayId = 0;

    /**
     * @var float|null
     * @SWG\Property(type="decimal", example=5.100)
     */
    public $size;

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
     * @var boolean|null
     * @SWG\Property(type="boolean", example=true)
     */
    public $airwaySuccess = null;

    /**
     * @var \Fisdap\Api\Shifts\Patients\Jobs\SetAirwayManagement
     * @SWG\Property(items=@SWG\Items(ref="#/definitions/Airway_Management"))
     */
    public $airwayManagement = null;

    public function setAirwayId($airwayId) {
        $this->airwayId = $airwayId;
    }

    /**
     * @param EntityManagerInterface $em
     * @param EventDispatcher $eventDispatcher
     *
     * @param BusDispatcher $busDispatcher
     * @param AirwayManagementRepository $airwayManagementRepository
     * @return Airway
     */
    public function handle(
        EntityManagerInterface $em,
        EventDispatcher $eventDispatcher,
        BusDispatcher $busDispatcher,
        AirwayManagementRepository $airwayManagementRepository)
    {
        $this->em = $em;

        // Try to grab an existing Airway. If not found, create a new one.
        $airway = $this->validResourceEntityManager(Airway::class, $this->airwayId);
        $airway = $airway ? $airway : new Airway;
        $airway->set_patient($this->patient);
        $airway->set_shift($this->shift);
        $airway->setProcedure($this->validResourceEntityManager(AirwayProcedure::class, $this->procedureId, true));

        $airway->setPerformedBy($this->performed);

        $airway->setDefaultSkills($this);

        if (!is_null($this->airwayManagement)) {
            if ($this->getShift() != null) {
                $this->airwayManagement->setShift($this->getShift());
            } else if ($this->getPatient()->getShift() != null) {
                $this->airwayManagement->setShift($this->getPatient()->getShift());
            }

            if ($airway != null) {
                $this->airwayManagement->setAirway($airway);
            } else if ($this->getPatient() != null) {
                $this->airwayManagement->setPatient($this->getPatient());
            }

            /** @var AirwayManagement $airMan */
            $airMan = $busDispatcher->dispatch($this->airwayManagement);
            $airMan->setUUID($this->uuid);
            $airMan->setAirway($airway);
            $airway->setAirwayManagement($airMan);

            $airMan->save();
        } else if (is_null($this->airwayManagement) && !is_null($airway->id)) {
            // We need to find any airwayManagement records belonging to this airway and nuke them.
            $airManRecords = $airwayManagementRepository->findBy(['airway' => $airway->id]);

            foreach ($airManRecords as $airManRecord) {
                $airwayManagementRepository->destroy($airManRecord);
            }
            $airway->setAirwayManagement(null);
        } else if (is_null($this->airwayManagement) && !is_null($this->getPatient())) {
            // We need to find any airwayManagement records belonging to this airway and nuke them.
            $airManRecords = $airwayManagementRepository->findBy(['patient' => $this->getPatient()->getId()]);

            foreach ($airManRecords as $airManRecord) {
                $airwayManagementRepository->destroy($airManRecord);
            }
            $airway->setAirwayManagement(null);
        }

        $eventDispatcher->fire($this->airwayId);

        return $airway;
    }
}

