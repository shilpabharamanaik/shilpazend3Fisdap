<?php

class MyFisdap_Widgets_PatientCareDocWidget extends MyFisdap_Widgets_Base
{
    public function render()
    {
        $user = $this->getWidgetUser();
        
        if ($user->isInstructor()) {
            $html = $this->renderInstructorView();
        } else {
            $html = $this->renderStudentView();
        }
        
        return $html;
    }
    
    public function renderInstructorView()
    {
        $user = $this->getWidgetUser();
        
        $shiftRepos = \Fisdap\EntityUtils::getRepository('ShiftLegacy');
        
        $lateShifts = $shiftRepos->getLateShiftAllStudents($this->getWidgetProgram()->id)->fetchAll();
                
        $html = '';
        
        if (count($lateShifts) == 0) {
            $html .= "<div class='patient-care-block-text'>Currently, none of your students have past due shifts.</div>";
        } elseif (count($lateShifts) > 1) {
            $html .= "<div class='patient-care-block-text'>These students still need to document and lock shifts that are now late.</div>";
        } else {
            if ($lateShifts[0]['late_shift_count'] == 1) {
                $html .= "<div class='patient-care-block-text'>This student still needs to document and lock a shift that is now late.</div>";
            } else {
                $html .= "<div class='patient-care-block-text'>This student still needs to document and lock shifts that are now late.</div>";
            }
        }
        
        foreach ($lateShifts as $shiftData) {
            $shiftString = 'shifts';
            
            if ($shiftData['late_shift_count'] == 1) {
                $shiftString = 'shift';
            }
            
            // Get the listing for the shifts themselves...
            $lateShiftsHtml = $this->renderInstructorViewStudentRow($shiftData['student_id']);
            
            $html .= "
				<div class='patient-care-upcoming'>
					<a href='/skills-tracker/shifts/index/studentId/{$shiftData['student_id']}'>{$shiftData['name']}</a>
					has
					<a href='#' class='patient-care-student-toggle' data-student_id='{$shiftData['student_id']}'>{$shiftData['late_shift_count']} {$shiftString}</a>
					</span>
					past due.
					
					<div class='patient-care-shift-list' data-student_id='{$shiftData['student_id']}' style='display: none;'>
					    $lateShiftsHtml
					</div>
				</div>
			";
        }
        
        $js = "
    		<script>
		        $('.patient-care-student-toggle').click(function(e){
		            e.preventDefault();
		            
		            var sid = $(this).attr('data-student_id');
		            
		            $('.patient-care-shift-list[data-student_id=\"' + sid + '\"]').toggle();
		            
		            return false;
		        });
    		</script>
		";
        
        $html .= "\n\n" . $js;
        
        
        return $html;
    }
    
    private function renderInstructorViewStudentRow($studentId)
    {
        $shiftRepos = \Fisdap\EntityUtils::getRepository('ShiftLegacy');
        
        $lateShiftRecords = $shiftRepos->getStudentLateShiftArray($studentId);
        
        $html = "";
        
        foreach ($lateShiftRecords as $lsr) {
            if ($lsr['start_datetime'] instanceof \DateTime) {
                $shiftDate = $lsr['start_datetime']->format('M d, Y | Hi');
            } else {
                $shiftDate = null;
            }
            $topRow = "<a href='/skills-tracker/shifts/my-shift/shiftId/{$lsr['id']}'>$shiftDate ({$lsr['hours']}hrs)</a>";
            $bottomRow = $lsr['site_abbreviation'] . " : " . $lsr['base_name'];
            $html .= "<div class='patient-care-late-shift'>$topRow<br />$bottomRow</div>";
        }
        
        return $html;
    }
    
