<?php
class Fisdap_Reports_SurgicalRotationCase extends Fisdap_Reports_Report
{
    public $header = '';

    public $footer = '';

    public $formComponents = array(
        'shiftInformationForm' => array(
            'title' => 'Select Shift Information',
            'options' => array(
                'pickPatientType' => FALSE,
            )
        ),
        'multistudentPicklist' => array(
            'title' => 'Select one student',
            'options' =>  array(
                'mode' => 'single',
                'loadJSCSS' => TRUE,
                'loadStudents' => TRUE,
                'showTotal' => TRUE,
                'studentVersion' => TRUE,
                'useSessionFilters' => TRUE,
                'sessionNamespace' => "ReportStudentFilter",
            ),
        )
    );

    private $diagnosticEndoIds = array(1507,1508,1509,1510,1511,1512,1513,1514,1515,1516);

    private $surgicalIds = array(1496,1497,1498,1499,1500,1501,1502,1503,1504,1505,1506);

    private $laborDeliveryId = 1517;

    private $generalSurgeryId = 1495;

    //We can't grab the eval names directly from the DB because the formatting differs and we can't easily grab the chunk of text we want from the title programmatically.
    private $evalNames = array(
        1495 => "General Surgery",
        1496 => "Cardiothoracic",
        1497 => "ENT",
        1498 => "Eyes",
        1499 => "GU",
        1500 => "Neuro",
        1501 => "OB/GYN",
        1502 => "Oral/Maxiofacial",
        1503 => "Orthopedics",
        1504 => "Peripheral Vascular",
        1505 => "Plastics",
        1506 => "Procurement/Transplant",
        1507 => "Bronchoscopy",
        1508 => "Colonoscopy",
        1509 => "Cystoscopy",
        1510 => "EGD",
        1511 => "ERCP",
        1512 => "Esophagoscopy",
        1513 => "Laryngoscopy",
        1514 => "Panendoscopy",
        1515 => "Sinoscopy",
        1516 => "Ureteroscopy",
        1517 => "Labor & Delivery"
    );

    //
    private $grandTotal = array(
        "first" => 0,
        "second" => 0,
        "obs" => 0,
        "total" => 0
    );

    private $surgicalSpecialtiesTotal = array(
        "first" => 0,
        "second" => 0,
        "obs" => 0,
        "total" => 0
    );


    /**
     * Run a query and any processing logic that produces the data contained in the report
     * Return a multidimensional array and it will be rendered as tables
     * OR return a string and it will be rendered as HTML
     * @return array
     */
    public function runReport() {
        //clean up site info
        $site_ids = $this->getSiteIds();

        $start_date = $this->config['startDate'];
        $end_date = $this->config['endDate'];

        $student_id = array($this->config['student']);

        $repo = \Fisdap\EntityUtils::getRepository('Report');

        $diagnosticEndoData = $repo->getSurgTechScrubRoleData($this->diagnosticEndoIds, $student_id, $start_date, $end_date, $site_ids);

        $this->addTallyTable("Diagnostic Endoscopy", $diagnosticEndoData);

        $surgicalData = $repo->getSurgTechScrubRoleData($this->surgicalIds, $student_id, $start_date, $end_date, $site_ids);

        $this->addTallyTable("Surgical Specialties", $surgicalData);

        $laborData = $repo->getSurgTechScrubRoleData($this->laborDeliveryId, $student_id, $start_date, $end_date, $site_ids);

        $this->addTallyTable("Labor & Delivery", $laborData);

        $generalSurgData = $repo->getSurgTechScrubRoleData($this->generalSurgeryId, $student_id, $start_date, $end_date, $site_ids);

        $this->addTallyTable("General Surgery", $generalSurgData);

        $this->addSumTable("Surgical Specialties (all except General Surgery)", $this->surgicalSpecialtiesTotal);

        $this->addSumTable("Grand Total", $this->grandTotal);
    }

    /**
     * This function adds a table to the report that consists of only a total row for the given data and does not add to our running totals for surgical specialties and grand total
     *
     * @param $tableName The title you'd like to appear for this table in the report as a string
     * @param $data an array structured like $this->grandTotal
     */
    protected function addSumTable($tableName, $data){
        $table = array(
            'title' => $tableName,
            'nullMsg' => "No evaluations found.",
            'head' => array(
                '0' => array(
                    '',
                    'First Scrub Role',
                    'Second Scrub Role',
                    'Observation Only',
                    'Total'
                )
            ),
            'body' => array(),
        );

        $table['body'][] = array(
            array(
                'data' => 'Total',
                'class' => 'center'
            ),
            array(
                'data' => $data['first'],
                'class' => 'center'
            ),
            array(
                'data' => $data['second'],
                'class' => "center"
            ),
            array(
                'data' => $data['obs'],
                'class' => "center"
            ),
            array(
                'data' => $data['total'],
                'class' => "center"
            ),
        );

        $this->data[] = array("type" => "table",
            "content" => $table);
    }

    /**
     * This function adds a table to the report and adds the values from $data to our running totals of surgical specialties and grand total.
     *
     * @param $tableName The title you'd like to appear for this table in the report as a string
     * @param $data An array containing eval ids and eval related totals, generated by $repo->getSurgTechScrubRoleData().
     */
    protected function addTallyTable($tableName, $data){
        //construct the table
        $table = array(
            'title' => $tableName,
            'nullMsg' => "No evaluations found.",
            'head' => array(
                '0' => array(
                    'Name',
                    'First Scrub Role',
                    'Second Scrub Role',
                    'Observation Only',
                    'Total'
                )
            ),
            'body' => array(),
        );

            foreach($data as $eval) {
                $table['body'][$eval['eval_id']] = array(
                    array(
                        'data' => $this->evalNames[$eval['eval_id']],
                        'class' => 'noSum noAverage'
                    ),
                    array(
                        'data' => $eval['first'],
                        'class' => 'center'
                    ),
                    array(
                        'data' => $eval['second'],
                        'class' => 'center'
                    ),
                    array(
                        'data' => $eval['obs'],
                        'class' => 'center'
                    ),
                    array(
                        'data' => $eval['total'],
                        'class' => 'center'
                    ),
                );

                //add first/second/obs to running surgicalSpecialties total (per CNM, everything besides General Surgery counts)
                if($eval['eval_id'] != 1495){
                    $this->surgicalSpecialtiesTotal['first'] += $eval['first'];
                    $this->surgicalSpecialtiesTotal['second'] += $eval['second'];
                    $this->surgicalSpecialtiesTotal['obs'] += $eval['obs'];
                    $this->surgicalSpecialtiesTotal['total'] += $eval['total'];
                }

                //Add tallies to running total
                $this->grandTotal['first'] += $eval['first'];
                $this->grandTotal['second'] += $eval['second'];
                $this->grandTotal['obs'] += $eval['obs'];
                $this->grandTotal['total'] += $eval['total'];
            }

            $footer = array(array("data" => "Total:", "class" => "right"));

            for($i = 1; $i <= 4; $i++){
                $footer[] = array("data" => "-", "class" => "center");
            }

            $table['foot']['sum'] = $footer;

            $this->data[] = array("type" => "table",
                "content" => $table);
        }
}