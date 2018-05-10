var filterHasInitialized = false;

function initGroupEditPicklist() {
	// we don't need the hybrid single student selector
	$(".picklist-ms-select").remove();

	// SET UP FILTERS
	// We only want this to happen once, so that the filters don't get all doubled up.
	if(!filterHasInitialized) {
		$("#picklist-fancy-filter").fancyFilter({
			width: 663,
			closeOnChange: false,
			onFilterSubmit: function(e){
				data = getGroupEditFilterData();
				return $.post("/account/group/get-filtered-students",
					data,
					function(response){
						getUsersResponse(response, false);
					}, "json").done();
			}
		});

		initPicklistButtons();

		filterHasInitialized = true;
	}

	updateFilterHeader();

	// get the students to start it off
	$(".ms-picklist-wrapper").css("opacity", "0.5").addClass('container_12');
	$(".picklist-ms-picker").append("<img id='first-ms-picker-load' src='/images/throbber_small.gif'>");
	data = getGroupEditFilterData();
	$.post("/account/group/get-filtered-students",
		data,
		function(response){
			getUsersResponse(response, true);
			$(".ms-picklist-wrapper").css("opacity", "1");
			$("#first-ms-picker-load").remove();
		}, "json");

	$("#section_chzn").find(".chzn-drop").css("width", "198px");
	$("#graduationMonth_chzn").find(".chzn-drop").css("width", "83px");
	$("#graduationYear_chzn").find(".chzn-drop").css("width", "83px");

	$("#section_chzn").find(".chzn-search").find("input").css("width", "163px");
	$("#graduationMonth_chzn").find(".chzn-search").find("input").css("width", "48px");
	$("#graduationYear_chzn").find(".chzn-search").find("input").css("width", "48px");

	$("#graduationYear_chzn").find(".chzn-results").css("max-height", "145px");
	$("#graduationMonth_chzn").find(".chzn-results").css("max-height", "145px");
	$("#section_chzn").find(".chzn-results").css("max-height", "112px");

	// SET UP LISTS
	initStudentLists();
	initSearch();
	initControlButtons();

	// if this is a mobile device, add special styling
	if (isWebkitMobile()) {
		$(".picklist-ms-picker").addClass("mobile-multiselect");
		$(".mobile-multiselect select[multiple]").css({"height": "auto", "padding": "0.2em"});
		$(".mobile-multiselect .picklist-control-buttons").css({"padding-top": 0});
	}

}

function getUsersResponse(response, updateChosen) {
	$('#available-list').empty();
	
	// we only want to update the chosen list on initialization
	if (updateChosen) {
		$('#chosen-list').empty();
		
		// add assigned users to the chosen list
		$.each(response['assigned'], function(val, label){
			$(".chosen-list").append(createOption(val, label));
		});
		
		// this variable will be used by the filterGroupEditList function to correctly
		// populate the available options
		assigned_students = {};
		assigned_students = response['assigned'];

	} else {
		// if we're just filtering the available list, use the current status of the chosen list
		assigned_students = {};
		$('.chosen-list').find("option").each(function(){
			assigned_students[$(this).val()] = $(this).text();
		});
	}
	
	students = {};
	students = response['assignable'];

	filterGroupEditList($(".available-list"), $(".search-list").val());
	updateFilterHeader();
	updateAssignedCount();
}


// Get an array of information about the filter to send to the ajax request, and update the count text
function getGroupEditFilterData(){
	var data = getFilterData();
	data['group_id'] = $('#hidden-group-id').val();
	data['assignment_type'] = $('#hidden-assignment-type').val();

	type = $('#hidden-assignment-type').val();
	
	selectedList = undefined;
	
	switch(type){
		case 'students':
			selectedList = $('#group_students');
            $("#modal_student_list .count-text").html("Total students selected: <span id='current_assigned_count'></span>");
			break;
		case 'tas':
			selectedList = $('#group_tas');
            $("#modal_student_list .count-text").html("Total teaching assistants selected: <span id='current_assigned_count'></span>");
			break;
		case 'instructors':
			selectedList = $('#group_instructors');
            $("#modal_student_list .count-text").html("Total instructors selected: <span id='current_assigned_count'></span>");
			break;
	}

	data['chosen_users'] = [];

	// Get the currently selected set of people and send that list up as well...
	selectedList.find('option').each(function (i, e){
		if($(e).val() > 0){
			data['chosen_users'].push($(e).val());
		}
	});

	return data;
}

function filterGroupEditList(list, searchTerm) {
	list.find("option").each(function(){$(this).remove();});

	//IE hack because it doesn't like null values for strings
	if (searchTerm == null) {
		searchTerm = "";
	}
	$.each(students, function(index, value) {
		var testedValue = value.toLowerCase();
		var testedSearchTerm = searchTerm.toLowerCase();
		if (testedValue.indexOf(testedSearchTerm) != -1){
			// add it
			newOpt = $(createOption(index, value));
			
			// If the student is in the assigned list, disable the most recently created option...
			if(assigned_students[index] !== undefined){
				disableOption(newOpt);
			}
			
			list.append(newOpt);

		}
	});
	
	// Only do the sort once- moved out of the above $.each().
	list.find("option").sort(sortList).appendTo(list);
}