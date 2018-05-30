<?php

/*
 *
 * Class Fisdap_Reports_AirwayManagement
 * This is the Airway Mangement report
 * Refer to Fisdap_Reports_Report for more documentation
 *
 * @author Hammer :)
 *
 */

class Fisdap_Reports_AirwayManagement extends Fisdap_Reports_Report
{
    protected $logger;
    // init our class variables
    public $header = '';
    public $footer = '';

    public $styles = array("/css/library/Fisdap/Reports/airway-management.css");
    public $scripts = array("/js/library/Fisdap/Reports/airway-management.js");

    public $formComponents = array(
        'goalSetTable' => array(
            'title' => 'Select a goal set',
        ),
        'shiftInformationForm' => array(
            'title' => 'Select shift information',
            'options' => array(
                'pickAuditStatus' => true,
                'selected' => array(
                    'sites' => array(),
                    'types' => array(),
                ),
            )
        ),
        'Reports_Form_AirwayManagementOptions' => array('title' => 'Report options',),
        'multistudentPicklist' => array(
            'title' => 'Select one or more student(s)',
            'options' => array(
                'mode' => 'multiple',
                'loadJSCSS' => true,
                'loadStudents' => true,
                'showTotal' => true,
                'studentVersion' => true,
                'useSessionFilters' => true,
                'sessionNamespace' => "ReportStudentFilter",
            ),
        ),
    );


    /*
     *
     * Actually runs our report.
     * Grabs the data (filtered based on user specs), parses it, & dumps it into table(s).
     *
     */
    public function runReport()
    {
        $this->logger = Zend_Registry::get('logger');
        $raw_data = $this->getAirwayManagementRawData();
        $data_framework = $this->getAMDataFramework();
        $parsed_am_data = $this->parseAMData($data_framework, $raw_data);

        $repo = \Fisdap\EntityUtils::getRepository('AirwayManagement');
        $raw_et_data = $repo->getETDataForReport(
            $this->config['multistudent_picklist_selected'],
            $this->getSiteIds(),
            $this->config['startDate'],
            $this->config['endDate'],
            $this->getTypeIds(),
            ($this->config['auditStatus'] == "all") ? false : true
        );

        $parsed_et_data = $this->parseETData($data_framework, $raw_et_data);

        if ($this->config['airway_management_report_type'] == 'detailed') {
            // run the detailed version of this report
            $this->runDetailedReport($parsed_am_data, $parsed_et_data);
        } else {
            // run the summary version of this report
            $this->runSummaryReport($parsed_am_data);
        }
    }

