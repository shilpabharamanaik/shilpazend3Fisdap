<?php namespace Fisdap\Api\Programs\Finder;

use Fisdap\Api\Queries\Exceptions\ResourceNotFound;
use Fisdap\Api\Queries\Specifications\ById;
use Fisdap\Api\ResourceFinder\ResourceFinder;
use Fisdap\Data\Program\Procedures\ProgramCardiacProcedureRepository;
use Fisdap\Data\Program\Procedures\ProgramIvProcedureRepository;
use Fisdap\Data\Program\Procedures\ProgramLabAssessmentRepository;
use Fisdap\Data\Program\Procedures\ProgramMedTypeRepository;
use Fisdap\Data\Program\Procedures\ProgramOtherProcedureRepository;
use Fisdap\Data\Program\ProgramLegacyRepository;
use Fisdap\Data\Program\Procedures\ProgramAirwayProcedureRepository;
use Fisdap\Entity\ProgramLegacy;
use Fisdap\Queries\Specifications\CommonSpec;
use Fisdap\Queries\Specifications\QueryModifiers\LeftFetchJoin;
use Happyr\DoctrineSpecification\Spec;
use Illuminate\Support\Collection;

/**
 * Service for retrieving programs
 *
 * @package Fisdap\Api\Programs
 *
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
final class ProgramsFinder extends ResourceFinder implements FindsPrograms
{
    /**
     * @var ProgramLegacyRepository
     */
    protected $repository;

    /**
     * @var ProgramAirwayProcedureRepository
     */
    protected $airwayRepo;

    /**
     * @var ProgramCardiacProcedureRepository
     */
    protected $cardiacRepo;

    /**
     * @var ProgramIvProcedureRepository
     */
    protected $ivRepo;

    /**
     * @var ProgramLabAssessmentRepository
     */
    protected $labRepo;

    /**
     * @var ProgramMedTypeRepository
     */
    protected $medRepo;

    /**
     * @var ProgramOtherProcedureRepository
     */
    protected $otherRepo;


    /**
     * @param ProgramLegacyRepository $repository
     * @param ProgramAirwayProcedureRepository $airwayRepo
     * @param ProgramCardiacProcedureRepository $cardiacRepo
     * @param ProgramIvProcedureRepository $ivRepo
     * @param ProgramLabAssessmentRepository $labRepo
     * @param ProgramMedTypeRepository $medRepo
     * @param ProgramOtherProcedureRepository $otherRepo
     */
    public function __construct(
        ProgramLegacyRepository $repository,
        ProgramAirwayProcedureRepository $airwayRepo,
        ProgramCardiacProcedureRepository $cardiacRepo,
        ProgramIvProcedureRepository $ivRepo,
        ProgramLabAssessmentRepository $labRepo,
        ProgramMedTypeRepository $medRepo,
        ProgramOtherProcedureRepository $otherRepo
    ) {
        $this->repository = $repository;
        $this->airwayRepo = $airwayRepo;
        $this->cardiacRepo = $cardiacRepo;
        $this->ivRepo = $ivRepo;
        $this->labRepo = $labRepo;
        $this->medRepo = $medRepo;
        $this->otherRepo = $otherRepo;
    }


    /**
     * @inheritdoc
     */
    public function getById($id, array $associations = null, array $associationIds = null)
    {
        $collection = Collection::make($associations);

        // Remove the includes for procedures. We're manually adding them later.
        $filtered = $collection->reject(function ($item) {
            return in_array(
                $item,
                [
                    'airway_procedures',
                    'cardiac_procedures',
                    'iv_procedures',
                    'lab_assessments',
                    'med_types',
                    'other_procedures'
                ]
            );
        });

        $spec = CommonSpec::makeSpecWithAssociations($filtered->all(), $associationIds);
        $spec->andX(new LeftFetchJoin('program_settings', 'program_settings'));
        $spec->andX(new ById($id));

        $programs = $this->repository->match($spec);

        if (empty($programs)) {
            throw new ResourceNotFound("No Program found with ID '$id'");
        }

        // Adding the manual includes, if any...
        /** @var ProgramLegacy $program */
        $program = $programs[0];
        if (!is_null($associations)) {
            if (in_array('airway_procedures', $associations)) {
                $airway_procedures = $this->airwayRepo->findBy(['program' => $id]);
                $program->setAirwayProcedures($airway_procedures);
            }

            if (in_array('cardiac_procedures', $associations)) {
                $cardiac_procedures = $this->cardiacRepo->findBy(['program' => $id]);
                $program->setCardiacProcedures($cardiac_procedures);
            }

            if (in_array('iv_procedures', $associations)) {
                $iv_procedures = $this->ivRepo->findBy(['program' => $id]);
                $program->setIvProcedures($iv_procedures);
            }

            if (in_array('lab_assessments', $associations)) {
                $lab_assessments = $this->labRepo->findBy(['program' => $id]);
                $program->setLabAssessments($lab_assessments);
            }

            if (in_array('med_types', $associations)) {
                $med_types = $this->medRepo->findBy(['program' => $id]);
                $program->setMedTypes($med_types);
            }

            if (in_array('other_procedures', $associations)) {
                $other_procedures = $this->otherRepo->findBy(['program' => $id]);
                $program->setOtherProcedures($other_procedures);
            }
        }

        return array_shift($programs);
    }
}
