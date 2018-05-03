<?php
/**
 * Class Fisdap_Reports_GraduationRequirements
 * This is the Graduation Requirements Report class
 * Refer to Fisdap_Reports_Report for more documentation
 */
class Fisdap_Reports_GraduationRequirements extends Fisdap_Reports_Report
{
    public $header = '';

    public $footer = '';

    public $formComponents = array(
		'goalSetTable' => array(
            'title' => 'Select a goal set',
        ),
        'shiftInformationForm' => array(
            'title' => 'Select shift information',
			'options' => array(
				'pickAuditStatus' => TRUE
			)
        ),
        'alsSelector' => array(
            'title' => 'Select definitions',
            'options' => array(
                'selected' => array('als-type', 'fisdap')
            )
        ),
        'multistudentPicklist' => array(
            'title' => 'Select one or more student(s)',
            'options' =>  array(
                'mode' => 'multiple',
                'loadJSCSS' => TRUE,
                'loadStudents' => TRUE,
                'showTotal' => TRUE,
				'studentVersion' => TRUE,
                'includeAnon' => TRUE,
                'useSessionFilters' => TRUE,
                'sessionNamespace' => "ReportStudentFilter",
            ),
        )
	);
    
	
	public $styles = array("/css/library/Fisdap/Reports/graduation-requirements.css");

    // DEBUG
    //protected $logger;

	// Constructor
    public function __construct($report, $config = array()) {
		/* Initialize action controller here */
		parent::__construct($report, $config);
		
		// default to the same site types as indicated in skillstracker settings
		$site_types = array('clinical', 'field', 'lab');
		$program_settings = $program = \Fisdap\Entity\ProgramLegacy::getCurrentProgram()->program_settings;
		$chosen_types = array();
		foreach ($site_types as $type) {
			$property = "include_".$type."_in_mygoals";
			if ($program_settings->{$property}) {
				$chosen_types[] = "0-".ucfirst($type);
			}
		}
        $this->formComponents['shiftInformationForm']['options']['selected']['sites'] = $chosen_types;

        //default to subject types from skillstracker settings
        $subject_types = $program_settings->subject_types_in_mygoals;

        $chosen_subject_types = array();
        foreach ($subject_types as $subject){
            $chosen_subject_types[] = $subject;
        }
        $this->formComponents['shiftInformationForm']['options']['selected']['types'] = $chosen_subject_types;

        // DEBUG
        // get the logger
        //$this->logger = \Zend_Registry::get('logger');
	}
	
