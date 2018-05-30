<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                                           *
*        Copyright (C) 1996-2011.  This is an unpublished work of           *
*                         Headwaters Software, Inc.                         *
*                            ALL RIGHTS RESERVED                            *
*        This program is a trade secret of Headwaters Software, Inc. and    *
*        it is not to be copied, distributed, reproduced, published, or     *
*        adapted without prior authorization of Headwaters Software, Inc.   *
*                                                                           *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

/**
 * This helper will display a table for an LPI data set
 */

/**
 * @package Reports
 * @author ahammond
 */
class Reports_View_Helper_LabPracticeReport extends Zend_View_Helper_Abstract
{
    
    /**
     * @var string the stuff to return
     */
    protected $returnContent;
    
    /** Decides which table to show based on params then returns it
     * @param  active bool does the table to return include active or inactive definitions?
     * @param certLevel the certification level id specified by the user (determines which definitions to use)
     * @param $students an array of student ids - could just be one depending on the user and the report type
     * @param summary NOT THE REPORT TYPE SPECIFIED, this is the summary table (vs inactive/active) will always be false if the reportType is detailed
     * @param dataOptions an array of options (report type, start/end dates)
     * @return HTML to be echoed
     */
    public function labPracticeReport($active, $certLevel, $students, $summary, $dataOptions)
    {
        // will modify the date range to make it more usable (will handle same day date range as well)
        $dateRange = $this->getDateRange($dataOptions['start_date'], $dataOptions['end_date']);
        $loggedInUser = \Fisdap\Entity\User::getLoggedInUser();
        
        // summary report type
        if ($dataOptions['reportType'] == 'summary') {
            // grab the summary data
            $data = $this->getLPIData($active, $certLevel, $students, $dateRange);
            
            // display it in a table (the summary table shows overall precentages, the items table displays a table for each definition)
            return ($summary) ? $this->getSummaryTable($data, $students) : $this->getItemsTable($data);
        }
        
        // detailed report type
        else {
            // if the user is an instructor pop off the single student, otherwise use the logged in user's data
            $studentId = ($loggedInUser->isInstructor()) ?  array_pop($students): $loggedInUser->getCurrentRoleData()->id;
            
            // display the details tables (a table for each definition)
            return $this->getDetailTable($this->getDetailedLPIData($active, $studentId, $dateRange), $studentId, $dateRange);
        }
    }
    
    /**
     * returns the actual HTML content for a detailed report
     * @param $data an array of data to be formatted
     * @param $studentId the student that this report belongs to
     * @param $dateRange an array with a 'start/end' date in mysql date format
     */
    public function getDetailTable($data, $studentId, $dateRange)
    {
        $student = \Fisdap\EntityUtils::getEntity("StudentLegacy", $studentId);
        
        // step through each definition and determine if their are LPIs
        foreach ($data as $defId => $defData) {
            $def = \Fisdap\EntityUtils::getEntity("PracticeDefinition", $defId);
            $returnContent = '<h2 class="table-header">' .  $def->name . '</h2>';
            
            // if the student has at least one attempt for the deifnition
            if (count($defData['details']) > 0) {
                
                /* not necessary, if skill columns come back, then re-implement this logic
                // does the horizontal scroll for longer tables
                //$numberOfSkills = count($def->practice_skills);
                //$bigTableClass = $this->getBigTableClass($numberOfSkills);

                //if($numberOfSkills > 3){$returnContent .= '<div class="wide-table-container">';}
                */
                
                // start with the thead
                //$returnContent .= '<table class="lab-pratice-report-table fisdap-table ' . $bigTableClass . '">';
                
                $returnContent .= '<table class="lab-pratice-report-table fisdap-table">';
                $returnContent .= '<thead class="small-thead">';
                $returnContent .= $this->getDetailsOrangeRow($def);
                $returnContent .= $this->getDetailsTotalsRow($def, $defData, $student, $dateRange);
                $returnContent .= "</thead>";
                
                // get a row for each attempt (lab practice item)
                $attemptCount = 1;
                foreach ($defData['details'] as $itemData) {
                    $returnContent .= $this->getDetailsAttemptRow($itemData, $attemptCount, $def, $defData);
                    $attemptCount++;
                }
                
                $returnContent .= "</table>";
                
            // close the scrollable div
                //if($numberOfSkills > 3){$returnContent .= '</div>';}
            } else {
                $returnContent .= "none<br /><br />";
            }
        }
        
        return $returnContent;
    }
    
