<?php
/**
 * Class Fisdap_Reports_Narrative
 * This is the Narrative Report class
 * Refer to Fisdap_Reports_Report for more documentation
 */
class Fisdap_Reports_LabPracticeGoals extends Fisdap_Reports_Report
{
    public $header = '';

    public $footer = '';

    public $formComponents = array(
		'Reports_Form_LabPracticeGoalsForm' => array(
            'title' => 'Select a display format',
			'options' => array(
				//'pickPatientType' => FALSE,
			),
        ),
		'multistudentPicklist' => array(
            'title' => 'Select one or more student(s)',
            'name' => 'student-picklist',
            'options' =>  array(
                'mode' => 'multiple',
                'loadJSCSS' => TRUE,
                'loadStudents' => TRUE,
                'showTotal' => TRUE,
				'studentVersion' => TRUE,
                'useSessionFilters' => TRUE,
                'sessionNamespace' => "ReportStudentFilter",
            ),
        ),
		//'eurekaModal' => array(),
    );

	public $labPracticeHelper;

	public $scripts = array(
		"/js/library/Fisdap/Reports/lab-practice-goals.js",
		"/js/jquery.eurekaGraph.js",
		"/js/library/SkillsTracker/View/Helper/eureka-modal.js",
		"/js/jquery.jqplot.min.js",
		"/js/syntaxhighlighter/scripts/shCore.min.js",
		"/js/syntaxhighlighter/scripts/shBrushJScript.min.js",
		"/js/syntaxhighlighter/scripts/shBrushXml.min.js",
		"/js/jquery.printElement.min.js",
	);
	
	public $styles = array(
		"/css/library/Fisdap/Reports/lab-practice-goals.css",
		"/css/jquery.jqplot.min.css",
		"/css/jquery.eurekaGraph.css",
		"/js/syntaxhighlighter/styles/shCoreDefault.min.css",
		"/js/syntaxhighlighter/styles/shThemejqPlot.min.css",
	);

	
    /**
     * Run a query and any processing logic that produces the data contained in the report
     * Return a multidimensional array and it will be rendered as tables
     * OR return a string and it will be rendered as HTML
     * @return array
     */
    public function runReport() {
		//Get an instance of the LabPracticeReport View helper
		$this->labPracticeHelper = new Reports_View_Helper_LabPracticeReport();
		
		//Setup date range
		$this->config['dateRangeObjects'] = $this->labPracticeHelper->getDateRange($this->config['dateRange']['startDate'], $this->config['dateRange']['endDate']);

		//Figure out if we have multiple students
		if ($this->config['reportType'] == "detailed") {
			$student = \Fisdap\EntityUtils::getEntity("StudentLegacy", $this->config['student']);
			$students = array($student->id => $student->user->getName());

            // add notice about cert level, if applicable
            if ($student->getCertification()->id != $this->config['certLevel']) {
                $notice  = "<div class='notice report-notice'>";
                $notice .= $student->user->getName()."'s certification level does not match the selected goal set. ";
                $notice .= "Try selecting ".$student->getCertification(true).".";
                $notice .= "</div>";
                $this->data[] = array("type" => "html", "content" => $notice);
            }

			$this->addItemDetailedTables($students);
		} else {
			$students = $this->getMultiStudentData(true);

            $this->addCertLevelWarning($students);
			
			$this->data[] = array("type" => "html", "content" => "<h2 class='section-header'>Summary</h3>");
			$this->addOverallSummaryTable($students);
			
			$this->data[] = array("type" => "html", "content" => "<h2 class='section-header'>Active Items</h3>");
			$this->addItemSummaryTables($students, true);
			
			$this->data[] = array("type" => "html", "content" => "<h2 class='section-header'>Inactive Items</h3>");
			$this->addItemSummaryTables($students, false);
		}

        // Making the HTML by hand here instead of using $this->view->eurekaModal because we don't have $this->view in CLI report running
        $eurekaModal = 	"<div id='eureka-modal'>\n
               <img src='/images/icons/delete.png' id='close-eureka-modal'>\n
               <div id='eureka-modal-content'></div>\n
            </div>";
		$this->data[] = array("type" => "html", "content" => $eurekaModal);
    }