    /*
     * Runs the detailed report.
     * 	- This report has a separate table for each student selected
     * 	- Each table has a footer that includes some summary information
     * 		- This footer is dynamic and is calculated via JavaScript
     *
     * @param Array $student_data the parsed data that is ready to be dumped into tables!
     *
     */
    public function runDetailedReport($student_data, $et_data)
    {
        $num_required = $this->getGoalSetForAirwayManagement()->getAirwayManagementNumberRequired();
        $goal_title = "goal: " . $num_required;

        // student through each our students to build our tables
        foreach ($student_data as $student_id => $data) {
            // * * * * * * * * * * * *
            //      AM TABLE
            // * * * * * * * * * * * *

            $student_table = $this->buildStudentTable($data['managements']);
            $eureka_attempts = $student_table['eureka_attempts'];
            $eureka_dates = $student_table['eureka_dates'];

            // grab our table title (this is also where our eureka graphs are generated)
            $table_title = $this->getDetailedTableTitle(
                $data['student_name'],
                count($data['managements']),
                $student_id,
                $eureka_attempts,
                $eureka_dates
            );

            // get the defaults for the table columns and footer
            $cols = array(
                'Shift',
                array("data" => 'Attempt', "class" => "am_attempt_number_column"),
                'Procedures',
                'Pt. Type',
                'Age',
                'Success'
            );
            $null_msg = "No airway management records found.";
            $footer = array(
                array(
                    'data' => 'Total attempts:<span class="airway_management_attempts_goal">' . $num_required . '</span>',
                    "class" => "right_justified_text",
                    "title" => $goal_title
                ),
                array(
                    'data' => '<span class="total_attempts_number" id="total_attempts_' . $student_id . '">0</span>',
                    'colspan' => 1,
                    'class' => 'total_attempts_goal_cell',
                    'title' => $goal_title
                ),
                array(
                    'data' => 'Success rate over last 20 attempts:',
                    'colspan' => 3,
                    "class" => "right_justified_text"
                ),
                array(
                    'data' => '<span class="success_rate_number" id="sucess_rate_' . $student_id . '">0</span>',
                    'class' => 'success_rate_goal_cell'
                )
            );

            // make a table and add it to our report
            $data_table = array(
                'title' => $table_title,
                'nullMsg' => $null_msg,
                'head' => array('0' => $cols),
                'body' => $student_table['body'],
                'foot' => array('0' => $footer)
            );
            $this->data['airway_management_' . $student_id] = array("type" => "table", "content" => $data_table);


            // * * * * * * * * * * * *
            //      ET TABLE
            // * * * * * * * * * * * *

            $et_table = $this->buildStudentTable($et_data[$student_id]['managements']);

            $repo = \Fisdap\EntityUtils::getRepository('AirwayManagement');
            $et_total_data = $repo->getETTotals(
                $student_id,
                $this->getSiteIds(),
                $this->config['startDate'],
                $this->config['endDate'],
                $this->getTypeIds(),
                ($this->config['auditStatus'] == "all") ? false : true
            );

            $et_string = 'N/A';
            if ($et_total_data['attempts'] >= $et_total_data['window']) {
                $et_string = floor(($et_total_data['success_count'] / $et_total_data['window']) * 100) . "% ";
                $et_string .= '('.$et_total_data['success_count'] . '/' . $et_total_data['window'].')';
            }

            $et_table_title = $data['student_name'] . "'s Endotracheal Intubations";

            $et_cols = array(
                'Shift',
                array("data" => 'Attempt', "class" => "am_attempt_number_column"),
                'Procedures',
                'Pt. Type',
                'Age',
                'Success'
            );
            $et_null_msg = "No ET records found.";
            $num_et_required = 10;
            $et_goal_title = "goal: " . $num_et_required;
            $et_footer = array(
                array(
                    'data' => 'Total attempts:<span class="airway_management_attempts_goal">' . $num_et_required . '</span>',
                    "class" => "right_justified_text",
                    "title" => $et_goal_title
                ),
                array(
                    'data' => '<span class="total_attempts_number" id="total_attempts_' . $student_id . '">0</span>',
                    'colspan' => 1,
                    'class' => 'total_attempts_goal_cell',
                    'title' => $goal_title
                ),
                array(
                    'data' => 'ET Success rate over last 10 attempts:',
                    'colspan' => 3,
                    "class" => "right_justified_text"
                ),
                array(
                    //'data' => '<span class="et_success_rate_number" id="et_sucess_rate_' . $student_id . '"><pre>'.$et_string.'<br/>'.print_r($raw, true).'</pre></span>',
                    'data' => '<span class="et_success_rate_number" id="et_sucess_rate_' . $student_id . '">'.$et_string.'</span>',
                    'class' => 'success_rate_goal_cell'
                )
            );

            $et_data_table = array(
                'title' => $et_table_title,
                'nullMsg' => $et_null_msg,
                'head' => array('0' => $et_cols),
                'body' => $et_table['body'],
                'foot' => array('0' => $et_footer)
            );
            $this->data['et_' . $student_id] = array("type" => "table", "content" => $et_data_table);
        } // end foreach student data
    } // end runDetailedReport