    /**
     * returns the actual HTML content for a summray report's summary table (the one with just precentages)
     * @param $data an array of data to be formatted
     * @param $students the students for each row
     */
    public function getSummaryTable($data, $students)
    {
        $returnContent = '<h2 class="table-header">All Items</h2>';
        $returnContent .= '<table class="lab-pratice-report-table fisdap-table">';
        $returnContent .= $this->getStandardOrangeRow();
    
        $count = 0;
        foreach ($this->getStudentSummary($data, $students) as $studentId => $totals) {
            $returnContent .= $this->getSummaryTableStudentRow($totals, $studentId, $count);
            $count++;
        }
        
        $returnContent .= "</table>";
        return $returnContent;
    }
    
    /**
     * returns the actual HTML content for a summray report's definitions' tables
     * @param $data an array of data to be formatted
     */
    public function getItemsTable($data)
    {
        foreach ($data as $defId => $studentArray) {
            $numberOfGoals = 0;
            $def = \Fisdap\EntityUtils::getEntity("PracticeDefinition", $defId);
            if ($def->peer_goal) {
                $numberOfGoals++;
            }
            if ($def->instructor_goal) {
                $numberOfGoals++;
            }
            if ($def->eureka_goal) {
                $numberOfGoals++;
            }
            
            $returnContent = '<h2 class="table-header">' .  $def->name . '</h2>';
            $returnContent .= '<table class="lab-pratice-report-table fisdap-table" data-shuffleRows="' . !$this->canSeeNames() . '">';
            $returnContent .= $this->getStandardOrangeRow($def);
                
            $count = 0;
            foreach ($studentArray as $studentId => $studentData) {
                $returnContent .= $this->getItemsTableStudentRow($studentId, $studentData, $count, $def, $numberOfGoals);
                $count++;
            }
            
            $returnContent .= '</table><br />';
        }
        
        return $returnContent;
    }
    
    /**
     * returns a string of HTML content for the dark gray row of the tables for the details report
     * @param $def the definition
     * @param $defData definition specific calculated data
     * @param $student the student who the detailed report is for
     */
    private function getDetailsTotalsRow($def, $defData, $student, $dateRange)
    {
        $returnContent = "<tr class='dark-row'>";
        $firstItem = $defData['details'][0];
        $lastItem = end($defData['details']);
        $dateText = ($firstItem['date'] == $lastItem['date']) ? $lastItem['date'] : $firstItem['date'] . " - " . $lastItem['date'];
        
        $returnContent .= 	"<th class='attempt-col'>" . $defData['totals'][0]['attemptCount'] . " attempts</th>";
        $returnContent .= 	"<th class='date-col'>" . $dateText . "</th>";
        $returnContent .= 	"<th class='details-eureka-col'>";
        
        if ($def->eureka_goal) {
            $returnContent .= '<a href="#" class="eureka-trigger"';
            $returnContent .=					'data-defId="' . $def->id . '"';
            $returnContent .=					'data-studentId="' . $student->id . '"';
            $returnContent .=					'data-startDate="' . $dateRange['start_date'] . '"';
            $returnContent .=					'data-endDate="' . $dateRange['end_date'] . '">View Graph</a>';
        }
        
        $returnContent .= 	"</th>";
        $returnContent .= 	"<th class='details-peer-col'>Passed: " . $defData['totals'][0]['totalPeerCheckoffs'] . "</th>";
        $returnContent .= 	"<th class='details-instructor-col'>Passed: " . $defData['totals'][0]['totalInstructorCheckoffs'] . "</th>";
        
        /* gone for now, definition change conflicts - maybe one day?
        foreach($def->practice_skills as $skill){
            $totalSkills = $defData['totals'][0]['totalPeerCheckoffs'] + $defData['totals'][0]['totalInstructorCheckoffs'];
            $returnContent .=	'<th class="skill-col"><div class="min-width-cell">Performed: ' . $totalSkills . '</div></th>';
        }
        */
        
        $returnContent .= "</tr>";
        return $returnContent;
    }
    