    /**
     * Run a query and any processing logic that produces the data contained in the report
     * Return a multidimensional array and it will be rendered as tables
     * OR return a string and it will be rendered as HTML
     * @return array
     */
    public function runReport() {
        // Set the mysql timeout higher
        \Fisdap\EntityUtils::getEntityManager()->getConnection()->exec( "SET SESSION wait_timeout = 100000" );
        \Zend_Registry::get('db')->query("SET SESSION wait_timeout = 100000");

        // DEBUG
        //$this->logger->debug('Memory usage GRR start: ' . round(memory_get_usage() / 1000 / 1000) . 'MB' );

		// get the form values situated
		$dataOptions['startDate'] = ($this->config['startDate']) ? new \DateTime($this->config['startDate']) : "";
		$dataOptions['endDate'] =  ($this->config['endDate']) ? new \DateTime($this->config['endDate']) : "";
		$dataOptions['subjectTypes'] = $this->getTypeIds();
		$dataOptions['shiftSites'] = $this->getSiteIds();
		$dataOptions['auditedOrAll'] = ($this->config['auditStatus'] == 'audited') ? 1 : 0;
        $dataOptions['alsType'] = $this->config['als-type'];
		
		// get the goalset
		$goalSetId = $this->config['selected-goalset'];
//        $goalSet = \Fisdap\EntityUtils::getEntity('GoalSet', $goalSetId);
        $goalSet = \Fisdap\EntityUtils::getRepository('GoalSet')->getGoalsForGoalSet($goalSetId);

		// loop through the students getting the data
		$sortableByLast = true;
		$students = $this->getMultiStudentData($sortableByLast);
		$goalsResults = array();
        foreach ($students as $student_id => $nameOptions) {
            if ($student_id > 0) { // add student only if student_id is valid
                $goals = new \Fisdap\Goals($student_id, $goalSet, $dataOptions, $nameOptions['first_last_combined']);
                //$this->logger->debug('Memory usage GRR after student ' . $student_id . ' goals obj: ' . round(memory_get_usage() / 1000 / 1000) . 'MB' );
                $goalsResults[$student_id] = $goals->getGoalsResults(null, true);
                unset($goals);
            }
            //$this->logger->debug('Memory usage GRR after student ' . $student_id . ' results compiled: ' . round(memory_get_usage() / 1000 / 1000) . 'MB' );
		}

		// ok, now organize the data into arrays we can use to make the tables
		// first get the goal categories
		$anyStudentsResults = current($goalsResults);
        $goalCategories = array_unique(array_keys($anyStudentsResults));

		// Sort the categories so they appear in the correct order (alphabetically)...
		sort($goalCategories);

		//Loop over each category and create a table for it
		foreach ($goalCategories as $goalCategory) {
           // var_dump($goalCategory);

            if($goalCategory == "Airway Management"){
                $this->getAirwayManagementTable($students, $goalsResults);
            }
            else {

                $title = $goalCategory;

                // set up the crazy table header
                $superHeaderRow = array(array("data" => "Student",
                    "class" => "superheader",
                    "rowspan" => 2));
                $subHeaderRow = array();
                foreach ($anyStudentsResults[$goalCategory] as $i => $goalResult) {
                    $catName = $goalResult->goal->def->name;

                    $superHeader = "<span class='superheader'>$catName</span><br>" .
                        "<span class='subheader'>goal: " . $goalResult->requirementDesc . "</span>";

					if ($goalResult->goal->def->use_op) {
						$superHeaderRow[] = array("data" => $superHeader, "colspan" => 2);
						$subHeaderRow[] = "O";
						$subHeaderRow[] = "P";
					} else {
						$superHeaderRow[] = array("data" => $superHeader, "rowspan" => 2, "class" => $title . "_superheader_row_cell");
					}
				}
				
				$superHeaderRow[] = array("data" => "Overall %",
					"class" => "superheader",
					"rowspan" => 2);

				// set up the table
				$table_data = array('title' => $title,
							'nullMsg' => "No skills found.",
							'head' => array(),
							'body' => array(),
						);
					
				if (!empty($superHeaderRow)) {
					$table_data['head'][] = $superHeaderRow;
				}
					
				if (!empty($subHeaderRow)) {
					$table_data['head'][] = $subHeaderRow;
				} else {
					foreach($table_data['head'][0] as $i => $headerRow) {
					$table_data['head'][0][$i]["rowspan"] = 1;
					//$headerRow['rowspan'] = 1;
					}
				}

                // Loop over each student for the given goal category

                $data = array();
                foreach ($goalsResults as $student_id => $studentGoalResult) {
                    // add the student's name
                    $dataRow = array(array("data" => $students[$student_id]['first_last_combined'],
                        "class" => "noAverage"));

                    // start a running total for this goal category
                    $totalRequired = 0;
                    $totalEarned = 0;

                    // add each section of this category
                    $sectionNum = 1;
                    foreach ($studentGoalResult[$goalCategory] as $goalResult) {
                        // add these skills to the running total
                        $totalRequired += $goalResult->goal->number_required;
                        $totalEarned += $goalResult->pointsTowardGoal;
                        $goalMet = $goalResult->met(true) ? "completed" : "";
                        $colClass = $sectionNum % 2 ? "evenCol" : "";

                        $observed = $goalResult->observedCountDesc;
                        $performed = $goalResult->performedCountDesc;

                        // note the gotcha with Airway Management
                        if (trim($goalResult->goal->def->short_name) == "Airway Management") {
                            $observed = "n/a";
                        }
						if ($goalResult->goal->def->use_op) {
							$dataRow[] = array("data" => $observed, "class" => "center $colClass");
							$dataRow[] = array("data" => $performed, "class" => "center $goalMet $colClass");
						} else {
							$dataRow[] = array("data" => $performed, "class" => "center $goalMet $colClass");
						}
                        $sectionNum++;
                    }

                    // add the overall percentage
                    $colClass = $sectionNum % 2 ? "evenCol" : "";
                    $overall = ($totalRequired == 0) ? 'n/a' : number_format($totalEarned / $totalRequired * 100, 1) . '%';
                    $dataRow[] = array("data" => $overall, "class" => "center $colClass");

                    // add the row
                    $table_data['body'][$goalResult->student_id] = $dataRow;
                }

            // add the footer to calculate averages, but only if there's more than one student
			if (count($students) > 1) {
				$footer = array(array("data" => "Averages:", "class" => "right"));
				foreach ($anyStudentsResults[$goalCategory] as $i => $goalResult) {
					if ($goalResult->goal->def->use_op) {
						$footer[] = array("data" => "O", "class" => "center");
						$footer[] = array("data" => "P", "class" => "center");
					} else {
						$footer[] = array("data" => "P", "class" => "center");
					}
				}
				$footer[] = array("data" => "%", "class" => "center percent");
				$table_data['foot']["average"] = $footer;
			}

			// add the table
			$this->data[] = array("type" => "table", "content" => $table_data);
            }
            //$this->logger->debug('Memory usage GRR after Goal Cat ' . $goalCategory . ': ' . round(memory_get_usage() / 1000 / 1000) . 'MB' );
        }
        unset($anyStudentsResults, $goalsResults, $goalCategories);

        //$this->logger->debug('Memory usage GRR at end of runReport: ' . round(memory_get_usage() / 1000 / 1000) . 'MB' );
    }