    /*
     * Runs the summary version of this report.
     * 	- This report has ONE table with each student having a single row.
     * 	- The rows are green if the student has met their 20/20 goal
     *
     * @param Array $student_data the parsed data that is ready to be dumped into tables!
     *
     */
    public function runSummaryReport($student_data)
    {
        $table_body = array();

        // step through each of our students to build the rows of our single table
        foreach ($student_data as $student_id => $data) {
            // init our variables for each student
            $attempts_array = array();
            $attempt_number = 1;

            // step through each airway management record and add it to our attempts array
            // if the student performed it
            if ($data['managements']) {
                foreach ($data['managements'] as $shift_id => $shift_am_data) {
                    foreach ($shift_am_data['attempts'] as $attempt) {
                        if ($attempt['performed']) {
                            // the student performed this attempt, add it to our array (regardless of success)
                            $attempts_array[$attempt_number] = ($attempt['success_boolean'] === true) ? 1 : 0;
                            $attempt_number++;
                        }
                    } // end for each shift attempts
                } // end for each management
            } // end if managements

            // get our success data (rate/attempt count/class names)
            $success_data = $this->getSuccessDataForSummary($attempts_array);

            // Run a query to get ET data.
            $repo = \Fisdap\EntityUtils::getRepository('AirwayManagement');
            $et_data = $repo->getETTotals(
                $student_id,
                $this->getSiteIds(),
                $this->config['startDate'],
                $this->config['endDate'],
                $this->getTypeIds(),
                ($this->config['auditStatus'] == "all") ? false : true
            );

            $et_string = 'N/A';
            if ($et_data['attempts'] >= $et_data['window']) {
                $et_string = floor(($et_data['success_count'] / $et_data['window']) * 100) . "%";
            }

            // build the row for this student
            $name_cell = array("data" => $data['student_name'], "class" => $success_data['class']);
            $attempt_cell = array("data" => count($attempts_array), "class" => $success_data['class']);
            $et_cell = array("data" => $et_string, "class" => $success_data['class']);
            $success_cell = array("data" => $success_data['rate'], "class" => $success_data['class']);
            $table_body[] = array($name_cell, $attempt_cell, $et_cell, $success_cell);
        } // end foreach student data

        // make the single table and add it to the report
        $columns = array('Student', 'Total attempts', 'ET Success rate over last 10 attempts', 'Success rate over last 20 attempts');
        $title = "Airway Management Summary";
        $null_msg = "No airway management records found.";
        $data_table = array(
            'title' => $title,
            'nullMsg' => $null_msg,
            'head' => array('0' => $columns),
            'body' => $table_body
        );
        $this->data['airway_management_summary'] = array("type" => "table", "content" => $data_table);
    } // end runSummaryReport


    /**
     * ---------------------------------------------------------------------------------------------------------------------------------------
     *            The following functions are helper functions, used when building the DETAILED version of this report.
     * ---------------------------------------------------------------------------------------------------------------------------------------
     */


    /*
     * Builds the table for an individual student (called only from the detailed report)
     * @param Array $managements the array of parsed data ready to be dumped into tables
     *
     * @return Array keyed array with:
     * 	- 'body' : the table body (a row for each attempt)
     * 	- 'eureka_attempts' : a keyed array of 1s and 0s (for each performed attempt) to be used when building the eureka graphs
     * 	- 'eureka_dates' : an array of DateTime objects that will be used when building the eureka graphs
     */
    public function buildStudentTable($managements)
    {
        $attempt_count = 1;
        $table_body = $eureka_attempts = $eureka_dates = array();

        // does this student have any airway management records?
        if ($managements) {
            foreach ($managements as $shift_id => $shift_am_data) {
                foreach ($shift_am_data['attempts'] as $attempt) {
                    // this is a valid attempt, do some fun stuff
                    if ($attempt['performed']) {
                        $attempt_number = $attempt_count;
                        $attempt_count++;

                        $attempt_class = ($attempt['success_boolean'] === true) ? "success_attempt_count_cell" : "failure_attempt_count_cell";

                        // add to our eureka arrays for later use
                        $eureka_attempts[$attempt_number] = ($attempt['success_boolean'] === true) ? 1 : 0;
                        $eureka_dates[$attempt_number] = $attempt['attempt_date'];
                    } else {
                        $attempt_number = "Observed";
                        $attempt_class = "";
                    }

                    $table_body[] = array(
                        "data" => array("data" => $shift_am_data['shift_info_display']),
                        array(
                            "data" => $attempt_number,
                            "title" => $attempt['attempt_title'],
                            "class" => $attempt_class
                        ),
                        array("data" => $attempt['procedures']),
                        array("data" => $attempt['subject']),
                        array("data" => $attempt['age'], "title" => $attempt['age_title']),
                        array("data" => $attempt['success'], "class" => "airway_management_success_cell")
                    );
                } // end foreach shift attempts
            } // end foreach airway management
        } // end if airway managements

        return array("body" => $table_body, "eureka_attempts" => $eureka_attempts, "eureka_dates" => $eureka_dates);
    } // end buildStudentTable