    /**
     * returns a string of HTML content for the orange row for tables on the detailed report
     * @param $def the definition
     */
    private function getDetailsOrangeRow($def)
    {
        $eurekaGoalOutput = ($def->eureka_goal) ? $def->eureka_goal . "/" . $def->eureka_window : "N/A";
        $returnContent = '<tr>';
        $returnContent .=	'<th class="attempt-col">Attempt</th>';
        $returnContent .=	'<th class="date-col">Date</th>';
        $returnContent .=	'<th class="details-eureka-col"><img src="/images/icons/lab_skills_icon_eureka_white.png">Eureka Goal: ' . $eurekaGoalOutput . '</th>';
        $returnContent .=	'<th class="details-peer-col"><img src="/images/icons/lab_skills_icon_peer_white.png">Peer Goal: ' . $def->peer_goal . '</th>';
        $returnContent .=	'<th class="details-instructor-col"><img src="/images/icons/lab_skills_icon_instructor_white.png">Instructor Goal: ' . $def->instructor_goal . '</th>';

        /* gone for now, definition change conflicts - maybe one day?
        // do a column for each skill associated with this definition
        foreach($def->practice_skills as $skill){
            $returnContent .=	'<th class="skill-col">' . $skill->name . '</th>';
        }
        */
        
        $returnContent .= "</tr>";
        return $returnContent;
    }
    
    /**
     * returns a string of HTML content for each attempt in the detail's report tables
     * @param $itemData the calculated data needed to print out the row for the item
     * @param $attemptCount just to set the odd/even row color
     * @param $def the definition
     * @param $defData the calculcated data needed to print out info the definition
     */
    private function getDetailsAttemptRow($itemData, $attemptCount, $def, $defData)
    {
        $rowClass = ($attemptCount%2 == 0) ? "even" : "odd";
        $successText = ($itemData['success']) ? "Pass" : "Fail";
        $successClass = "success";
        $eurekaSuccess = (($def->eureka_goal) && ($itemData['itemId'] == $defData['totals'][0]['eurekaPoint'])) ? $successClass : "";
        $peerSuccess = ($itemData['itemId'] == $defData['totals'][0]['peerGoalReached']) ? $successClass : "";
        $instructorSuccess = ($itemData['itemId'] == $defData['totals'][0]['instructorGoalReached']) ? $successClass : "";
        $unconfirmedWarning = ($itemData['confirmationAlert']) ? "<span class='confirmAlert'>unconfirmed</span>" : "";
        
        $returnContent = '<tr class="' . $rowClass . '">';
        $returnContent .= 	"<td class='attempt-col'>" . $attemptCount . " " . $unconfirmedWarning . "</td>";
        $returnContent .= 	"<td class='date-col'>" . $itemData['date'] . "</td>";
        $returnContent .= 	"<td class='details-eureka-col " . $eurekaSuccess . "'>" . $successText . "</td>";
        $returnContent .= 	"<td class='details-peer-col " . $peerSuccess . "'>" . $itemData['peerCheckoff'] . "</td>";
        $returnContent .= 	"<td class='details-instructor-col " . $instructorSuccess . "'>" . $itemData['instructorCheckoff'] . "</td>";
        
        /* gone for now, definition change conflicts - maybe one day?
        foreach($def->practice_skills as $skill){
            $includeSkill = "";
            // if it's successful, make sure it isn't unconfirmed before adding a checkmark
            if($itemData['success']){
                $skillStatus = ($itemData['confirmationAlert']) ? "incomplete" : "checkmark";
                $includeSkill = "<img class='" . $skillStatus  . "' src='/images/icons/" . $skillStatus . ".png'>";
            }
            $returnContent .=	'<td class="skill-col">' . $includeSkill . '</td>';
        }
        */
        
        $returnContent .= "</tr>";
        return $returnContent;
    }
    
