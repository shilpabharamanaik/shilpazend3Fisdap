<?php
/**
 * Class Fisdap_Reports_PasswordSchedule
 * This is the Print Serial Number Report class
 *
 * Use this report to see the testing password schedule
 */
class Fisdap_Reports_PasswordSchedule extends Fisdap_Reports_Report
{
    public $header = '';

    public $footer = '';

    public $formComponents = array(
        'Reports_Form_PasswordScheduleForm' => array(
            'title' => 'Report options',
			'options' => array(
				'pickPatientType' => FALSE,
			),
        ),
    );
	
	/**
     * This report is only visible to staff
     */
	public static function hasPermission($userContext) {
		return $userContext->getUser()->isStaff();
	}

    /**
     * Run a query and any processing logic that produces the data contained in the report
     * Return a multidimensional array and it will be rendered as tables
     * OR return a string and it will be rendered as HTML
     * @return array
     */
    public function runReport() {
		// clean up the form info
		$exam_ids = $this->config['exams'];
		
		$start_date = $this->config['dateRange']['startDate'];
		$end_date = $this->config['dateRange']['endDate'];
		$dates =  Util_FisdapDate::get_dates_in_range($start_date, $end_date, '+1 day', 'n/j/y');
		
		$headers = array('Exam');
		foreach ($dates as $date) {
			$headers[] = $date;
		}
		
		// make the table
        $passwordTable = array(
			'title' => "Password Schedule",
			'nullMsg' => "No passwords found.",
            'head' => array('0' => $headers),
            'body' => array(),
        );
		
		// loop through the exams and get the passwords
		$exams = array();
		foreach($exam_ids as $exam_id) {
			$exam = \Fisdap\EntityUtils::getEntity("MoodleTestDataLegacy", $exam_id);
			
			$rowData = array(array('data' => $exam->test_name));
			
			$passwords = \Fisdap\EntityUtils::getRepository('MoodleTestDataLegacy')->get_passwords($exam_id, $start_date, $end_date, 'n/j/y');
			foreach ($dates as $date) {
				$rowData[] = array('data' => $passwords[$date], 'class' => 'center');
			}
			
			// add the row
			$passwordTable['body'][$exam_id] = $rowData;
		}
		
		$this->data['passwords'] = array("type" => "table",
										 "content" => $passwordTable);
    }
	
	/**
	 * Return a custom short label/description of the productivity report
	 * Overrides parent method
	 */
	public function getShortConfigLabel() {
		$exam_count = count($this->config['exams']);
		$label = "Passwords for ".$exam_count." ".Util_String::pluralize("exam", $exam_count);
	
	 	// return the label
		return $label;
	}

}