    /**
     * Check all the students' cert levels against the goal set cert level and add a warning if any don't match
     *
     * @param $students
     *
     */
    private function addCertLevelWarning($students)
    {
        // loop through all the students and add them to a list of students whose cert levels don't match when applicable
        $mismatchedStudents = array();
        $certLevels = \Fisdap\EntityUtils::getRepository('StudentLegacy')->getStudentCertLevels(array_keys($students));
        foreach ($students as $sid => $nameOptions) {
            // add student to mismatched list, if applicable
            if ($certLevels[$sid]['level_id'] != $this->config['certLevel']) {
                $mismatchedStudents[] = $nameOptions['first_last_combined'] . " (" . $certLevels[$sid]['level_name'] . ")";
            }
        }

        // only add the warning if some students don't match
        if (count($mismatchedStudents) > 0) {
            // construct the notice
            if (count($students) == count($mismatchedStudents) && count($mismatchedStudents > 1)) {
                $studentPhrase = "None of these students' certification levels";
            } else if (count($mismatchedStudents) == 1) {
                $studentPhrase = "This student's certification level does not";
            } else {
                $studentPhrase = count($mismatchedStudents) . " students' certification levels do not";
            }

            $notice = "<div class='notice report-notice'>";
            $notice .= $studentPhrase . " match the selected goal set. ";
            $notice .= "If you aren't seeing the results you expect, try selecting a different combination. ";
            $notice .= "<a class='toggleDetails'>Details</a>";
            $notice .= "<div class='details'>";
            $notice .= "<div>" . implode("</div><div>", $mismatchedStudents) . "</div>";
            $notice .= "</div>";
            $notice .= "</div>";

            // add the notice to the report
            $this->data[] = array("type" => "html", "content" => $notice);
        }
    }

	public function addItemDetailedTables($students)
	{
		$student_id = current(array_keys($students));
		$dateRange = $this->config['dateRangeObjects'];
		$data = $this->labPracticeHelper->getDetailedLPIData(true, $student_id, $dateRange, $this->config['certLevel']);
		$shift_summary_display_helper = new \Fisdap_View_Helper_ShiftSummaryDisplayHelper();
		
		// step through each definition and determine if their are LPIs
		foreach($data as $defId => $defData){
			
			$def = \Fisdap\EntityUtils::getEntity("PracticeDefinition", $defId);
			
			//Format the eureka goal output
			$eurekaGoalOutput = ($def->eureka_goal) ? $def->eureka_goal . "/" . $def->eureka_window : "N/A";
			
			//Format the date range of the found LPIs
			$firstItem = $defData['details'][0];
			$lastItem = end($defData['details']);
			$dateText = ($firstItem['date'] == $lastItem['date']) ? $lastItem['date'] : $firstItem['date'] . " - " . $lastItem['date'];
			
			//Format the eureka graph icon
			$eurekaIcon = "";
			if($def->eureka_goal){
				$eurekaIcon .= '<a href="#" class="eureka-trigger"'
							. 'data-defId="' . $def->id . '"'
							. 'data-studentId="' . $student_id . '"'
							. 'data-startDate="' . $dateRange['start_date'] . '"'
							. 'data-endDate="' . $dateRange['end_date'] . '">View Graph</a>';
			}
			
			// make a table
			$itemTable = array(
				'title' => $def->name,
				'nullMsg' => "No skills found.",
				'head' => array(
					'0' => array( // there's only 1 row header for this report
						'Attempt',
						'Date',
						'<img src="/images/icons/lab_skills_icon_eureka_white.png">Eureka Goal: ' . $eurekaGoalOutput,
						'<img src="/images/icons/lab_skills_icon_peer_white.png">Peer Goal: ' . $def->peer_goal,
						'<img src="/images/icons/lab_skills_icon_instructor_white.png">Instructor Goal: ' . $def->instructor_goal,
					),
					'1' => array(
						array("data" => $defData['totals'][0]['attemptCount'] . " attempts", "class" => "attempt-col"),
						array("data" => $dateText, "class" => "date-col"),
						array("data" => $eurekaIcon, 'class' => "no-sort-col details-eureka-col"),
						array("data" => "Passed: " . $defData['totals'][0]['totalPeerCheckoffs'], "class" => "details-peer-col"),
						array("data" => "Passed: " . $defData['totals'][0]['totalInstructorCheckoffs'], "class" => "details-instructor-col"),
					),
				),
				'body' => array(),
			);
			
			// get a row for each attempt (lab practice item)
			$attemptCount = 1;
			foreach($defData['details'] as $itemData){
				$successText = ($itemData['success']) ? "Pass" : "Fail";
				$successClass = "success";
				$eurekaSuccess = (($def->eureka_goal) && ($itemData['itemId'] == $defData['totals'][0]['eurekaPoint'])) ? $successClass : "";
				$peerSuccess = ($itemData['itemId'] == $defData['totals'][0]['peerGoalReached']) ? $successClass : "";
				$instructorSuccess = ($itemData['itemId'] == $defData['totals'][0]['instructorGoalReached']) ? $successClass : "";
				$unconfirmedWarning = ($itemData['confirmationAlert']) ? "<span class='confirmAlert'>unconfirmed</span>" : "";
				
				// use shift view helper to display date/shift link
				$shiftData = array('shift_id' => $itemData['shift_id'],
								   'start_datetime' => new \DateTime($itemData['date']),
								   'type' => $itemData['shift_type'],
								   );
                $summary_options = array('display_size' => 'small', 'sortable' => true);
                $shift_info = $shift_summary_display_helper->shiftSummaryDisplayHelper($shiftData, null, null, $summary_options);
				
				// add the row
			    $itemTable['body'][$itemData['itemId']] = array(
			        array(
			            'data' => $attemptCount . " " . $unconfirmedWarning,
			            'class' => 'attempt-col center',
			        ),
					array(
						'data' => $shift_info,
			            'class' => 'date-col center',
					),
					array(
						'data' => $successText,
			            'class' => 'center details-eureka-col ' . $eurekaSuccess,
					),
					array(
						'data' => $itemData['peerCheckoff'],
			            'class' => "center details-peer-col " . $peerSuccess,
					),
					array(
						'data' => $itemData['instructorCheckoff'],
			            'class' => "center details-instructor-col " . $instructorSuccess,
					),
			    );
				$attemptCount++;
			}
			
			$this->data[] = array("type" => "table", "content" => $itemTable);
		}
	}
	