    /**
     * returns a string of HTML content for the orange row for tables on the summary report
     * @param $def the definition (def will be there if this is not the summary table - only a regular definition table)
     */
    private function getStandardOrangeRow($def = null)
    {
        if ($def) {
            $eurekaGoalOutput = ($def->eureka_goal) ? $def->eureka_goal . "/" . $def->eureka_window : "N/A";
            $peerGoalText = " Goal: " . $def->peer_goal;
            $instructorGoalText = " Goal: " . $def->instructor_goal;
            $eurekaGoalText = " Goal: " . $eurekaGoalOutput;
        }
        
        $returnContent = '<thead><tr>';
        $returnContent .=	'<th class="student-col">Student</th>';
        $returnContent .=	'<th class="peer-col"><img src="/images/icons/lab_skills_icon_peer_white.png">Peer' . $peerGoalText . '</th>';
        $returnContent .=	'<th class="instructor-col"><img src="/images/icons/lab_skills_icon_instructor_white.png">Instructor' . $instructorGoalText . '</th>';
        $returnContent .=	'<th class="eureka-col"><img src="/images/icons/lab_skills_icon_eureka_white.png">Eureka' . $eurekaGoalText . '</th>';
        $returnContent .=	'<td class="precent-col">% Complete</td>';
        $returnContent .= '</tr></thead>';
        
        return $returnContent;
    }
    
    /**
     * returns a string of HTML content for each row in the summary table (summary report type)
     * @param $totals an array of totals to be displayed
     * @param $studentId the student who belongs to this row
     * @param $count used for odd/even rows
     */
    private function getSummaryTableStudentRow($totals, $studentId, $count)
    {
        $student = \Fisdap\EntityUtils::getEntity("StudentLegacy", $studentId);
        $rowClass = ($count%2 == 0) ? "even" : "odd";
        
        $peerSuccess = ($totals['peerCheckoff'] == 100) ? "success" : "";
        $instructorSuccess = ($totals['instructorCheckoff'] == 100) ? "success" : "";
        $eurekaStatus = ($totals['eurekaCheckoff'] == 100) ? "success" : "";
        $overallPrecent = ($totals['overallPrecent'] == 100) ? "success" : "";
                        
        if ($this->canSeeNames() || $student->user->id == \Fisdap\Entity\User::getLoggedInUser()->id) {
            $studentName = $student->user->getName();
        } else {
            $studentName = "Anonymous";
        }
            
        $returnContent = '<tr class="' . $rowClass . '">';
        $returnContent .= 	"<td class='student-col'>" . $studentName . "</td>";
        $returnContent .= 	"<td class='peer-col " . $peerSuccess . "'>" . $totals['peerCheckoff'] . "%</td>";
        $returnContent .= 	"<td class='instructor-col " . $instructorSuccess . "'>" . $totals['instructorCheckoff'] . "%</td>";
        $returnContent .= 	"<td class='eureka-col " . $eurekaStatus . "'>" . $totals['eurekaCheckoff'] . "%</td>";
        $returnContent .= 	"<td class='precent-col " . $overallPrecent . "'>" . $totals['overallPrecent'] . "%</td>";
        $returnContent .= "</tr>";
        return $returnContent;
    }
    
