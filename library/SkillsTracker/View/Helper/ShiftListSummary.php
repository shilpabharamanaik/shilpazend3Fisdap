<?php

/* * **************************************************************************
 *
 *         Copyright (C) 1996-2011.  This is an unpublished work of
 *                          Headwaters Software, Inc.
 *                             ALL RIGHTS RESERVED
 *         This program is a trade secret of Headwaters Software, Inc.
 *         and it is not to be copied, distributed, reproduced, published,
 *         or adapted without prior authorization
 *         of Headwaters Software, Inc.
 *
 * ************************************************************************** */

/**
 * This class 
 *
 * @author astevenson
 */
class SkillsTracker_View_Helper_ShiftListSummary
{
	public function shiftListSummary($shiftsPartials, $student)
	{
		$shifts = Util_Db::extractDBResults($shiftsPartials, 'shift', 'id');
		
		$summaries = \Fisdap\Shifts::getShiftsSummaries($shifts);
		
		$nameString = ($student) ?
			$student->user->first_name . " " . $student->user->last_name : '';
		
		$html = "<br />";
		
		foreach($summaries as $key => $vals){
			$html .= "<div class='grid_4'>";
			$html .= "<h4>$key</h4>";
			foreach($vals as $subKey => $subVal){
				$html .= $subKey . ": " . $subVal . "<br />";
			}
			$html .= "</div>";
		}
		
		$html .= "<div class='clear'></div>";
		$html .= "<br />";
		
		$studentLine = "See " . $nameString . "'s <br />";
		
		$html .= "<div class='grid_4'>$studentLine<a href='#' class='launch-report-config' data-studentid='".$student->id."' data-reporttype='Hours'>Hours Report</a></div>";
		$html .= "<div class='grid_4'>$studentLine<a href='#' class='launch-report-config' data-studentid='".$student->id."' data-reporttype='GraduationRequirements'>Graduation Requirements Report</a></div>";
		$html .= "<div class='grid_4'>$studentLine<a href='#' class='launch-report-config' data-studentid='".$student->id."' data-reporttype='Attendance'>Attendance Report</a></div>";
		$html .= "<div class='clear'></div>";
		
		return $html;
	}
}
