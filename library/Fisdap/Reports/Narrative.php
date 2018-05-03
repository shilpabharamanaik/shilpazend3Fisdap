<?php
/**
 * Class Fisdap_Reports_Narrative
 * This is the Narrative Report class
 * Refer to Fisdap_Reports_Report for more documentation
 */
class Fisdap_Reports_Narrative extends Fisdap_Reports_Report
{
    public $header = '';

    public $footer = '';

    public $formComponents = array(
        'shiftInformationForm' => array(
            'title' => 'Select shift information',
			'options' => array(
				'pickPatientType' => FALSE,
			),
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
        ),
    );

    public $styles = array("/css/library/Fisdap/Reports/narrative.css");

    /**
     * Run a query and any processing logic that produces the data contained in the report
     * Return a multidimensional array and it will be rendered as tables
     * OR return a string and it will be rendered as HTML
     * @return array
     */
    public function runReport() {
		$student_id = $this->config['student'];
		$student = \Fisdap\EntityUtils::getEntity('StudentLegacy', $student_id);
		$students = array($student_id);
		
		// clean up the site info
		$site_ids = $this->getSiteIds();
		
		$start_date = $this->config['startDate'];
		$end_date = $this->config['endDate'];
		
        // Run a query to get data.
        $repo = \Fisdap\EntityUtils::getRepository('StudentLegacy');
		$data = $repo->getStudentNarrativeData($students, $site_ids, $start_date, $end_date);
        
        // make a table
        $narrativeTable = array(
			'title' => $student->getFullName() . "'s Narratives",
			'nullMsg' => "No narratives found.",
            'head' => array(
				'0' => array( // there's only 1 row header for this report
                    'Shift Info',
                    'Narrative',
                ),
            ),
            'body' => array(),
        );
		
		$shift_summary_display_helper = new Fisdap_View_Helper_ShiftSummaryDisplayHelper();
		
		// get the data for the chosen student
		if ($data[$student_id]) {
			foreach ($data[$student_id] as $id => $narrative_info) {
				
				// format the shift info
				$shift = \Fisdap\EntityUtils::getEntity("ShiftLegacy", $narrative_info['shift_id']);
				$date = $shift->start_datetime->format('M j Y, Hi');
				$location = $shift->getLocation();
				$preceptor = \Fisdap\EntityUtils::getEntity("PreceptorLegacy", $narrative_info['preceptor_id']);
				$preceptorName = ($preceptor) ? $preceptor->first_name . " " . $preceptor->last_name : "";

                $summary_options = array('display_size' => 'large', 'sortable' => true);
                $shift_info = $shift_summary_display_helper->shiftSummaryDisplayHelper(null, null, $shift, $summary_options);
				
				/*
				$shift_info = 	"<h4 class='".$shift->type."'>$date</h4>".
								"<h4 class='location'>$location</h4>".
								"<div class='details'>
									Preceptor: $preceptorName<br>
									Shift: ".$shift->id."<br>
									Run: ".$narrative_info['run_id']."
								</div>";*/
				
				// format the narrative text
				$narrative = $narrative = \Fisdap\EntityUtils::getEntity('Narrative', $id);
				
			    // add the row
			    $narrativeTable['body'][$id] = array(
			        array(
			            'data' => $shift_info,
			            'class' => 'shift_info',
			        ),
					array(
						'data' => $narrative->getProcedureText(),
			            'class' => 'narrative_text',
					)
			    );
			}
		}
        
        // add the table to this report
        $this->data['narratives'] = array("type" => "table",
										  "content" => $narrativeTable);
    }
}