    /**
     * returns a string of HTML content for each row of a definition table (for a summary reoprt type)
     * @param $studentId the student who belongs to this row
     * @param $studentData the data that will be printed out
     * @param $count used for odd/even rows
     * @param $def the definitions
     */
    private function getItemsTableStudentRow($studentId, $studentData, $count, $def, $numberOfGoals)
    {
        $student = \Fisdap\EntityUtils::getEntity("StudentLegacy", $studentId);
        
        // set up classes for successes and odd/even rows
        $successClass = "success";
        $rowClass = ($count%2 == 0) ? "even" : "odd";
        $peerSuccess = ($studentData['peer-checkoffs'] >= $def->peer_goal) ? $successClass : "";
        $instructorSuccess = ($studentData['instructor-checkoffs'] >= $def->instructor_goal) ? $successClass : "";
        $eurekaSuccess = ($def->eureka_goal && $studentData['eureka']) ? $successClass : "";
        $complete = (($studentData['precentage'] == 100)) ? $successClass : "";
        
        if ($this->canSeeNames() || $student->user->id == \Fisdap\Entity\User::getLoggedInUser()->id) {
            $studentName = $student->user->getName();
        } else {
            $studentName = "Anonymous";
        }

        $returnContent = '<tr class="' . $rowClass . '">';
        $returnContent .=	 '<td class="student-col">' . $studentName . '</td>';
        $returnContent .= 	 '<td class="peer-col ' . $peerSuccess . '">' . $studentData['peer-checkoffs'] . '</td>';
        $returnContent .= 	 '<td class="instructor-col ' . $instructorSuccess . '">' . $studentData['instructor-checkoffs'] . '</td>';
        $returnContent .=	 '<td class="eureka-col ' . $eurekaSuccess . '">';
        
        if ($def->eureka_goal) {
            $returnContent .= '<a href="#" class="eureka-trigger"';
            $returnContent .=					'data-defId="' . $def->id . '"';
            $returnContent .=					'data-studentId="' . $student->id . '">';
            $returnContent .= ($studentData['eureka']) ? "Met" : "Not Met";
            $returnContent .= '</a>';
        } else {
            $returnContent .= 	  "N/A";
        }
        
        $returnContent .=      '</td>';
        $returnContent .= 	   '<td class="precent-col ' . $complete . '">';
        $returnContent .=			($numberOfGoals > 0) ? $studentData['precentage'] . "%" : "N/A";
        $returnContent .=	   '</td>';
        $returnContent .= '</tr>';
        return $returnContent;
    }
    
    /*
     * gets the data for a detailed report
     * @param $active bool active/inactive definitions
     * @param $studentId the id of student the detailed report is for
     * @param $dateRange array with a start/end date formatted as a mysql date
     * @param $certLevel given cert level id for goal set, if not given use student's
     */
    public function getDetailedLPIData($active, $studentId, $dateRange, $certLevel = null)
    {
        $student = \Fisdap\EntityUtils::getEntity("StudentLegacy", $studentId);
        $program = $student->program;
        $certLevel = $certLevel ? $certLevel : $student->getCertification();
        $itemRepo = \Fisdap\EntityUtils::getRepository('PracticeItem');
        $definitions = \Fisdap\EntityUtils::getRepository('PracticeDefinition')->getProgramDefinitions($program, $certLevel, false, $active);
        $data = array();
        
        foreach ($definitions as $def) {
            $data[$def->id] = array();
            $data[$def->id]['details'] = array();
            $data[$def->id]['totals'] = array();

            $items = $itemRepo->getItemsForReport($def->id, $student->id, $dateRange);
            $totalPeerCheckoffs = $totalInstructorCheckoffs = 0;
            $eurekaArray[$def->id] = array();
            $attemptCount = 0;
            $peerGoalReached = $instructorGoalReached = 0;

            if ($items) {
                foreach ($items as $item) {
                    $eurekaArray[$def->id][$item->id] = array();
                    $attemptCount++;
                    $evalType = $item->evaluator_type->entity_name;
                    $peerCheckoff = "";
                    $instructorCheckoff = "";
                    $noConfirmationAlert = "";
                    
                    if ($evalType == "StudentLegacy") {
                        $peerEvaluator = \Fisdap\EntityUtils::getEntity("StudentLegacy", $item->evaluator_id);
                        if ($item->passed) {
                            $peerCheckoff = "<img class='checkmark' src='/images/icons/checkmark.png'> ";
                            $totalPeerCheckoffs++;
                            if ($totalPeerCheckoffs == $def->peer_goal) {
                                $peerGoalReached = $item->id;
                            }
                        } else {
                            $peerCheckoff = "<div class='missing-checkoff'></div> ";
                        }

                        if (($peerEvaluator instanceof \Fisdap\Entity\StudentLegacy) && ($peerEvaluator->user instanceof \Fisdap\Entity\User)) {
                            $peerCheckoff .= "<div class='floatFix'>" . $peerEvaluator->user->getName() . "</div>";
                        }
                        array_push($eurekaArray[$def->id][$item->id], $item->passed);
                    } elseif ($evalType == "InstructorLegacy") {
                        $instructorEvaluator = \Fisdap\EntityUtils::getEntity("InstructorLegacy", $item->evaluator_id);
                        
                        if ($item->passed) {
                            if ($item->confirmed) {
                                $instructorCheckoff = "<img class='checkmark' src='/images/icons/checkmark.png'> ";
                            } else {
                                $instructorCheckoff = "<img class='incomplete' src='/images/icons/incomplete.png'> ";
                            }
                        } else {
                            $instructorCheckoff = "<div class='missing-checkoff'></div> ";
                        }

                        if (($instructorEvaluator instanceof \Fisdap\Entity\InstructorLegacy) && ($instructorEvaluator->user instanceof \Fisdap\Entity\User)) {
                            $instructorCheckoff .= "<div class='floatFix'>" . $instructorEvaluator->user->getName() . "</div>";
                        }

                        if (!$item->confirmed) {
                            $noConfirmationAlert = true;
                        } else {
                            if ($item->passed) {
                                $totalInstructorCheckoffs++;
                                if ($totalInstructorCheckoffs == $def->instructor_goal) {
                                    $instructorGoalReached = $item->id;
                                }
                            }
                            array_push($eurekaArray[$def->id][$item->id], $item->passed);
                        }
                    }

                    if ($item->shift instanceof \Fisdap\Entity\ShiftLegacy) {
                        $row = array(
                            "date" => $item->shift->start_datetime->format("n/j/y"),
                            "itemId" => $item->id,
                            "success" => $item->passed,
                            "peerCheckoff" => $peerCheckoff,
                            "instructorCheckoff" => $instructorCheckoff,
                            "confirmationAlert" => $noConfirmationAlert,
                            "shift_id" => $item->shift->id,
                            "shift_type" => $item->shift->type,
                        );

                        array_push($data[$def->id]['details'], $row);
                    }
                }
            }
            
            $eurekaResults = $itemRepo->calculateEurekaFromArray($eurekaArray[$def->id], $def->eureka_goal, $def->eureka_window);
            
            // finish off the array by including some definition details
            $defDetails = array(
                                "eurekaPoint" =>  $itemRepo->getEurekaPoint($eurekaArray[$def->id], $def->eureka_goal, $def->eureka_window),
                                "totalPeerCheckoffs" => $totalPeerCheckoffs,
                                "totalInstructorCheckoffs" => $totalInstructorCheckoffs,
                                "attemptCount" => $attemptCount,
                                "peerGoalReached" => $peerGoalReached,
                                "instructorGoalReached" => $instructorGoalReached,
                                "studentName" => $student->user->first_name . " " . $student->user->last_name
                                );
            
            array_push($data[$def->id]['totals'], $defDetails);
        }
        return $data;
    }
    
