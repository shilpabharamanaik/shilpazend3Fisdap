<?php

class MyFisdap_Widgets_LabSkillsReport extends MyFisdap_Widgets_Base
{
	public function render(){
		$widgetSession = new \Zend_Session_Namespace("WidgetData");
		
		$html = '';
		
		// Get the current stats for the lab goals...
		$labResults = $this->getLabGoalResults();
		
		
		$html .= '
			<script type="text/javascript" src="/js/jquery.eurekaGraph.js"></script>
			<script type="text/javascript" src="/js/jquery.printElement.min.js"></script>
			<script type="text/javascript" src="/js/library/SkillsTracker/View/Helper/eureka-modal.js"></script>
			<script type="text/javascript" src="/js/jquery.jqplot.min.js"></script>
			<script type="text/javascript" src="/js/syntaxhighlighter/scripts/shCore.min.js"></script>
			<script type="text/javascript" src="/js/syntaxhighlighter/scripts/shBrushJScript.min.js"></script>
			<script type="text/javascript" src="/js/syntaxhighlighter/scripts/shBrushXml.min.js"></script>
			
			<link type="text/css" rel="stylesheet" media="screen" href="/css/jquery.jqplot.min.css">
			<link type="text/css" rel="stylesheet" media="screen" href="/css/jquery.eurekaGraph.css">
			<link type="text/css" rel="stylesheet" media="screen" href="/js/syntaxhighlighter/styles/shCoreDefault.min.css">
			<link type="text/css" rel="stylesheet" media="screen" href="/js/syntaxhighlighter/styles/shThemejqPlot.min.css">
			
			<script type="text/javascript" src="/js/skills-tracker/shifts/eureka_modal.js"></script>

		';
		
		$html .= "<div class='goals-widget-background'>";
		
		$html .= "
		<div class='grid_12'>
			<span class='grid_4'>&nbsp;</span>
			<span class='grid_5 title_heading'><img src='/images/icons/lab_skills_icon_peer_white.png' title='Peer Reviews'/></span>
			<span class='grid_1 title_heading'><img src='/images/icons/lab_skills_icon_instructor_white.png' title='Instructor Signoffs'/></span>
			<span class='grid_2 title_heading'><img src='/images/icons/lab_skills_icon_eureka_white.png' title='Eureka Point Reached'/></span>
		</div>
		<div class='clear'></div>
		";
		
		$count = 1;
		
		// Several layers of output here.  First, output the category headers.
		foreach($labResults['category_data'] as $categoryId => $categoryData){

			$html .= "<div class='goals-widget-header'>" . $categoryData['category_name'] . "</div>";

			// Now loop over all of the active definitions for this category...
			foreach($categoryData['definitions'] as $defId){
				$html .= "<div class='grid_12'>";
				$html .= "<div class='grid_4 goal_heading'>" . $labResults['definition_data'][$defId]['definition_name'] . "</div>";
				
				// Get the values from the lab results for this student...
				$stats = $this->getStudentStatistics($labResults, $categoryId, $defId);
				
				if($stats['peer_goal'] == 0){
					$percent = 100;
				}else{
					$percent = floor(($stats['peer_actual'] / $stats['peer_goal']) * 100);
				}
				
				// Cap the percent at 100%...  Again...
				if($percent > 100){
					$percent = 100;
				}
				
				$halfPercent = $percent / 2.3;
				
				$percentClass = $this->getPercentClass($percent);
				
				if($percent == 100){
					$checkImage = "<div class='goal-complete-checkmark'><img src='/images/icons/checkmark-dark-gray.png'/></div>";
				}else{
					$checkImage = '';
				}
				
				if($stats['peer_goal'] > 0){
					$fontAdjustment = ($percent == 0) ? "zero-precent-margin-fix" : "";

					$html .= "
						<div class='grid_5'>
							<div class='$percentClass percent_bar' style='width: {$halfPercent}%'>
								$checkImage
							</div>
							<span class='percent_font percent_float_text " . $fontAdjustment . "'>
								{$percent}%
								<span class='percent_subtext'>
									({$stats['peer_actual']} of {$stats['peer_goal']})
								</span>
							</span>
						</div>
					";
				}else{
					$html .= "<div class='grid_5 percent_subtext no-goal'><span class='no-peer-goal'>N/A</span></div>";
				}
				
				if($stats['instructor_goal'] > 0){
					$html .= "<div class='grid_1 goal_heading'>" . "{$stats['instructor_actual']} of {$stats['instructor_goal']}" . "</div>";
				}else{
					$html .= "<div class='grid_1 percent_subtext no-goal centered-txt'>N/A</div>";
				}
				
				if($stats['eureka_goal'] > 0 && $stats['eureka_window'] > 0){
					$metEurekaOuttext = ($stats['met_eureka']?'Met':'Not Met');
					
					$html .= "
						<div class='grid_2 centered-txt'>
							<a href='/reports/lab-practice' class='open_eureka' data-defId='{$defId}' data-studentId='" . $this->getUser()->getCurrentRoleData()->id . "'>{$metEurekaOuttext}</a>
						</div>
					";
				}else{
					$html .= "<div class='grid_2 centered-txt'>N/A</div>";
				}
				
				$html .= "</div>";
				
				$html .= "<div class='clear'></div>";
			}
			
			if($count < count($labResults['category_data'])){
				$html .= "<div class='divide-line'></div>";
			}
			else {
				$html .= "<div class='btm-padding'></div>";
			}
			$count++;

		}
		

		
		$html .= "</div>";
		
		// Do stuff for the eureka modal
		$view = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getResource('view');
		$html .= $view->eurekaModal();
		
		return $html;
	}
	
