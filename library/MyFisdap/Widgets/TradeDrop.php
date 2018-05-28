<?php

class MyFisdap_Widgets_TradeDrop extends MyFisdap_Widgets_Base
{
	protected $registeredCallbacks = array('updateClassSection');

	public function render(){
		$this_instructor = \Fisdap\Entity\User::getLoggedInUser()->getCurrentRoleData();

		//$trades = \Fisdap\EntityUtils::getRepository('ShiftLegacy')->getPendingTradeDrops($this->data['classSection']);
		$trades = \Fisdap\EntityUtils::getRepository('ShiftLegacy')->getPendingTradeDrops();

		$html = '';

		//$html .= $this->renderSectionDropdown();

		$divId = $this->getNamespacedName('trade_drop_content_div');

		$hiddenListId = $this->getNamespacedName('trade_drop_items');

        if ($trades) {
            $tradeCount = $trades->rowCount();
        } else {
            $tradeCount = 0;
        }

		if($tradeCount > 0){
			$recordsSeen = 0;
			$displayCutoffCount = 6;

			$recordHtml = '';
			$hiddenHtml = '';

			foreach($trades as $trade){
			    $student_id = $trade['Student_id'];

			    if ($this_instructor->isRelevantStudent($student_id)) {
				$item = "
					<div>
						<a href='/oldfisdap/redirect?loc=index.html@target_pagename=scheduler/tradeApproval.html'>
							{$trade['StartDate']} @ {$trade['StartTime']}, {$trade['student_name']}
						</a>
					</div>
				";

				if($recordsSeen >= $displayCutoffCount){
					$hiddenHtml .= $item;
				} else {
					$recordHtml .= $item;
				}

				$recordsSeen++;
			    }
			}

			if ($recordsSeen == 0) {
			    $title = "No pending trade or drop requests";
			} else if ($recordsSeen == 1) {
			    $title = "$recordsSeen pending trade or drop request";
			} else {
			    $title = "$recordsSeen pending trade and drop requests";
			}

			$html = "
				<div id='$divId'>
					<div class='trade-drop-title'>$title</div>
			";
			$html .= $recordHtml;

			if ($hiddenHtml != '') {
				$toggleId = $this->getNamespacedName('toggle');

				$count = $recordsSeen - $displayCutoffCount;

				$html .= "<div><a href='#' id='$toggleId-1'>Click here to toggle $count more records</a></div>";

				$html .="	<script>
						$(function(){
							$('#{$toggleId}-1').click(function(event){
								event.preventDefault();
								$('#{$hiddenListId}').toggle();
								$('#{$toggleId}-1').toggle();

							});
							$('#{$toggleId}-2').click(function(event){
								event.preventDefault();
								$('#{$hiddenListId}').toggle();
								$('#{$toggleId}-1').toggle();

							});
						});
					</script>
				";


				$hiddenHtml .= "<div><a href='#' id='$toggleId-2'>Hide extra items</a></div>";

				$html .= "<div class='trade-drop-items' id='$hiddenListId' style='display: none;'>" . $hiddenHtml . "</div>";
			}



			$html .= "
				</div>
			";
		}else{
			$html .= "<div class='trade-drop-title' id='$divId'>No pending trade or drop requests</div>";
		}

		return $html;
	}

	public function renderSectionDropdown(){
		$divId = $this->getNamespacedName('trade_drop_content_div');

		$selectId = $this->getNamespacedName('class_section_select');

		$html = "<select id='$selectId'>";

		$programId = $this->getWidgetProgram()->id;

		$sections = \Fisdap\EntityUtils::getRepository('ClassSectionLegacy')->getNamesByProgram($programId);

		$sections[0] = '2012 - My really really really really really really long section name';

		foreach($sections as $id => $name){
			// Strip off the first 4 digits of the name- this should be the year.
			$year = substr($name, 0, 4);

			// Don't show sections that are older than 2 years old
			if($year >= (date('Y')-2)){
				$shortName = $name;

				if(strlen($name) > 30){
					$shortName = substr($name, 0, 30) . "...";
				}

				$selectedText = ($id == $this->data['classSection']?"SELECTED='SELECTED'":"");
				$html .= "<option value='$id' $selectedText>$shortName</option>";
			}
		}

		$html .= "
			</select>
			<br /><br />
		";

		$html .= "<script>
			$('#{$selectId}').change(function(){
				data = {classSection: $('#{$selectId}').val()};

				callback = function(retData){
					reloadWidget({$this->widgetData->id});
				}

				$('#{$divId}').replaceWith('<img src=\"/images/throbber_small.gif\" />');

				routeAjaxRequest({$this->widgetData->id}, 'updateClassSection', data, callback);
			});
		</script>";

		return $html;
	}

	public function updateClassSection($data){
		$this->data['classSection'] = $data['classSection'];

		$this->saveData();

		return true;
	}

	public function getDefaultData(){
		return array("classSection" => null);
	}

	public static function userCanUseWidget($widgetId){
		$user = \Fisdap\EntityUtils::getEntity('MyFisdapWidgetData', $widgetId)->user;

		//This widget is only for legacy scheduler
		if ($user->getCurrentProgram()->scheduler_beta) {
			return false;
		}

		// User must be an instructor that has the "Edit Scheduler" permission...
		if($user->isInstructor() && ($user->hasPermission('Edit Field Schedules') || $user->hasPermission('Edit Clinic Schedules') || $user->hasPermission('Edit Lab Schedules'))){
			return true;
		}

		return false;
	}
}