    /*
     * gets the data for a summary report
     * @param $active bool active/inactive definitions
     * @param $certLevel the cert level that will be used to determine which definitions should be grabbed
     * @param $students an array of students to be included in the report
     * @param $dateRange array with a start/end date formated as a mysql date
     */
    public function getLPIData($active, $certLevel, $students, $dateRange)
    {
        $program = \Fisdap\EntityUtils::getEntity('ProgramLegacy', \Fisdap\Entity\User::getLoggedInUser()->getProgramId());
        $itemRepo = \Fisdap\EntityUtils::getRepository('PracticeItem');
        $definitions = \Fisdap\EntityUtils::getRepository('PracticeDefinition')->getProgramDefinitions($program, $certLevel, false, $active);
        $data = array();
        $eurekaAttempts = array();
        
        foreach ($definitions as $def) {
            $data[$def->id] = array();
            $items = $itemRepo->getItemsForReport($def->id, array_values($students), $dateRange);

            foreach ($items as $item) {
                if (!$data[$def->id][$item->student->id]) {
                    $data[$def->id][$item->student->id] = $this->getDefaultStudentArray();
                }
                
                if ($item->passed) {
                    $evalType = $item->evaluator_type->entity_name;
                    if ($evalType == "StudentLegacy") {
                        $data[$def->id][$item->student->id]['peer-checkoffs']++;
                    } elseif ($evalType == "InstructorLegacy" && $item->confirmed) {
                        $data[$def->id][$item->student->id]['instructor-checkoffs']++;
                    }
                }
                
                if ($def->eureka_goal) {
                    if (!$eurekaAttempts[$item->student->id][$def->id]) {
                        $eurekaAttempts[$item->student->id][$def->id] = array();
                    }
                    
                    if (($evalType == "StudentLegacy") || ($evalType == "InstructorLegacy" && $item->confirmed)) {
                        $eurekaSuccess = ($item->passed) ? 1 : 0;
                        array_push($eurekaAttempts[$item->student->id][$def->id], $eurekaSuccess);
                    }
                }
            }

            foreach ($students as $student) {
                // this student didn't have any practice items - default their array
                if (!$data[$def->id][$student]) {
                    $data[$def->id][$student] = $this->getDefaultStudentArray();
                } else {
                    // this student already has an array from the practice items.
                    // we just need to finish that by caluclating the precentages and the eureka success
                    if ($def->eureka_goal) {
                        $data[$def->id][$student]["eureka"] = $itemRepo->hasEureka($eurekaAttempts[$student][$def->id], $def->eureka_goal, $def->eureka_window);
                    } else {
                        $data[$def->id][$student]["eureka"] = 0;
                    }
                    
                    $data[$def->id][$student]["precentage"] = $this->calculatePercentage($def, $data[$def->id][$student]['peer-checkoffs'], $data[$def->id][$student]['instructor-checkoffs']);
                }
            }
        }
        return $data;
    }
    
