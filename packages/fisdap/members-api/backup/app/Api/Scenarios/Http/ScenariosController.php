<?php namespace Fisdap\Api\Scenarios\Http;

use Aws\S3\Exception\S3Exception;
use Fisdap\Api\Http\Controllers\Controller;
use Fisdap\Api\Queries\Exceptions\ResourceNotFound;
use Fisdap\Api\Scenarios\Finder\ScenariosFinder;
use Fisdap\Api\Scenarios\Transformation\ScenarioTransformer;
use Fisdap\Data\Scenario\ScenarioRepository;
use Fisdap\Data\Skill\AirwayRepository;
use Fisdap\Data\Skill\CardiacInterventionRepository;
use Fisdap\Data\Skill\IvRepository;
use Fisdap\Data\Skill\MedRepository;
use Fisdap\Data\Skill\OtherInterventionRepository;
use Fisdap\Entity\Scenario;
use Fisdap\Entity\Skill;
use Fisdap\Fractal\CommonInputParameters;
use Fisdap\Fractal\ResponseHelpers;
use Doctrine\ORM\EntityManagerInterface;
use Fisdap\JBL\Authentication\Exceptions\ServerException;
use Illuminate\Support\Facades\App;
use League\Fractal\Manager;
use Swagger\Annotations as SWG;
use ZipArchive;

