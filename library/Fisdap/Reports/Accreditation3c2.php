<?php
use Fisdap\Api\Client\Auth\UserAuthorization;
use Fisdap\Api\Client\HttpClient\HttpClientInterface;
use Fisdap\Api\Client\Reports\Gateway\ReportsGateway;
use GuzzleHttp\Client;

/**
 * Class Fisdap_Reports_AccreditationGoals
 * This is the CoAEMSP Appendix G/H Student Patient Contact Matrix Report class
 * Refer to Fisdap_Reports_Report for more documentation
 */
class Fisdap_Reports_Accreditation3c2 extends Fisdap_Reports_Report
{
    public $header = '';

    public $footer = '';

    public $formComponents = array(
        'goalSetTable' => array(
            'title' => 'Select a goal set',
            'options' => array(
                "excludeGoalSetTemplates" => array(2, 3),
                "requiredGoalDefs" => array(137, 138) // Only show goal sets that have the Adult Dysp and Ped Dysp goal.
            ),
        ),
        'shiftInformationForm' => array(
            'title' => 'Select shift information',
            'options' => array(
                'pickAuditStatus' => true,
                'selected' => array('sites' => array('0-Lab', '0-Clinical', '0-Field'),
                    ),
                'siteTypes' => array("Clinical", "Field", "Lab"),
            ),
        ),
        'multistudentPicklist' => array(
            'title' => 'Select one or more student(s)',
            'options' =>  array(
                'mode' => 'multiple',
                'loadJSCSS' => true,
                'loadStudents' => true,
                'showTotal' => true,
                'useSessionFilters' => true,
                'sessionNamespace' => "ReportStudentFilter",
            ),
        )
    );


    public $styles = array("/css/library/Fisdap/Reports/3c2.css");
    public $scripts = array("/js/library/Fisdap/Utils/jspdf.min.js");
    /**
     * This report is only available to instructors
     */
    public static function hasPermission($userContext)
    {
        return ($userContext->isInstructor());
    }

