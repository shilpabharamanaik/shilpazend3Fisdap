<?php
/**
 * Class Fisdap_Reports_AccreditationGoals
 * This is the CoAEMSP Appendix G/H Student Patient Contact Matrix Report class
 * Refer to Fisdap_Reports_Report for more documentation
 */
class Fisdap_Reports_AccreditationGoals extends Fisdap_Reports_Report
{
    public $header = '';

    public $footer = '';

    public $formComponents = array(
        'goalSetTable' => array(
            'title' => 'Select a goal set',
            'options' => array(
                "excludeGoalSetTemplates" => array(2, 3)
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

    public $procedures = array(
            "Medications" => "Safely Administer Medications",
            "Live Intubation" => "Live Intubations",
            "IVs" => "Safely Gain Venous Access",
            "Ventilations" => "Ventilate a Patient",
            "New Born" => "Assessment of Newborn",
            "Infant" => "Assessment of Infant",
            "Toddler" => "Assessment of Toddler",
            "Preschooler" => "Assessment of Preschooler",
            "School Age" => "Assessment of School Agers",
            "Adolescent" => "Assessment of Adolescents",
            "Adult" => "Assessment of Adults",
            "Geriatric" => "Assessment of Geriatrics",
            "Obstetrics" => "Assessment of Obstetric Patients",
            "Trauma" => "Assessment of Trauma Patients",
            "Psychiatric" => "Assessment of Psychiatric Patients",
            "Medical" => "Assessment of Medical Patients",
            "Chest Pain" => "Assess and Plan RX of Chest Pain",
            "Breathing problem" => "Assess and Plan RX of Breathing problem",
            "Change in responsiveness" => "Assess and Plan RX of Change in responsiveness",
            "Abdominal Pain" => "Assess and Plan RX of Abdominal pain",
            "AMS" => "Assess and Plan RX of Altered Mental Status",
            "Team Lead Total" => "Field Internship Team Leads",
            );

    public $procedures2017 = array(
            "Medications" => "Safely Administer Medications",
            "Live Intubation" => "Live Intubations",
            "IVs" => "Safely Gain Venous Access",
            "Ventilations" => "Ventilate a Patient",
            "New Born" => "Assessment of Newborn",
            "Infant" => "Assessment of Infant",
            "Toddler" => "Assessment of Toddler",
            "Preschooler" => "Assessment of Preschooler",
            "School Age" => "Assessment of School Agers",
            "Adolescent" => "Assessment of Adolescents",
            "Adult" => "Assessment of Adults",
            "Geriatric" => "Assessment of Geriatrics",
            "Obstetrics" => "Assessment of Obstetric Patients",
            "Trauma" => "Assessment of Trauma Patients",
            "Psychiatric" => "Assessment of Psychiatric Patients",
            "Medical" => "Assessment of Medical Patients",
            "Chest Pain" => "Assess and Plan RX of Chest Pain",
            "Adult Dyspnea" => "Assess and Plan RX of Adult Breathing problem",
            "Pediatric Dyspnea" => "Assess and Plan RX of Pediatric Breathing problem",
            "Change in responsiveness" => "Assess and Plan RX of Change in responsiveness",
            "Abdominal Pain" => "Assess and Plan RX of Abdominal pain",
            "AMS" => "Assess and Plan RX of Altered Mental Status",
            "Team Lead Total" => "Field Internship Team Leads",
    );


    public $styles = array("/css/library/Fisdap/Reports/graduation-requirements.css");
    
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
     * @return array
     */
    public function runReport()
    {
        // set up the table! start with the header
        $title = "CoAEMSP (Appendix G/H) Student Patient Contact Matrix";
        $superHeaderRow = array(array("data" => "Procedure",
                    "class" => "superheader",
                    "rowspan" => 2),
                array("data" => "# Required by Program",
                    "class" => "superheader",
                    "rowspan" => 2),
                array("data" => "Lab",
                    "class" => "superheader",
                    "colspan" => 2),
                array("data" => "Clinical",
                    "class" => "superheader",
                    "colspan" => 2),
                array("data" => "Field",
                    "class" => "superheader",
                    "colspan" => 2),
                array("data" => "All",
                    "class" => "superheader",
                    "colspan" => 2),
                );
        $subHeaderRow = array(array("data" => "Average",
                    "class" => "right no-search",),
                array("data" => "Range",
                    "class" => "right no-search",),
                array("data" => "Average",
                    "class" => "right no-search",),
                array("data" => "Range",
                    "class" => "right no-search",),
                array("data" => "Average",
                    "class" => "right no-search",),
                array("data" => "Range",
                    "class" => "right no-search",),
                array("data" => "Average",
                    "class" => "right no-search",),
                array("data" => "Range",
                    "class" => "right no-search",),
                );
        $table_data = array('title' => $title,
                'nullMsg' => "No skills found.",
                'head' => array('1' => $superHeaderRow, '2' => $subHeaderRow),
                'body' => array(),
                );

        // organize the form values
        $shiftOptions['startDate'] = $this->config['startDate'];
        $shiftOptions['endDate'] = $this->config['endDate'];
        $shiftOptions['subjectTypes'] = $this->getTypeIds();
        $shiftOptions['shiftSites'] = $this->getSiteIds();
        $shiftOptions['audited'] = ($this->config['auditStatus'] == 'audited') ? 1 : 0;

        $students = $this->getMultiStudentData();

        // get the goalset
        $goalSetId = $this->config['selected-goalset'];
        $goalSet = \Fisdap\EntityUtils::getRepository('GoalSet')->getGoalsForGoalSet($goalSetId);

        // loop through the hard coded procedures getting the data for each row
        $repo = \Fisdap\EntityUtils::getRepository('Report');

        // The new 2017 requirements goal set(s) do not have a "Beathing problem" goal.
        // To retain backwards compatibility, check if the Breathing problem goal exists...
        // if not, use the hard coded procedure list that has Adult/Peds Dyspnea.
        $proList = $this->procedures;
        if (!$goal = $goalSet->getGoalByName("Breathing problem")) {
            $proList = $this->procedures2017;
        }

        foreach ($proList as $procedure => $description) {
            if ($goal = $goalSet->getGoalByName($procedure)) {
                $required = $goal->number_required;

                //get lab data
                $rawData = $repo->getStudentGoalsData($goal, array_keys($students), $shiftOptions, array("lab"));
                $labData = $this->parseGoalsResults($rawData);
                $labRange = $labData['max']." to ".$labData['min'];

                // get clinical data
                $rawData = $repo->getStudentGoalsData($goal, array_keys($students), $shiftOptions, array("clinical"));
                $clinicalData = $this->parseGoalsResults($rawData);
                $clinicalRange = $clinicalData['max']." to ".$clinicalData['min'];

                // get field data
                $rawData = $repo->getStudentGoalsData($goal, array_keys($students), $shiftOptions, array("field"));
                $fieldData = $this->parseGoalsResults($rawData);
                $fieldRange = $fieldData['max']." to ".$fieldData['min'];

                // get overall total data
                $rawData = $repo->getStudentGoalsData($goal, array_keys($students), $shiftOptions, array("field", "clinical", "lab"));
                $allData = $this->parseGoalsResults($rawData);
                $allRange = $allData['max']." to ".$allData['min'];

                $dataRow = array($description,
                        array("data" => $required, "class" => "center"),
                        array("data" => $labData['average'], "class" => "right"),
                        array("data" => $labRange, "class" => "right"),
                        array("data" => $clinicalData['average'], "class" => "right"),
                        array("data" => $clinicalRange, "class" => "right"),
                        array("data" => $fieldData['average'], "class" => "right"),
                        array("data" => $fieldRange, "class" => "right"),
                        array("data" => $allData['average'], "class" => "right"),
                        array("data" => $allRange, "class" => "right"),
                );

                // add the row
                $table_data['body'][] = $dataRow;
            }
        }

        // add the table
        $this->data[] = array("type" => "table",
                "content" => $table_data,
                "options" => array("noSort" => true, "noSearch" => true),
                );
        //var_export($this->data);
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

    private function parseGoalsResults($results)
    {
        // ok, now get the average, max and min
        $average = 0;
        $max = 0;
        $min = 100000000;
        $resultCount = 0;
        $studentCount = count($results);
        foreach ($results as $count) {
            $average += $count;
            $max = ($count > $max) ? $count : $max;
            $min = ($count < $min) ? $count : $min;
        }

        // figure out the average
        $average = floor($average/$studentCount);
        $average = ($average < 1) ? "Under 1" : $average;

        return array("average" => $average,
                "max" => $max,
                "min" => $min,
                );
    }
}