/**
 * Handles HTTP transport and data transformation for student-related routes
 *
 * @package Fisdap\Api\Shifts
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
final class ScenariosController extends Controller
{
    use ResponseHelpers, CommonInputParameters;

    private $finder;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    // [Systolic, Diastolic, Mean]
    private $iSimBPLookup = array(
        ["index" => 0, "systolic" => 0, "diastolic" => 0, "mean" => 0],
        ["index" => 1, "systolic" => 1, "diastolic" => 0, "mean" => 0.5],
        ["index" => 2, "systolic" => 4, "diastolic" => 2, "mean" => 3],
        ["index" => 3, "systolic" => 7, "diastolic" => 5, "mean" => 6],
        ["index" => 4, "systolic" => 10, "diastolic" => 5, "mean" => 7.5],
        ["index" => 5, "systolic" => 13, "diastolic" => 8, "mean" => 10.5],
        ["index" => 6, "systolic" => 16, "diastolic" => 11, "mean" => 13.5],
        ["index" => 7, "systolic" => 19, "diastolic" => 14, "mean" => 16.5],
        ["index" => 8, "systolic" => 22, "diastolic" => 17, "mean" => 19.5],
        ["index" => 9, "systolic" => 25, "diastolic" => 20, "mean" => 22.5],
        ["index" => 10, "systolic" => 28, "diastolic" => 23, "mean" => 25.5],
        ["index" => 11, "systolic" => 31, "diastolic" => 25, "mean" => 28],
        ["index" => 12, "systolic" => 34, "diastolic" => 27, "mean" => 30.5],
        ["index" => 13, "systolic" => 37, "diastolic" => 29, "mean" => 33],
        ["index" => 14, "systolic" => 40, "diastolic" => 31, "mean" => 35.5],
        ["index" => 15, "systolic" => 43, "diastolic" => 33, "mean" => 38],
        ["index" => 16, "systolic" => 46, "diastolic" => 34, "mean" => 40],
        ["index" => 17, "systolic" => 49, "diastolic" => 36, "mean" => 42.5],
        ["index" => 18, "systolic" => 52, "diastolic" => 37, "mean" => 44.5],
        ["index" => 19, "systolic" => 55, "diastolic" => 39, "mean" => 47],
        ["index" => 20, "systolic" => 58, "diastolic" => 40, "mean" => 49],
        ["index" => 21, "systolic" => 61, "diastolic" => 42, "mean" => 51.5],
        ["index" => 22, "systolic" => 64, "diastolic" => 43, "mean" => 53.5],
        ["index" => 23, "systolic" => 67, "diastolic" => 45, "mean" => 56],
        ["index" => 24, "systolic" => 70, "diastolic" => 46, "mean" => 58],
        ["index" => 25, "systolic" => 73, "diastolic" => 48, "mean" => 60.5],
        ["index" => 26, "systolic" => 76, "diastolic" => 49, "mean" => 62.5],
        ["index" => 27, "systolic" => 79, "diastolic" => 51, "mean" => 65],
        ["index" => 28, "systolic" => 82, "diastolic" => 53, "mean" => 67.5],
        ["index" => 29, "systolic" => 85, "diastolic" => 55, "mean" => 70],
        ["index" => 30, "systolic" => 88, "diastolic" => 57, "mean" => 72.5],
        ["index" => 31, "systolic" => 91, "diastolic" => 59, "mean" => 75],
        ["index" => 32, "systolic" => 94, "diastolic" => 61, "mean" => 77.5],
        ["index" => 33, "systolic" => 97, "diastolic" => 63, "mean" => 80],
        ["index" => 34, "systolic" => 100, "diastolic" => 65, "mean" => 82.5],
        ["index" => 35, "systolic" => 103, "diastolic" => 67, "mean" => 85],
        ["index" => 36, "systolic" => 106, "diastolic" => 69, "mean" => 87.5],
        ["index" => 37, "systolic" => 109, "diastolic" => 71, "mean" => 90],
        ["index" => 38, "systolic" => 112, "diastolic" => 73, "mean" => 92.5],
        ["index" => 39, "systolic" => 115, "diastolic" => 75, "mean" => 95],
        ["index" => 40, "systolic" => 118, "diastolic" => 77, "mean" => 97.5],
        ["index" => 41, "systolic" => 121, "diastolic" => 79, "mean" => 100],
        ["index" => 42, "systolic" => 124, "diastolic" => 80, "mean" => 102],
        ["index" => 43, "systolic" => 127, "diastolic" => 82, "mean" => 104.5],
        ["index" => 44, "systolic" => 130, "diastolic" => 83, "mean" => 106.5],
        ["index" => 45, "systolic" => 133, "diastolic" => 84, "mean" => 108.5],
        ["index" => 46, "systolic" => 136, "diastolic" => 86, "mean" => 111],
        ["index" => 47, "systolic" => 139, "diastolic" => 87, "mean" => 113],
        ["index" => 48, "systolic" => 142, "diastolic" => 88, "mean" => 115],
        ["index" => 49, "systolic" => 145, "diastolic" => 89, "mean" => 117],
        ["index" => 50, "systolic" => 148, "diastolic" => 91, "mean" => 119.5],
        ["index" => 51, "systolic" => 151, "diastolic" => 92, "mean" => 121.5],
        ["index" => 52, "systolic" => 154, "diastolic" => 93, "mean" => 123.5],
        ["index" => 53, "systolic" => 157, "diastolic" => 95, "mean" => 126],
        ["index" => 54, "systolic" => 160, "diastolic" => 96, "mean" => 128],
        ["index" => 55, "systolic" => 163, "diastolic" => 97, "mean" => 130],
        ["index" => 56, "systolic" => 166, "diastolic" => 99, "mean" => 132.5],
        ["index" => 57, "systolic" => 169, "diastolic" => 100, "mean" => 134.5],
        ["index" => 58, "systolic" => 172, "diastolic" => 101, "mean" => 136.5],
        ["index" => 59, "systolic" => 175, "diastolic" => 102, "mean" => 138.5],
        ["index" => 60, "systolic" => 178, "diastolic" => 103, "mean" => 140.5],
        ["index" => 61, "systolic" => 181, "diastolic" => 104, "mean" => 142.5],
        ["index" => 62, "systolic" => 184, "diastolic" => 105, "mean" => 144.5],
        ["index" => 63, "systolic" => 187, "diastolic" => 106, "mean" => 146.5],
        ["index" => 64, "systolic" => 190, "diastolic" => 108, "mean" => 149],
        ["index" => 65, "systolic" => 193, "diastolic" => 109, "mean" => 151],
        ["index" => 66, "systolic" => 196, "diastolic" => 110, "mean" => 153],
        ["index" => 67, "systolic" => 199, "diastolic" => 111, "mean" => 155],
        ["index" => 68, "systolic" => 202, "diastolic" => 112, "mean" => 157],
        ["index" => 69, "systolic" => 205, "diastolic" => 113, "mean" => 159],
        ["index" => 70, "systolic" => 208, "diastolic" => 114, "mean" => 161],
        ["index" => 71, "systolic" => 211, "diastolic" => 115, "mean" => 163],
        ["index" => 72, "systolic" => 214, "diastolic" => 116, "mean" => 165],
        ["index" => 73, "systolic" => 217, "diastolic" => 117, "mean" => 167],
        ["index" => 74, "systolic" => 220, "diastolic" => 119, "mean" => 169.5],
        ["index" => 75, "systolic" => 223, "diastolic" => 120, "mean" => 171.5],
        ["index" => 76, "systolic" => 226, "diastolic" => 121, "mean" => 173.5],
        ["index" => 77, "systolic" => 229, "diastolic" => 122, "mean" => 175.5],
        ["index" => 78, "systolic" => 232, "diastolic" => 123, "mean" => 177.5],
        ["index" => 79, "systolic" => 235, "diastolic" => 124, "mean" => 179.5],
        ["index" => 80, "systolic" => 238, "diastolic" => 125, "mean" => 181.5],
        ["index" => 81, "systolic" => 241, "diastolic" => 126, "mean" => 183.5],
        ["index" => 82, "systolic" => 244, "diastolic" => 127, "mean" => 185.5],
        ["index" => 83, "systolic" => 247, "diastolic" => 128, "mean" => 187.5],
        ["index" => 84, "systolic" => 250, "diastolic" => 130, "mean" => 190],
        ["index" => 85, "systolic" => 253, "diastolic" => 131, "mean" => 192],
        ["index" => 86, "systolic" => 256, "diastolic" => 132, "mean" => 194],
        ["index" => 87, "systolic" => 259, "diastolic" => 133, "mean" => 196],
        ["index" => 88, "systolic" => 262, "diastolic" => 134, "mean" => 198],
        ["index" => 89, "systolic" => 265, "diastolic" => 135, "mean" => 200],
        ["index" => 90, "systolic" => 268, "diastolic" => 136, "mean" => 202],
        ["index" => 91, "systolic" => 271, "diastolic" => 137, "mean" => 204],
        ["index" => 92, "systolic" => 274, "diastolic" => 138, "mean" => 206],
        ["index" => 93, "systolic" => 277, "diastolic" => 139, "mean" => 208],
        ["index" => 94, "systolic" => 280, "diastolic" => 141, "mean" => 210.5],
        ["index" => 95, "systolic" => 283, "diastolic" => 142, "mean" => 212.5],
        ["index" => 96, "systolic" => 286, "diastolic" => 143, "mean" => 214.5],
        ["index" => 97, "systolic" => 289, "diastolic" => 144, "mean" => 216.5],
        ["index" => 98, "systolic" => 292, "diastolic" => 145, "mean" => 218.5],
        ["index" => 99, "systolic" => 295, "diastolic" => 146, "mean" => 220.5],
        ["index" => 100, "systolic" => 298, "diastolic" => 147, "mean" => 222.5],
        ["index" => 101, "systolic" => 301, "diastolic" => 148, "mean" => 224.5],
    );

    /**
     * @param ScenariosFinder $finder
     * @param Manager $fractal
     * @param EntityManagerInterface $em
     * @param ScenarioTransformer $transformer
     */
    public function __construct(
        ScenariosFinder $finder,
        Manager $fractal,
        EntityManagerInterface $em,
        ScenarioTransformer $transformer
    ) {
        $this->finder = $finder;
        $this->fractal = $fractal;
        $this->em = $em;
        $this->transformer = $transformer;
    }

    /**
     * Retrieve the specified resource from storage
     *
     * @param $scenarioId
     * @param ScenarioRepository $scenarioRepository
     *
     * @param AirwayRepository $airwayRepository
     * @param CardiacInterventionRepository $cardiacInterventionRepository
     * @param IvRepository $ivRepository
     * @param MedRepository $medRepository
     * @param OtherInterventionRepository $otherInterventionRepository
     * @return \Illuminate\Http\JsonResponse
     * @throws ServerException
     * @SWG\Get(
     *     tags={"Scenarios"},
     *     path="/scenarios/{scenarioId}/alsi",
     *     summary="Get a scenario by ID, formatted for export to ALSi",
     *     description="Get a scenario by ID, formatted for export to ALSi",
     *     @SWG\Parameter(name="scenarioId", in="path", required=true, type="integer"),
     *     @SWG\Response(response="200", description="Success"),
     * )
     */
    public function alsi(
        $scenarioId,
        ScenarioRepository $scenarioRepository,
        AirwayRepository $airwayRepository,
        CardiacInterventionRepository $cardiacInterventionRepository,
        IvRepository $ivRepository,
        MedRepository $medRepository,
        OtherInterventionRepository $otherInterventionRepository
    ) {
        /** @var Scenario $scenario */
        $scenario = $scenarioRepository->getOneById($scenarioId);
        if (empty($scenario) || is_null($scenario)) {
            throw new ResourceNotFound("No scenario found with ID '$scenarioId'");
        }


        if (empty($scenario->getPatient()) || is_null($scenario->getPatient())) {
            throw new ResourceNotFound("There is no Patient record associated with this Scenario. This is likely caused by old, bad data.");
        }

        $alsi = array();
        $alsi['version'] = 4;
        $alsi['name'] = (strlen($scenario->getTitle()) > 0 ? $scenario->getTitle() : 'Fisdap Scenario');
        if (!is_null($scenario->getPatient()->getGender())) {
            $alsi['patientGender'] = ($scenario->getPatient()->getGender()->getId() == 2 ? 0 : ($scenario->getPatient()->getGender()->getId() == 1 ? 1 : 2));
        } else {
            $alsi['patientGender'] = 2;
        }

        $alsi['patientAge'] = $scenario->getPatient()->getAge();

        $artifacts = array();

        $scenarioName = "Fisdap";
        if (sizeof($scenario->getPatient()->getComplaintNames()) > 0) {
            $scenarioName = $scenario->getPatient()->getComplaintNames();
        }

        $logo = array();
        $logo['media'] = array("fisdap-logo.jpg");
        $logo['sendToStudent'] = false;
        $logo['name'] = $scenarioName." scenario";
        $logo['description'] = "Powered by Fisdap";
        $logo['type'] = "v";
        $artifacts[] = $logo;

        // NOTES
        $notes = array();
        $notes['value'] = "Notes:"."\n".
            $scenario->getNotes();
        $notes['type'] = "teachingpoint";
        $notes['format'] = 0;
        $artifacts[] = $notes;

        // PATIENT INFO
        $dispatch = array();
        $dispatch['value'] = "Information Provided By Dispatch:"."\n".
            $scenario->getPatientInformation();
        $dispatch['type'] = "teachingpoint";
        $dispatch['format'] = 0;
        $artifacts[] = $dispatch;

        // AGE/GENDER/WEIGHT
        $age = array();
        $age['value'] = "".
            "Age: ".$scenario->getPatient()->getAge()."\n".
            "Gender: ".($scenario->getPatient()->getGender() ? $scenario->getPatient()->getGender()->getName() : "")."\n".
            "Ethnicity: ".($scenario->getPatient()->getEthnicity() ? $scenario->getPatient()->getEthnicity()->getName() : "")."\n".
            "Weight: ".$scenario->getPatientWeight()." ".($scenario->getWeightUnit() ? $scenario->getWeightUnit()->getName() : "");
        $age['type'] = "teachingpoint";
        $age['format'] = 0;
        $artifacts[] = $age;

        // SAMPLE
        $sample = array();
        $sample['value'] = "SAMPLE\n".
            "Signs & Symptoms: ".$scenario->getSampleSigns()."\n".
            "Allergies: ".$scenario->getSampleAllergies()."\n".
            "Medication: ".$scenario->getSampleMedications()."\n".
            "Prior History: ".$scenario->getSamplePriorHistory()."\n".
            "Last Oral Intake: ".$scenario->getSampleLastOralIntake()."\n".
            "Events: ".$scenario->getSampleEvents();
        $sample['type'] = "teachingpoint";
        $sample['format'] = 0;
        $artifacts[] = $sample;

        // OPQRST
        $opqrst = array();
        $opqrst['value'] = "OPQRST\n".
            "Onset: ".$scenario->getOpqrstOnset()."\n".
            "Provocation: ".$scenario->getOpqrstProvocation()."\n".
            "Quality: ".$scenario->getOpqrstQuality()."\n".
            "Radiation: ".$scenario->getOpqrstRadiation()."\n".
            "Severity: ".$scenario->getOpqrstSeverity()."\n".
            "Time: ".$scenario->getOpqrstTime();
        $opqrst['type'] = "teachingpoint";
        $opqrst['format'] = 0;
        $artifacts[] = $opqrst;

        $vitals = $scenario->getPatient()->getVitals();
        $vCount = 0;

        foreach ($vitals as $vital) {
            $vCount++;

            $vInfo = array();
            $vInfo['jumpTime'] = 0;
            $vInfo['defibEnabled'] = 0;
            $vInfo['trendValue'] = 0;
            $vInfo['name'] = "Vitals #".$vCount;
            $vInfo['type'] = "quickpick";

            if ($vital->systolic_bp > 0 && $vital->diastolic_bp > 0) {
                $mean = ($vital->systolic_bp + $vital->diastolic_bp) / 2;
            } else {
                $mean = 0;
            }

            $vInfo['parameters'] = [
                [
                    "value" => ($vital->pulse_rate ? $vital->pulse_rate : 0),
                    "valueType" => 0,
                    "parameter" => 0 // Heart Rate | Pulse
                ],
                [
                    "value" => $this->getClosestBP($mean),
                    "valueType" => 0,
                    "parameter" => 2 // Blood Pressure Mapping
                ],
                [
                    "value" => ($vital->spo2 ? $vital->spo2 : 0),
                    "valueType" => 0,
                    "parameter" => 3 // spo2
                ],
                [
                    "value" => ($vital->end_tidal_co2 ? $vital->end_tidal_co2 : 0),
                    "valueType" => 0,
                    "parameter" => 4 // etco2
                ],
                [
                    "value" => ($vital->resp_rate ? $vital->resp_rate : 0),
                    "valueType" => 0,
                    "parameter" => 5 // rr
                ],
                [
                    "value" => ($vital->temperature ? $vital->temperature : 0),
                    "valueType" => 0,
                    "parameter" => 6 // temp
                ]
            ];

            $artifacts[] = $vInfo;
        }

        // Physical Exam
        $physical = array();
        $physical['value'] = "".
            "HEENT: ".$scenario->getPhysicalHeent()."\n".
            "Neck: ".$scenario->getPhysicalNeck()."\n".
            "Chest: ".$scenario->getPhysicalChest()."\n".
            "Abdomen: ".$scenario->getPhysicalAbdomen()."\n".
            "Pelvis: ".$scenario->getPhysicalPelvis()."\n".
            "Lower Extremities: ".$scenario->getPhysicalLowerExtremities()."\n".
            "Upper Extremities: ".$scenario->getPhysicalUpperExtremities()."\n".
            "Posterior: ".$scenario->getPhysicalPosterior();
        $physical['type'] = "teachingpoint";
        $physical['format'] = 0;
        $artifacts[] = $physical;

        // Primary Impression
        $primaryImpression = array();
        $primaryImpression['value'] = "Primary Impression: ".($scenario->getPatient()->primary_impression ? $scenario->getPatient()->primary_impression->name : "");
        $primaryImpression['type'] = "teachingpoint";
        $primaryImpression['format'] = 0;
        $artifacts[] = $primaryImpression;

        // Secondary Impression
        $secondaryImpression = array();
        $secondaryImpression['value'] = "Secondary Impression: ".($scenario->getPatient()->secondary_impression ? $scenario->getPatient()->secondary_impression->name : "");
        $secondaryImpression['type'] = "teachingpoint";
        $secondaryImpression['format'] = 0;
        $artifacts[] = $secondaryImpression;

        // Special Considerations
        $special = array();
        $special['value'] = "Special Considerations: ".$scenario->getAssessmentSpecialConsideration();
        $special['type'] = "teachingpoint";
        $special['format'] = 0;
        $artifacts[] = $special;

        $essential = array();
        $important = array();
        $not = array();

        foreach ($scenario->getSkills() as $skill) {
            $skill = $skill->toArray();
            switch($skill['skill_type']) {
                case "Airway":
                    $skill['skill'] = $airwayRepository->find($skill['skill_id']);
                    break;
                case "CardiacIntervention":
                    $skill['skill'] = $cardiacInterventionRepository->find($skill['skill_id']);
                    break;
                case "Iv":
                    $skill['skill'] = $ivRepository->find($skill['skill_id']);
                    break;
                case "Med":
                    $skill['skill'] = $medRepository->find($skill['skill_id']);
                    break;
                case "OtherIntervention":
                    $skill['skill'] = $otherInterventionRepository->find($skill['skill_id']);
                    break;
            }

            if (isset($skill['skill']) && $skill['skill'] !== null) {
                if ($skill['priority'] == 3) {
                    $essential[] = $skill;
                } else if ($skill['priority'] == 2) {
                    $important[] = $skill;
                } else if ($skill['priority'] == 1) {
                    $not[] = $skill;
                }
            }
        }

        foreach ($essential as $essentialSkill) {
            /** @var Skill $es */
            $es = $essentialSkill['skill'];
            if (!is_null($es) && $es instanceof Skill) {
                $newES['value'] = $es->getProcedureText(false);
                $newES['type'] = "teachingpoint";
                $newES['format'] = 0;
                $artifacts[] = $newES;
            }
        }

        foreach ($important as $importantSkill) {
            /** @var Skill $is */
            $is = $importantSkill['skill'];
            if (!is_null($is) && $is instanceof Skill) {
                $newIS['value'] = $is->getProcedureText(false);
                $newIS['type'] = "teachingpoint";
                $newIS['format'] = 0;
                $artifacts[] = $newIS;
            }
        }

        foreach ($not as $notSkill) {
            /** @var Skill $ns */
            $ns = $notSkill['skill'];
            if (!is_null($ns) && $ns instanceof Skill) {
                $newNS['value'] = $ns->getProcedureText(false);
                $newNS['type'] = "teachingpoint";
                $newNS['format'] = 0;
                $artifacts[] = $newNS;
            }
        }

        // Inapprop./Dangerous
        $danger = array();
        $danger['value'] = "Inappropriate/Dangerous Interventions: ".$scenario->getDangerousActions();
        $danger['type'] = "teachingpoint";
        $danger['format'] = 0;
        $artifacts[] = $danger;

        // Change in Condition
        $change = array();
        $change['value'] = "Change in Condition: ".$scenario->getCurveball();
        $change['type'] = "teachingpoint";
        $change['format'] = 0;
        $artifacts[] = $change;

        // Crit Fail
        $crit = array();
        $crit['value'] = "Critical Failures: ".$scenario->getCriticalFailures();
        $crit['type'] = "teachingpoint";
        $crit['format'] = 0;
        $artifacts[] = $crit;

        $alsi['artifacts'] = $artifacts;

        // We don't appear to have anything in our scenarios that would map to these default alsi values.
        // These are the default values that are set when creating a scenario via their app.
        $alsi['defaults'] = [
            'Sync' => 1,
            'TimerDefault' => 10,
            'RR' => 0,
            'Pacing' => 1,
            'Cust2' => 0,
            'ETCO2' => 0,
            'Temp' => 0,
            'ModeDefault' => 0,
            'PacingThreshold' => 50,
            'Cust3' => 0,
            'NIBP' => 1,
            'BP' => 0,
            'Cust1' => 0,
            'SpO2' => 10,
            'ECG' => 1,
            'ShockDefault' => 18
        ];

        $alsi['custom1'] = -1;
        $alsi['custom2'] = -1;
        $alsi['custom3'] = -1;

        $fName = preg_replace('/[^a-zA-Z0-9\s]/i', '', $scenario->getTitle());
        if (strlen($fName) <= 0) {
            $fName = 'Fisdap Scenario';
        }

        // File creation time!
        $zipFileName = $fName . ".asz";
        $alsiFileName = strtoupper(uniqid("")) . ".alsi";

        $dir = public_path()."/scenarios/";

        // Create the zip file
        /** @var ZipArchive $zip */
        $zip = new ZipArchive();
        $zip->open($dir.$zipFileName, ZipArchive::CREATE);
        $zip->addFromString($alsiFileName, json_encode($alsi));
        $zip->addFile(public_path() . "/scenarios/fisdap-logo.jpg", "fisdap-logo.jpg");
        $zip->close();

        try {
            // Create the S3 interface
            $s3 = App::make('aws')->createClient('s3');

            // Delete the S3 "folder" for this scenario (the scenario ID), ensuring that old files don't pile up.
            $s3->deleteMatchingObjects(env('MRAPI_SCENARIOS_S3_BUCKET'), $scenario->getId());

            // Upload the file to S3.
            $s3->putObject(array(
                'Bucket' => env('MRAPI_SCENARIOS_S3_BUCKET'),
                'Key' => "{$scenario->getId()}/{$zipFileName}",
                'Body' => fopen($dir . $zipFileName, 'rb'),
                'ACL' => 'public-read'
            ));

            $plainUrl = $s3->getObjectUrl(env('MRAPI_SCENARIOS_S3_BUCKET'), "{$scenario->getId()}/{$zipFileName}");

            // Delete the temp ziparchive file.
            unlink($dir.$zipFileName);
        } catch (S3Exception $e) {
            throw new ServerException("Error: " . $e->getMessage());
        }

        return $this->respondWithArray(
            array(
                "data" => [
                    "fileName" => $zipFileName,
                    "URL" => $plainUrl
                ]
            )
        );
    }

    private function getClosestBP($search) {
        $closest = null;
        $rtv = null;
        foreach ($this->iSimBPLookup as $item) {
            if ($closest === null || abs($search - $closest) > abs($item['mean'] - $search)) {
                $closest = $item['mean'];
                $rtv = $item['index'];
            }
        }
        return $rtv;
    }
}
