<?php

/**
 * Class Fisdap_Reports_Eureka
 * This is the Eureka Report! This guy is a little bit different than some of the others (data doesn't really end up in a table)
 *
 * @author Hammer :)
 * 
 */
class Fisdap_Reports_Eureka extends Fisdap_Reports_Report
{
    public $header = '<div class="eureka_report_legend"></div>';
    public $footer = '';
	public $combined_procedure_ids = '';

    public $formComponents = array(
		'Reports_Form_EurekaReportOptions' => array(
            'title' => 'Report options',
        ),
        'shiftInformationForm' => array(
            'title' => 'Select shift information',
        ),
        'multistudentPicklist' => array(
            'title' => 'Select one or more student(s)',
            'options' =>  array(
                'mode' => 'multiple',
                'loadJSCSS' => TRUE,
                'loadStudents' => TRUE,
                'showTotal' => TRUE,
				'studentVersion' => TRUE,
                'useSessionFilters' => TRUE,
                'sessionNamespace' => "ReportStudentFilter",
            ),
        )
    );
	
	
    /**
     * Run a query and any processing logic that produces the data contained in the report
     * This function returns a multidementional array (with type: Eureka so Report.php will treat it differently)
     * and the HTML to be rendered is the "content" value.
     */
    public function runReport()
	{
		// these are the only types where we measure success
		$procedure_types = array("airway", "iv", "other");
		
		// get the raw data from a few giant queries (one for each procedure type: 3 max)
		$raw_data = $this->getRawEurekaData($procedure_types);
		
		// build a data framework to handle sorting and null cases
		$data_framework = array();
		$selected_procedure_names = $this->getSortedSelectedProcedureNames();
		$single_graph = ($this->config['eureka_combine_graphs']) ? true : false;
		
		// use our selected students to build the data framework
		foreach($this->getMultiStudentData() as $student_id => $nameOptions)
		{
			$data_framework[$student_id] = array();
			$data_framework[$student_id]['student_name'] = $nameOptions['first_last_combined'];
			$data_framework[$student_id]['procedures'] = array();
			
			if($single_graph){
				// set up a single array for each selected procedured (combined for the single graph)
				$combined_pro_id = "";
				$combined_pro_names = "";
				$pro_count = 0;
				
				foreach($selected_procedure_names as $pro_id => $procedure_name){
					
					$combined_pro_id .= ($pro_count == 0) ? "" : "_";
					$combined_pro_names .= ($pro_count == 0) ? "" : ", ";
					
					$combined_pro_id .= $pro_id;
					$combined_pro_names .= $procedure_name;
					$pro_count++;
					
				}
				
				$data_framework[$student_id]['procedures'][$combined_pro_id] = array();
				$data_framework[$student_id]['procedures'][$combined_pro_id]['procedure_name'] = $combined_pro_names;
				$data_framework[$student_id]['procedures'][$combined_pro_id]['attempts'] = array();
				$this->combined_procedure_ids = $combined_pro_id;
			}
			else {
				// set up an array for each procedure
				foreach($selected_procedure_names as $pro_id => $procedure_name){
					$data_framework[$student_id]['procedures'][$pro_id] = array();
					$data_framework[$student_id]['procedures'][$pro_id]['procedure_name'] = $procedure_name;
					$data_framework[$student_id]['procedures'][$pro_id]['attempts'] = array();
				}
			}
			
		} // end for each student
		
		// now that we've got our raw data, let's go through and parse it in a way that will be easy to generate HTML for
		$parsed_data = $this->parseEurekaData($procedure_types, $raw_data, $data_framework);
		
		// initialize our data: give it a type "eureka" so it's treated differently than traditional reports
		// then build the HTML to be rendered using getEurekaReportHTML()
		$this->data['eureka'] = array("type" => "eureka", "content" => $this->getEurekaReportHTML($parsed_data));
		
    } // end runReport()
	
	
	
	
	
