<?php

class MyFisdap_Widgets_StudentSearch extends MyFisdap_Widgets_Base
{
	protected $registeredCallbacks = array('findStudents');
	
	public function render(){
		$searchFieldId = $this->getNamespacedName('searchField');
		$searchButtonId = $this->getNamespacedName('searchButton');
		$searchResultsId = $this->getNamespacedName('searchResults');
		$spinnerId = $this->getNamespacedName('searchSpinner');
		$clearContainerName = 'clearContainer_' . $this->widgetData->id;
		$searchHandlerName = 'searchHandler_' . $this->widgetData->id;
		
		$initFieldName = 'initField_' . $this->widgetData->id;
		$defaultViewName = 'defaultView_' . $this->widgetData->id;
		$hiddenDivId = $this->getNamespacedName('hiddenResults');
		$toggleHiddenName = 'toggleHidden_' . $this->widgetData->id;
		
		$html = <<<EOF
			<div class='student-search-widget'>
				<div class='grid_10'>
					<input type='text' id='{$searchFieldId}' value='First name, last name, or email'/>
				</div>
				<div class='grid_2'>
					<button id='{$searchButtonId}'>Go</button>
					<img src='/images/throbber_small.gif' id='{$spinnerId}'/>
				</div>
				<span class='clear'></span>
				
				<div id='$searchResultsId' class='results-container'>
					
				</div>
			</div>
			
			<script>
				var {$defaultViewName} = false;
				
				function {$clearContainerName}(){
					$('#{$searchResultsId}').empty();
					$('#{$searchResultsId}').hide();
					{$initFieldName}();
				}
			
				function {$initFieldName}(){
					{$defaultViewName} = true;
					$('#{$searchFieldId}').css('color', '#757575').val('First name, last name, or email');
				}
				
				function {$toggleHiddenName}(){
					$('#{$hiddenDivId}').toggle();
				}
				
				$(function(){
					
					$('#{$searchFieldId}').click(function(){
						$(this).css('color', '#000000');
						if({$defaultViewName}){
							$(this).val('');
							{$defaultViewName} = false;
						}
					}).blur(function(){
						if($(this).val() == ''){
							{$initFieldName}();
						}
					});
					
					{$initFieldName}();
					
					$('#{$searchButtonId}').button().parent().addClass('green-buttons itty-bitty');
				});
				
				$(function(){
					$('#{$searchResultsId}').hide();
					$('#{$spinnerId}').hide();
					
					spinner = $("<img src='/images/throbber_small.gif' />");
					
					{$searchHandlerName} = function(){		
						if($('#{$searchFieldId}').val() != ''){
							$('#{$searchButtonId}').hide();
							$('#{$spinnerId}').show();
						
							data = {
								criteria: $('#{$searchFieldId}').val()
							};
							
							routeAjaxRequest({$this->widgetData->id}, 'findStudents', data, function(resp){
								$('#{$searchButtonId}').show();
								$('#{$spinnerId}').hide();
								
								container = $('#{$searchResultsId}');
								container.empty();
								container.show();
								
								if(resp['student_data'].length > 0){
									seenRecords = 0;
									addedExtra = false;
									
									$(resp['student_data']).each(function(index, value){
										seenRecords++;
										
										newParent = $("<div class='grid_12 student-record'></div>");
										leftDiv = $("<div class='grid_8'><span>" + value['name'] + "</span><br /><span>(" + value['cert'] + ", " + value['grad_date'] + ")</span></div>");
										rightDiv = $("<div class='grid_4'></div>");
										
										if(resp['permissions']['account']){
											rightDiv.append($("<div class='student-search-link'><a href='/account/edit/student/studentId/" + value['id'] + "'>Account</a></div>"));										}
										
										if(resp['permissions']['schedule']){
											rightDiv.append($("<div class='student-search-link'><a href='/skills-tracker/shifts/calendar'>Schedule</a></div>"));
										}
										
										if(resp['permissions']['skills']){
											rightDiv.append($("<div class='student-search-link'><a href='/skills-tracker/shifts/index/studentId/" + value['id'] + "'>Skills</a></div>"));
										}
										
										clearSpan = $("<span class='clear'></span>");
										
										newParent.append(leftDiv);
										newParent.append(rightDiv);
										newParent.append(clearSpan);
										
										// Determine which bucket to add it to...
										if(seenRecords > 5 && !addedExtra){
											addedExtra = true;
											container = $("<div id={$hiddenDivId}></div>").hide();
										}
										container.append(newParent);
										container.append(clearSpan.clone());
									});
									
									if(addedExtra){
										// Add in a "expand" link...
										container.append($("<a onclick='{$toggleHiddenName}(); return false'>Hide extra records</a>"));
										$('#{$searchResultsId}').append($("<a onclick='{$toggleHiddenName}(); return false'>Show/Hide " + resp['student_data'].length + " more records</a>"));
										$('#{$searchResultsId}').append(container);
									}
								} else {
									container.append($("<div class='no-results-message'>No results found matching your search.</div>"));
								}
								
								// Always add in a clear button to empty out the results.
								buttonContainer = $("<div class='clear-button-container'></div>");
								clearButton = $("<a href='#' onclick='{$clearContainerName}(); return false;'>Clear</a>");
								
								buttonContainer.append(clearButton);
								
								container.append(buttonContainer);
							});
						}
					};
					
					$('#{$searchButtonId}').click({$searchHandlerName});
					$('#{$searchFieldId}').keypress(function(e){
						if(e.which == 13){
							{$searchHandlerName}();
						}
					});
				});
			</script>
EOF;

		return $html;
	}
	
	public function findStudents($data){
		$user = $this->getWidgetUser();
		$students = \Fisdap\EntityUtils::getRepository('User')->findStudents($user->getProgramId(), $data['criteria']);
		
		$results = array(
			'student_data' => array(),
			'permissions' => array()
		);
		
		$results['permissions']['account'] = $user->hasPermission('Edit Student Accounts');
		$results['permissions']['schedule'] = $user->hasPermission('View Schedules');
		$results['permissions']['skills'] = $user->hasPermission('View All Data');
		
		foreach($students as $student){
			$atom = array();
			
			$atom['name'] = $student->first_name . ' ' . $student->last_name;
			$atom['cert'] = $student->getCertification()->description;
			
			$atom['grad_date'] = $student->getGraduationDate()->format('m Y');
			$atom['id'] = $student->id;
			
			$results['student_data'][] = $atom;
		}
		
		return $results;
	}
	
	public function getDefaultData(){
		return array();
	}
	
	public static function userCanUseWidget($widgetId){
		// Only instructors can view this widget if they have at least one of the following perms:
		// Edit Student Accounts
		// View All Data
		// View Schedules
		$user = \Fisdap\EntityUtils::getEntity('MyFisdapWidgetData', $widgetId)->user;
		
		if($user->isInstructor()){
			return $user->hasPermission('Edit Student Accounts') && $user->hasPermission('View All Data') && $user->hasPermission('View Schedules');
		}
		
		return false;
	}
}