<?php namespace Fisdap\Api\Shifts\Patients\Finder;

use Fisdap\Api\Queries\Exceptions\ResourceNotFound;
use Fisdap\Api\Queries\Specifications\ById;
use Fisdap\Api\Queries\Specifications\ByShift;
use Fisdap\Api\Shifts\Patients\Queries\PatientQueryParameters;
use Fisdap\Data\Patient\PatientRepository;
use Fisdap\Data\Skill\AirwayRepository;
use Fisdap\Data\Skill\CardiacInterventionRepository;
use Fisdap\Data\Skill\IvRepository;
use Fisdap\Data\Skill\MedRepository;
use Fisdap\Data\Skill\OtherInterventionRepository;
use Fisdap\Data\Skill\VitalRepository;
use Fisdap\Entity\Patient;
use Fisdap\Queries\Specifications\CommonSpec;
use Happyr\DoctrineSpecification\BaseSpecification;
use Happyr\DoctrineSpecification\Spec;

/**
 * Service for retrieving one patient
 *
 * @package Fisdap\Api\Shifts\Patients
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
final class PatientsFinder implements FindsPatients
{
    /**
     * @var PatientRepository
     */
    protected $repository;

    /**
     * @var AirwayRepository
     */
    protected $airwayRepo;

    /**
     * @var CardiacInterventionRepository
     */
    protected $cardiacRepo;

    /**
     * @var IvRepository
     */
    protected $ivRepo;

    /**
     * @var MedRepository
     */
    protected $medRepo;

    /**
     * @var OtherInterventionRepository
     */
    protected $otherRepo;

    /**
     * @var VitalRepository
     */
    protected $vitalRepo;

    /**
     * @param PatientRepository $repository
     * @param AirwayRepository $airwayRepo
     * @param CardiacInterventionRepository $cardiacRepo
     * @param IvRepository $ivRepo
     * @param MedRepository $medRepo
     * @param OtherInterventionRepository $otherRepo
     * @param VitalRepository $vitalRepo
     */
    public function __construct(
        PatientRepository $repository,
        AirwayRepository $airwayRepo,
        CardiacInterventionRepository $cardiacRepo,
        IvRepository $ivRepo,
        MedRepository $medRepo,
        OtherInterventionRepository $otherRepo,
        VitalRepository $vitalRepo
    ) {
        $this->repository = $repository;
        $this->airwayRepo = $airwayRepo;
        $this->cardiacRepo = $cardiacRepo;
        $this->ivRepo = $ivRepo;
        $this->medRepo = $medRepo;
        $this->otherRepo = $otherRepo;
        $this->vitalRepo = $vitalRepo;
    }


    /**
     * @param PatientQueryParameters $queryParams
     * @return mixed
     */
    public function findShiftPatients($queryParams)
    {
        $spec = Spec::andX(
            new ByShift($queryParams->getShiftId()),
            ($queryParams->getDateFrom() ? Spec::gt('updated', $queryParams->getDateFrom()) : null)
        );

        return $this->repository->match($spec, null);
    }

    /**
     * @inheritdoc
     */
    public function getById($id, array $associations = null, array $associationIds = null, $asArray = false)
    {
        $spec = CommonSpec::makeSpecWithAssociations($associations, $associationIds);
        $spec->andX(new ById($id));

        $patients = $this->repository->match($spec, $asArray ? Spec::asArray() : null);

        if (empty($patients)) {
            throw new ResourceNotFound("No patient found with ID '$id'");
        }

        /** @var Patient $patient */
        $patient = $patients[0];

        $airway_procedures = $this->airwayRepo->findBy(['patient' => $id]);
        $patient->setAirways($airway_procedures);

        $cardiac_procedures = $this->cardiacRepo->findBy(['patient' => $id]);
        $patient->setCardiacInterventions($cardiac_procedures);

        $iv_procedures = $this->ivRepo->findBy(['patient' => $id]);
        $patient->setIvs($iv_procedures);

        $med_types = $this->medRepo->findBy(['patient' => $id]);
        $patient->setMeds($med_types);

        $other_procedures = $this->otherRepo->findBy(['patient' => $id]);
        $patient->setOtherInterventions($other_procedures);

        $vitals = $this->vitalRepo->findBy(['patient' => $id]);
        $patient->setVitals($vitals);

        return array_shift($patients);
    }
}