	/**
	 * ---------------------------------------------------------------------------------------------------------------------------------------
	 * 			The following funcions are for getting the eureka data and parsing it into a readable array
	 * ---------------------------------------------------------------------------------------------------------------------------------------
	*/
	
	
	/*
	 * Run the 3 queries (depending on use selection) and put the results into a "raw data" array
	 * This is just the reutnred array from getArrayResult()
	 *
	 * @param Array $procedure_types an array of the procedure types we'll be using ("airway", "iv", "other")
	 *
	 * @return Array $raw_data the raw data returned from our repo
	 */
	public function getRawEurekaData($procedure_types)
	{
		// set up parameters to make our function call a bit more read-able
		$selected_skills = $this->parseSkillIds();
		$site_ids = $this->getSiteIds();
		$patient_type_ids = $this->getTypeIds();
		$student_ids = explode(',', $this->config['multistudent_picklist_selected']);

		$repo = \Fisdap\EntityUtils::getRepository('Report');
		$raw_data = array();
		
		// run this query for each of our procedure types (happens at max 3 times)
		foreach($procedure_types as $pro_type){
			if(!empty($selected_skills[$pro_type])){
				$raw_data[$pro_type] = $repo->getEurekaProcedureData($pro_type,
																	 $selected_skills[$pro_type],
																	 $site_ids,
																	 $patient_type_ids,
																	 $student_ids,
																	 $this->config['startDate'],
																	 $this->config['endDate']);
			}
		}
		
		return $raw_data;
		
	} // end getRawEurekaData()
	
	
	
	/*
	 * Parse the eureka data. Take the messy raw data array and turn it into a readable associative array using our "data_framework"
	 *
	 * @param Array $procedure_types an array of the procedure types we'll be using ("airway", "iv", "other")
	 * @param Array $raw_data the raw data returned from our queries (keyed by procedure type)
	 * @param Array $data_framework the data framework we built to handle null cases, already has our selected students and selected procedures
	 *
	 * @return Array $parsed_data the array that will be used to build the HTML
	 */
	public function parseEurekaData($procedure_types, $raw_data, $data_framework)
	{
		$parsed_data = $data_framework;
		$single_graph = ($this->config['eureka_combine_graphs']) ? true : false;
		
		// the view helper will help with displaying shift info
		$shift_summary_display_helper = new Fisdap_View_Helper_ShiftSummaryDisplayHelper();
		
		// step through each of our possible procedure types (airway, iv, other)
		foreach($procedure_types as $pro_type){
			
			// did we even get any results for this type?
			if($raw_data[$pro_type]){
				
				foreach($raw_data[$pro_type] as $data){
					
					// throw these into some variables since this array could get big and ugly
					$student_id = $data['student']['id'];
					$pro_id = ($single_graph) ? $this->combined_procedure_ids : $data['procedure']['id'];
					$shift_id = $data['shift']['id'];
					
					// is this the first time we've seen this particular shift for this procedure for this student? Initialize this array so we can start tracking attempts.
					if(empty($parsed_data[$student_id]['procedures'][$pro_id]['attempts'][$shift_id])){
						$shift_info_display = $shift_summary_display_helper->shiftSummaryDisplayHelper($this->getShiftInfoDataArray($shift_id, $data['shift']));
						
						$parsed_data[$student_id]['procedures'][$pro_id]['attempts'][$shift_id] = array();
						$parsed_data[$student_id]['procedures'][$pro_id]['attempts'][$shift_id]['shift_info_display'] = $shift_info_display;
						$parsed_data[$student_id]['procedures'][$pro_id]['attempts'][$shift_id]['shift_start_datetime'] = $data['shift']['start_datetime'];
						$parsed_data[$student_id]['procedures'][$pro_id]['attempts'][$shift_id]['shift_attempts'] = array();
						
					}
					
					// count up the 'attempts' field for this procedure. mark as unsuccessful
					if($data['attempts'] > 1){
						for($i = 1; $i < $data['attempts']; $i++){
							$key = 'attempt_' . $i . '_' . $data['id'];
							$parsed_data[$student_id]['procedures'][$pro_id]['attempts'][$shift_id]['shift_attempts'][$key] = array();
							$parsed_data[$student_id]['procedures'][$pro_id]['attempts'][$shift_id]['shift_attempts'][$key]['success'] = 0;
							$parsed_data[$student_id]['procedures'][$pro_id]['attempts'][$shift_id]['shift_attempts'][$key]['pro_name'] = $data['procedure']['name'];
						}
					}
					
					// now mark this attempt as successful/failure	
					$parsed_data[$student_id]['procedures'][$pro_id]['attempts'][$shift_id]['shift_attempts'][$data['id']] = array();
					$parsed_data[$student_id]['procedures'][$pro_id]['attempts'][$shift_id]['shift_attempts'][$data['id']]['success'] = $data['success'];
					$parsed_data[$student_id]['procedures'][$pro_id]['attempts'][$shift_id]['shift_attempts'][$data['id']]['pro_name'] = $data['procedure']['name'];
					
					
				} // end foreach data for this procedure type
			} // end if there was data for this procedure type
			
		} // end foreach procedure type possbile
		
		return $parsed_data;
		
	} // end parseEurekaData()
	
	
	
