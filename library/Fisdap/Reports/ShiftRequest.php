<?php
/**
 * Class Fisdap_Reports_Report
 * This is the base class for Fisdap 2.0 Reports
 * Includes methods for quickly generating forms and standard data display options
 * Create a new Fisdap Report by extending this class.
 */
class Fisdap_Reports_ShiftRequest extends Fisdap_Reports_Report
{
    public $header = '';

    public $footer = '';

    public $formComponents = array(
        'shiftInformationForm' => array(
            'title' => 'Select shift information',
            'options' => array(
				'pickPatientType' => FALSE,
				'selected' => array('sites' => array()),
			),
        ),
        'multistudentPicklist' => array(
            'title' => 'Select one or more student(s)',
            'options' =>  array(
                'loadJSCSS' => TRUE,
                'loadStudents' => TRUE,
                'showTotal' => TRUE,
				'studentVersion' => TRUE,
                'useSessionFilters' => TRUE,
                'sessionNamespace' => "ReportStudentFilter",
            ),
        ),
    );
    
    /**
     * Run a query and any processing logic that produces the data contained in the report
     * Return a multidimensional array and it will be rendered as tables
     * OR return a string and it will be rendered as HTML
     * @return array
     */
    public function runReport() {

		// get the form info        
        $students = $this->getMultiStudentData(true);
		$site_ids = $this->getSiteIds();
		$start_date = $this->config['startDate'];
		$end_date = $this->config['endDate'];

        // If nothing is done below, we parse a blank array
        $this->data = array();
		
        // Reference the reports repo, where we lift heavy things!
        $repo = \Fisdap\EntityUtils::getRepository('Report');

        // Actually do the heavy things.
        $data = $repo->getShiftRequestData(array_keys($students),$site_ids, $start_date, $end_date);

        $table_data = array(
        	'title' => 'Shift Requests',
        	'nullMsg' => 'No shifts found.',
        	'head' => array(
        		'001' => array( // second row headers
        			array(
        				'data' => 'Student Name',
        				'class' => 'column-title',
        			),
					array(
						'data' => 'Drops Requested',
						'class' => 'column-title',
					),
					array(
						'data' => 'Drop Completed',
						'class' => 'column-title',
					),
					array(
						'data' => 'Swaps Requested',
						'class' => 'column-title',
					),
					array(
						'data' => 'Swaps Completed',
						'class' => 'column-title',
					),
					array(
						'data' => 'Covers Requested',
						'class' => 'column-title',
					),
					array(
						'data' => 'Covers Completed',
						'class' => 'column-title',
					),
					array(
						'data' => 'Total Requested',
						'class' => 'column-title',
					),
					array(
						'data' => 'Total Completed',
						'class' => 'column-title',
					),

				),
			),
        	'body'	=> array(),
        );

        // Loop through each of the sekected students
    	foreach ($students as $student_id => $nameOptions) {
    		$table_data['body'][$student_id] = array(
    			array("data" => $nameOptions['first_last_combined'], "class"=>"left noSum"),
    			array("data" => $data[$student_id]['drop']['requested'], "class"=>"center"),
    			array("data" => $data[$student_id]['drop']['approved'], "class"=>"center"),
    			array("data" => $data[$student_id]['swap']['requested'], "class"=>"center"),
    			array("data" => $data[$student_id]['swap']['approved'], "class"=>"center"),
    			array("data" => $data[$student_id]['cover']['requested'], "class"=>"center"),
    			array("data" => $data[$student_id]['cover']['approved'], "class"=>"center"),
    			array("data" => $data[$student_id]['total']['requested'], "class"=>"center"),
    			array("data" => $data[$student_id]['total']['approved'], "class"=>"center")
    		);

        }

        // add the footer to calculate totals, but only if there's more than one row
        if (count($table_data['body']) > 1) {
            $footer = array(array("data" => "Total:", "class" => "right"));

            for ($i=0; $i<8; $i++) {
                $footer[] = array("data" => "-", "class" => "center");
            }

            $table_data['foot']["sum"] = $footer;
        }

        // Add all of our data to the report
        $this->data[] = array(
            "type" => "table",
            "content" => $table_data,
        );
    }
    
}
