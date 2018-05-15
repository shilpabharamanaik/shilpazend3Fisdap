<?php namespace Fisdap\Api\Reports\Finder;

use Fisdap\Api\Queries\Exceptions\ResourceNotFound;
use Fisdap\Api\Queries\Specifications\ById;
use Fisdap\Api\ResourceFinder\ResourceFinder;
use Fisdap\Data\Goal\GoalRepository;
use Fisdap\Data\GoalSet\GoalSetRepository;
use Fisdap\Data\Program\Procedures\ProgramCardiacProcedureRepository;
use Fisdap\Data\Program\Procedures\ProgramIvProcedureRepository;
use Fisdap\Data\Program\Procedures\ProgramLabAssessmentRepository;
use Fisdap\Data\Program\Procedures\ProgramMedTypeRepository;
use Fisdap\Data\Program\Procedures\ProgramOtherProcedureRepository;
use Fisdap\Data\Program\ProgramLegacyRepository;
use Fisdap\Data\Program\Procedures\ProgramAirwayProcedureRepository;
use Fisdap\Data\Report\ReportRepository;
use Fisdap\Data\Student\StudentLegacyRepository;
use Fisdap\Data\User\UserRepository;
use Fisdap\Entity\ProgramLegacy;
use Fisdap\Entity\Proxy\__CG__\AscendLearning\Lti\Entities\User;
use Fisdap\Queries\Specifications\CommonSpec;
use Fisdap\Queries\Specifications\QueryModifiers\LeftFetchJoin;
use Happyr\DoctrineSpecification\Spec;
use Illuminate\Support\Collection;

/**
 * Service for retrieving reports
 *
 * @package Fisdap\Api\Reports
 *
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
final class ReportsFinder extends ResourceFinder implements FindsReports
{
    /**
     * @var GoalRepository
     */
    protected $goalRepository;

    /**
     * @var ReportRepository
     */
    protected $reportRepository;

    /**
     * @var StudentLegacyRepository
     */
    protected $studentLegacyRepository;


    /**
     * @param GoalRepository $goalRepository
     * @param ReportRepository $reportRepository
     * @param StudentLegacyRepository $studentLegacyRepository
     */
    public function __construct(
        GoalRepository $goalRepository,
        ReportRepository $reportRepository,
        StudentLegacyRepository $studentLegacyRepository
    ) {
        $this->goalRepository = $goalRepository;
        $this->reportRepository = $reportRepository;
        $this->studentLegacyRepository = $studentLegacyRepository;
    }

    /**
     * @inheritdoc
     */
    public function get3c2ReportData($goalSetId, $startDate, $endDate, $subjectTypeIds, $siteIds, $studentIds, $audited)
    {
        $procedures = array(
            "MALE",     // Male Patients
            "FEMALE",   // Female Patients
            2,          // MED ADMIN
            54,         // ETT
            6,          // BVM/VENTILATIONS
            3,          // IV / IO
            14,         // NEWBORN
            13,         // INFANT
            12,         // TODDLER
            11,         // PRE-SCHOOL
            10,         // SCHOOL AGE
            9,          // ADOLESCENT
            15,         // TOTAL PEDI.
            8,          // ADULT
            7,          // GERIATRIC
            18,         // OB
            17,         // TRAUMA
            26,         // CARDIAC
            16,         // PSYCH
            137,        // A. DYSPNEA
            138,        // P. DYSPNEA
            35,         // SYNCOPE
            19,         // ABDOMINAL
            31,         // AMS
            76,         // TEAM LEADER
            125,        // FIELD HRS.
            105,        // CLINICAL HRS.
        );

        $shiftOptions = array();
        $shiftOptions['startDate'] = $startDate;
        $shiftOptions['endDate'] = $endDate;
        $shiftOptions['subjectTypes'] = ($subjectTypeIds ? explode(",", $subjectTypeIds) : null);
        $shiftOptions['shiftSites'] = ($siteIds ? explode(",", $siteIds) : null);
        $shiftOptions['audited'] = ($audited===true ? 1 : 0);

        $goals = $this->goalRepository->getGoalsForGoalSet($goalSetId);

        $reportData = array();
        $ids = explode(",", $studentIds);
        foreach ($ids as $studentId) {
            $studentId = intval($studentId);

            // Need to do a check here to see if the student exists.
            $student = $this->studentLegacyRepository->getOneById($studentId);
            if ($student) {
                $goalData = array();
                foreach ($procedures as $procedure) {
                    $goal = $goals->getGoalById($procedure);
                    if ($goal || is_string($procedure)) {
                        $reportData["requirements"][] = array(
                            "def_id" => $procedure,
                            "name" => ($goal ? $goal->name : null),
                            "required" => ($goal ? $goal->number_required : null)
                        );

                        if (!is_string($procedure)) {
                            $goalData[] = array(
                                "id" => $procedure,
                                "value" => intval($this->reportRepository->get3c2Data($goal, $studentId, $shiftOptions))
                            );
                        } else {
                            // These are not real goals. We have to handle them on their own.
                            switch ($procedure) {
                                case "MALE":
                                    $goalData[] = array(
                                        "id" => $procedure,
                                        "value" => intval($this->studentLegacyRepository->getStudentPatientGenderData($studentId, $shiftOptions, true))
                                    );
                                    break;
                                case "FEMALE":
                                    $goalData[] = array(
                                        "id" => $procedure,
                                        "value" => intval($this->studentLegacyRepository->getStudentPatientGenderData($studentId, $shiftOptions, false))
                                    );
                                    break;
                            }
                        }
                    }
                }

                $reportData["students"][] = array(
                    "id" => $studentId,
                    "goals" => $goalData
                );
            }
        }

        return array("data" => $reportData);
    }
}