	/*
	 * Parses data for a particular shift into a format that our shift summary display view helper can understand
	 * @param Int $shift_id the ID of the shift
	 * @param Array $shift_data our arrayResult() array of shift data
	 * 
	 * @return Array $shift_info_data the data parsed into an array that our view helper will use
	 */
	public function getShiftInfoDataArray($shift_id, $shift_data)
	{
		$shift_info_data = array();
		$shift_info_data['shift_id'] = $shift_id;
		$shift_info_data['start_datetime'] = $shift_data['start_datetime'];
		$shift_info_data['type'] = $shift_data['type'];
		$shift_info_data['duration'] = $shift_data['hours'];
		$shift_info_data['site_name'] = $shift_data['site']['name'];
		$shift_info_data['base_name'] = $shift_data['base']['name'];
		
		/*
		$shift_info_data['instructors'] = $shift_entity->getInstructorList();
		$shift_info_data['preceptors'] = $shift_entity->getPreceptorList();
		*/
		
		return $shift_info_data;
		
	} // end getShiftInfoDataArray()
	
	
	
	/*
	 * Takes data from chosen and puts it into a usable format
	 * These procedures have their type has a prefix, so we'll need to get creatative to get their actual ID
	 * 
	 * @return Array procedure types (3 possible: Airway, IV, Other) with an array of proc. ids
	 */
	public function parseSkillIds()
	{
		$skills = $this->config['eureka_skills'];
		$selected_skills = array("airway" => array(), "iv" => array(), "other" => array());
		
		if($skills){
			foreach($skills as $skill_form_value){
				$skill_pieces = explode("procedure_", $skill_form_value);
				$type = $skill_pieces[0];
				$selected_skills[$type][] = $skill_pieces[1];
			}
		}
		
		return $selected_skills;
	
	} // end parseSkillIds()
	
	
	/*
	 * Based on the users selections, we'll sort a list of procedure names
	 * This will do all of the sorting we need for the eventual output (sorted by students, then by procedure name)
	 * 
	 * @return Array $selected_procedure_names sorted alphabetically
	 */
	public function getSortedSelectedProcedureNames()
	{
		$selected_skills = $this->parseSkillIds();
		$selected_procedure_names = array();
		
		// assume we have something selected
		foreach($selected_skills as $type => $pro_ids){
			
			// is there anything selected for this type (airway, iv, other) ?
			if($pro_ids){
				foreach($pro_ids as $id){
					if($type == "other"){$procedure_entity_name = "OtherProcedure";}
					else if($type == "iv"){$procedure_entity_name = "IvProcedure";}
					else {$procedure_entity_name = "AirwayProcedure";}
					
					$entity = \Fisdap\EntityUtils::getEntity($procedure_entity_name, $id);
					$selected_procedure_names[$id] = $entity->name;
				}
			}
			
		}
		
		asort($selected_procedure_names);	
		return $selected_procedure_names;
		
	} // end getSortedSelectedProcedureNames()
	
	
	
	
	
	/**
	 * ---------------------------------------------------------------------------------------------------------------------------------------
	 * 			The following funcions are for actually stepping through the data and building the HTML string
	 * 			This is quite different from the other reports, since most of them handle this step in Report.php
	 * ---------------------------------------------------------------------------------------------------------------------------------------
	 */
	
	
	/*
	 * Turns the eureka data into an HTML string to be rendered
	 * @param array $data_by_student the data generated by the eureka report form
	 * 
	 * @return String $output the HTML string to be returned to Report.php and eventually rendered
	 */
	public function getEurekaReportHTML($data_by_student)
	{
		$output = "";
		
		// data is organized by student/procedure, step through each student and render their data
		foreach($data_by_student as $student_id => $student_data){
			
			$student_name = $student_data['student_name'];
			 
			$output .= "<div class='eureka_student_report_wrapper'>";
			$output .= 		"<h2 class='section-header'>" . $student_name . "</h2>";
			 
			foreach($student_data['procedures'] as $pro_id => $pro_data){
				
				$attempts_table = $this->renderAttemptsTable($pro_data['attempts']);
				$pro_name = $pro_data['procedure_name'];
				$eureka_id = $student_id . "_" . $pro_id;
				
				$output .= 	"<h3>" . $pro_name . " attempts</h3>";
				$output .= 	"<div class='eureka_skill_wrapper'>";
				$output .= 		$attempts_table['html'];
				$output .=		$this->renderGraph($student_name, $eureka_id, $pro_name, $attempts_table['attempts'], $attempts_table['attempt_dates']);
				$output .= 		"<div class='clear'></div>";
				$output .= 	"</div>";
				
				$output .= 	"<div class='clear'></div>";
				
			} // end foreach student's procedures
			 
			$output .= "</div>"; // close .eureka_student_report_wrapper
			$output .= "<div class='clear'></div>";
			
		} // end foreach student
		
		return $output;
		
	} // end renderReport()
	
	
	
