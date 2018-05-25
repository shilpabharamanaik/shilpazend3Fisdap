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
 * This helper will display the nav bar across the various reports pages
 */

/**
 * @package Reports
 */
class Reports_View_Helper_ReportsNavBar extends Zend_View_Helper_Abstract
{
	/*
	 * Array of button visiblity
	 * (all values except for request_count_html are Boolean)
	 */
	protected $buttons;
	
	/*
	 * Array of recent reports run by this user
	 */
	protected $recent;
	
	/*
	 * String the name of the current page
	 */
	protected $page;
	
	/**
	 * This will build the HTML for the nav bar that appears on all Reports pages.
	 * This will handle which links should be visible depending on permissions, account type, current app location
	 * @param String $page the name of the current page: 'main', 'history', 'goals', 'reports_settings'
	 * 
	 * @return string the menu rendered as html
	 */
	public function reportsNavBar($page = null)
	{
		$this->page = $page;
		
		// add our css/js
		$this->view->headScript()->appendFile("/js/library/Fisdap/View/Helper/navbar-menu.js");
		$this->view->headLink()->appendStylesheet("/css/library/Fisdap/View/Helper/navbar-menu.css");
		
		
		// handle permssions
		$user = \Fisdap\Entity\User::getLoggedInUser();
		$is_student = ($user->getCurrentRoleName() == 'instructor') ? false : true;
		$instructor = $user->getCurrentRoleData();

		// now for button 
		$this->buttons['history'] = TRUE;
		$this->buttons['main'] = TRUE;
		$this->buttons['goals'] = TRUE;
		$this->buttons['settings'] = ($is_student) ? false : $instructor->hasPermission("Edit Program Settings");
		
		// get the three most recent reports run by this user
		$user_context = $user->getCurrentUserContext();
		$recentConfigurations = \Fisdap\EntityUtils::getRepository('Report')->getRecentActiveConfigs($user_context->id, 3);
		$recentReports = array();
		

		// configure the data about these reports for use in the nav bar
		foreach ($recentConfigurations as $i => $configuration) {
			$recentReports[$configuration->id] = array('report' => $configuration->report->name,
													   'class' => $configuration->report->class,
													   'description' => $configuration->getDescription());
		}
		
		
		$this->recent = $recentReports;
		// render the view
		return $this->getHTML();
		
	} // end navBar()
	
	
	/*
	 * Will render the HTML for the navbar
	 * (uses the global buttons array to determine visibility)
	 *
	 * @return String the rendered HTML
	 */
	public function getHTML()
	{
		$html  = '<ul id="nav-bar">';
		
		// ADD BUTTONS IN RIGHT TO LEFT ORDER, SINCE THEY FLOAT RIGHT
		
		// settings button
		if($this->buttons['settings']){
			$settings_id = "id='reports_nav_bar_settings'";
			
			if ($this->page == 'settings') {
				$html .= "<li " . $settings_id . " class='single-navbar-option settings-link active'>Settings</li>";
			} else {
				$html .= "<li " . $settings_id . " class='single-navbar-option settings-link'><a href='/reports/settings'>Settings</a></li>";
			}
		}
		
		// Goals button
		if($this->buttons['goals']){
			$goals_id = "id='reports_nav_bar_goals'";
			
			if ($this->page == 'goals') {
				$html .= "<li " . $goals_id . " class='single-navbar-option active'>Goals</li>";
			} else {
				$html .= "<li " . $goals_id . " class='single-navbar-option'><a href='/reports/goals'>Goals</a></li>";
			}
		}
		
		// Main button
		if($this->buttons['main']){
			$all_reports_id = "id='reports_nav_bar_allreports'";
			if ($this->page == 'main') {
				$html .= "<li " . $all_reports_id . " class='single-navbar-option active'>All Reports</li>";
			} else {
				$html .= "<li " . $all_reports_id . "class='single-navbar-option'><a href='/reports'>All Reports</a></li>";
			}
		}
		
		// history drop down
		if($this->buttons['history']){
			$history_id = "id='reports_nav_bar_history'";
			$html .= '<div class="dashed-right-border"></div>';
			$html .=	"<li " . $history_id . " class='nav-bar-menu-item' data-dropDownId='navbar-options'>";
			$html .=		"Recent Reports";
			$html .=		'<ul id="navbar-options" class="navbar-options">';
			
			if($this->recent) {
				foreach ($this->recent as $config_id => $info) {
					$report = $info['report'];
					$class = $info['class'];
					$description = $info['description'];
					$url = "/reports/index/display/report/$class/config/$config_id";
					$html .=	"<li>
									<a class='navbar-sub-option' href='$url'>
										<div class='sub-option-title'>$report</div>
										<div class='sub-option-description'>$description</div>
									</a>
								</li>";
				}
			} else {
				$html .=		"<li class='inactive'>
									<div class='sub-option-title'>You have not run any reports.</div>
								</li>";
			}
			$html .=			'<li><a class="navbar-sub-option" href="/reports/index/history">Report history...</a></li>';
			$html .=		"</ul>";
			$html .=	"</li>";
		}
		
		
		$html .= "</ul>";
		
		return $html;
		
	} // end getHTML()

} // end Reports_View_Helper_NavBar