    public function renderStudentView()
    {
        $user = $this->widgetData->user;
        
        $shiftRepos = \Fisdap\EntityUtils::getRepository('ShiftLegacy');
        
        $html = '';
        
        $hours = 72;
        
        // Print out the past due shifts...
        $lateShifts = $shiftRepos->getStudentLateShifts($user->getCurrentRoleData()->id);
        $shifts = $shiftRepos->getUpcomingShifts($user->getCurrentRoleData()->id, $hours);
        
        if ($lateShifts->rowCount() >= 1 || $shifts->rowCount() >= 1) {
            foreach ($shifts as $shiftData) {
                $shift = \Fisdap\EntityUtils::getEntity('ShiftLegacy', $shiftData['Shift_id']);
            
                $deadline = strtotime($shiftData['deadline']) - time();
                $deadline = round($deadline/3600, 2);
                $hoursUntilDue = round($deadline/.5) * .5;
                
                $html .= "
					<div class='patient-care-upcoming'>" .
                    "<span class='patient-care-upcoming-title'>Due in $hoursUntilDue hours</span>:<br />" .
                    "<a href='/skills-tracker/shifts/my-shift/shiftId/{$shiftData['Shift_id']}'>" .
                    $shift->start_datetime->format('m-d-Y, Hi') . ", " .
                    $shift->site->name . ", " .
                    $shift->base->name .
                    "</a>" .
                    "</div>";
            }
            
            
            
            if ($lateShifts->rowCount() > 0) {
                $html .= "<h2 class='patient-care-gray-header'>Past Due</h2>";
            }
            
            $currentDisplayCount = 0;
            $maxDefaultVisible = 3;
            $hasOverflowed = false;
            $togglerDivName = $this->getNamespacedName('patient_care_toggler');
            $toggleDivName = $this->getNamespacedName('patient_care_hidden_shifts');
            
            foreach ($lateShifts as $lateShiftData) {
                if ($currentDisplayCount == $maxDefaultVisible) {
                    $hasOverflowed = true;
                
                    $html .= "
					<a class='patient-care-toggle' id='$togglerDivName-1' href='#'>Show " . ($lateShifts->rowCount()-$maxDefaultVisible) . " more records</a>
					<div id='$toggleDivName' style='display: none'>
					";
                }
                
                // Catch any Entity Not Found errors, so that we can display the rest of the list
                // in case just one or two shifts have unexpected problems
                // display error text if a shift cannot be properly loaded
                try {
                    $shift = \Fisdap\EntityUtils::getEntity('ShiftLegacy', $lateShiftData['id']);
        
                    $html .= "
					<div class='patient-care-upcoming'>" .
                    "<a href='/skills-tracker/shifts/my-shift/shiftId/{$lateShiftData['id']}'>" .
                    $shift->start_datetime->format('m-d-Y, Hi') . ", " .
                    $shift->site->name . ", " .
                    $shift->base->name .
                    "</a>" .
                    "</div>";
                } catch (\Doctrine\ORM\EntityNotFoundException $e) {
                    $html .= "<div class='patient-care-upcoming'>Error loading information for shift ID " . $lateShiftData['id'] . "</div>";
                }
                
            
                $currentDisplayCount++;
            }
            
            // If we have hidden shifts, close out the toggle div, add in
            if ($hasOverflowed) {
                $html .= "
					<a class='patient-care-toggle' id='$togglerDivName-2' href='#'>Hide extra shifts</a>
					</div>
					</div>
					<script>
						$('#$togglerDivName-1').click(function(){
							$('#$togglerDivName-1').toggle();
							$('#$toggleDivName').toggle();
							return false;
						});
						$('#$togglerDivName-2').click(function(){
							$('#$togglerDivName-1').toggle();
							$('#$toggleDivName').toggle();
							return false;
						});
					</script>
				";
            }
        } else {
            $html .= "<div class='patient-care-block-text'>No upcoming or past due shifts found.</div>";
        }
        
        return $html;
    }
    
    public function getDefaultData()
    {
        return array();
    }
    
    public static function userCanUseWidget($widgetId)
    {
        $user = \Fisdap\EntityUtils::getEntity('MyFisdapWidgetData', $widgetId)->user;
        
        // User has to:
        // Be a student using skills tracker
        // Be an instructor who has permission to view all data, and have at least one
        // student who uses skills tracker.
        if ($user->isInstructor() && $user->hasPermission("View All Data")) {
            // This query returns a 1 if a student exists in the instructors program that uses skills tracker, or 0 if it doesn't...
            $sql = "
				SELECT IF(COUNT(*) > 0, 1, 0) as result FROM StudentData sd INNER JOIN SerialNumbers sn ON sn.Student_id = sd.Student_id WHERE sd.Program_id = " . $user->getProgramId() . " AND ((sn.Configuration & 1) = 1);
			";
            
            $conn = \Fisdap\EntityUtils::getEntityManager()->getConnection();
            $result = $conn->query($sql);

            $row = $result->fetch();
            
            if ($row !== false) {
                if ($row['result'] == 1) {
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            // Check the logged in user (presumably a student) to see if they have access to
            // skills tracker...
            $sn = $user->getCurrentUserContext()->getPrimarySerialNumber();
            
            if ($sn && $sn->hasSkillsTracker()) {
                return true;
            }
        }
        
        return false;
    }
}