    /*
     * Returns the HTML to be used in the title of the Detailed report
     * This includes the HTML needed for the Eureka graph modal
     *
     * @param String $name the full name of the student
     * @param Int $data_count the number of airway management attempts (need at least 1 to get a eureka graph)
     * @param Int $student_id the ID of the student
     * @param Array $eureka_attempts the keyed array of 1s and 0s that'll be used as attempts for the graph
     * @param Array $eureka_dates the array of DateTime objects that'll be used for the eureka graphs
     *
     * @return String $table_title the HTML to be used as the title for the detailed report
     */
    public function getDetailedTableTitle($name, $data_count, $student_id, $eureka_attempts, $eureka_dates)
    {
        $table_title = $name . "'s Airway Management";

        if ($data_count > 0) {

            // generates the HTML for the eureka graph for ALL attempts
            $eureka_helper = new Fisdap_View_Helper_EurekaGraph();
            $all_graph_id = "airway_management_eureka_all_attempts_" . $student_id;
            $all_eureka_msg = $this->getEurekaMsg($name, true);
            $all_time_attempts = $eureka_helper->eurekaGraph(
                $eureka_attempts,
                $eureka_dates,
                20,
                20,
                $all_graph_id,
                true,
                $all_eureka_msg
            );

            // generates the HTML for the eureka graph for just the last 20 attempts
            $coa_attempts = $this->getCoaEurekaGraph($eureka_attempts, $eureka_dates, $name, $student_id);

            // build the HTML for the dialog box that will contain both graphs
            $table_title .= "<a href='#' class='eureka-goal-met am_dialog_trigger' id='am_eureka_dialog_trigger_" . $student_id . "'></a>";
            $table_title .= "<div data-studentid=" . $student_id . " class='am_eureka_dialog' id='am_eureka_dialog_" . $student_id . "'>";
            $table_title .= "<div class='all_time_attempts_wrapper'>";
            $table_title .= $all_time_attempts;
            $table_title .= "</div>";
            $table_title .= "<div class='coa_attempts_wrapper'>";
            $table_title .= $coa_attempts;
            $table_title .= "</div>";
            $table_title .= "</div>";
        }

        return $table_title;
    } // end getDetailedTableTitle

    /*
     * Builds the HTML for the Eureka graph that meets the CoA requirements
     * 	 - 20/20 goal for the last 20 attempts only
     *
     * @param Array $eureka_attempts the keyed array of 1s and 0s (this is all time attempts)
     * @param Array $eureka_dates the array of DateTime objects that corresponds with the dates of attempts
     * @param String $name the name of the student
     * @param Int $student_id the ID of the student
     *
     * @return String $eureka_graph the HTML to be used for the CoA eureka graph
     */
    public function getCoaEurekaGraph($eureka_attempts, $eureka_dates, $name, $student_id)
    {
        $total_attempts = count($eureka_attempts);
        $starting_point = $total_attempts - 20;

        $coa_attempts = array();
        $coa_dates = array();

        if ($starting_point < 0) {
            $coa_attempts = $eureka_attempts;
            $coa_dates = $eureka_dates;
        } else {
            for ($i = $starting_point; $i < $total_attempts; $i++) {
                if (isset($eureka_attempts[$i])) {
                    $coa_attempts[] = $eureka_attempts[$i];
                    $coa_dates[] = $eureka_dates[$i];
                }
            }
        }

        $coa_eureka_helper = new Fisdap_View_Helper_EurekaGraph();
        $graph_id = "airway_management_eureka_coa_attempts_" . $student_id;
        $eureka_msg = $this->getEurekaMsg($name, false);
        $eureka_graph = $coa_eureka_helper->eurekaGraph(
            $coa_attempts,
            $coa_dates,
            20,
            20,
            $graph_id,
            true,
            $eureka_msg
        );

        return $eureka_graph;
    } // end getCoaEurekaGraph


    /*
     * The Eureka Graph view helper allows you to add a unique title to the graph.
     * In this report, we also include a link to toggle between the last 20 attempts, and all attempts.
     * This function returns the HTML necessary to accomplish this.
     *
     * @param String $name the name of the student
     * @param Boolean $all true if this is for the eureka graph showing all attempts/false if it is just for the last 20 attempts
     *
     * @return String $msg the HTML to use as the eureka title
     */
    public function getEurekaMsg($name, $all)
    {
        $trigger_class = ($all) ? "show_coa_attempts_trigger" : "show_all_attempts_trigger";

        $msg = $name . "'s Airway Management";

        $msg .= "<span class='eureka_dialog_subheader'>";
        $msg .= ($all) ? " (all attempts)" : " (last 20 attempts)";
        $msg .= "</span>";

        $msg .= "<a href='#' class='" . $trigger_class . "'>";
        $msg .= ($all) ? "show last 20 attempts" : "show all attempts";
        $msg .= "</a>";

        return $msg;
    } // end getEurekaMsg


    /**
     * ---------------------------------------------------------------------------------------------------------------------------------------
     *            The following funcions are helper functions, used when building the SUMMARY version of this report.
     * ---------------------------------------------------------------------------------------------------------------------------------------
     */

