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
 * Shift summary display helper
 * Will display info about a shift in a consistent way
 * 
 * @author Hammer :)
 */
class Fisdap_View_Helper_ShiftSummaryDisplayHelper extends Zend_View_Helper_Abstract
{
	/*
	 * the arary of data to display!
	 * 
	 * must haves if 'small' display size:
	 * ['shift_id']
	 * ['start_datetime'] -> date time object
	 * ['type'] -> lab/clinical/field
	 *
	 * must haves if 'large' display size:
	 * the 3 list above
	 * ['duration']
	 * ['site_name']
	 * ['base_name']
	 *
	 * optional for 'large' display size:
	 * ['instructors'] -> comma separated string of names (from shift's event ONLY)
	 * ['preceptors'] -> comma separated string of names (from shift's event ONLY)
	 */
	public $shift_data;
	
	/*
	 * Can send in a shift id, a shift entity, or an array of shift values
	 * @param String $display_size can either be 'large' or 'small'
	 * 		'large' : will include all shift info and have larger font
	 *      'medium': includes date and location info, with larger font
	 * 		'small' : only includes start date, and smaller font
	 */
	public function shiftSummaryDisplayHelper($shift_data = null, $shift_id = null, $shift_entity = null, $summary_options = array())
	{
		$html = "";

        // work out the options for this display
        $display_size = (isset($summary_options['display_size'])) ? $summary_options['display_size'] : "large";
        $shift_link = (isset($summary_options['shift_link'])) ? $summary_options['shift_link'] : true;
		
		if ($shift_id) {
			// if we have an ID, grab the entity
			$shift_entity = \Fisdap\EntityUtils::getEntity("ShiftLegacy", $shift_id);
		}
		
		if ($shift_entity) {
			// take the entity, and put the data we care about into an array
			$data = array();
			$data['shift_id'] = $shift_entity->id;
			$data['start_datetime'] = $shift_entity->start_datetime;
			$data['type'] = $shift_entity->site->type;
			
			if ($display_size == 'large' || $display_size == 'medium') {
                try {
                    $base_name = $shift_entity->base->name;
                } catch (Exception $e) {
                    $base_name = "unknown";
                }
                //$base_name = empty($shift_entity->base) ? $shift_entity->base->name : "unknown";
                $data['duration'] = $shift_entity->hours;
                $data['site_name'] = $shift_entity->site->name;
                $data['base_name'] = $base_name;
            }

            if ($display_size == 'large') {
				$data['instructors'] = $shift_entity->getInstructorList();
				$data['preceptors'] = $shift_entity->getPreceptorList();
			}
			
			$this->shift_data = $data;
		} else {
			// we already have a shift data array!
			$this->shift_data = $shift_data;
		}
		
		if ($this->shift_data) {
            if ($shift_link) {
                $html .= "<a title='Go to Skills & Pt. Care' href='/skills-tracker/shifts/my-shift/shiftId/" . $this->shift_data['shift_id'] . "' class='shift_summary_wrapper shift_summary_display_" . $display_size . "' data-shiftid='" . $this->shift_data['shift_id'] . "'>";
            } else {
                $html .= "<div class='shift_summary_wrapper shift_summary_display_" . $display_size . "' data-shiftid='" . $this->shift_data['shift_id'] . "'>";
            }

            if ($summary_options['show_icon']) {
                $html .=		"<img class='site-icon' src='/images/icons/" . $this->shift_data['type'] . "SiteIconColor.png'>";
            }

            $html .=		"<div class='site-info'>";
            $html .=		$this->getDateTimeDisplay($display_size);
			
			if ($display_size == 'large') {
				$html .=		$this->getLocationDisplay();
				$html .=		$this->getPreceptorDisplay();
				$html .=		$this->getInstructorDisplay();
			}

            if ($display_size == 'medium') {
                $html .=		$this->getLocationDisplay();
            }

            if ($summary_options['show_attendance']) {
				$html .=		"<div class='shift_summary_basic_display'>";
				if ($shift_entity->start_datetime->format("U") < date("U")) {
					$html .= $shift_entity->attendence->name. " | ";
				}
				$html .=		"Shift ID: ".$shift_entity->id."</div>";
            }

            $html .= "</div>";
            $html .= "<div class='clear'></div>";

            if ($shift_link) {
                $html .= "</a>";
            } else {
                $html .= "</div>";
            }
		}
		
		// if we need to be able to sort this by date, prepend a hidden formatted date
		if ($summary_options['sortable']) {
			$start_datetime = $this->shift_data['start_datetime'];
			$sort_string = $start_datetime->format('YmdHi');
			$html = "<span class='hidden'>$sort_string</span>".$html;
		}
		
		return $html;
	
	} // end shiftSummaryDisplayHelper()
	
	
	
	
	/*
	 * ---------------------------------------- and some helper functions! ----------------------------------------
	 */
	
	
	/*
	 * Returns $html to be rendered for the date/time display
	 * @param String $display_size, if 'small' will not include time/duration
	 *
	 */
	public function getDateTimeDisplay($display_size)
	{
		$duration = $this->shift_data['duration'];
		$start_datetime = $this->shift_data['start_datetime'];
		$duration_display = (is_numeric( $duration ) && floor( $duration ) != $duration) ? $duration : intval($duration);
		
		$display  = "<div class='" . $this->shift_data['type'] . " shift_summary_date_display'>";
		$display .= 		$start_datetime->format('M j, Y');
		
		if ($display_size == 'large' || $display_size == 'medium') {
			$display .= 		$start_datetime->format(' | Hi');
			$display .= 		" (" . $duration_display . "hr";
			$display .= 		($duration_display != 1) ? "s" : "";
			$display .=		")";
		}
		
		$display .= "</div>";
		return $display;
		
	} // end getDateTimeDisplay() 
	
	/*
	 * Returns the html to be rendered for the preceptor list.
	 * If no preceptors, will return an empty string
	 */
	public function getPreceptorDisplay()
	{
		$display = "";
		
		if($this->shift_data['preceptors']){
			$display .= "<div class='shift_summary_preceptor_display'>";
			$display .=		$this->shift_data['preceptors'];
			$display .= "</div>";
		}
		
		return $display;
	
	} // end getPreceptorDisplay()


	/*
	 * Returns the html to be rendered for the location
	 * This includes SITE NAME: BASE NAME
	 */
	public function getLocationDisplay()
	{
		$display  = "<div class='shift_summary_location_display'>";
		$display .=		$this->shift_data['site_name'] . ": ";
		$display .=		$this->shift_data['base_name'];
		$display .= "</div>";
		
		return $display;
	
	} // end getLocationDisplay()
	
	
	/*
	 * Returns the html to be rendered for the instructor list.
	 * If no instructors, will return an empty string
	 */
	public function getInstructorDisplay()
	{
		$display = "";
		
		if($this->shift_data['instructors']){
			$display .= "<div class='shift_summary_instructor_display'>";
			$display .=		$this->shift_data['instructors'];
			$display .= "</div>";
		}
		
		return $display;
	
	} // end getInstructorDisplay()

    /*
	 * Returns the html to be rendered for the attendance status.
	 * If the shift is in the future, will return an empty string
	 */
    public function getAttendanceDisplay()
    {
        $display = "";

        if ($this->shift_data['instructors']) {
            $display .= "<div class='shift_summary_instructor_display'>";
            $display .=		$this->shift_data['instructors'];
            $display .= "</div>";
        }

        return $display;

    } // end getInstructorDisplay()
	
	
} // end Fisdap_View_Helper_ShiftSummaryDisplayHelper