    public function getAirwayManagementTable($students, $goal_results)
    {
        $table_body = array();
        $am_goal = 0;

        // step through each of our students to build the rows of our single table
        foreach($students as $student_id => $student_display_name_options){

            $am_data = $goal_results[$student_id]['Airway Management'];
            $am_goal = $am_data['number_required'];

            $et_goal_reached_class = ($am_data['et_success_rate']['success_rate'] == 100) ? "completed" : "";
            $goal_reached_class = ($am_data['coa_success_rate']['success_rate'] == 100) ? "completed" : "";
            $attempts_goal_reached_class = ($am_data['attempts']['performed'] >= $am_goal) ? "completed" : "";

            $student_row = array();

            // student name
            $student_row[] = array("data" => $student_display_name_options['first_last_combined'], "class" => "noAverage");

            // total attempts
            $student_row[] = array("data" => $am_data['attempts']['performed'], "class" => $attempts_goal_reached_class . " center");
						
            // success rate over last 20 attempts
            $student_row[] = array("data" => $am_data['coa_success_rate']['success_rate'] . "%", "class" => $goal_reached_class. " center");

            //simulations
            $student_row[] = array("data" => $am_data['attempts']['sims'], "class" => "center");
			
			if($am_data['attempts']['performed'] > 0){
				 // et success rate over last 10 attempts
				$student_row[] = array("data" => $am_data['et_success_rate']['success_rate'] . "%", "class" => $et_goal_reached_class. " center");
			}
			else{
			 // et success rate over last 10 attempts
            $student_row[] = array("data" => "0%", "class" => $et_goal_reached_class. " center");
			}
            
			//total patient types
			$student_row[] = array("data" => $am_data['attempts']['neonate'], "class" => "center");
            $student_row[] = array("data" => $am_data['attempts']['infant'], "class" => "center");
            $student_row[] = array("data" => $am_data['attempts']['pediatric'], "class" => "center");
            $student_row[] = array("data" => $am_data['attempts']['adult'], "class" => "center");
            $student_row[] = array("data" => $am_data['attempts']['unknown'], "class" => "center");


            $table_body[] = $student_row;

        } // end foreach student

        // make the table and add it to the report
        $title = "Airway Management";
        $null_msg = "No airway management records found.";

        $super_header = array();
        $super_header[] = array("data" => "Student", "class" => "superheader", "rowspan" => 2);
        $super_header[] = array("data" => "Attempts<br><span class='am_subheader'>goal: " . $am_goal . "</span>", "class" => "superheader", "rowspan" => 2);
        $super_header[] = array("data" => "Success Rate<br><span class='am_subheader'>(over last 20 attempts)</span>", "class" => "superheader", "rowspan" => 2);
        $super_header[] = array("data" => "Simulations", "class" => "superheader", "rowspan" => 2);
		$super_header[] = array("data" => "ET Success Rate<br><span class='am_subheader'>(over last 10 attempts)</span>", "class" => "superheader", "rowspan" => 2);
        $super_header[] = array("data" => "Patient type", "class" => "superheader", "colspan" => 5);

        $sub_header = array();
        $sub_header[] = array("data" => "Neonate", "class" => 'subheader');
        $sub_header[] = array("data" => "Infant", "class" => 'subheader');
        $sub_header[] = array("data" => "Pediatric", "class" => 'subheader');
        $sub_header[] = array("data" => "Adult", "class" => 'subheader');
        $sub_header[] = array("data" => "Unknown", "class" => 'subheader');

        $data_table = array('title' => $title,'nullMsg' => $null_msg,'head' => array('0' => $super_header, '1' => $sub_header),'body' => $table_body);

        if(count($students) > 1) {
            $footer = array();
            $footer[] = array("data" => "Averages:", "class" => "right");
            $footer[] = array("data" => "", "class" => "center");
            $footer[] = array("data" => "", "class" => "center percent");
            $footer[] = array("data" => "", "class" => "center");
            $footer[] = array("data" => "", "class" => "center");
            $footer[] = array("data" => "", "class" => "center");
            $footer[] = array("data" => "", "class" => "center");
            $footer[] = array("data" => "", "class" => "center");
            $footer[] = array("data" => "%", "class" => "center");
            $data_table['foot']["average"] = $footer;
        }

        $note = "*Successful Airway Management performed before Aug 1, 2013 is NOT included.";
        $footer_note = array(array("data" => $note, "colspan" => 10));
        $data_table['foot']['notes'] = $footer_note;

        $this->data[] = array("type" => "table","content" => $data_table);

    }
	
