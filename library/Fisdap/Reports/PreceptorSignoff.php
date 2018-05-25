<?php

/**
 * Class Fisdap_Reports_PreceptorSignoff
 * This is the preceptor signoff Report! This guy is a little bit different than some of the others
 * (data doesn't really end up in a table) - very similar to the eureka report
 *
 * @author Hammer :)
 * 
 */
class Fisdap_Reports_PreceptorSignoff extends Fisdap_Reports_Report
{
    public $header = '';
    public $footer = '';
	public $combined_procedure_ids = '';

    public $formComponents = array(
		'Reports_Form_PreceptorSignoffReportOptions' => array(
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
		// get the raw data from a few giant queries (one for each procedure type: 3 max)
		$raw_data = $this->getRawPreceptorSignoffData();
		
		// build a data framework to handle sorting and null cases
		$data_framework = array();
		$selected_eval_names = $this->getSortedSelectedEvalTypeNames();
		
		// use our selected students to build the data framework
		foreach($this->getMultiStudentData(TRUE) as $student_id => $nameOptions)
		{
			$data_framework[$student_id] = array();
			$data_framework[$student_id]['student_name'] = $nameOptions['first_last_combined'];
			$data_framework[$student_id]['data'] = array();
			
			// set up an array for each evaluation type
			foreach($selected_eval_names as $type_id => $name){
				$data_framework[$student_id]['data'][$type_id] = array();
				$data_framework[$student_id]['data'][$type_id]['eval_name'] = $name;
				$data_framework[$student_id]['data'][$type_id]['ratings'] = array();
			}
			
		} // end for each student
		
		// now that we've got our raw data, let's go through and parse it in a way that will be easy to generate HTML for
		$parsed_data = $this->parsePreceptorSignoffData($raw_data, $data_framework);
		
		// initialize our data: give it a type "eureka" so it's treated differently than traditional reports
		// then build the HTML to be rendered using getPreceptorSignoffReportHTML()
		$this->data['preceptor_signoff'] = array("type" => "eureka", "content" => $this->getPreceptorSignoffReportHTML($parsed_data));
		
    } // end runReport()
	
	
	
	
	
	/**
	 * ---------------------------------------------------------------------------------------------------------------------------------------
	 * 			The following funcions are for getting the eureka data and parsing it into a readable array
	 * ---------------------------------------------------------------------------------------------------------------------------------------
	*/
	
	
	/*
	 * Run just 1 query and put the results into a "raw data" array
	 * This is just the reutnred array from getArrayResult()
	 * @return Array $raw_data the raw data returned from our repo
	 */
	public function getRawPreceptorSignoffData()
	{
		// set up parameters to make our function call a bit more read-able
		$site_ids = $this->getSiteIds();
		$student_ids = $this->config['multistudent_picklist_selected'];
		$repo = \Fisdap\EntityUtils::getRepository('Report');
		
		$raw_data = $repo->getPreceptorSignOffData($this->config['preceptor_signoff_evaluation_types'],
												$site_ids,
												$student_ids,
												$this->config['startDate'],
												$this->config['endDate']);
				
		return $raw_data;
		
	} // end getRawEurekaData()
	
	
	
	/*
	 * Parse the data. Take the messy raw data array and turn it into a readable associative array using our "data_framework"
	 * Our framework is already sorted by student name/evaluation type name
	 *
	 * @param Array $raw_data the raw data returned from our queries (keyed by evaluation type)
	 * @param Array $data_framework the data framework we built to handle null cases, already has our selected students and selected eval types
	 *
	 * @return Array $parsed_data the array that will be used to build the HTML
	 */
	public function parsePreceptorSignoffData($raw_data, $data_framework)
	{
		$parsed_data = $data_framework;
		
		// the view helper will help with displaying shift info
		$shift_summary_display_helper = new Fisdap_View_Helper_ShiftSummaryDisplayHelper();
			
		// did we even get any results for this evaluation type?
		foreach($raw_data as $data){
			
			// throw these into some variables since this array could get big and ugly
			$rating = $data;
			$signoff = $rating['signoff'];
			$student_id = $signoff['student']['id'];
			$eval_id = $rating['type']['id'];
			$shift = $signoff['run']['shift'];
			$shift_id = $shift['id'];
			
			// is this the first time we've seen this particular shift for this eval type for this student? Initialize this array so we can start tracking ratings.
			if(empty($parsed_data[$student_id]['data'][$eval_id]['ratings'][$shift_id])){
				$shift_info_display = $shift_summary_display_helper->shiftSummaryDisplayHelper($this->getShiftInfoDataArray($shift_id, $shift));
				
				$parsed_data[$student_id]['data'][$eval_id]['ratings'][$shift_id] = array();
				$parsed_data[$student_id]['data'][$eval_id]['ratings'][$shift_id]['shift_info_display'] = $shift_info_display;
				$parsed_data[$student_id]['data'][$eval_id]['ratings'][$shift_id]['shift_start_datetime'] = $shift['start_datetime'];
				$parsed_data[$student_id]['data'][$eval_id]['ratings'][$shift_id]['student_ratings'] = array();
				$parsed_data[$student_id]['data'][$eval_id]['ratings'][$shift_id]['preceptor_ratings'] = array();
			}
			
			// now set this rating based on the rater type
			if($rating['rater_type']['id'] == 2){
				// preceptor rating
				$parsed_data[$student_id]['data'][$eval_id]['ratings'][$shift_id]['preceptor_ratings'][] = $rating['value'];	
			}
			else {
				// student rating
				$parsed_data[$student_id]['data'][$eval_id]['ratings'][$shift_id]['student_ratings'][] = $rating['value'];
			}
			
		} // end foreach data
		
		return $parsed_data;
		
	} // end parsePreceptorSignoffData()
	
	
	
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
	 * Based on the users selections, we'll sort a list of evaluation type names
	 * This will do all of the sorting we need for the eventual output (sorted by students, then by eval name)
	 * 
	 * @return Array $selected_type_names sorted alphabetically
	 */
	public function getSortedSelectedEvalTypeNames()
	{
		$selected_type_names = array();
		
		// assume we have something selected
		foreach($this->config['preceptor_signoff_evaluation_types'] as $type_id){
			$entity = \Fisdap\EntityUtils::getEntity("PreceptorRatingType", $type_id);
			$selected_type_names[$type_id] = $entity->name;
		}
		
		asort($selected_type_names);	
		return $selected_type_names;
		
	} // end getSortedSelectedEvalTypeNames()
	
	
	
	
	
	/**
	 * ---------------------------------------------------------------------------------------------------------------------------------------
	 * 			The following funcions are for actually stepping through the data and building the HTML string
	 * 			This is quite different from the other reports, since most of them handle this step in Report.php
	 * ---------------------------------------------------------------------------------------------------------------------------------------
	 */
	
	
	/*
	 * Turns the preceptor signoff data into an HTML string to be rendered
	 * @param array $data_by_student the data generated by the preceptor signoff report form
	 * 
	 * @return String $output the HTML string to be returned to Report.php and eventually rendered
	 */
	public function getPreceptorSignoffReportHTML($data_by_student)
	{
		$output = "";
		
		// data is organized by student/eval type, step through each student and render their data
		foreach($data_by_student as $student_id => $student_data){
			
			$student_name = $student_data['student_name'];
			 
			$output .= "<div class='eureka_student_report_wrapper preceptor_signoff_eureka_report_wrapper'>";
			$output .= 		"<h2 class='section-header preceptor_signoff_eureka_report_wrapper'>" . $student_name . "</h2>";
			 
			foreach($student_data['data'] as $eval_id => $rating_data){
				
				$ratings_table = $this->renderRatingsTable($rating_data['ratings']);
				$eval_name = $rating_data['eval_name'];
				$student_eureka_id = "student_" . $student_id . "_" . $eval_id;
				$preceptor_eureka_id = "preceptor_" . $student_id . "_" . $eval_id;
				
				$preceptor_ratings = (count($ratings_table['preceptor_ratings']) > 0) ? true : false;
				$student_ratings = (count($ratings_table['student_ratings']) > 0) ? true : false;
				
				$output .= 	"<h3>" . $eval_name . " ratings</h3>";
				$output .= 	"<div class='eureka_skill_wrapper'>";
				$output .= 		$ratings_table['html'];
				$output .=		"<div class='signoffs_graphs_wrapper'>";
				$output .=			$this->renderGraph($student_name, $student_eureka_id, $eval_name, $ratings_table['student_ratings'], $ratings_table['student_ratings_dates'], $student_ratings, $preceptor_ratings);
				$output .=			$this->renderGraph($student_name, $preceptor_eureka_id, $eval_name, $ratings_table['preceptor_ratings'], $ratings_table['preceptor_ratings_dates'], $student_ratings, $preceptor_ratings);
				$output .= 			"<div class='clear'></div>";
				$output .= 		"</div>";
				$output .= 		"<div class='clear'></div>";
				$output .= 	"</div>";
				
				$output .= 	"<div class='clear'></div>";
				
			} // end foreach student's ratings
			 
			$output .= "</div>"; // close .eureka_student_report_wrapper
			$output .= "<div class='clear'></div>";
			
		} // end foreach student
		
		return $output;
		
	} // end renderReport()
	
	
	
	/*
	 * Using the Fisdap eureka graph view helper, this function returns an HTML string containing a jQuery plugin-ready eureka graph
	 * 
	 * @param String $student_name the first and last name of the graph's student
	 * @param String $eureka_id a unique ID (made up of a 'student_' or 'preceptor_' student_id and eval_type_id) so more than one eureka graph can be on a single page
	 * @param String $eval_name the name of the evaluation type (i.e. Physical Exam, Team Leadership, etc)
	 * @param Array $ratings an array of 1s and 0s (to represent - in order - successes/failures for graph)
	 * @param Array $dates an array of DateTime objects. These dates are in the same order as $ratings (eureka graph view helper needs this)
	 * @param Boolean $has_student_ratings true if there is at least 1 student rating (need to know for including legends and misc classes)
	 * @param Boolean $has_preceptor_ratings true if there is at least 1 preceptor rating (need to know for including legends and misc classes)
	 * 
	 * @return String $output the HTML string to be returned to Report.php and eventually rendered
	*/
	public function renderGraph($student_name, $eureka_id, $eval_name, $ratings, $dates, $has_student_ratings, $has_preceptor_ratings)
	{
		$rater_type = (strpos($eureka_id, 'student') !== FALSE) ? "student" : "preceptor";
		
		// need to create a new instance of this object for EVERY graph we want to generate
		$eureka_helper = new Fisdap_View_Helper_EurekaGraph();
		
		// some things will look a little different if we have no attempts
		$legend = false;
		$no_attempt_class = "no_eureka_attempt_table";
		$msg = "No " . $rater_type . " ratings found";
		
		// we do have ratings, set up some variables
		if(count($ratings) > 0){
			$legend = true;
			$no_attempt_class = "";
			$msg = $student_name . " " . $eval_name . " Eureka (" . $rater_type . ")";
		}
		
		if($rater_type == "student"){
			$legend = ($has_student_ratings && !$has_preceptor_ratings) ? true : false;
		}
		
		// output our graph/wrapper. This is not the graph itself, but builds HTML elements so the jQuery plugin can be called
		// add a throbber so these can be generated after the HTML is appended to the report page
		$output  = "<div class='eureka_graph_wrapper " . $rater_type . "_signoff_eureka " . $no_attempt_class . "'>";
		$output .=	"<img src='/images/throbber_small.gif' class='loading_eureka_graph_throbber'>";
		$output .=	$eureka_helper->eurekaGraph($ratings, $dates, $this->config['eureka_goal'], $this->config['eureka_window'], $eureka_id, $legend, $msg);
		$output .= "</div>"; // close eureka_graph_wrapper
		
		return $output;
		
	} // end renderGraph()
	
	
	
	/*
	 * Builds the HTML for the ratings table. This table appears to the right of each eureka graph (if there is at least 1 rating)
	 * The table uses the standard shift summary display and pairs it will cell(s) containing a column for each student/preceptor rating (0,1,2,null)
	 * 
	 * @param Array $ratings_by_shift an array of ratings keyed by shift_id
	 * 
	 * @return Array containing HTML to be rendered and four arrays:
	 * 		$student_ratings - containing 1s and 0s, and $student_ratings_dates - containing corresponding DateTime objects
	 * 		$preceptor_ratings - containing 1s and 0s, and $preceptor_ratings_dates - containing corresponding DateTime objects
	 * 		This will be used for eureka graphs later
	 * 		If there are no $ratings_by_shift, the function quits immediately
	 */
	public function renderRatingsTable($ratings_by_shift)
	{
		// quit if we don't have attempts
		if(!$ratings_by_shift){return;}
		
		// initialize some values
		$student_ratings = array();
		$preceptor_ratings = array();
		$student_ratings_dates = array();
		$preceptor_ratings_dates = array();
		
		// set up some HTML for the table, include a header row
		$output  = "<div class='eureka_table_wrapper'>";
		$output .=	"<div class='fixed_eureka_attempts_thead'>";
		$output .=		"<table class='no_data_table'><thead><tr><th class='eureka_shift_info_cell'>Shift</th><th class='eureka_success_cell'>Student rating</th><th class='eureka_success_cell'>Preceptor rating</th></tr></thead></table>";
		$output .=	"</div>";
		$output .= 	"<div class='eureka_attempts_table_wrapper'>";
		$output .=		"<table class='eureka_attempts_table no_data_table'>";
		
		// step through each attempt (grouped by shift)
		foreach($ratings_by_shift as $shift_id => $rating_data){
			
			$student_rating_count = count($rating_data['student_ratings']);
			$preceptor_rating_count = count($rating_data['preceptor_ratings']);
			
			$rowspan = 1;
			$rowspan_offset = ($student_rating_count > $preceptor_rating_count) ? $student_rating_count : $preceptor_rating_count;
			
			if($rowspan_offset != 1){
				$rowspan = $rowspan_offset;
			}
			
			$output .= 		"<tr class='eureka_row'>";
			$output .=			$this->getShiftInfoCell($shift_id, $rating_data['shift_info_display'], $rowspan);
			
			for($i = 0; $i < $rowspan_offset; $i++){
				
				$student_value = $rating_data['student_ratings'][$i];
				$preceptor_value = $rating_data['preceptor_ratings'][$i];
				
				$output .= 	($i > 1) ? "<tr class='eureka_row'>" : "";
				$output .=		$this->getRatingCell($shift_id, $student_value, "student_rating_cell");
				$output .=		$this->getRatingCell($shift_id, $preceptor_value, "preceptor_rating_cell");
				$output .= 	"</tr>"; // close .eureka_row

				$student_eureka_value = $this->getEurekaValueFromRating($student_value);
				if($student_eureka_value !== False){
					$student_ratings[] = $student_eureka_value;
					$student_ratings_dates[] = $rating_data['shift_start_datetime'];
				}
				
				$preceptor_eureka_value = $this->getEurekaValueFromRating($preceptor_value);
				if($preceptor_eureka_value !== False){
					$preceptor_ratings[] = $preceptor_eureka_value;
					$preceptor_ratings_dates[] = $rating_data['shift_start_datetime'];
				}
			}
			
		} // end foreach ratings_by_shift
		
		
		$output .= 		"</table>";  // close .eureka_attempts_table
		$output .= 	"</div>"; 		 // close .eureka_attempts_table_wrapper
		$output .= "</div>"; 		// close .eureka_table_wrapper
		
		
		// return the html for rendering, the array of 1s and 0s and the array of attempt dates (for the eureka graph)
		return array("html" => $output, "student_ratings" => $student_ratings, "student_ratings_dates" => $student_ratings_dates, "preceptor_ratings" => $preceptor_ratings, "preceptor_ratings_dates" => $preceptor_ratings_dates);
	
	} // end renderRatingsTable()
	
	
	/*
	 * Gets the valid eureka value (0 or 1) from a 0,1,2,null data set
	 * 	0 and 1 will be 0
	 * 	2 will be 1
	 * 	and null will return false
	 * 
	 * @param Int $value will be 0, 1, 2, or null
	 * @return mixed
	 */
	public function getEurekaValueFromRating($value)
	{
		$return_val = false;
		
		if(isset($value) && $value != -1){
			$return_val = ($value === 0 || $value == 1) ? 0 : 1;
		}
		
		return $return_val;
	}
	
	/*
	 * Gets the HTML for a single rating cell to be used in the ratings table.
	 * 
	 * @param Int $shift_id the ID of the shift this attempt belongs to
	 * @param Int $rating_value 0,1,2,null
	 *
	 * @return String $output the HTML to be rendered
	 */
	public function getRatingCell($shift_id, $rating_value, $cell_class)
	{
		$rating_class = "gray_rating";
		$rating_display = "N/A";
		$img = "";
		
		if($rating_value == 2){
			$rating_class = "green_rating";
			$img = "<img src='/images/icons/scenario-valid.png' class='eureka_attempts_checkmark'>";
			$rating_display = $rating_value;
		}
		else if($rating_value === 0 || $rating_value == 1){
			$rating_class = "red_rating";
			$img = "<img src='/images/icons/scenario-invalid.png' class='eureka_attempts_x'>";
			$rating_display = $rating_value;
		}
		else if(is_null($rating_value)){
			$rating_display = "No rating";
		}
		
		$output  = "<td class='eureka_success_cell " . $rating_class . " " . $cell_class. "' data-attemptfor='" . $shift_id ."' >";
		$output .=		$rating_display;
		$output .=		$img;
		$output .= "</td>";
		
		return $output;
		
	} // end getRatingCell()
	
	
	
	/*
	 * Gets the HTML for the shift info cell to be used in the attempt table.
	 *
	 * @param Int $shift_id the ID of the shift
	 * @param String $shift_info_html the HTML generated from the shift summary view helper
	 * @param Int $rowspan the rowspan for this cell (based on number of ratings for this shift)
	 *
	 * @return String $output the HTML to be rendered
	 */
	public function getShiftInfoCell($shift_id, $shift_info_html, $rowspan)
	{
		$output  = "<td class='eureka_shift_info_cell' data-shiftid='" . $shift_id . "' rowspan='" . $rowspan . "'>";
		$output .=		$shift_info_html;
		$output .= "</td>";
		
		return $output;
		
	} // end getShiftInfoCell()
	
	
	
} // end Eureka()