    /**
     * Run a query and any processing logic that produces the data contained in the report
     * Return a multidimensional array and it will be rendered as tables
     * OR return a string and it will be rendered as HTML
     *
     * @return array
     */
    public function runReport()
    {
        // Well, this should clearly be encapsulated in a class/service of its own,
        // buuuut since this is currently the only place we're hitting the API we'll do it here.
        $container = Zend_Registry::get('container');
        $idmsConfig = $container->make('config')->get('idms');
        $idmsClient = new Client;
        $idmsResponse = $idmsClient->post(
            $idmsConfig['base_url'] . '/token',
            [
                'auth' => [
                    $idmsConfig['client_id'],
                    $idmsConfig['client_secret']
                ],
                'form_params' => [
                    'grant_type' => 'client_credentials',
                ]
            ]
        );

        $idmsResponse = json_decode($idmsResponse->getBody()->getContents(), true);

        // bind a UserAuthorization class in the service container for use by the MRAPI Client
        $userContextId = $this->currentUser->context()->getId();
        $userAuthorization = new UserAuthorization($idmsResponse['access_token'], $userContextId);

        $container->instance('Fisdap\Api\Client\Auth\UserAuthorization', $userAuthorization);


        // set up the table! start with the header
        $title = "CoAEMSP Summary Tracking";


        $requirementsHeader = array(
            array(
                "id" => null,
                "data" => "Requirements",
                "class" => "no-search",
            ),
            array(
                "id" => "MALE",
                "data" => "",
                "class" => "right no-search",
            ),
            array(
                "id" => "FEMALE",
                "data" => "",
                "class" => "right no-search",
            ),
            array(
                "id" => 2,
                "data" => "",
                "class" => "right no-search",
            ),
            array(
                "id" => 54,
                "data" => "",
                "class" => "right no-search",
            ),
            array(
                "id" => 6,
                "data" => "",
                "class" => "right no-search",
            ),
            array(
                "id" => 3,
                "data" => "",
                "class" => "right no-search",
            ),
            array(
                "id" => 14,
                "data" => "",
                "class" => "right no-search",
            ),
            array(
                "id" => 13,
                "data" => "",
                "class" => "right no-search",
            ),
            array(
                "id" => 12,
                "data" => "",
                "class" => "right no-search",
            ),
            array(
                "id" => 11,
                "data" => "",
                "class" => "right no-search",
            ),
            array(
                "id" => 10,
                "data" => "",
                "class" => "right no-search",
            ),
            array(
                "id" => 9,
                "data" => "",
                "class" => "right no-search",
            ),
            array(
                "id" => 15,
                "data" => "",
                "class" => "right no-search",
            ),
            array(
                "id" => 8,
                "data" => "",
                "class" => "right no-search",
            ),
            array(
                "id" => 7,
                "data" => "",
                "class" => "right no-search",
            ),
            array(
                "id" => 18,
                "data" => "",
                "class" => "right no-search",
            ),
            array(
                "id" => 17,
                "data" => "",
                "class" => "right no-search",
            ),
            array(
                "id" => 26,
                "data" => "",
                "class" => "right no-search",
            ),
            array(
                "id" => 16,
                "data" => "",
                "class" => "right no-search",
            ),
            array(
                "id" => 137,
                "data" => "",
                "class" => "right no-search",
            ),
            array(
                "id" => 138,
                "data" => "",
                "class" => "right no-search",
            ),
            array(
                "id" => 35,
                "data" => "",
                "class" => "right no-search",
            ),
            array(
                "id" => 19,
                "data" => "",
                "class" => "right no-search",
            ),
            array(
                "id" => 31,
                "data" => "",
                "class" => "right no-search",
            ),
            array(
                "id" => 76,
                "data" => "",
                "class" => "right no-search",
            ),
            array(
                "id" => 125,
                "data" => "",
                "class" => "right no-search",
            ),
            array(
                "id" => 105,
                "data" => "",
                "class" => "right no-search",
            ),
            array(
                "id" => null,
                "data" => "",
                "class" => "no-search",
            ),
        );

        $header = array(
            array(
                "data" => "",
                "class" => "superheader",
                "colspan" => 1
            ),
            array(
                "data" => "Male Patients",
                "class" => "superheader",
                "colspan" => 1
            ),
            array(
                "data" => "Female Patients",
                "class" => "superheader",
                "colspan" => 1
            ),
            array(
                "data" => "MED ADMIN",
                "class" => "superheader",
                "colspan" => 1
            ),
            array(
                "data" => "ETT",
                "class" => "superheader",
                "colspan" => 1
            ),
            array(
                "data" => "BVM/VENTILATIONS",
                "class" => "superheader",
                "colspan" => 1
            ),
            array(
                "data" => "IV / IO",
                "class" => "superheader",
                "colspan" => 1
            ),
            array(
                "data" => "NEWBORN",
                "class" => "superheader",
                "colspan" => 1
            ),
            array(
                "data" => "INFANT",
                "class" => "superheader",
                "colspan" => 1
            ),
            array(
                "data" => "TODDLER",
                "class" => "superheader",
                "colspan" => 1
            ),
            array(
                "data" => "PRE-SCHOOL",
                "class" => "superheader",
                "colspan" => 1
            ),
            array(
                "data" => "SCHOOL AGE",
                "class" => "superheader",
                "colspan" => 1
            ),
            array(
                "data" => "ADOLESCENT",
                "class" => "superheader",
                "colspan" => 1
            ),
            array(
                "data" => "TOTAL PEDI",
                "class" => "superheader",
                "colspan" => 1
            ),
            array(
                "data" => "ADULT",
                "class" => "superheader",
                "colspan" => 1
            ),
            array(
                "data" => "GERIATRIC",
                "class" => "superheader",
                "colspan" => 1
            ),
            array(
                "data" => "OB",
                "class" => "superheader",
                "colspan" => 1
            ),
            array(
                "data" => "TRAUMA",
                "class" => "superheader",
                "colspan" => 1
            ),
            array(
                "data" => "CARDIAC",
                "class" => "superheader",
                "colspan" => 1
            ),
            array(
                "data" => "PSYCH",
                "class" => "superheader",
                "colspan" => 1
            ),
            array(
                "data" => "A. DYSPNEA",
                "class" => "superheader",
                "colspan" => 1
            ),
            array(
                "data" => "P. DYSPNEA",
                "class" => "superheader",
                "colspan" => 1
            ),
            array(
                "data" => "SYNCOPE",
                "class" => "superheader",
                "colspan" => 1
            ),
            array(
                "data" => "ABDOMINAL",
                "class" => "superheader",
                "colspan" => 1
            ),
            array(
                "data" => "AMS",
                "class" => "superheader",
                "colspan" => 1
            ),
            array(
                "data" => "TEAM LEADER",
                "class" => "superheader",
                "colspan" => 1
            ),
            array(
                "data" => "FIELD HRS.",
                "class" => "superheader",
                "colspan" => 1
            ),
            array(
                "data" => "CLIN. HRS.",
                "class" => "superheader",
                "colspan" => 1
            ),
            array(
                "data" => "",
                "class" => "superheader",
                "colspan" => 1
            ),
        );

        $students = $this->getMultiStudentData();
        $studentIds = array();
        foreach ($students as $key => $value) {
            $studentIds[] = $key;
        }

        // get the goalset
        $goalSetId = $this->config['selected-goalset'];

        /** @var ReportsGateway $reportsGateway */
        $reportsGateway = $container->make('Fisdap\Api\Client\Reports\Gateway\ReportsGateway');
        $reportData = $reportsGateway->get3c2ReportData(
            $this->user->context()->program->id,
            $goalSetId,
            $this->config['startDate'],
            $this->config['endDate'],
            $this->getTypeIds(),
            $this->getSiteIds(),
            $studentIds,
            ($this->config['auditStatus'] == 'audited') ? true : false
        );

        // Grab the requirements for each of the goals/headers.
        foreach ($reportData["requirements"] as $headerData) {
            foreach ($requirementsHeader as $key => $reqHeader) {
                if ($headerData["def_id"] == $reqHeader["id"]) {
                    $requirementsHeader[$key]["data"] = $headerData["required"];
                }
            }
        }

        $table_data = array('title' => $title,
            'nullMsg' => "No skills found.",
            'head' => array('1' => $header, '2' => $requirementsHeader),
            'body' => array(),
        );

        foreach ($reportData["students"] as $student) {
            // Fill the student name into the first col.
            $dataRow = array(
                $students[$student["id"]]["first_last_combined"]
            );

            // Skip first col, but iterate through the rest.
            for ($i = 1; $i < sizeof($requirementsHeader)-1; $i++) {
                $found = false;
                $reqHeader = $requirementsHeader[$i];
                if (isset($student["goals"])) {
                    foreach ($student["goals"] as $goal) {
                        if ($goal["id"] == $reqHeader["id"]) {
                            $dataRow[] = array(
                                "data" => $goal["value"],
                                "class" => "right "
                            );
                            $found = true;
                        }
                    }
                }

                if (!$found) {
                    $dataRow[] = array(
                        "data" => "",
                        "class" => "right"
                    );
                }
            }

            // Check totals, calculate final column.
            // Skip first three columns since they aren't actually a goal requirement.
            $completedText = "Complete";
            $completedClass = "req-complete";
            for ($i = 3; $i < sizeof($requirementsHeader); $i++) {
                if (intval($dataRow[$i]['data']) < intval($requirementsHeader[$i]['data'])) {
                    $completedText = "Not Complete";
                    $completedClass = "req-incomplete";
                    break;
                }
            }

            $dataRow[] = array(
                "data" => $completedText,
                "class" => " ".$completedClass
            );

            // Add the row to the table
            $table_data['body'][] = $dataRow;
        }

        // add the table
        $this->data[] = array(
            "type" => "table",
            "content" => $table_data,
            "options" => array("noSort" => true, "noSearch" => true),
        );
    }

    public function goalSetTableValidate($info)
    {
        // make sure we have a goal set
        $goalSet = $this->config["selected-goalset"];
        if ($goalSet <= 0) {
            $this->valid = false;
            $this->errors["selected-goalset"][] = "Please select a goal set.";
        }
    }
}