    /*
     * Calculates the success rate of the last 20 attempts.
     * If 100% will return a class name
     * 	- This will be used to turn the cells green to indicate the student has reached their goal.
     *
     * @param Array $attempts a keyed array of 1s and 0s (keyed by attempt number)
     *
     * @return Array keyed array with:
     * 	- 'rate' : the success rate String to display
     * 	- 'class' : empty string if the studnet has not reached their goal
     */
    public function getSuccessDataForSummary($attempts)
    {
        $total_attempts = count($attempts);
        $starting_point = $total_attempts - 19;

        // only determine success for the last 20 attempts. The attempts array is keyed to start at 1.
        $success_count = 0;
        for ($i = $starting_point; $i <= $total_attempts; $i++) {
            if ($attempts[$i] === 1) {
                $success_count++;
            }
        }

        // do we have enough attempts to calculate a success rate?
        if ($total_attempts >= 20) {
            $success_rate = ($success_count / 20) * 100;
            $success_goal_class = ($success_rate == 100) ? "eureka_reached" : "";
            $success_rate = $success_rate . "%";
        } else {
            $success_rate = "N/A";
            $success_goal_class = "";
        }

        return array("rate" => $success_rate, "class" => $success_goal_class);
    } // end getSuccessDataForSummary


    /**
     * ---------------------------------------------------------------------------------------------------------------------------------------
     *            The following funcions are for getting all of the data and parsing it into a readable array
     * ---------------------------------------------------------------------------------------------------------------------------------------
     */


    /*
     * Runs the single query needed to get our airway management data
     * Also handles the filtering from the form report options at this level
     *
     * @return Array $raw_data the raw data from our getArrayResults() query
     */
    public function getAirwayManagementRawData()
    {
        // clean up our form data
        $site_ids = $this->getSiteIds();
        $student_ids = $this->config['multistudent_picklist_selected'];
        $start_date = $this->config['startDate'];
        $end_date = $this->config['endDate'];
        $patient_type = $this->getTypeIds();
        $audited_only = ($this->config['auditStatus'] == "all") ? false : true;

        // Run a query to get data.
        $repo = \Fisdap\EntityUtils::getRepository('AirwayManagement');
        $raw_data = $repo->getDataForReport(
            $student_ids,
            $site_ids,
            $start_date,
            $end_date,
            $patient_type,
            $audited_only
        );

        return $raw_data;
    } // end getAirwayManagementRawData


    /*
     * Parses the raw airway management data into something that is readable
     * by both the summary and detailed versions of the report.
     *
     * @param Array $data_framework the framework we'll use for organizing our data
     * @param Array $raw_data the raw data we need to parse
     *
     * @return Array $parsed_data the clean/parsed data to be used to report
     */
    public function parseAMData($data_framework, $raw_data)
    {
        // begin by using our framework
        $parsed_data = $data_framework;

        // grab a goal set -- this is used JUST for age descriptions
        $goal_set = $this->getGoalSetForAirwayManagement();

        // we'll need this guy for displaying our shift summaries (initialize it here, so we only have to do it once)
        $summary_helper = new Fisdap_View_Helper_ShiftSummaryDisplayHelper();

        if ($raw_data) {

            // step through each of our airway management records
            foreach ($raw_data as $am_data) {

                // set up some parameters to make this process a bit cleaner
                $shift = $am_data['shift'];
                $shift_id = $shift['id'];
                $stu_id = $shift['student']['id'];

                $parsed_data[$stu_id]['student_name'] = $shift['student']['first_name'] . " " . $shift['student']['last_name'];

                // if this is the detailed report and we are hiding observed and this was not performed by, don't include this step
                $include_attempt = true;
                if (!$am_data['performed_by']) {
                    if (($this->config['airway_management_report_type'] == 'detailed') && (!$this->config['include_observed_airway_managements'])) {
                        $include_attempt = false;
                    }
                }

                if ($include_attempt) {
                    // is this the first time we've seen this shift?
                    if (!$parsed_data[$stu_id]['managements'][$shift_id]) {
                        $parsed_data[$stu_id]['managements'][$shift_id] = $this->initAMShiftArray(
                            $shift,
                            $summary_helper
                        );
                    }

                    $parsed_am_data = $this->parseAttemptData($am_data, $shift['start_datetime'], $goal_set);
                    $parsed_data[$stu_id]['managements'][$shift_id]['attempts'][$am_data['id']] = $parsed_am_data;
                }
            } // end foreach raw data
        } // end if raw data

        return $parsed_data;
    } // end parseAMData