	/*
	 * Using the Fisdap eureka graph view helper, this function returns an HTML string containing a jQuery plugin-ready eureka graph
	 * 
	 * @param String $student_name the first and last name of the graph's student
	 * @param String $eureka_id a unique ID (made up of a student_id and attempt_id) so more than one eureka graph can be on a single page
	 * @param String $pro_name the name of the procedure (i.e. IV, Blood Draw, Manual ventilation, etc)
	 * @param Array $attempts an array of 1s and 0s (to represent - in order - successes/failures for graph)
	 * @param Array $dates an array of DateTime objects. These dates are in the same order as $attempts (eureka graph view helper needs this)
	 * 
	 * @return String $output the HTML string to be returned to Report.php and eventually rendered
	*/
	public function renderGraph($student_name, $eureka_id, $pro_name, $attempts, $dates)
	{
		// need to create a new instance of this object for EVERY graph we want to generate
		$eureka_helper = new Fisdap_View_Helper_EurekaGraph();
		
		// some things will look a little different if we have no attempts
		$legend = false;
		$no_attempt_class = "no_eureka_attempt_table";
		$msg = "No attempts found";
		
		// we do haev attempts, set up some variables
		if(count($attempts) > 0){
			$legend = true;
			$no_attempt_class = "";
			$msg = $student_name . " " . $pro_name . " Eureka";
		}
		
		// output our graph/wrapper. This is not the graph itself, but builds HTML elements so the jQuery plugin can be called
		// add a throbber so these can be generated after the HTML is appended to the report page
		$output  = "<div class='eureka_graph_wrapper " . $no_attempt_class . "'>";
		$output .=		"<img src='/images/throbber_small.gif' class='loading_eureka_graph_throbber'>";
		$output .=		$eureka_helper->eurekaGraph($attempts, $dates, $this->config['eureka_goal'], $this->config['eureka_window'], $eureka_id, $legend, $msg);
		$output .=  "</div>"; // close eureka_graph_wrapper
		
		return $output;
		
	} // end renderGraph()
	
	
	
