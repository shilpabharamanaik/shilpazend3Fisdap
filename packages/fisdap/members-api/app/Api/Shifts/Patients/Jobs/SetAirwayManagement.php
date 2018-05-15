<?php namespace Fisdap\Api\Shifts\Patients\Jobs;

use Doctrine\ORM\EntityManagerInterface;
use Fisdap\Api\Jobs\Job;
use Fisdap\Api\Jobs\RequestHydrated;
use Fisdap\Data\AirwayManagement\AirwayManagementRepository;
use Fisdap\Entity\Airway;
use Fisdap\Entity\AirwayManagement;
use Fisdap\Entity\AirwayManagementSource;
use Fisdap\Entity\Patient;
use Fisdap\Entity\PracticeItem;
use Fisdap\Entity\ShiftLegacy;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use Swagger\Annotations as SWG;

/**
 * Class AirwayManagement
 * @package Fisdap\Api\Shifts\Patients\Jobs
 * @author  Isaac White <isaac.white@ascendlearning.com>
 * @author  Nick Karnick <nkarnick@fisdap.net>
 *
 * @SWG\Definition(
 *     definition="Airway_Management",
 *     description="This takes care of assigning ownership of the task of taking care of the patient's airway.",
 *     required={ "airwayManagementSourceId", "practiceItemId" }
 * )
 */
final class SetAirwayManagement extends Job implements RequestHydrated
{
    /**
     * @var string|null
     * @SWG\Property(type="string", example="ABC123")
     */
    public $uuid = null;

    /**
     * @var integer
     * @SWG\Property(type="integer")
     */
    public $id;

    /**
     * @var integer
     * @See AirwayManagementSource
     * @SWG\Property(type="integer", example=2)
     */
    public $airwayManagementSourceId;

    /**
     * @var integer|null
     * @See PracticeItem
     * This is not the same as Subject
     * @SWG\Property(type="integer")
     */
    public $practiceItemId;

    /**
     * @var boolean
     * @SWG\Property(type="boolean", example=true)
     */
    public $success;

    /**
     * @var boolean
     * @SWG\Property(type="boolean", example=true)
     */
    public $performed;

    /**
     * @var Patient
     * @See Patient
     */
    protected $patient;

    /**
     * @var ShiftLegacy
     * @See ShiftLegacy
     */
    protected $shift;

    /**
     * @var Airway
     * @See Airway
     */
    protected $airway;

    /**
     * @param EntityManagerInterface $em
     * @param EventDispatcher $eventDispatcher
     * @param AirwayManagementRepository $airwayManagementRepository
     * @return AirwayManagement|null
     */
    public function handle(
        EntityManagerInterface $em,
        EventDispatcher $eventDispatcher,
        AirwayManagementRepository $airwayManagementRepository
    ) {
        $this->em = $em;

        /**
         * This check here allows us to make this into a PUT transaction,
         * thus we only need one Job class to handle Create/Update
         */
        if ($this->patient) {
            $airwayManagement = $airwayManagementRepository->findOneBy(['patient' => $this->patient->getId()]);
        } elseif ($this->airway) {
            $airwayManagement = $airwayManagementRepository->findOneBy(['airway' => $this->airway->id]);
        }
        $airwayManagement = $airwayManagement ? $airwayManagement : new AirwayManagement;

        $airwayManagement->setAirwayManagementSource($this->validResourceEntityManager(AirwayManagementSource::class, $this->airwayManagementSourceId));

        if ($this->practiceItemId) {
            $airwayManagement->setPracticeItem($this->validResourceEntityManager(
                PracticeItem::class,
                $this->practiceItemId
            ));
        }

        $airwayManagement->success = $this->success;
        $airwayManagement->performed_by = $this->performed;
        $airwayManagement->shift = ($this->shift ? $this->shift : $this->patient->getShift());
        $airwayManagement->subject = ($this->patient ? $this->patient->getSubject() : null);
        $airwayManagement->patient = $this->patient;

        $eventDispatcher->fire($airwayManagement->id);

        return $airwayManagement;
    }

    public function rules()
    {
        return [
            'id'             => 'integer',
            'airwayManagementSourceId' => 'required|integer',
            'practiceItemId' => 'integer',
            'performed'      => 'boolean',
            'size'           => 'integer',
            'success'        => 'boolean',
            'attempts'       => 'integer',
        ];
    }

    public function setPatient(Patient $patient)
    {
        $this->patient = $patient;
    }

    public function setShift(ShiftLegacy $shift)
    {
        $this->shift = $shift;
    }

    public function setAirway(Airway $airway)
    {
        $this->airway = $airway;
    }
}