	private function getUser(){
		$user = \Fisdap\Entity\User::getLoggedInUser();
		
		// In this case, pull the user from the session...
		if($user->isInstructor()){
			// This gets set in the ShiftsController.  Kind of gross, but it works.
			$widgetSession = new \Zend_Session_Namespace("WidgetData");
			
			return \Fisdap\EntityUtils::getEntity('User', $widgetSession->user_id);
		}else{
			return $user;
		}
	}
	
	/**
	 * This function calculates and returns the necessary values for the goal progress lines.
	 * 
	 * @param unknown_type $labResults
	 * @param unknown_type $categoryId
	 * @param unknown_type $defId
	 * 
	 * @return Array containing the percentage complete, goal total, instructor signoff count,
	 * total signoff count, and whether or not the eureka point has been hit.
	 */
	private function getStudentStatistics(&$labResults, $categoryId, $defId){
		$user = $this->getUser();
		
		$items = $labResults['item_data'][$categoryId][$defId][$user->getCurrentRoleData()->id];
		$defRecord = $labResults['definition_data'][$defId];
		
		$returnValues = array();
		
		$returnValues['peer_goal'] = $defRecord['peer_goal'];
		$returnValues['peer_actual'] = 0;
		
		$returnValues['instructor_goal'] = $defRecord['instructor_goal'];
		$returnValues['instructor_actual'] = 0;
		
		if(is_array($items)){
			$itemList = array();
			
			foreach($items as $itemId => $item){
				if($item['evaluator_type_id'] == 2){
					if($item['passed']){
						$returnValues['peer_actual']++;
						array_push($itemList, 1);
					}
					else {
						array_push($itemList, 0);
					}

				}
				else if($item['evaluator_type_id'] == 1 && $item['confirmed']){
					if($item['passed']){
						$returnValues['instructor_actual']++;
						array_push($itemList, 1);
					}
					else {
						array_push($itemList, 0);
					}
				}
			}
			
			$practiceItemRepo = \Fisdap\EntityUtils::getRepository('PracticeItem');
			
			$returnValues['met_eureka'] = $practiceItemRepo->hasEureka($itemList, $defRecord['eureka_goal'], $defRecord['eureka_window']);
		}
		
		$returnValues['eureka_window'] = $defRecord['eureka_window'];
		$returnValues['eureka_goal'] = $defRecord['eureka_goal'];
		
		return $returnValues;
	}
	
	private function getLabGoalResults(){
		$itemRepo = \Fisdap\EntityUtils::getRepository('PracticeItem');
		
		$user = $this->getUser();
		$program = \Fisdap\EntityUtils::getEntity('ProgramLegacy', $user->getProgramId());
		
		$studentIDArray = array($user->getCurrentRoleData()->id);
		
		$labResults = $itemRepo->getItems($program, $user->getCurrentRoleData()->getCertification(), $studentIDArray);
		
		return $labResults;
	}
	
	/**
	 * Override the default to remove padding
	 *
	 * @return String containing the HTML for the widget container.
	 */
	public function renderContainer(){
		$widgetContents = $this->render();
	
		$header = $this->renderHeader();
	
		$html = <<<EOF
			<div id='widget_{$this->widgetData->id}_container' class='widget-container widget-container-blank' data-widget-id='{$this->widgetData->id}'>
				<div class='widget-title-bar'>
					$header
				</div>
				<div id='widget_{$this->widgetData->id}_render' class='widget-render'>
					{$widgetContents}
				</div>
			</div>
EOF;
		
		return $html;
	}
	
	public function renderTitle()
	{
		$program = \Fisdap\Entity\ProgramLegacy::getCurrentProgram();
		$title = $program->hasSkillsPractice() ? "Skills Practice Goals" : "Lab Goals";
		return "<span class='widget-title'>$title</span>";
	}
	
	/**
	 * This function returns the CSS class for the given percent.
	 * 
	 * @param integer $percent to find the class for.
	 */
	private function getPercentClass($percent){
		if($percent >= 0 && $percent < 15){
			$percentClass = 'percent_0-14';
		}elseif($percent >= 15 && $percent < 30){
			$percentClass = 'percent_15-30';
		}elseif($percent >= 30 && $percent < 45){
			$percentClass = 'percent_30-44';
		}elseif($percent >= 45 && $percent < 65){
			$percentClass = 'percent_45-64';
		}elseif($percent >= 65 && $percent < 85){
			$percentClass = 'percent_65-84';
		}elseif($percent >= 85 && $percent < 100){
			$percentClass = 'percent_85-99';
		}elseif($percent >= 100){
			$percentClass = 'percent_100';
		}
		
		return $percentClass;
	}
	
	/**
	 * Only allow students.
	 * @param integer $widgetId ID of the widget data entry, used to pull back the user assigned to that widget instance.
	 * @return boolean True if the user can view this widget, false if it shouldn't show up.
	 */
	public static function userCanUseWidget($widgetId){
		$widgetData = \Fisdap\EntityUtils::getEntity('MyFisdapWidgetData', $widgetId);
		
		$user = $widgetData->user;
		
		// This widget should always show for students, and should show for instructors on the lab skills page.
		if($widgetData->section == 'lab-skills-widgets'){
			// Always show on this page.
			return true;
		}elseif(!$user->isInstructor()){
			// Check to see if the student has and LPI entries to determine if this widget should show on the dash...
			$practiceItemRepo = \Fisdap\EntityUtils::getRepository('PracticeItem');
			
			$studentData = $user->getCurrentRoleData();
			
			$lpis = $practiceItemRepo->findBy(array('student' => $studentData->id));
			
			// If there are no LPI's in existence for this user yet, don't show the widget.  Only show it if
			// there is at least one LPI instance in the DB for the student.
			if(count($lpis) > 0){
				return true;
			}else{
				return false;
			}
		}

		return false;
	}
}