    /*
     * Builds the parsed array of data for an INDIVIDUAL attempt
     * @param Boolean $value if true the attempt was successful
     *
     * @return Array $parsed_data the data parsed into a readable array
     */
    public function parseAttemptData($attempt, $shift_start_datetime, $goal_set)
    {
        $parsed_data = array();
        $parsed_data['patient_id'] = $attempt['patient']['id'];
        $parsed_data['skill_order'] = $attempt['skill_order'];

        $success_bool = $attempt['success'];
        $source = $attempt['airway_management_source']['id'];

        // deal with success stuff
        $success_img = $this->getAMSuccessImg($success_bool);
        $parsed_data['success'] = $this->getAMSuccessDisplay($source, $attempt['performed_by'], $success_img);
        $parsed_data['success_boolean'] = $success_bool;

        // subject and attempt date time
        $parsed_data['subject'] = $attempt['subject']['name'] . " - " . $attempt['subject']['type'];
        $parsed_data['attempt_date'] = $shift_start_datetime;

        // performed by and attempt title attribute
        if ($attempt['performed_by'] === true) {
            $parsed_data['performed'] = 1;
            $parsed_data['attempt_title'] = "";
        } else {
            $parsed_data['performed'] = 0;
            $parsed_data['attempt_title'] = "Airway management was observed";
        }

        // age
        $age = $this->getAMAgeValue($source, $attempt['patient'], $goal_set);
        $parsed_data['age'] = $age['age'];
        $parsed_data['age_title'] = $age['age_title'];

        // procedures
        $parsed_data['procedures'] = $this->getAMAttemptProcedures($source, $attempt, $attempt['patient']['airways']);

        return $parsed_data;
    } // end getParsedAMAttemptData


    /*
     * Returns an array of strings used to describe the patient's age --
     * 	if the airway managemnt source was from the patient care form
     * 	otherwise, just returns 'N/A'
     *
     * @param Int $source 1,2,3 the id of the airway management source
     * @param Array $patient our array of patient data
     * @param Fisdap_Entity_GoalSet $goal_set the goal set we'll use to describe patient ages
     *
     * @return Array $age_data -- contains the age description and the age title attribute
     */
    public function getAMAgeValue($source, $patient, $goal_set)
    {
        $age_data = array();

        // this came from the patient care form, gather a bit more info
        if ($source == 2 && isset($patient['age'])) {
            $age = ((intval($patient['age']) * 12) + (intval($patient['months']))) / 12;

            $age_data['age'] = $goal_set->getAgeFieldName($age, true);
            $age_data['age_title'] = $patient['age'] . " y ";
            $age_data['age_title'] .= ($patient['months']) ? $patient['months'] . " mo" : "";
        } else {
            $age_data['age'] = "N/A";
            $age_data['age_title'] = null;
        }

        return $age_data;
    } // end getAMAgeValue


    /*
     * Returns the HTML for the procedures cell
     * @param Boolean $value if true the attempt was successful
     *
     * @return String $img the image tag to be displayed
     */
    public function getAMAttemptProcedures($source, $attempt, $patient_airways = null)
    {
        $procedures = "";

        if ($source == 1) {
            // lab practice, the procedure is the lab practice definition name
            $procedures = $attempt['practice_item']['practice_definition']['name'];
        } else {
            if ($source == 2) {
                // patient care, the procedures should be each 'airway' done on this patient
                $airway_procedures = array();
                if ($patient_airways) {
                    foreach ($patient_airways as $airway_data) {
                        $airway_procedures[] = $airway_data['procedure']['type'];
                    }
                }
                $procedures = implode("<br />", array_unique($airway_procedures));
            } else {
                // quick add clinical, use the airway procedure that was attached to this airway management
                $procedures = $attempt['airway']['procedure']['type'];
            }
        }

        return $procedures;
    } // end getAMAttemptProcedures


    /*
     * Makes our life easy. Returns the framework/structure for our data (based on user selections)
     * This will help with sorting and null cases.
     *
     * @return Array $data_framework the framework we'll use for the rest of the report
     */
    public function getAMDataFramework()
    {
        $data_framework = array();

        // build a framework (this will make our list alphabetical without any more work)
        foreach ($this->getMultiStudentData() as $student_id => $nameOptions) {
            $data_framework[$student_id] = array();
            $data_framework[$student_id]['student_name'] = $nameOptions['first_last_combined'];
            $data_framework[$student_id]['managements'] = array();
        }

        return $data_framework;
    } // end getAMDataFramework