	public function addItemSummaryTables($students, $active = true)
	{
		$data = $this->labPracticeHelper->getLPIData($active, $this->config['certLevel'], array_keys($students), $this->config['dateRangeObjects']);
		
		foreach ($data as $defId => $studentArray) {
			$numberOfGoals = 0;
			$def = \Fisdap\EntityUtils::getEntity("PracticeDefinition", $defId);
			if($def->peer_goal){$numberOfGoals++;}
			if($def->instructor_goal){$numberOfGoals++;}
			if($def->eureka_goal){$numberOfGoals++;}
			
			//Format additional data for table header
			$eurekaGoalOutput = ($def->eureka_goal) ? $def->eureka_goal . "/" . $def->eureka_window : "N/A";
			$peerGoalText = " Goal: " . $def->peer_goal;
			$instructorGoalText = " Goal: " . $def->instructor_goal;
			$eurekaGoalText = " Goal: " . $eurekaGoalOutput;
			
			// make a table
			$itemTable = array(
				'title' => $def->name,
				'nullMsg' => "No skills found.",
				'head' => array(
					'0' => array(
						array("data" => "Student", "class" => "student-col"),
						array("data" => '<img src="/images/icons/lab_skills_icon_peer_white.png">Peer' . $peerGoalText, "class" => "peer-col"),
						array("data" => '<img src="/images/icons/lab_skills_icon_instructor_white.png">Instructor' . $instructorGoalText, 'class' => "instructor-col"),
						array("data" => '<img src="/images/icons/lab_skills_icon_eureka_white.png">Eureka' . $eurekaGoalText, "class" => "eureka-col"),
						array("data" => "% Complete", "class" => "precent-col"),
					),
				),
				'body' => array(),
			);
			
			foreach($studentArray as $studentId => $studentData) {
				// set up classes for successes
				$successClass = "success";
				$peerSuccess = ($studentData['peer-checkoffs'] >= $def->peer_goal) ? $successClass : "";
				$instructorSuccess = ($studentData['instructor-checkoffs'] >= $def->instructor_goal) ? $successClass : "";
				$eurekaSuccess = ($def->eureka_goal && $studentData['eureka']) ? $successClass : "";
				$complete = (($studentData['precentage'] == 100)) ? $successClass : "";
				
				//Get student name from list of students
				$studentName = $students[$studentId]['first_last_combined'];
				
				//Format the eureka graph icon
				
				if($def->eureka_goal){
					$eurekaIcon = '<a href="#" class="eureka-trigger"'
								. 'data-defId="' . $def->id . '"'
								. 'data-studentId="' . $studentId . '"'
								. 'data-startDate="' . $this->config['dateRangeObjects']['start_date'] . '"'
								. 'data-endDate="' . $this->config['dateRangeObjects']['end_date'] . '">' . ($studentData['eureka'] ? "Met" : "Not Met") . '</a>';
				} else {
					$eurekaIcon = "N/A";
				}
				
				// add the row
			    $itemTable['body'][$studentId] = array(
			        array(
			            'data' => $studentName,
			            'class' => 'student-col',
			        ),
					array(
						'data' => $studentData['peer-checkoffs'],
			            'class' => 'peer-col ' . $peerSuccess,
					),
					array(
						'data' => $studentData['instructor-checkoffs'],
			            'class' => 'instructor-col ' . $instructorSuccess,
					),
					array(
						'data' => $eurekaIcon,
			            'class' => "eureka-col " . $eurekaSuccess,
					),
					array(
						'data' => $numberOfGoals > 0 ? $studentData['precentage'] . "%" : "N/A",
			            'class' => "precent-col " . $complete,
					),
			    );
			}
			
			$this->data[] = array("type" => "table", "content" => $itemTable);
		}
	}
	