    /*
     * Returns an array for each student for
     * the summary table (overall completion)
     */
    public function getStudentSummary($data, $students)
    {
        $studentTotals = array();
        
        foreach ($students as $studentId) {
            $defGoalCount = 0;
            
            
            $totals = array(
                        "studentPeerTotals" => 0,
                        "studentInstructorTotals" => 0,
                        "studentEurekaTotals" => 0,
                        "defPeerTotals" => 0,
                        "defInstructorTotals" => 0,
                        "defEurekaTotals" => 0
            );
            
            foreach ($data as $defId => $studentArray) {
                $def = \Fisdap\EntityUtils::getEntity("PracticeDefinition", $defId);

                $totals['defPeerTotals'] += $def->peer_goal;
                $totals['defInstructorTotals'] += $def->instructor_goal;
                
                // Don't include peer checkoffs if the instructor hasn't set a goal
                // Don't over count checkoffs past the goal
                if ($def->peer_goal) {
                    $totals['studentPeerTotals'] += ($studentArray[$studentId]['peer-checkoffs'] > $def->peer_goal ? $def->peer_goal : $studentArray[$studentId]['peer-checkoffs']);
                    $defGoalCount++;
                }
                
                if ($def->instructor_goal) {
                    $totals['studentInstructorTotals'] += ($studentArray[$studentId]['instructor-checkoffs'] > $def->instructor_goal ? $def->instructor_goal : $studentArray[$studentId]['instructor-checkoffs']);
                    $defGoalCount++;
                }

                // if there is a eureka goal set...
                if ($def->eureka_goal) {
                    $defGoalCount++;
                    $totals['defEurekaTotals']++;
                    $totals['studentEurekaTotals'] += $studentArray[$studentId]['eureka'];
                }
            }

            $peerCheckoffPercentage = $this->getIndividualPercentage($totals['defPeerTotals'], $totals['studentPeerTotals']);
            $instructorCheckoffPercentage = $this->getIndividualPercentage($totals['defInstructorTotals'], $totals['studentInstructorTotals']);
            $eurekaCheckoffPercentage = $this->getIndividualPercentage($totals['defEurekaTotals'], $totals['studentEurekaTotals']);

            //Now average the percentages that matter (i.e. Not N/A)
            if ($defGoalCount > 0) {
                $percentages = array();
                if (is_numeric($peerCheckoffPercentage)) {
                    $percentages[] = $peerCheckoffPercentage;
                }
                if (is_numeric($instructorCheckoffPercentage)) {
                    $percentages[] = $instructorCheckoffPercentage;
                }
                if (is_numeric($eurekaCheckoffPercentage)) {
                    $percentages[] = $eurekaCheckoffPercentage;
                }
                
                if (!empty($percentages)) {
                    $percent = array_sum($percentages)/count($percentages);
                } else {
                    $percent = 0;
                }
                
                $overallPercentage = round($percent, 1);
            } else {
                $overallPercentage = "N/A";
            }
            
            $studentTotals[$studentId] = array("peerCheckoff" => $peerCheckoffPercentage,
                                               "instructorCheckoff" => $instructorCheckoffPercentage,
                                               "eurekaCheckoff" => $eurekaCheckoffPercentage,
                                               "overallPrecent" => $overallPercentage,
                                               );
        }
        
        return $studentTotals;
    }