    public function parseETData($data_framework, $raw_data)
    {
        // ET data needs to be sorted. Sort it first by patient ID, then skill order.
        usort(
            $raw_data,
            function ($a, $b) {
                if ($a['shift']['start_datetime'] == $b['shift']['start_datetime']) {
                    if ($a['patient']['id'] == $b['patient']['id']) {
                        return $a['skill_order'] > $b['skill_order'];
                    } else {
                        return $a['patient']['id'] > $b['patient']['id'];
                    }
                } else {
                    return $a['shift']['start_datetime'] > $b['shift']['start_datetime'];
                }
            }
        );

        // begin by using our framework
        $parsed_data = $data_framework;

        // grab a goal set -- this is used JUST for age descriptions
        $goal_set = $this->getGoalSetForAirwayManagement();

        // we'll need this guy for displaying our shift summaries (initialize it here, so we only have to do it once)
        $summary_helper = new Fisdap_View_Helper_ShiftSummaryDisplayHelper();

        if ($raw_data) {

            // step through each of our airway management records
            foreach ($raw_data as $am_data) {

                // set up some parameters to make this process a bit cleaner
                $shift = $am_data['shift'];
                $shift_id = $shift['id'];
                $stu_id = $shift['student']['id'];

                $parsed_data[$stu_id]['student_name'] = $shift['student']['first_name'] . " " . $shift['student']['last_name'];

                // is this the first time we've seen this shift?
                if (!$parsed_data[$stu_id]['managements'][$shift_id]) {
                    $parsed_data[$stu_id]['managements'][$shift_id] = $this->initETShiftArray(
                        $shift,
                        $summary_helper
                    );
                }

                // If attempts is null, assume only one attempt
                if ($am_data['attempts'] < 1) {
                    $am_data['attempts'] = 1;
                }

                for ($i = 0; $i < $am_data['attempts']; $i++) {
                    if ($i+1 < $am_data['attempts']) {
                        // If there is more than one attempt, every one except the last one is a failure.
                        $parsed_am_data = $this->parseETAttemptData($am_data, $shift['start_datetime'], $goal_set, false);

                        array_push($parsed_data[$stu_id]['managements'][$shift_id]['attempts'], $parsed_am_data);
                    } else {
                        // The final attempt should look at the success value.
                        $parsed_am_data = $this->parseETAttemptData($am_data, $shift['start_datetime'], $goal_set, $am_data['success']);

                        array_push($parsed_data[$stu_id]['managements'][$shift_id]['attempts'], $parsed_am_data);
                    }
                }
            } // end foreach raw data
        } // end if raw data

        return $parsed_data;
    } // end parseETData

    public function parseETAttemptData($attempt, $shift_start_datetime, $goal_set, $success)
    {
        $parsed_data = array();
        $parsed_data['patient_id'] = $attempt['patient']['id'];
        $parsed_data['skill_order'] = $attempt['skill_order'];

        $success_bool = $attempt['success'];

        $success_img = $this->getAMSuccessImg($success);
        $parsed_data['success'] = $this->getAMSuccessDisplay(2, $attempt['performed_by'], $success_img);
        $parsed_data['success_boolean'] = $success_bool;
        $parsed_data['subject'] = $attempt['subject']['name'] . " - " . $attempt['subject']['type'];
        $parsed_data['attempt_date'] = $shift_start_datetime;

        // performed by and attempt title attribute
        if ($attempt['performed_by'] === true) {
            $parsed_data['performed'] = 1;
            $parsed_data['attempt_title'] = "";
        } else {
            $parsed_data['performed'] = 0;
            $parsed_data['attempt_title'] = "Airway management was observed";
        }


        // age
        $age = $this->getETAgeValue($attempt['patient'], $goal_set);
        $parsed_data['age'] = $age['age'];
        $parsed_data['age_title'] = $age['age_title'];

        // procedures
        $parsed_data['procedures'] = $attempt['procedure']['name'];

        return $parsed_data;
    } // end getParsedETAttemptData

    public function initETShiftArray($shift, $helper)
    {
        $am_shift_array = array();
        $shift_info_data_array = $this->getAMShiftInfoDataArray($shift);
        $summary_options = array('display_size' => 'large', 'sortable' => true);
        $am_shift_array['shift_info_display'] = $helper->shiftSummaryDisplayHelper(
            $shift_info_data_array,
            null,
            null,
            $summary_options
        );
        $am_shift_array['attempts'] = array();

        return $am_shift_array;
    }