	public function goalSetTableValidate($info) {
		  // make sure we have a goal set
		  $goalSet = $this->config["selected-goalset"];
		  if ($goalSet <= 0) {
				$this->valid = false;
				$this->errors["selected-goalset"][] = "Please select a goal set.";
		  }
	}


    /**
	  * Return a short label/description of the report using report configuration
	  * Useful in listing saved Report Configurations as a saved report history
	  * Override this if your report should display something different!
	  */
	public function getShortConfigLabel() {
		  //var_export($this->config);
		  // get the student name or # of students

		  $studentsLabel = '';

		  // if we're in single student mode
		  if (isset($this->config['student']) &&
			  is_numeric($this->config['student']) &&
			  $this->config['picklist_mode'] != 'multiple') {
			   $student = \Fisdap\EntityUtils::getEntity('StudentLegacy', $this->config['student']);
			   if ($student) {
					$studentsLabel = $student->user->getName();
			   }
		  } else if (isset($this->config['multistudent_picklist_selected'])) {
			   $students = explode(",", $this->config['multistudent_picklist_selected']);
			   if (count($students) > 1) {
					$studentsLabel = count($students) . ' students';
			   } else {
					$student = \Fisdap\EntityUtils::getEntity('StudentLegacy', $students[0]);
					$studentsLabel = $student->user->getName();
			   }

			   if ($this->config['anonymous'] == 1) { $studentsLabel .= ", anon."; }
		  }

          // get the goalset
          $goalSetId = $this->config['selected-goalset'];
          $goalSet = \Fisdap\EntityUtils::getEntity('GoalSet', $goalSetId);

          $goalsLabel = '';
          if(strlen(trim($goalSet->name))>0) {
              $goalsLabel = ", " . $goalSet->name;
          }

	 	  // return the label
		  return $studentsLabel . $goalsLabel;
    }
}