    /**
     * calculates the final percentage for a given definition/student
     *
     * @param $def
     * @param $peerCheckoffs
     * @param $instructorCheckoffs
     *
     * @return integer
     */
    private function calculatePercentage($def, $peerCheckoffs, $instructorCheckoffs)
    {
        $totalGoals = 0;
        $totalCheckoffs = 0;

        if ($def->peer_goal) {
            //Add the peer goal to the overall goal
            $totalGoals += $def->peer_goal;

            //Don't over count checkoffs past the goal
            if ($peerCheckoffs > $def->peer_goal) {
                $totalCheckoffs += $def->peer_goal;
            } else {
                $totalCheckoffs += $peerCheckoffs;
            }
        }

        if ($def->instructor_goal) {
            //Add the instructor goal to the overall goal
            $totalGoals += $def->instructor_goal;

            //Don't over count checkoffs past the goal
            if ($instructorCheckoffs > $def->instructor_goal) {
                $totalCheckoffs += $def->instructor_goal;
            } else {
                $totalCheckoffs += $instructorCheckoffs;
            }
        }
        
        if ($totalGoals > 0) {
            $percent = ($totalCheckoffs / $totalGoals) * 100;
            $totalPercentage = round($percent, 1);
        } else {
            $totalPercentage = "N/A";
        }
        
        return $totalPercentage;
    }
    
    /*
     * returns the percentage
     * @param defTotal the number of total checkoffs needed for all definitions for a certification
     * @param checkoffs the total number of checkoffs a student has for all of their definitions
     */
    private function getIndividualPercentage($defTotal, $checkoffs)
    {
        if ($defTotal > 0) {
            $percent = round((($checkoffs / $defTotal) * 100), 1);
            if ($percent > 100) {
                $percent = 100;
            }
        } else {
            $percent = "N/A";
        }
        return $percent;
    }
    
        
    /*
     * returns an array full of defaults for the
     * students who have not attempted a practice definition yet
     */
    private function getDefaultStudentArray()
    {
        return array("peer-checkoffs" => 0,
                    "instructor-checkoffs" => 0,
                    "eureka" => 0,
                    "precentage" => 0
                    );
    }
    
            
    /*
     * returns an associative array for the date range
     * the start and end dates will be in friendly format for the query
     * also handles if the dates are the same
     * @param $startDate string in mm/dd/yyyy format
     * @param $endDate string in mm/dd/yyyy format
     */
    public function getDateRange($startDate, $endDate)
    {
        $dateRange = array();

        if ($startDate) {
            $start = new DateTime($startDate);
            $dateRange["start_date"] = $start->format('Y-m-d H:i:s');
        }

        if ($endDate) {
            $end = new DateTime($endDate);
            $end->modify("+1 day");
            $end->modify("-1 second");
            $dateRange["end_date"] = $end->format('Y-m-d H:i:s');
        }

        return $dateRange;
    }
    
    /**
     * returns a class name to be added to the table if there are too many skills (makes it wider)
     * @param $numberOfSkills the number of columns that will have to be included
     */
    private function getBigTableClass($numberOfSkills)
    {
        if ($numberOfSkills > 3) {
            $bigTableClass = 'wide-table';
            if ($numberOfSkills > 5) {
                $bigTableClass = 'really-wide-table';
            }
        } else {
            $bigTableClass = '';
        }
        
        return $bigTableClass;
    }
    
    /**
     * returns true if the logged in user can see names on the report
     * returns false if the logged in user is a student or an instructor with the 'reports without names' permission
     */
    private function canSeeNames()
    {
        $loggedInUser = \Fisdap\Entity\User::getLoggedInUser();
        if (!$loggedInUser->isInstructor() || !$loggedInUser->hasPermission('View Reports')) {
            return false;
        }
        
        return true;
    }
}