	/*
	 * Builds the HTML for the attempts table. This table appears to the right of each eureka graph (if there is at least 1 attempt)
	 * The table uses the standard shift summary display and pairs it will cell(s) containing a checkmark or x for a successful/failed attempt.
	 * 
	 * @param Array $shift_attempts an array of attempts keyed by shift_id
	 * 
	 * @return Array containing HTML to be rendered and two arrays:
	 * 		($attempts - containing 1s and 0s, and $dates - containing corresponding DateTime objects) to be used for eureka graphs later
	 * 		If there are no shift_attempts, the function quits immediately
	 */
	public function renderAttemptsTable($shift_attempts)
	{
		// quit if we don't have attempts
		if(!$shift_attempts){return;}
		
		// initialize some values
		$attempt_count = 1;
		$attempts_array = array();
		$attempt_dates = array();
		
		// set up some HTML for the table, include a header row
		$output  = "<div class='eureka_table_wrapper'>";
		$output .=	"<div class='fixed_eureka_attempts_thead'>";
		$output .=		"<table class='no_data_table'><thead><tr><th class='eureka_shift_info_cell'>Shift</th><th class='eureka_success_cell'>Attempts</th></tr></thead></table>";
		$output .=	"</div>";
		$output .= 	"<div class='eureka_attempts_table_wrapper'>";
		$output .=		"<table class='eureka_attempts_table no_data_table'>";
		
		// step through each attempt (grouped by shift)
		foreach($shift_attempts as $shift_id => $attempt_data){
			
			// count up the number of attempts for this shift
			$count = count($attempt_data['shift_attempts']);
				
			$output .= 		"<tr class='eureka_row'>";
			$output .=			$this->getShiftInfoCell($shift_id, $count, $attempt_data['shift_info_display']);
			$output .= 		($count != 1) ? "</tr>" : "";
			
			// go through each attempt
			foreach($attempt_data['shift_attempts'] as $attempt_id => $this_attempt_data){
				
				$success = $this_attempt_data['success'];
				$pro_name = $this_attempt_data['pro_name'];
				
				$output .= 	($count != 1) ? "<tr class='eureka_row'>" : "";
				$output .=		$this->getAttemptCell($attempt_count, $shift_id, $success, $attempt_data['shift_start_datetime'], $pro_name);
				$output .= 	"</tr>"; // close .eureka_row
				 
				// keep track of these for eureka
				$attempts_array[] = ($success) ? "1" : "0";
				$attempt_dates[] = $attempt_data['shift_start_datetime'];
				$attempt_count++;
				
			} // end foreach attempt for this shift
			
		} // end foreach $shift_attempts
		
		$output .= 		"</table>";  // close .eureka_attempts_table
		$output .= 	"</div>"; 		 // close .eureka_attempts_table_wrapper
		$output .= "</div>"; 			 // close .eureka_table_wrapper
		
		// return the html for rendering, the array of 1s and 0s and the array of attempt dates (for the eureka graph)
		return array("html" => $output, "attempts" => $attempts_array, "attempt_dates" => $attempt_dates);
	
	} // end renderAttemptsTable()
	
	
	
	/*
	 * Gets the HTML for a single attempt cell to be used in the attempt table.
	 * 
	 * @param Int $attempt_count the total number of attempts
	 * @param Int $shift_id the ID of the shift this attempt belongs to
	 * @param Boolean $success true if the attempt was successful, false if the attempt was unsuccessful
	 * @param DateTime $shift_start the start DateTime obejct of the shift (that the attempt came from)
	 *
	 * @return String $output the HTML to be rendered
	 */
	public function getAttemptCell($attempt_count, $shift_id, $success, $shift_start, $pro_name)
	{
		$output  = "<td data-attemptNumber='" . $attempt_count . "' class='eureka_success_cell' data-successfor='" . $shift_id ."'";
		$output .=	"title='" . $this->getAttemptTdTitle($success, $shift_start, $pro_name) . "'>";
		$output .=	($success) ? "<img src='/images/icons/scenario-valid.png' class='eureka_attempts_checkmark'>" : "<img src='/images/icons/scenario-invalid.png' class='eureka_attempts_x'>";
		$output .= "</td>";
		
		return $output;
		
	} // end getAttemptCell()
	
	
	
	/*
	 * Gets the HTML for the shift info cell to be used in the attempt table.
	 *
	 * @param Int $shift_id the ID of the shift
	 * @param Int $count the number of attempts for this shift (used to determine rowspan)
	 * @param String $shift_info_html the HTML generated from the shift summary view helper
	 *
	 * @return String $output the HTML to be rendered
	 */
	public function getShiftInfoCell($shift_id, $count, $shift_info_html)
	{
		$rowspan = ($count == 1) ? $count : $count+1;
		$output  = "<td class='eureka_shift_info_cell' data-shiftid='" . $shift_id . "' rowspan='" . $rowspan . "'>";
		$output .=	$shift_info_html;
		$output .= "</td>";
		
		return $output;
		
	} // end getShiftInfoCell()
	
	
	
	/*
	 * Gets the value for the title attribute on the attempt TD element
	 * 
	 * @param Boolean $success true if the attempt was successful, false if the attempt was unsuccessful
	 * @param DateTime $shift_start the start DateTime obejct of the shift (that the attempt came from)
	 *
	 * @return String $title the title for the attempt table cell
	 */
	public function getAttemptTdTitle($success, $shift_date, $pro_name)
	{
		// after the eureka graph jQuery plugin is called, this attribute may have "Eureka reached!" prepended.
		$single_graph = ($this->config['eureka_combine_graphs']) ? true : false;
		$title  = ($success) ? "Successful " : "Unsuccessful ";
		$title .= ($single_graph) ? $pro_name . " " : "";
		$title .= "attempt on ";
		$title .= $shift_date->format('M j, Y');
		
		return $title;
		
	} // end getAttemptTdTitle()
	
	
	
} // end Eureka()