	public function addOverallSummaryTable($students)
	{
		$data = $this->labPracticeHelper->getLPIData(true, $this->config['certLevel'], array_keys($students), $this->config['dateRangeObjects']);
		
		// make a table
		$summaryTable = array(
			'title' => "All Items",
			'nullMsg' => "No skills found.",
			'head' => array(
				'0' => array(
					array("data" => "Student", "class" => "student-col"),
					array("data" => '<img src="/images/icons/lab_skills_icon_peer_white.png">Peer', "class" => "peer-col"),
					array("data" => '<img src="/images/icons/lab_skills_icon_instructor_white.png">Instructor', 'class' => "instructor-col"),
					array("data" => '<img src="/images/icons/lab_skills_icon_eureka_white.png">Eureka', "class" => "eureka-col"),
					array("data" => "% Complete", "class" => "precent-col"),
				),
			),
			'body' => array(),
		);
		
		//Loop over all of the students adding a row for each
		foreach($this->labPracticeHelper->getStudentSummary($data, array_keys($students)) as $studentId => $totals) {
			$peerSuccess = ($totals['peerCheckoff'] == 100) ? "success" : "";
			$instructorSuccess = ($totals['instructorCheckoff'] == 100) ? "success" : "";
			$eurekaStatus = ($totals['eurekaCheckoff'] == 100) ? "success" : "";
			$overallPrecent = ($totals['overallPrecent'] == 100) ? "success" : "";
							
			$studentName = $students[$studentId]['first_last_combined'];
			
			// add the row
			$summaryTable['body'][$studentId] = array(
				array(
					'data' => $studentName,
					'class' => 'student-col',
				),
				array(
					'data' => $totals['peerCheckoff'] . '%',
					'class' => 'peer-col ' . $peerSuccess,
				),
				array(
					'data' => $totals['instructorCheckoff'] . '%',
					'class' => 'instructor-col ' . $instructorSuccess,
				),
				array(
					'data' => $totals['eurekaCheckoff'] . '%',
					'class' => "eureka-col " . $eurekaStatus,
				),
				array(
					'data' => $totals['overallPrecent'] . '%',
					'class' => "precent-col " . $overallPrecent,
				),
			);
		}
		
		$this->data[] = array("type" => "table", "content" => $summaryTable);
	}

}