    public function getETAgeValue($patient, $goal_set)
    {
        $age_data = array();

        // this came from the patient care form, gather a bit more info
        if (isset($patient['age'])) {
            $age = ((intval($patient['age']) * 12) + (intval($patient['months']))) / 12;

            $age_data['age'] = $goal_set->getAgeFieldName($age, true);
            $age_data['age_title'] = $patient['age'] . " y ";
            $age_data['age_title'] .= ($patient['months']) ? $patient['months'] . " mo" : "";
        } else {
            $age_data['age'] = "N/A";
            $age_data['age_title'] = null;
        }

        return $age_data;
    } // end getAMAgeValue

    /**
     * We'll need a default goal set for determining an age description.
     * This function will get the default paramedic goal set for the current program.
     * If there isn't one set, the function will return the national standards goal set (id 1)
     *
     * @throws Exception
     *
     * @return \Fisdap\Entity\GoalSet $goal_set the 'default' goal set to be used for this report
     */
    public function getGoalSetForAirwayManagement()
    {
        $goal_set = null;
        if (isset($this->config['selected-goalset']) && $this->config['selected-goalset'] != '') {
            $goal_set = \Fisdap\EntityUtils::getEntity('GoalSet', $this->config['selected-goalset']);
        }

        if (!$goal_set instanceof \Fisdap\Entity\GoalSet) {
            throw new Exception('No goalset selected, or could not load goalset. Cannot run report.');
        }

        return $goal_set;
    } // end getGoalSetForAirwayManagement


    /*
     * This function is called when we've come across a new shift while parsing our data.
     * Set up the framework for a shift's airway management attempts and grab the info display.
     *
     * @param Array $shift our arrayResult() array of shift data
     * @param Fisdap_View_Helper_ShiftSummaryDisplayHelper $helper the helper used to display the shift summaries
     *
     * @return Array $am_shift_array the data parsed into an array
     */
    public function initAMShiftArray($shift, $helper)
    {
        $am_shift_array = array();
        $shift_info_data_array = $this->getAMShiftInfoDataArray($shift);
        $summary_options = array('display_size' => 'large', 'sortable' => true);
        $am_shift_array['shift_info_display'] = $helper->shiftSummaryDisplayHelper(
            $shift_info_data_array,
            null,
            null,
            $summary_options
        );
        $am_shift_array['attempts'] = array();

        return $am_shift_array;
    } // end initAMShiftArray


    /*
     * Parses data for a particular shift into a format that our shift summary display view helper can understand
     * @param Int $shift_id the ID of the shift
     * @param Array $shift_data our arrayResult() array of shift data
     *
     * @return Array $shift_info_data the data parsed into an array that our view helper will use
     */
    public function getAMShiftInfoDataArray($shift_data)
    {
        $shift_info_data = array();
        $shift_info_data['shift_id'] = $shift_data['id'];
        $shift_info_data['start_datetime'] = $shift_data['start_datetime'];
        $shift_info_data['type'] = $shift_data['type'];
        $shift_info_data['duration'] = $shift_data['hours'];
        $shift_info_data['site_name'] = $shift_data['site']['name'];
        $shift_info_data['base_name'] = $shift_data['base']['name'];
        return $shift_info_data;
    } // end getAMShiftInfoDataArray


    /*
     * Returns the image that will be used in the success cell for an airway management attempt
     * @param Boolean $value if true the attempt was successful
     *
     * @return String $img the image tag to be displayed
     */
    public function getAMSuccessImg($value)
    {
        $img = "<img class='airway_management_success_img' src='/images/icons/";
        $img .= ($value === true) ? "scenario-valid.png'>" : "scenario-invalid.png'>";
        return $img;
    } // end getAMSuccessImg


    /*
     * Returns the HTML that will be used in the success cell for an airway management attempt.
     * If the attempt was performed by teh student, the image will be used.
     * If it was obeserved, an "N/A" will be displayed.
     * @param Boolean $value if true the attempt was successful
     *
     * @return String $img the HTML to be displayed
     */
    public function getAMSuccessDisplay($type, $performed, $success_img)
    {
        $html = (($type == 2 && !$performed) || !$performed) ? "<span class='airway_management_na_success'>N/A</span>" : $success_img;
        return $html;
    } // end getAMSuccessDisplay
} // end AirwayManagement class
